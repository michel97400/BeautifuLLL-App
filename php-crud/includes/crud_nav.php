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

    <a href="index.php?action=etudiant_list" class="crud-nav-item <?= $current_action === 'etudiant_list' || $current_action === 'creer_etudiant' || $current_action === 'modifier_etudiant' ? 'active' : '' ?>">
        <span class="nav-icon">📋</span>
        Étudiants
    </a>

    <a href="index.php?action=matiere_list" class="crud-nav-item <?= $current_action === 'matiere_list' || $current_action === 'creer_matiere' || $current_action === 'modifier_matiere' ? 'active' : '' ?>">
        <span class="nav-icon">📚</span>
        Matières
    </a>

    <a href="index.php?action=niveau_list" class="crud-nav-item <?= $current_action === 'niveau_list' || $current_action === 'creer_niveau' || $current_action === 'modifier_niveau' ? 'active' : '' ?>">
        <span class="nav-icon">📊</span>
        Niveaux
    </a>

    <a href="index.php?action=role_list" class="crud-nav-item <?= $current_action === 'role_list' || $current_action === 'creer_role' || $current_action === 'modifier_role' ? 'active' : '' ?>">
        <span class="nav-icon">👥</span>
        Rôles
    </a>

    <a href="index.php?action=agent_list" class="crud-nav-item <?= $current_action === 'agent_list' || $current_action === 'creer_agent' || $current_action === 'modifier_agent' ? 'active' : '' ?>">
        <span class="nav-icon">🤖</span>
        Agents IA
    </a>

    <a href="index.php?action=session_list" class="crud-nav-item <?= $current_action === 'session_list' || $current_action === 'creer_session' || $current_action === 'modifier_session' ? 'active' : '' ?>">
        <span class="nav-icon">💬</span>
        Sessions
    </a>

    <a href="index.php?action=message_list" class="crud-nav-item <?= $current_action === 'message_list' || $current_action === 'creer_message' || $current_action === 'modifier_message' ? 'active' : '' ?>">
        <span class="nav-icon">📨</span>
        Messages
    </a>
</nav>
