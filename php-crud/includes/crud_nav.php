<?php
/**
 * Navigation secondaire pour les pages CRUD
 * Affiche une barre de navigation horizontale pour naviguer entre les différentes sections CRUD
 */

// Déterminer la page active
$current_action = $_GET['action'] ?? '';
?>

<nav class="crud-nav">
    <a href="index.php" class="crud-nav-item <?= !isset($_GET['action']) || $_GET['action'] === '' ? 'active' : '' ?>">
        <span class="nav-icon">🏠</span>
        Tableau de bord
    </a>

    <a href="index.php?action=etudiant_list" class="crud-nav-item <?= $current_action === 'etudiant_list' ? 'active' : '' ?>">
        <span class="nav-icon">📋</span>
        Liste des étudiants
    </a>

    <a href="index.php?action=creer_etudiant" class="crud-nav-item <?= $current_action === 'creer_etudiant' || $current_action === 'modifier_etudiant' ? 'active' : '' ?>">
        <span class="nav-icon">➕</span>
        Créer un étudiant
    </a>

    <!-- Prêt pour extension : autres entités CRUD -->
    <!--
    <a href="index.php?action=role_list" class="crud-nav-item <?= $current_action === 'role_list' ? 'active' : '' ?>">
        <span class="nav-icon">👥</span>
        Rôles
    </a>

    <a href="index.php?action=niveau_list" class="crud-nav-item <?= $current_action === 'niveau_list' ? 'active' : '' ?>">
        <span class="nav-icon">📊</span>
        Niveaux
    </a>
    -->
</nav>
