CREATE TABLE Role(
   id_role VARCHAR(50),
   nom_role VARCHAR(50) NOT NULL,
   PRIMARY KEY(id_role)
);

CREATE TABLE matieres(
   id_matieres VARCHAR(50),
   nom_matieres VARCHAR(50) NOT NULL,
   description_matiere VARCHAR(50),
   PRIMARY KEY(id_matieres)
);

CREATE TABLE niveau(
   id_niveau VARCHAR(50),
   libellé_niveau VARCHAR(50),
   PRIMARY KEY(id_niveau)
);

CREATE TABLE etudiants(
   id_users VARCHAR(50),
   nom VARCHAR(50) NOT NULL,
   prénom VARCHAR(50) NOT NULL,
   email VARCHAR(50) NOT NULL,
   avatar VARCHAR(50),
   passwordhash VARCHAR(50) NOT NULL,
   date_inscription DATETIME NOT NULL,
   consentement_rgpd DATETIME NOT NULL,
   id_niveau VARCHAR(50) NOT NULL,
   id_role VARCHAR(50) NOT NULL,
   PRIMARY KEY(id_users),
   UNIQUE(email),
   FOREIGN KEY(id_niveau) REFERENCES niveau(id_niveau),
   FOREIGN KEY(id_role) REFERENCES Role(id_role)
);

CREATE TABLE agent(
   id_agents VARCHAR(50),
   nom_agent VARCHAR(50) NOT NULL,
   type_agent VARCHAR(50) NOT NULL,
   avatar_agent DATETIME,
   est_actif BOOLEAN,
   description VARCHAR(50),
   date_creation DATETIME,
   prompt_systeme VARCHAR(50),
   id_matieres VARCHAR(50),
   id_users VARCHAR(50) NOT NULL,
   PRIMARY KEY(id_agents),
   UNIQUE(nom_agent),
   FOREIGN KEY(id_matieres) REFERENCES matieres(id_matieres),
   FOREIGN KEY(id_users) REFERENCES etudiants(id_users)
);

CREATE TABLE SESSION_CONVERSATION(
   id_session VARCHAR(50),
   date_heure_debut DATETIME NOT NULL,
   duree_session TIME NOT NULL,
   date_heure_fin DATETIME NOT NULL,
   id_agents VARCHAR(50) NOT NULL,
   id_users VARCHAR(50) NOT NULL,
   PRIMARY KEY(id_session),
   FOREIGN KEY(id_agents) REFERENCES agent(id_agents),
   FOREIGN KEY(id_users) REFERENCES etudiants(id_users)
);

CREATE TABLE MESSAGE(
   id_message VARCHAR(50),
   role VARCHAR(50),
   contenu VARCHAR(50),
   date_envoi DATETIME,
   id_session VARCHAR(50) NOT NULL,
   PRIMARY KEY(id_message),
   FOREIGN KEY(id_session) REFERENCES SESSION_CONVERSATION(id_session)
);
