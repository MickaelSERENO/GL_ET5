\echo 'Clear the database'

DROP SCHEMA public CASCADE;
CREATE SCHEMA public;

-----------
--Functions
-----------
\echo 'implements functions'

CREATE OR REPLACE FUNCTION checkTaskDate(in INTEGER, in DATE) RETURNS BOOLEAN AS $$
	BEGIN
		RETURN (SELECT COUNT(id) FROM AbstractTask WHERE id = $1 AND startDate < $2) > 0;
	END
	$$ LANGUAGE plpgsql;

\echo 'Implements tables'

--------------------------------------------------
--Contact tables (contact client, EndUser, Client)
--------------------------------------------------

CREATE TABLE Contact
(
	name    VARCHAR(64),
	surname VARCHAR(64),
	email   VARCHAR(128),
	PRIMARY KEY(email)
);

CREATE TABLE EndUser
(
	contactEmail VARCHAR(128) NOT NULL,
	password     VARCHAR(60) NOT NULL,
	isActive     BOOLEAN      NOT NULL,
	CHECK(character_length(password) = 60),
	FOREIGN KEY(contactEmail) REFERENCES Contact(email),
	PRIMARY KEY(contactEmail)
);

CREATE TABLE Client
(
	email       VARCHAR(128),
	name        VARCHAR(64),
	description TEXT,
	PRIMARY KEY(email)

);

CREATE TABLE ClientContact
(
	contactEmail VARCHAR(128) NOT NULL,
	clientEmail  VARCHAR(128) NOT NULL,
	telephone    VARCHAR(32),
	FOREIGN KEY(contactEmail) REFERENCES Contact(email),
	FOREIGN KEY(clientEmail)  REFERENCES Client(email),
	PRIMARY KEY(contactEmail)
);

-----------------------------------------------------------
--Role tables (Collaborator, ProjectManager, Administrator)
-----------------------------------------------------------
CREATE TABLE Collaborator
(
	userEmail VARCHAR(128) NOT NULL,
	FOREIGN KEY(userEmail) REFERENCES EndUser(contactEmail),
	PRIMARY KEY(userEmail)	
);

CREATE TABLE ProjectManager
(
	userEmail VARCHAR(128) NOT NULL,
	FOREIGN KEY(userEmail) REFERENCES EndUser(contactEmail),
	PRIMARY KEY(userEmail)	
);

CREATE TABLE Administrator
(
	userEmail VARCHAR(128) NOT NULL,
	FOREIGN KEY(userEmail) REFERENCES EndUser(contactEmail),
	PRIMARY KEY(userEmail)	
);

--------------------------------------------
--Notification tables (Notification, Sender)
--------------------------------------------
CREATE TABLE Notification
(
	id      SERIAL  PRIMARY KEY,
	theDate DATE    NOT NULL,
	title   TEXT    NOT NULL,
	message TEXT,
	read    BOOLEAN NOT NULL
);

CREATE TABLE Sender
(
	idNotification INTEGER,
	emailSender    VARCHAR(128),
	emailReceiver  VARCHAR(128) NOT NULL,
	FOREIGN KEY(idNotification) REFERENCES Notification(id),
	FOREIGN KEY(emailSender)    REFERENCES EndUser(contactEmail),
	FOREIGN KEY(emailReceiver)  REFERENCES EndUser(contactEmail),
	PRIMARY KEY(idNotification, emailSender, emailReceiver)
);

------------------------------------------------------------------------------------
--Project tables (PROJECT_STATUS, Project, ProjectCollaborator Project_Notification)
------------------------------------------------------------------------------------
CREATE TYPE PROJECT_STATUS AS ENUM('NOT_STARTED', 'STARTED', 'CLOSED_VISIBLE', 'CLOSED_INVISIBLE');

CREATE TABLE Project
(
	id           SERIAL       PRIMARY KEY,
	managerEmail VARCHAR(128) NOT NULL,
	contactEmail VARCHAR(128) NOT NULL,
	name         VARCHAR(128) NOT NULL,
	description  TEXT,
	startDate    DATE NOT NULL,
	endDate      DATE NOT NULL,
	status       PROJECT_STATUS NOT NULL,
	CHECK (startDate < endDate),
	FOREIGN KEY(managerEmail) REFERENCES ProjectManager(userEmail),
	FOREIGN KEY(contactEmail) REFERENCES ClientContact(contactEmail)
);

