-- Migration pour mettre à jour la table message avec les bons noms de colonnes
-- À exécuter dans phpMyAdmin ou via MySQL CLI

USE ia_educative;

-- Vérifier d'abord si les colonnes existent avec les anciens noms
-- Si oui, les renommer. Si non, elles ont déjà les bons noms.

-- Renommer role_message en emetteur (si elle existe)
ALTER TABLE message CHANGE COLUMN role_message emetteur VARCHAR(50) NOT NULL;

-- Renommer contenu en contenu_message (si elle existe)
-- Note: Si contenu_message existe déjà, cette ligne causera une erreur - c'est normal
ALTER TABLE message CHANGE COLUMN contenu contenu_message VARCHAR(50) NOT NULL;

-- Renommer date_envoi en date_heure_message (si elle existe)
ALTER TABLE message CHANGE COLUMN date_envoi date_heure_message DATETIME NOT NULL;

-- Optionnel: Augmenter la taille de contenu_message pour permettre des messages plus longs
ALTER TABLE message MODIFY COLUMN contenu_message TEXT NOT NULL;

SELECT 'Migration terminée avec succès!' AS status;
