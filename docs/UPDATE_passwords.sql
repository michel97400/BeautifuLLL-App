-- Script pour mettre à jour les mots de passe avec des hash bcrypt corrects
-- À exécuter dans phpMyAdmin ou MySQL

-- Mise à jour de l'administrateur Pierre Dubois
-- Email: pierre.dubois@test.com
-- Mot de passe: admin123
UPDATE Etudiants
SET passwordhash = '$2y$10$DqD7jF3j0qD/XUvR4OvEyu9ArOXlklVq81UP6AUoVXfyV2Oa136eS'
WHERE email = 'pierre.dubois@test.com';

-- Mise à jour de l'étudiant Jean Dupont
-- Email: jean.dupont@test.com
-- Mot de passe: etudiant123
UPDATE Etudiants
SET passwordhash = '$2y$10$D7UEhK7dt4EPUwJPronBiOd0BCMbw9IS39KffI5RKT8JGGd.y/p0O'
WHERE email = 'jean.dupont@test.com';

-- Mise à jour de l'étudiante Marie Martin
-- Email: marie.martin@test.com
-- Mot de passe: etudiant123
UPDATE Etudiants
SET passwordhash = '$2y$10$D7UEhK7dt4EPUwJPronBiOd0BCMbw9IS39KffI5RKT8JGGd.y/p0O'
WHERE email = 'marie.martin@test.com';

-- Vérification (optionnel)
SELECT id_etudiant, nom, prenom, email, LEFT(passwordhash, 20) as hash_debut, id_role
FROM Etudiants
ORDER BY id_role;
