

CREATE TABLE Role(
   id_role INT AUTO_INCREMENT,
   nom_role VARCHAR(50) NOT NULL UNIQUE,
   PRIMARY KEY(id_role)
);

CREATE TABLE Matieres(
   id_matieres INT AUTO_INCREMENT,
   nom_matieres VARCHAR(50) NOT NULL,
   description_matiere TEXT,
   PRIMARY KEY(id_matieres)
);

CREATE TABLE Niveau(
   id_niveau INT AUTO_INCREMENT,
   libelle_niveau VARCHAR(50) NOT NULL,
   PRIMARY KEY(id_niveau)
);

CREATE TABLE Etudiants(
   id_users INT AUTO_INCREMENT,
   nom VARCHAR(50) NOT NULL,
   prenom VARCHAR(50) NOT NULL,
   email VARCHAR(100) NOT NULL,
   avatar VARCHAR(255),
   passwordhash VARCHAR(255) NOT NULL,
   date_inscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
   consentement_rgpd BOOLEAN NOT NULL DEFAULT FALSE,
   id_niveau INT NOT NULL,
   id_role INT NOT NULL,
   PRIMARY KEY(id_users),
   UNIQUE(email),
   FOREIGN KEY(id_niveau) REFERENCES Niveau(id_niveau),
   FOREIGN KEY(id_role) REFERENCES Role(id_role)
);

CREATE TABLE Agent(
   id_agents INT AUTO_INCREMENT,
   nom_agent VARCHAR(50) NOT NULL,
   type_agent VARCHAR(50) NOT NULL,
   avatar_agent VARCHAR(255),
   est_actif BOOLEAN DEFAULT TRUE,
   description TEXT,
   date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
   prompt_systeme TEXT,
   id_matieres INT,
   id_users INT NOT NULL,
   PRIMARY KEY(id_agents),
   UNIQUE(nom_agent),
   FOREIGN KEY(id_matieres) REFERENCES Matieres(id_matieres),
   FOREIGN KEY(id_Etudiant) REFERENCES Etudiants(id_users)
);

CREATE TABLE Session_conversation(
   id_session INT AUTO_INCREMENT,
   date_heure_debut DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
   duree_session TIME,
   date_heure_fin DATETIME,
   id_agents INT NOT NULL,
   id_users INT NOT NULL,
   PRIMARY KEY(id_session),
   FOREIGN KEY(id_agents) REFERENCES Agent(id_agents),
   FOREIGN KEY(id_Etudiant) REFERENCES Etudiants(id_users)
);

CREATE TABLE Message(
   id_message INT AUTO_INCREMENT,
   role ENUM('user', 'assistant') NOT NULL,
   contenu TEXT VARCHAR(500) NOT NULL,
   date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP,
   id_session INT NOT NULL,
   PRIMARY KEY(id_message),
   FOREIGN KEY(id_session) REFERENCES Session_conversation(id_session)
);


INSERT INTO Role (nom_role) VALUES 
('Administrateur'),
('Etudiant');

INSERT INTO Niveau (libelle_niveau) VALUES ('6_eme'), ('5_eme'), ('4_eme'), ('3_eme'), ('Second'), ('Premiere'), ('Terminale');

INSERT INTO Matieres (nom_matieres) VALUES ('Français'), ('Anglais'), ('Mathématique'), ('Histoire-géo'), ('Biologie'), ('Phisique'), ('Chimie');
INSERT INTO Etudiants (nom, prenom, email, avatar, passwordhash, date_inscription, consentement_rgpd, id_role, id_niveau) VALUES ('Dupont', 'Jean', 'jean.dupont@test.com', 'avatar1.jpg', '$2y$10$hash1234567890', '2025-10-17 14:30:00', 1, 2, 1), ('Martin', 'Marie', 'marie.martin@test.com', 'avatar2.jpg', '$2y$10$hash0987654321', '2025-10-17 15:00:00', 1, 2, 2), ('Dubois', 'Pierre', 'pierre.dubois@test.com', 'avatar3.jpg', '$2y$10$hash1122334455', '2025-10-17 15:30:00', 1, 1, 1);
