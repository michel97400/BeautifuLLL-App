<?php

require_once './config/Database.php';
require_once './model/etudiant.php';


use Config\Database;

$db = new Database();
$connection = $db->connect();

use Models\agent;


// Créer une instance de la classe agent
$agent = new agent();



//Données de test
$nom = 'Dupont';
$prenom = 'Jean';
$email = 'jean.dupont@example.com';
$passwordhash = password_hash('motdepasse123', PASSWORD_DEFAULT);

//Création de l'utilisateur
$result = $agent->create(
    $nom,
    $prenom,
    $email,
    'avatar_default.png', // avatar
    $passwordhash,
    date('Y-m-d H:i:s'), // date_inscription
    1, // consentement_rgpd
    2  // id_role
);

// Message de confirmation
if ($result) {
    echo "✅ Role créé avec succès !";
} else {
    echo "❌ Échec de la création";
}

?>