CREATE TABLE ProjectCollaborator
(
	projectID         INTEGER,
	collaboratorEmail VARCHAR(128) NOT NULL,
	FOREIGN KEY(projectID)         REFERENCES Project(id),
	FOREIGN KEY(collaboratorEmail) REFERENCES EndUser(contactEmail),
	PRIMARY KEY(projectID, collaboratorEmail)
);

CREATE TABLE ProjectNotification
(
	projectID      INTEGER,
	notificationID INTEGER,
	FOREIGN KEY(projectID)      REFERENCES Project(id),
	FOREIGN KEY(notificationID) REFERENCES Notification(id),
	PRIMARY KEY(projectID, notificationID)
);

---------------------------------------
--Task tables (AbstractTask, TaskOrder)
---------------------------------------
CREATE TABLE AbstractTask
(
	id          SERIAL       PRIMARY KEY,
	idProject   INTEGER      NOT NULL,
	name        VARCHAR(128) NOT NULL,
	description TEXT,
	startDate   DATE         NOT NULL,
	FOREIGN KEY(idProject) REFERENCES Project(id)
);

CREATE TABLE TaskOrder
(
	predecessorID INTEGER,
	successorID   INTEGER,
	CHECK(predecessorID != successorID),
	FOREIGN KEY(predecessorID) REFERENCES AbstractTask(id),
	FOREIGN KEY(successorID)   REFERENCES AbstractTask(id),
	PRIMARY KEY(predecessorID, successorID)
);

CREATE TABLE Task
(
	id                INTEGER,
	endDate           DATE,
	initCharge        INTEGER,
	computedCharge    INTEGER,
	remaining         INTEGER,
	chargeConsumed    INTEGER,
	advancement       INTEGER,
	collaboratorEmail VARCHAR(128),
	CHECK(advancement >= 0 AND advancement <= 100),
	CHECK(chargeConsumed = computedCharge - remaining),
	CHECK(chargeConsumed >= 0 AND computedCharge >= 0 AND remaining >= 0 AND initCharge >= 0),
	CHECK(checkTaskDate(id, endDate) = TRUE),
	FOREIGN KEY(id)                REFERENCES AbstractTask(id),
	FOREIGN KEY(collaboratorEmail) REFERENCES EndUser(contactEmail),
	PRIMARY KEY(id)
);

CREATE TABLE Marker
(
	id INTEGER,
	FOREIGN KEY(id) REFERENCES AbstractTask(id),
	PRIMARY KEY(id)
);

CREATE TABLE TaskHierarchy
(
	idMother INTEGER,
	idChild  INTEGER,
	counted  BOOLEAN,
	FOREIGN KEY(idMother) REFERENCES Task(id),
	FOREIGN KEY(idChild)  REFERENCES AbstractTask(id),
	Primary Key(idMother, idChild)
);

----------
--Triggers
----------

\echo 'Implements triggers'

