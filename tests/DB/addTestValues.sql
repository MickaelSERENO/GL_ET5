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
INSERT INTO Contact VALUES ('Karine', 'Legro', 'karine.legros@woodcorp.com');
INSERT INTO ClientContact VALUES ('karine.legros@woodcorp.com', 'contact@woodcorp.com', '0627202562');

--Add a Project
INSERT INTO Project(managerEmail, contactEmail, name, description, startDate, endDate, status) VALUES ('stacy.gromat@email.com', 'karine.legros@woodcorp.com', 'Logiciel d''inventaire', 'Création d''un logiciel d''inventaire pour Woodcorp pour mieux gérer son matériel', '2017-12-27', '2018-12-27', 'STARTED');
INSERT INTO Project(managerEmail, contactEmail, name, description, startDate, endDate, status) VALUES ('stacy.gromat@email.com', 'karine.legros@woodcorp.com', 'Stick and Track', 'Création de stickers', '2018-02-20', '2018-02-27', 'STARTED');
INSERT INTO ProjectCollaborator VALUES(1, 'jean.dupont@email.com');

--Add Tasks
INSERT INTO AbstractTask(idProject, name, description, startDate) VALUES (1, 'Définir le besoin du client', 'Définir le besoin avec le client', '2017-12-27');
INSERT INTO AbstractTask(idProject, name, description, startDate) VALUES (1, 'Création de la base de données', 'Création de la base de données', '2018-01-27');
INSERT INTO AbstractTask(idProject, name, description, startDate) VALUES (1, 'Création de l''interface', 'Création de l''interface', '2018-03-27');
INSERT INTO AbstractTask(idProject, name, description, startDate) VALUES (1, 'Page d''accueil', 'Page d''accueil', '2018-03-27');
INSERT INTO AbstractTask(idProject, name, description, startDate) VALUES (1, 'Page de consultation de l''inventaire', 'Page de consultaton de l''inventaire', '2018-05-27');
INSERT INTO AbstractTask(idProject, name, description, startDate) VALUES (1, 'Page de saisie des informations de la base de donnes', 'Page de saisie des informations de la base de données', '2018-07-27');
INSERT INTO AbstractTask(idProject, name, description, startDate) VALUES (1, 'Livraison', 'Livraison du produit', '2018-09-28');

INSERT INTO Task   VALUES (1, '2018-01-27', '35', '35', '15', '20', '57', 'jean.dupont@email.com');
INSERT INTO Task   VALUES (2, '2018-03-27', '55', '55', '55', '0',  '0',  'jean.dupont@email.com');
INSERT INTO Task   VALUES (3, '2018-05-27', '35', '35', '35', '0',  '0',  'jean.dupont@email.com');
INSERT INTO Task   VALUES (4, '2018-05-27', '35', '35', '35', '0',  '0',  'jean.dupont@email.com');
INSERT INTO Task   VALUES (5, '2018-07-27', '35', '35', '35', '0',  '0',  'jean.dupont@email.com');
INSERT INTO Task   VALUES (6, '2018-09-27', '35', '35', '35', '0',  '0',  'jean.dupont@email.com');
INSERT INTO Marker VALUES (7);

--Add Tasks hierarchy and task order
INSERT INTO TaskHierarchy VALUES (3, 4, true);
INSERT INTO TaskHierarchy VALUES (3, 5, true);
INSERT INTO TaskHierarchy VALUES (3, 6, true);
INSERT INTO TaskOrder     VALUES (2, 3);

--Add Notifications 
INSERT INTO notification VALUES (1, '2018-06-02','Test 1', 'message du test 1', false);
INSERT INTO notification VALUES (2, '2018-06-04','Test 2', 'message du test 2', true);
INSERT INTO notification VALUES (3, '2018-06-06','Test 3', 'message du test 3', true);
INSERT INTO notification VALUES (4, '2018-02-25','Test 4', 'message du test 4', false);
INSERT INTO notification VALUES (5, '2018-02-26','Test 5', 'message du test 5', false);
INSERT INTO notification VALUES (6, '2018-06-11','Test 6', 'message du test 6', false);
INSERT INTO notification VALUES (7, '2018-06-12','Test 7', 'message du test 7', false);
INSERT INTO notification VALUES (8, '2018-06-13','Test 8', 'message du test 8', false);


INSERT INTO sender VALUES (1, 'stacy.gromat@email.com', 'jean.dupont@email.com');
INSERT INTO sender VALUES (2, 'stacy.gromat@email.com', 'jean.dupont@email.com');
INSERT INTO sender VALUES (3, 'stacy.gromat@email.com', 'jean.dupont@email.com');
INSERT INTO sender VALUES (4, 'jean.dupont@email.com','stacy.gromat@email.com');
INSERT INTO sender VALUES (5, 'jean.dupont@email.com','stacy.gromat@email.com');
INSERT INTO sender VALUES (6, 'stacy.gromat@email.com', 'jean.dupont@email.com');
INSERT INTO sender VALUES (7, 'stacy.gromat@email.com', 'jean.dupont@email.com');
INSERT INTO sender VALUES (8, 'stacy.gromat@email.com', 'jean.dupont@email.com');

--Add ProjectNotifications 
INSERT INTO ProjectNotification VALUES (1, 1);
INSERT INTO ProjectNotification VALUES (1, 2);
INSERT INTO ProjectNotification VALUES (1, 3);
INSERT INTO ProjectNotification VALUES (2, 4);
INSERT INTO ProjectNotification VALUES (2, 5);
INSERT INTO ProjectNotification VALUES (1, 6);
INSERT INTO ProjectNotification VALUES (1, 7);
INSERT INTO ProjectNotification VALUES (1, 8);

