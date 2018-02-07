--Add a collaborator
INSERT INTO Contact      VALUES ('Jean', 'Dupont', 'jean.dupont@email.com');
INSERT INTO EndUser      VALUES ('jean.dupont@email.com', '$2y$10$ZRffHRCZxBuD545YelpwS.bTFhxFogn7yxfIMuBhBIrZUcXorVKl2', TRUE);
INSERT INTO Collaborator VALUES ('jean.dupont@email.com'); 

--Add a Project Manager
INSERT INTO Contact        VALUES ('Stacy', 'Gromat', 'stacy.gromat@email.com');
INSERT INTO EndUser        VALUES ('stacy.gromat@email.com', '$2y$10$ZRffHRCZxBuD545YelpwS.bTFhxFogn7yxfIMuBhBIrZUcXorVKl2', TRUE);
INSERT INTO ProjectManager VALUES ('stacy.gromat@email.com');

--Add a Client
INSERT INTO Client VALUES ('contact@woodcorp.com', 'Woodcorp');
INSERT INTO Contact VALUES ('Legro', 'Karine', 'karine.legros@woodcorp.com');
INSERT INTO ClientContact VALUES ('karine.legros@woodcorp.com', 'contact@woodcorp.com', '0627202562');

--Add a Project
INSERT INTO Project(managerEmail, contactEmail, name, description, startDate, endDate, status) VALUES ('stacy.gromat@email.com', 'karine.legros@woodcorp.com', 'Logiciel d''inventaire', 'La société woodcorp a besoin qu''on fasse un logiciel d''inventaire pour pouvoir mieux gérer son inventaire', '2017-12-27', '2018-12-27', 'STARTED');
INSERT INTO ProjectCollaborator VALUES(1, 'jean.dupont@email.com');

--Add Tasks
INSERT INTO AbstractTask(idProject, name, description, startDate) VALUES (1, 'Définir le besoin du client', 'Définir le besoin avec le client', '2017-12-27');
INSERT INTO AbstractTask(idProject, name, description, startDate) VALUES (1, 'Création de la base de données', 'Création de la base de données', '2018-01-27');
INSERT INTO AbstractTask(idProject, name, description, startDate) VALUES (1, 'Création de l''interface', 'Création de l''interface', '2018-03-27');
INSERT INTO AbstractTask(idProject, name, description, startDate) VALUES (1, 'Page d''accueil', 'Page d''accueil', '2018-03-27');
INSERT INTO AbstractTask(idProject, name, description, startDate) VALUES (1, 'Page de consultation de l''inventaire', 'Page de consultaton de l''inventaire', '2018-04-27');
INSERT INTO AbstractTask(idProject, name, description, startDate) VALUES (1, 'Page de saisie des informations de la base de donnes', 'Page de saisie des informations de la base de données', '2018-05-27');
INSERT INTO Task VALUES (1, '2018-01-27', '35', '35', '15', '20', '57', 'jean.dupont@email.com');
INSERT INTO Task VALUES (2, '2018-03-27', '55', '55', '55', '0',  '0',  'jean.dupont@email.com');
INSERT INTO Task VALUES (3, '2018-04-27', '35', '35', '35', '0',  '0',  'jean.dupont@email.com');
INSERT INTO Task VALUES (4, '2018-04-27', '35', '35', '35', '0',  '0',  'jean.dupont@email.com');
INSERT INTO Task VALUES (5, '2018-05-27', '35', '35', '35', '0',  '0',  'jean.dupont@email.com');
INSERT INTO Task VALUES (6, '2018-06-27', '35', '35', '35', '0',  '0',  'jean.dupont@email.com');

--Add Tasks hierarchy and task order
INSERT INTO TaskHierarchy VALUES (3, 4, true);
INSERT INTO TaskHierarchy VALUES (3, 5, true);
INSERT INTO TaskHierarchy VALUES (3, 6, true);
INSERT INTO TaskOrder     VALUES (1, 2);
INSERT INTO TaskOrder     VALUES (2, 3);


--Add Notifications 
INSERT INTO notification VALUES (1, '2018-06-02','Test 1', 'lalala', false);
INSERT INTO notification VALUES (2, '2018-06-04','Test 2', 'lololo', true);
INSERT INTO notification VALUES (3, '2018-06-06','Test 3', 'lilili', true);

INSERT INTO sender VALUES (1, 'stacy.gromat@email.com', 'jean.dupont@email.com');
INSERT INTO sender VALUES (2, 'stacy.gromat@email.com', 'jean.dupont@email.com');
INSERT INTO sender VALUES (3, 'stacy.gromat@email.com', 'jean.dupont@email.com');