--Trigger functions
CREATE OR REPLACE FUNCTION checkTaskHierarchy() RETURNS TRIGGER AS $triggerTaskHierarchy$
	DECLARE
		i int;
		lastID int;
		lastRow TaskHierarchy%ROWTYPE;
	BEGIN
		lastID := NEW.idMother;
		i      := 0;
		WHILE i < 2 
		LOOP	
			lastRow = (SELECT *  FROM TaskHierarchy WHERE idChild = $1);
			IF lastRow = NULL THEN
				RETURN FALSE;
			ELSE
				lastID = lastRow.idMother;
				IF lastRow.counted THEN
					i := i+1;
				END IF;
			END IF;
		END LOOP;
		RAISE EXCEPTION 'The level of the hierarchy is too high. Limit set to 3';
	END
	$triggerTaskHierarchy$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION checkEndUser() RETURNS TRIGGER AS $triggerEndUser$ 
	BEGIN
		IF (SELECT COUNT(id) FROM Project WHERE Project.managerEmail = NEW.contactEmail AND 
			(Project.status <> 'CLOSED_VISIBLE' AND Project.status <> 'CLOSED_INVISIBLE') AND 
			New.isActive = FALSE) <> 0 THEN
			RAISE EXCEPTION 'The project manager contains an unfinished project';
		ELSIF (SELECT COUNT(id) FROM Project, ProjectCollaborator WHERE Project.id = ProjectCollaborator.projectID AND 
			NEW.contactEmail = ProjectCollaborator.collaboratorEmail AND 
			(Project.status <> 'CLOSED_VISIBLE' AND Project.status <> 'CLOSED_INVISIBLE') AND 
			New.isActive = FALSE) <> 0 THEN
			RAISE EXCEPTION 'The collaborator contains an unfinished project';
		END IF;
		RETURN New;
	END
	$triggerEndUser$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION checkProjectManager() RETURNS TRIGGER AS $triggerProjectManager$ 
	BEGIN
		IF (SELECT COUNT(*) FROM Collaborator WHERE Collaborator.userEmail = New.userEmail) > 0 THEN
			RAISE EXCEPTION 'The ProjectManager has the same ID than a Collaborator';
		END IF;
		RETURN New;
	END
	$triggerProjectManager$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION checkCollaborator() RETURNS TRIGGER AS $triggerCollaborator$ 
	BEGIN
		IF (SELECT COUNT(*) FROM ProjectManager WHERE ProjectManager.userEmail = New.userEmail) > 0 THEN
			RAISE EXCEPTION 'The Collaborator has the same ID than a ProjectManager';
		END IF;
		RETURN New;
	END
	$triggerCollaborator$ LANGUAGE plpgsql;

--Check Project
CREATE OR REPLACE FUNCTION checkProject() RETURNS TRIGGER AS $triggerProject$ 
	BEGIN
		IF (SELECT COUNT(*) FROM EndUser WHERE contactEmail = New.managerEmail AND isActive = TRUE) = 0 THEN
			RAISE EXCEPTION 'The manager is not active';
		END IF;
		RETURN New;
	END
	$triggerProject$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION checkProjectAfterInsert() RETURNS TRIGGER AS $triggerProjectAfterInsert$ 
	BEGIN
		--Insert automatically the project manager into the list
		INSERT INTO ProjectCollaborator VALUES (New.id, New.managerEmail);
		RETURN New;
	END
	$triggerProjectAfterInsert$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION checkTask() RETURNS TRIGGER AS $triggerTask$ 
	BEGIN
		IF (SELECT COUNT(*) FROM Marker WHERE id = New.id) > 0 THEN
			RAISE EXCEPTION 'The Task has the same ID than a marker';
		ELSIF (SELECT COUNT(*) FROM ProjectCollaborator, AbstractTask 
		  	   WHERE AbstractTask.id = New.id AND idProject = ProjectCollaborator AND
		       New.collaboratorEmail = ProjectManager.collaboratorEmail) = 0 THEN
			RAISE EXCEPTION 'The collaborator is not part of the project collaborator list';	
		ELSIF (SELECT COUNT(*) FROM Project, AbstractTask WHERE AbstractTask.id = New.id AND AbstractTask.projectID = Project.ID AND AbstractTask.startDate >= Project.startDate AND New.endDate <= Project.endDate) = 0 THEN
			RAISE EXCEPTION 'The task is not within the project date';
		END IF;
		RETURN New;
	END
	$triggerTask$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION checkTaskOrder() RETURNS TRIGGER AS $triggerTaskOrder$ 
	BEGIN
		IF (SELECT COUNT(*) FROM AbstractTask AS T1, AbstractTask AS T2, Marker
		   	WHERE T1.id = New.predecessorID AND T2.id = New.successorID AND T1.idProject = T2.idProject AND
		    T1.startDate <= T2.startDate AND T1.id = Marker.id) = 0  AND
		   (SELECT COUNT(*) FROM AbstractTask AS T1, AbstractTask AS T2, Task
		   	WHERE T1.id = New.predecessorID AND T2.id = New.successorID AND T1.idProject = T2.idProject AND
			Task.endDate <= T2.startDate AND T1.id = Task.id) = 0 THEN
			RAISE EXCEPTION 'The tasks must be in the same project and the date must be correct';
		END IF;
		RETURN New;
	END
	$triggerTaskOrder$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION checkMarker() RETURNS TRIGGER AS $triggerMarker$ 
	BEGIN
		IF (SELECT COUNT(*) FROM Task WHERE id = New.id) > 0 THEN
			RAISE EXCEPTION 'The Marker has the same ID than a Task';
		END IF;
		RETURN New;
	END
	$triggerMarker$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION checkProjectCollaborator() RETURNS TRIGGER AS $triggerProjectCollaborator$ 
	BEGIN
		IF (SELECT COUNT(*) FROM EndUser, Collaborator WHERE contactEmail = New.collaboratorEmail AND 
			userEmail = New.collaboratorEmail AND isActive = TRUE) = 0  AND 
			(SELECT COUNT(*) FROM EndUser, ProjectManager WHERE contactEmail = New.collaboratorEmail AND
			userEmail = New.collaboratorEmail AND isActive = TRUE) = 0 THEN
			RAISE EXCEPTION 'The collaborator must be an active project manager or an active collaborator.';
		END IF;
		RETURN New;
	END
	$triggerProjectCollaborator$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION checkDeleteProjectCollaborator() RETURNS TRIGGER AS $triggerDeleteProjectCollaborator$ 
	BEGIN
		IF (SELECT COUNT(*) FROM Project WHERE managerEmail = New.collaboratorEmail AND Project.id = New.projectID) = 1 THEN 
			RAISE EXCEPTION 'Cannot delete the project manager from the list of the collaborator for this project';
		END IF;
		RETURN New;
	END
	$triggerDeleteProjectCollaborator$ LANGUAGE plpgsql;

