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
                    <a href="?action=creer_etudiant">Créer un étudiant</a>
                    <a href="?action=etudiant_list">Liste des étudiants</a>
                <?php endif; ?>
                <?php if ($user): ?>
                    <a href="?action=deconnexion" style="color: #dc3545;">Déconnexion</a>
                <?php else: ?>
                    <a href="?action=connect">Connexion</a>
                <?php endif; ?>
                <a href="?action=contact">Contact</a>

            </nav>
        </header>

        <div class="main-content">
            <?php
            // L'ancienne section d'affichage du message de déconnexion est supprimée ici.
            // ... (reste du code inchangé) ...

            if (isset($_GET['action']) && $_GET['action'] === 'creer_etudiant') {
                include 'php-crud/views/etudiant_form.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'modifier_etudiant'){
                include 'php-crud/views/etudiant_form.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'connect'){
                include 'php-crud/views/form_connect.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'deconnexion'){
                include 'php-crud/views/logout.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'etudiant_list'){
                include 'php-crud/views/etudiant_list.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'supprimer_etudiant'){
                include 'php-crud/views/etudiant_delete.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'acces_refuse'){
                include 'php-crud/views/acces_refuse.php';

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