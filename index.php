<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user = $_SESSION['user'] ?? null;
$isAdmin = $user && isset($user['role']) && $user['role'] === 'Administrateur';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beautiful AI</title> <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=arrow_upward_alt" />
</head>
<body>
    <main>
        <?php
        // Déplacé ici pour apparaître au-dessus de l'en-tête de la page
        if (isset($_GET['message']) && $_GET['message'] === 'deconnecte') {
            echo '<div style="color: green; text-align:center; margin-bottom:20px; padding:15px; border-radius:5px; background-color: #d4edda;">';
            echo '<strong>Vous avez été déconnecté avec succès.</strong>';
            echo '</div>';
        }
        ?>

        <header>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1>Bienvenue sur BeautifuLLL AI</h1>
                <?php if ($user): ?>
                    <div style="text-align: right; font-size: 14px;">
                        <span style="color: #0078d7; font-weight: bold;">
                            Connecté en tant que : <?= htmlspecialchars($user['prenom'] ?? $user['nom'] ?? $user['email']) ?>
                        </span>
                        <br>
                        <span style="color: #666;">
                            Rôle : <?= htmlspecialchars($user['role'] ?? 'Non défini') ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
            <nav>
                <a href="index.php">Accueil</a>
                <?php if ($isAdmin): ?>
                    <div class="dropdown">
                        <span class="dropdown-toggle">Administration</span>
                        <div class="dropdown-content">
                            <a href="?action=etudiant_list">Étudiants</a>
                            <a href="?action=matiere_list">Matières</a>
                            <a href="?action=niveau_list">Niveaux</a>
                            <a href="?action=role_list">Rôles</a>
                            <a href="?action=agent_list">Agents</a>
                            <a href="?action=session_list">Sessions</a>
                            <a href="?action=message_list">Messages</a>
                        </div>
                    </div>
                    <a href="index.php?action=agent-ia">Agent IA</a>
                <?php endif; ?>

                <a href="?action=contact">Contact</a>

                <?php if ($user): ?>
                    <a href="index.php?action=agent-ia">Agent IA</a>
                    <a href="?action=deconnexion" style="color: #dc3545;">Déconnexion</a>
                <?php else: ?>
                    <a href="?action=connect">Connexion</a>
                <?php endif; ?>
            </nav>
        </header>

        <div class="main-content">
            <?php


            if (isset($_GET['action']) && $_GET['action'] === 'creer_etudiant') {
                include 'php-crud/views/etudiants/form.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'modifier_etudiant'){
                include 'php-crud/views/etudiants/form.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'connect'){
                include 'php-crud/views/auth/login.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'deconnexion'){
                include 'php-crud/views/auth/logout.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'etudiant_list'){
                include 'php-crud/views/etudiants/list.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'supprimer_etudiant'){
                include 'php-crud/views/etudiants/delete.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'matiere_list'){
                include 'php-crud/views/matieres/list.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'creer_matiere'){
                include 'php-crud/views/matieres/form.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'modifier_matiere'){
                include 'php-crud/views/matieres/form.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'supprimer_matiere'){
                include 'php-crud/views/matieres/delete.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'niveau_list'){
                include 'php-crud/views/niveaux/list.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'creer_niveau'){
                include 'php-crud/views/niveaux/form.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'modifier_niveau'){
                include 'php-crud/views/niveaux/form.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'supprimer_niveau'){
                include 'php-crud/views/niveaux/delete.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'role_list'){
                include 'php-crud/views/roles/list.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'creer_role'){
                include 'php-crud/views/roles/form.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'modifier_role'){
                include 'php-crud/views/roles/form.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'agent-ia'){
                if (!isset($_SESSION['agent_ia_matiere'])) {
                    include 'php-crud/views/ai_assistant/agent_matiere_form.php';
                } else {
                    include 'php-crud/views/ai_assistant/chat_card.php';
                }

            } elseif (isset($_GET['action']) && $_GET['action'] === 'supprimer_role'){
                include 'php-crud/views/roles/delete.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'agent_list'){
                include 'php-crud/views/agents/list.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'creer_agent'){
                include 'php-crud/views/agents/form.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'modifier_agent'){
                include 'php-crud/views/agents/form.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'supprimer_agent'){
                include 'php-crud/views/agents/delete.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'session_list'){
                include 'php-crud/views/sessions/SessionConversation_list.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'creer_session'){
                include 'php-crud/views/sessions/SessionConversation_form.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'modifier_session'){
                include 'php-crud/views/sessions/SessionConversation_form.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'supprimer_session'){
                include 'php-crud/views/sessions/SessionConversation_delete.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'message_list'){
                include 'php-crud/views/messages/list.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'creer_message'){
                include 'php-crud/views/messages/form.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'modifier_message'){
                include 'php-crud/views/messages/form.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'supprimer_message'){
                include 'php-crud/views/messages/delete.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'acces_refuse'){
                include 'php-crud/views/shared/acces_refuse.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'contact'){
                echo '<div style="text-align: center; margin: 50px auto; padding: 20px; border: 1px solid #ccc; max-width: 400px; border-radius: 8px;">';
                echo '<h2>Contact - Simplon</h2>';
                echo '<p><strong>Adresse :</strong></p>';
                echo '<p>Résidence Compostelle 11, rue Saint-Jacques<br>97400 Saint Denis</p>';
                echo '<p><strong>Téléphone :</strong> 262725182</p>';
                echo '</div>';

            }

             else {
                echo '<p>Bienvenue sur BeautifuLLL AI. Sélectionnez une action dans le menu.</p>';
            }
            ?>
        </div>
        <footer>
            <p>&copy; 2025 BeautifuLLL AI. Tous droits réservés.</p>
        </footer>
    </main>
</body>
</html>