--Check the status of an EndUser
CREATE TRIGGER triggerEndUser BEFORE UPDATE
	ON EndUser
	FOR EACH ROW
		EXECUTE PROCEDURE checkEndUser();


--Check the status of the project manager
CREATE TRIGGER triggerProjectManager BEFORE INSERT OR UPDATE
	ON ProjectManager
	FOR EACH ROW
		EXECUTE PROCEDURE checkProjectManager();


--Check the status of the collaborator
CREATE TRIGGER triggerCollaborator   BEFORE INSERT OR UPDATE
	ON Collaborator
	FOR EACH ROW
		EXECUTE PROCEDURE checkCollaborator();

--Check Project
CREATE TRIGGER triggerProject BEFORE INSERT OR UPDATE
	ON Project
	FOR EACH ROW
		EXECUTE PROCEDURE checkProject();

--Do some treatment after inserting a project
CREATE TRIGGER triggerProjectAfterInsert AFTER INSERT
	ON Project
	FOR EACH ROW
		EXECUTE PROCEDURE checkProjectAfterInsert();

--Check Tasks
CREATE TRIGGER triggerTask BEFORE UPDATE OR INSERT
	ON Task
	FOR EACH ROW
		EXECUTE PROCEDURE checkTask();

--Check Task order
CREATE TRIGGER triggerTaskOrder BEFORE UPDATE OR INSERT
	ON TaskOrder
	FOR EACH ROW
		EXECUTE PROCEDURE checkTaskOrder();

--Check Marker
CREATE TRIGGER triggerMarker BEFORE UPDATE OR INSERT
	ON Marker
	FOR EACH ROW
		EXECUTE PROCEDURE checkMarker();

--Check TaskHierarchy
CREATE TRIGGER triggerTaskHierarchy BEFORE UPDATE OR INSERT
	ON TaskHierarchy
	FOR EACH ROW
		EXECUTE PROCEDURE checkTaskHierarchy();

--Check ProjectCollaborator
CREATE TRIGGER triggerProjectCollaborator BEFORE UPDATE OR INSERT
	ON ProjectCollaborator
	FOR EACH ROW
		EXECUTE PROCEDURE checkProjectCollaborator();

--Check the deletion of the projet collaborator list
CREATE TRIGGER triggerDeleteProjectCollaborator BEFORE DELETE
	ON ProjectCollaborator
	FOR EACH ROW
		EXECUTE PROCEDURE checkDeleteProjectCollaborator();

\echo 'create an administrator. Password : password'

INSERT INTO Contact VALUES ('Anna', 'Demars', 'administrator@email.com');
INSERT INTO EndUser VALUES ('administrator@email.com', '$2y$10$ZRffHRCZxBuD545YelpwS.bTFhxFogn7yxfIMuBhBIrZUcXorVKl2', TRUE);
INSERT INTO Administrator(userEmail) VALUES ('administrator@email.com');

INSERT INTO ProjectManager(userEmail) VALUES ('administrator@email.com');
