<?php
require_once './config/Database.php'; // ou './config/Database.php' selon l'emplacement

use Config\Database;

$db = new Database();
$connection = $db->connect();

if ($connection) {
    echo "✅ Connexion réussie à la base de données !";
} else {
    echo "❌ Échec de la connexion";
}
?>