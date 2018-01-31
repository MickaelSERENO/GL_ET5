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
	name    VARCHAR(64) NOT NULL,
	surname VARCHAR(64) NOT NULL,
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
	name        VARCHAR(64) NOT NULL,
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
	message TEXT    NOT NULL,
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
	description  TEXT NOT NULL,
	startDate    DATE NOT NULL,
	endDate      DATE NOT NULL,
	status       PROJECT_STATUS NOT NULL,
	CHECK (startDate < endDate),
	FOREIGN KEY(managerEmail) REFERENCES ProjectManager(userEmail),
	FOREIGN KEY(contactEmail) REFERENCES ClientContact(contactEmail)
);

CREATE TABLE ProjectCollaborator
(
	idProject         INTEGER,
	collaboratorEmail VARCHAR(128) NOT NULL,
	FOREIGN KEY(idProject)         REFERENCES Project(id),
	FOREIGN KEY(collaboratorEmail) REFERENCES EndUser(contactEmail),
	PRIMARY KEY(idProject, collaboratorEmail)
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
CREATE TYPE TASK_STATUS AS ENUM('STARTED', 'NOT_STARTED', 'LATE');

CREATE TABLE AbstractTask
(
	id          SERIAL       PRIMARY KEY,
	idProject   INTEGER      NOT NULL,
	name        VARCHAR(128) NOT NULL,
	description TEXT         NOT NULL,
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
	endDate           DATE    NOT NULL,
	initCharge        INTEGER NOT NULL,
	computedCharge    INTEGER NOT NULL,
	remaining         INTEGER NOT NULL,
	chargeConsumed    INTEGER NOT NULL,
	advancement       INTEGER NOT NULL,
	collaboratorEmail VARCHAR(128),
	status            TASK_STATUS DEFAULT 'NOT_STARTED',
	CHECK(advancement >= 0 AND advancement <= 100),
	CHECK(chargeConsumed = computedCharge - remaining),
	CHECK(chargeConsumed >= 0 AND computedCharge >= 0 AND remaining >= 0 AND initCharge >= 0),
	CHECK(advancement * computedCharge <= 100*chargeConsumed AND (advancement+1)*computedCharge >= 100*chargeConsumed),
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
	idMother INTEGER NOT NULL,
	idChild  INTEGER NOT NULL,
	counted  BOOLEAN NOT NULL,
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
		--Check if they have the same project
		IF (SELECT COUNT(*) FROM AbstractTask AS T1, AbstractTask AS T2 WHERE T1.id = New.idMother AND T2.id = New.idChild AND T1.idProject = T2.idProject AND T2.startDate >= T1.startDate) = 0 THEN
			RAISE EXCEPTION 'The two task are not part of the same project or the start date are not compatible';
		END IF;
		lastID := NEW.idMother;
		i      := 0;
		WHILE i < 2 
		LOOP	
			SELECT * INTO lastRow FROM TaskHierarchy WHERE idChild = lastID;
			IF (SELECT COUNT(*) FROM TaskHierarchy WHERE idChild = lastID) = 0 THEN
				RETURN New;
			ELSE
				lastID := lastRow.idMother;
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
		  	   WHERE AbstractTask.id = New.id AND AbstractTask.idProject = ProjectCollaborator.idProject AND
		       New.collaboratorEmail = ProjectCollaborator.collaboratorEmail) = 0 THEN
			RAISE EXCEPTION 'The collaborator is not part of the project collaborator list';	
		ELSIF (SELECT COUNT(*) FROM Project, AbstractTask WHERE AbstractTask.id = New.id AND AbstractTask.idProject = Project.id AND AbstractTask.startDate >= Project.startDate AND New.endDate <= Project.endDate) = 0 THEN
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

CREATE OR REPLACE FUNCTION afterTaskUpdate() RETURNS TRIGGER AS $$
	BEGIN
		EXECUTE updateProjectDate((SELECT idProject FROM AbstractTask WHERE AbstractTask.id = New.id));
		RETURN New;
	END
	$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION afterTaskHierarchyUpdate() RETURNS TRIGGER AS $$
	BEGIN
		EXECUTE updateProjectDate((SELECT DISTINCT idProject FROM AbstractTask WHERE AbstractTask.id = New.idMother LIMIT 1));
		RETURN New;
	END
	$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION updateProjectDate(in _ID INTEGER) RETURNS VOID AS $$
	DECLARE
		r Task%ROWTYPE;
	BEGIN
		FOR r IN SELECT Task.* FROM AbstractTask, Task, Project 
				 WHERE AbstractTask.idProject = Project.id AND Task.ID = AbstractTask.ID AND Task.ID NOT IN (SELECT idChild FROM TaskHierarchy)
		LOOP
			PERFORM * FROM updateTaskDate(r);
		END LOOP;
	END
	$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION updateTaskDate(mother Task, out startDate DATE, out endDate DATE) AS $$
	DECLARE
		_endDate   Date;
		_startDate Date;
		r          int;
		subTask Task%ROWTYPE;
	BEGIN
		endDate    := mother.endDate;
		startDate  := (SELECT AbstractTask.startDate FROM AbstractTask WHERE mother.id = AbstractTask.id);

		FOR r IN SELECT idChild FROM TaskHierarchy WHERE idMother = mother.id
		LOOP
			SELECT * INTO subTask FROM Task WHERE id = r;
			SELECT TD.endDate, TD.startDate INTO _endDate, _startDate FROM updateTaskDate(subTask) AS TD;
			IF endDate < _endDate THEN
				RAISE NOTICE 'Update date FROM % to %', endDate, _endDate;
				endDate = _endDate;
			END IF;

			IF startDate > _startDate THEN
				startDate = _startDate;
			END IF;
		END LOOP;
		UPDATE Task SET endDate = updateTaskDate.endDate WHERE id = mother.id;
		UPDATE AbstractTask SET startDate = updateTaskDate.startDate WHERE id = mother.id;
	END
	$$ LANGUAGE plpgsql;
	
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

--Do some treatment after changing a project
CREATE TRIGGER triggerAfterTaskUpdate AFTER DELETE OR UPDATE OR INSERT
	ON Task
	FOR EACH ROW
		WHEN (pg_trigger_depth() = 0)
		EXECUTE PROCEDURE afterTaskUpdate();

CREATE TRIGGER triggerAfterAbstractTaskUpdate AFTER DELETE OR UPDATE OR INSERT
	ON AbstractTask
	FOR EACH ROW
		WHEN (pg_trigger_depth() = 0)
		EXECUTE PROCEDURE afterTaskUpdate();

CREATE TRIGGER triggerAfterTaskHierarchyUpdate AFTER DELETE OR UPDATE OR INSERT
	ON TaskHierarchy
	FOR EACH ROW
		WHEN (pg_trigger_depth() = 0)
		EXECUTE PROCEDURE afterTaskHierarchyUpdate();

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
