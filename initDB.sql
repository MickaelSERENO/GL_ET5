DROP SCHEMA public CASCADE;
CREATE SCHEMA public;

-----------
--Functions
-----------

CREATE OR REPLACE FUNCTION checkTaskDate(in INTEGER, in DATE) RETURNS BOOLEAN AS $$
	BEGIN
		RETURN (SELECT COUNT(id) FROM AbstractTask WHERE id = $1 AND startDate < $2) > 0;
	END
	$$ LANGUAGE plpgsql;

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
	password     VARCHAR(128) NOT NULL,
	isActive     BOOLEAN      NOT NULL,
	CHECK(character_length(password) = 128),
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
	id        INTEGER,
	userEmail VARCHAR(128) NOT NULL,
	FOREIGN KEY(userEmail) REFERENCES EndUser(contactEmail),
	PRIMARY KEY(id)	
);

CREATE TABLE ProjectManager
(
	id        INTEGER,
	userEmail VARCHAR(128) NOT NULL,
	FOREIGN KEY(userEmail) REFERENCES EndUser(contactEmail),
	PRIMARY KEY(id)	
);

CREATE TABLE Administrator
(
	id        INTEGER,
	userEmail VARCHAR(128) NOT NULL,
	FOREIGN KEY(userEmail) REFERENCES EndUser(contactEmail),
	PRIMARY KEY(id)	
);

--------------------------------------------
--Notification tables (Notification, Sender)
--------------------------------------------
CREATE TABLE Notification
(
	id      INTEGER,
	theDate DATE NOT NULL,
	title   TEXT NOT NULL,
	message TEXT,
	read    BOOLEAN NOT NULL,
	PRIMARY KEY(id)
);

CREATE TABLE Sender
(
	idNotification INTEGER,
	emailSender    VARCHAR(128),
	emailReceiver  VARCHAR(128),
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
	id           INTEGER,
	managerID    INTEGER      NOT NULL,
	contactEmail VARCHAR(128) NOT NULL,
	name         VARCHAR(128) NOT NULL,
	description  TEXT,
	startDate    DATE NOT NULL,
	endDate      DATE NOT NULL,
	status       PROJECT_STATUS NOT NULL,
	CHECK (startDate < endDate),
	FOREIGN KEY(managerID)    REFERENCES ProjectManager(id),
	FOREIGN KEY(contactEmail) REFERENCES ClientContact(contactEmail),
	PRIMARY KEY(id)
);

CREATE TABLE ProjectCollaborator
(
	projectID      INTEGER,
	collaboratorID INTEGER,
	FOREIGN KEY(projectID)      REFERENCES Project(id),
	FOREIGN KEY(collaboratorID) REFERENCES Collaborator(id),
	PRIMARY KEY(projectID, collaboratorID)
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
	id          INTEGER,
	idProject   INTEGER      NOT NULL,
	name        VARCHAR(128) NOT NULL,
	description TEXT,
	startDate   DATE         NOT NULL,
	FOREIGN KEY(idProject) REFERENCES Project(id),
	PRIMARY KEY(id)
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
	id             INTEGER,
	endDate        DATE,
	initCharge     INTEGER,
	computedCharge INTEGER,
	remaining      INTEGER,
	chargeConsumed INTEGER,
	advancement    INTEGER,
	CHECK(advancement >= 0 AND advancement <= 100),
	CHECK(chargeConsumed = computedCharge - remaining),
	CHECK(chargeConsumed >= 0 AND computedCharge >= 0 AND remaining >= 0 AND initCharge >= 0),
	CHECK(checkTaskDate(id, endDate) = TRUE),
	FOREIGN KEY(id) REFERENCES AbstractTask(id),
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

CREATE OR REPLACE FUNCTION checkProjectManager() RETURNS TRIGGER AS $triggerProjectManager$ 
	BEGIN
		IF (SELECT COUNT(id) FROM Project, EndUser WHERE Project.managerID = NEW.id AND (Project.status <> 'CLOSED_VISIBLE' AND Project.status <> 'CLOSED_INVISIBLE') AND 
			NEW.userEmail = EndUser.contactEmail AND EndUser.isActive = FALSE) > 0 THEN
			RAISE EXCEPTION 'The project manager contains an unfinished project';
		ELSIF (SELECT COUNT(*) FROM ProjectManager WHERE ProjectManager.id = New.id) > 0 THEN
			RAISE EXCEPTION 'The ProjectManager has the same ID than a Collaborator';
		END IF;
	END
	$triggerProjectManager$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION checkCollaborator() RETURNS TRIGGER AS $triggerCollaborator$ 
	BEGIN
		IF (SELECT COUNT(id) FROM Project, ProjectCollaborator, EndUser WHERE Project.id = ProjectCollaborator.projectID AND (Project.status <> 'CLOSED_VISIBLE' AND Project.status <> 'CLOSED_INVISIBLE') AND 
			ProjectCollaborator.collaboratorID = NEW.id AND NEW.userEmail = EndUser.contactEmail AND EndUser.isActive = FALSE) > 0 THEN
			RAISE EXCEPTION 'The collaborator contains an unfinished project';
		ELSIF (SELECT COUNT(*) FROM ProjectManager WHERE id = New.id) > 0 THEN
			RAISE EXCEPTION 'The Collaborator has the same ID than a ProjectManager';
		END IF;
	END
	$triggerCollaborator$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION checkTask() RETURNS TRIGGER AS $triggerTask$ 
	BEGIN
		IF (SELECT COUNT(*) FROM Marker WHERE id = New.id) > 0 THEN
			RAISE EXCEPTION 'The Task has the same ID than a marker';
		END IF;
	END
	$triggerTask$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION checkMarker() RETURNS TRIGGER AS $triggerMarker$ 
	BEGIN
		IF (SELECT COUNT(*) FROM Task WHERE id = New.id) > 0 THEN
			RAISE EXCEPTION 'The Marker has the same ID than a Task';
		END IF;
	END
	$triggerMarker$ LANGUAGE plpgsql;


--Check the status of the project manager
CREATE TRIGGER triggerProjectManager BEFORE UPDATE
	ON ProjectManager
	FOR EACH ROW
		EXECUTE PROCEDURE checkProjectManager();

--Check the status of the collaborator
CREATE TRIGGER triggerCollaborator   BEFORE UPDATE
	ON Collaborator
	FOR EACH ROW
		EXECUTE PROCEDURE checkCollaborator();

--Check Tasks
CREATE TRIGGER triggerTask BEFORE UPDATE OR INSERT
	ON Task
	FOR EACH ROW
		EXECUTE PROCEDURE checkTask();

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
