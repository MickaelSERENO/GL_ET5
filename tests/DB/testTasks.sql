\i initDB.sql

\echo 'Test the Task'
\echo 'Need a project manager and a collaborator'

\echo 'Insert a project manager'
INSERT INTO Contact VALUES ('Gromat', 'Stacy', 'stacygromat@gmail.com');
INSERT INTO EndUser VALUES ('stacygromat@gmail.com', '$2y$10$ZRffHRCZxBuD545YelpwS.bTFhxFogn7yxfIMuBhBIrZUcXorVKl2', FALSE);
INSERT INTO ProjectManager(userEmail) VALUES ('stacygromat.com');

\echo 'Insert a collaborator'
INSERT INTO Contact VALUES ('Sereno', 'Mickael', 'serenomickael@gmail.com');
INSERT INTO EndUser VALUES ('serenomickael@gmail.com', '$2y$10$ZRffHRCZxBuD545YelpwS.bTFhxFogn7yxfIMuBhBIrZUcXorVKl2', FALSE);
INSERT INTO Collaborator(userEmail) VALUES ('serenomickael@gmail.com');

\echo 'Need a client'
INSERT INTO Client VALUES ('contact@woodcorp.com', 'Woodcorp');
INSERT INTO Contact VALUES ('Legro', 'Karine', 'karine.legros@woodcorp.com');
INSERT INTO ClientContact VALUES ('karine.legros@woodcorp.com', 'contact@woodcorp.com', '0627202562');

\echo 'Insert a project'
INSERT INTO Project(managerEmail, contactEmail, name, description, startDate, endDate, status) VALUES ('stacygromat@gmail.com', 'karine.legros@woodcorp.com', 'The first project', 'This project is the first one', '2017-12-27', '2018-12-27', 'STARTED');

\echo 'Insert into the project the collaborator sereno mickael'
INSERT INTO ProjectCollaborator(1, 'serenomickael@gmail.com');

\echo 'Insert two task'

INSERT INTO AbstractTask(idProject, name, description, startDate) VALUES ();

\echo 'Insert a End user

