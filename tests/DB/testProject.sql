\i initDB.sql
\echo 'Test about projects'
\echo 'Insert a client and a contact client for woodcorp. Should not failed'

INSERT INTO Client VALUES ('contact@woodcorp.com', 'Woodcorp');
INSERT INTO Contact VALUES ('Legro', 'Karine', 'karine.legros@woodcorp.com');
INSERT INTO ClientContact VALUES ('karine.legros@woodcorp.com', 'contact@woodcorp.com', '0627202562');

\echo 'Insert a project. It should not failed'
INSERT INTO Project(managerEmail, contactEmail, name, description, startDate, endDate, status) VALUES ('administrator@email.com', 'karine.legros@woodcorp.com', 'The first project', 'This project is the first one', '2017-12-27', '2018-12-27', 'STARTED');


\echo 'Insert another project. Should fail because of the date (end < begin)'
INSERT INTO Project(managerEmail, contactEmail, name, description, startDate, endDate, status) VALUES ('administrator@email.com', 'karine.legros@woodcorp.com', 'The first project', 'This project is the first one', '2018-12-27', '2017-12-27', 'STARTED');

\echo 'This should fail. Make the manager of this project into not active'
UPDATE EndUser SET isActive = False WHERE contactEmail = 'administrator@email.com';

\echo 'Insert another project manager'
INSERT INTO Contact VALUES ('Sereno', 'Mickael', 'serenomickael@gmail.com');
INSERT INTO EndUser VALUES ('serenomickael@gmail.com', '$2y$10$ZRffHRCZxBuD545YelpwS.bTFhxFogn7yxfIMuBhBIrZUcXorVKl2', FALSE);
INSERT INTO ProjectManager(userEmail) VALUES ('serenomickael@gmail.com');

\echo 'Cannot make this project manager also a collaborator'
INSERT INTO Collaborator(userEmail) VALUES ('serenomickael@gmail.com');

\echo 'Try to put him into a project. Should fail because the user is not active'
INSERT INTO Project(managerEmail, contactEmail, name, description, startDate, endDate, status) VALUES ('serenomickael@gmail.com', 'karine.legros@woodcorp.com', 'The second project', 'This project is the second one', '2017-12-27', '2018-12-27', 'STARTED');

\echo 'Remove it has project manager and make him a collaborator'
DELETE FROM ProjectManager WHERE userEmail = 'serenomickael@gmail.com';
INSERT INTO Collaborator(userEmail) VALUES ('serenomickael@gmail.com');

\echo 'Make him participate to the project created above'
INSERT INTO ProjectCollaborator VALUES (1, 'serenomickael@gmail.com');

\echo 'This should fail. Make the collaborator not active anymore but he has still a project'
UPDATE EndUser SET isActive = False WHERE contactEmail = 'serenomickael@gmail.com';

