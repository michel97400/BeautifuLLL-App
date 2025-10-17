<?php

require_once './config/Database.php';
require_once './model/etudiant.php';
require_once './model/role.php';

use Config\Database;
//use Models\User;
use Models\Role;

// Créer une instance de la classe User
//$user = new User();
$role = new Role();

$nomRole = 'Administrateur';
$result_role = $role->create($nomRole);

// Données de test
//$nom = 'Dupont';
//$prenom = 'Jean';
//$email = 'jean.dupont@example.com';
//$passwordhash = password_hash('motdepasse123', PASSWORD_DEFAULT);

// Création de l'utilisateur
//$result = $user->create(
    //null, // id_user
    //$nom,
    //$prenom,
    //$email,
    //'avatar_default.png', // avatar
    //$passwordhash,
    //date('Y-m-d H:i:s'), // date_inscription
    //1, // consentement_rgpd
    //2  // id_role
//);

// Message de confirmation
if ($result_role) {
    echo "✅ Role créé avec succès !";
} else {
    echo "❌ Échec de la création";
}

?>