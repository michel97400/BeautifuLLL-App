<?php
// Fichier de vérification des droits administrateur
// À inclure en haut des pages réservées aux admins

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    // Non connecté -> redirection vers la page de connexion
    header('Location: /BeautifuLLL-App/index.php?action=connect');
    exit;
}

// Vérifier si l'utilisateur a le rôle administrateur
if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'Administrateur') {
    // Connecté mais pas admin -> accès refusé
    header('Location: /BeautifuLLL-App/index.php?action=acces_refuse');
    exit;
}

// Si on arrive ici, l'utilisateur est admin et peut accéder à la page
?>
