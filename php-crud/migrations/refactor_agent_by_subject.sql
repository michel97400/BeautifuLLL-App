-- ================================================================
-- Migration: Refactorisation Agent par Matiere
-- Date: 2025-01-03
-- Description: Transformer agents possedes par etudiants en agents par matiere
-- IMPORTANT: Sauvegarder la base de donnees AVANT d'executer ce script
-- ================================================================

-- Utiliser la base de donnees
USE ia_educative;

START TRANSACTION;

-- ================================================================
-- ETAPE 1: SAUVEGARDE DES DONNEES EXISTANTES
-- ================================================================

-- Creer table de backup si elle n'existe pas
DROP TABLE IF EXISTS Agent_backup_20250103;
CREATE TABLE Agent_backup_20250103 AS SELECT * FROM Agent;

-- Afficher combien d'agents ont ete sauvegardes
SELECT CONCAT('Backup cree: ', COUNT(*), ' agents sauvegardes') AS info FROM Agent_backup_20250103;

-- ================================================================
-- ETAPE 2: SUPPRIMER LES ANCIENS AGENTS
-- ================================================================

-- Supprimer tous les agents existants (architecture incompatible)
-- ATTENTION: Les sessions et messages lies seront aussi supprimes en cascade
DELETE FROM Agent;
SELECT 'Tous les agents ont ete supprimes' AS info;

-- ================================================================
-- ETAPE 3: AJOUTER LES NOUVELLES COLONNES LLM
-- ================================================================

-- Ajouter colonne model
ALTER TABLE Agent
ADD COLUMN model VARCHAR(100) DEFAULT 'openai/gpt-oss-20b' AFTER prompt_systeme;

-- Ajouter colonne temperature
ALTER TABLE Agent
ADD COLUMN temperature DECIMAL(3,2) DEFAULT 0.70 AFTER model;

-- Ajouter colonne max_tokens
ALTER TABLE Agent
ADD COLUMN max_tokens INT DEFAULT 8192 AFTER temperature;

-- Ajouter colonne top_p
ALTER TABLE Agent
ADD COLUMN top_p DECIMAL(3,2) DEFAULT 1.00 AFTER max_tokens;

-- Ajouter colonne reasoning_effort
ALTER TABLE Agent
ADD COLUMN reasoning_effort ENUM('low', 'medium', 'high') DEFAULT 'medium' AFTER top_p;

SELECT 'Colonnes LLM ajoutees avec succes' AS info;

-- ================================================================
-- ETAPE 4: MODIFIER LA COLONNE id_matieres (OBLIGATOIRE)
-- ================================================================

-- Rendre id_matieres NOT NULL
ALTER TABLE Agent
MODIFY COLUMN id_matieres INT NOT NULL;

SELECT 'Colonne id_matieres rendue obligatoire' AS info;

-- ================================================================
-- ETAPE 5: AJOUTER CONTRAINTE UNIQUE SUR id_matieres
-- ================================================================

-- Ajouter contrainte unique (un seul agent par matiere)
ALTER TABLE Agent
ADD CONSTRAINT unique_agent_per_matiere UNIQUE(id_matieres);

SELECT 'Contrainte unique ajoutee sur id_matieres' AS info;

-- ================================================================
-- ETAPE 6: SUPPRIMER LA COLONNE id_etudiant
-- ================================================================

-- Recuperer le nom exact de la contrainte de cle etrangere
-- (Le nom peut varier selon la version MySQL)
SET @fk_name = (
    SELECT CONSTRAINT_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = 'ia_educative'
    AND TABLE_NAME = 'Agent'
    AND COLUMN_NAME = 'id_etudiant'
    AND REFERENCED_TABLE_NAME IS NOT NULL
    LIMIT 1
);

-- Supprimer la contrainte de cle etrangere si elle existe
SET @sql = IF(@fk_name IS NOT NULL,
    CONCAT('ALTER TABLE Agent DROP FOREIGN KEY ', @fk_name),
    'SELECT "Aucune contrainte FK a supprimer" AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Supprimer la colonne id_etudiant
ALTER TABLE Agent
DROP COLUMN id_etudiant;

SELECT 'Colonne id_etudiant supprimee avec succes' AS info;

-- ================================================================
-- ETAPE 7: MODIFIER prompt_systeme (OBLIGATOIRE)
-- ================================================================

-- Rendre prompt_systeme NOT NULL
ALTER TABLE Agent
MODIFY COLUMN prompt_systeme TEXT NOT NULL;

SELECT 'Colonne prompt_systeme rendue obligatoire' AS info;

-- ================================================================
-- VALIDATION FINALE
-- ================================================================

-- Afficher la nouvelle structure de la table
DESCRIBE Agent;

-- Afficher les contraintes
SELECT
    CONSTRAINT_NAME,
    CONSTRAINT_TYPE
FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
WHERE TABLE_SCHEMA = 'ia_educative' AND TABLE_NAME = 'Agent';

-- Compter les agents (devrait etre 0)
SELECT CONCAT('Nombre d agents apres migration: ', COUNT(*)) AS info FROM Agent;

COMMIT;

-- ================================================================
-- FIN DE LA MIGRATION
-- ================================================================
SELECT '========================================' AS '';
SELECT 'MIGRATION TERMINEE AVEC SUCCES' AS '';
SELECT '========================================' AS '';
SELECT 'Prochaine etape: Executer seed_default_agents.sql' AS '';
SELECT '========================================' AS '';
