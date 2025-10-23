<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main>
        <header>
            <h1>Bienvenue sur BeautifuLLL AI</h1>
            <nav>
                <a href="index.php">Accueil</a>
                <a href="?action=creer_etudiant">Créer un étudiant</a>
                <a href="?action=etudiant_list">Liste des étudiants</a>
                <a href="?action=connect">Connexion</a>
                <a href="#">Contact</a>

            </nav>
        </header>
        <div class="main-content">
            <?php
            if (isset($_GET['action']) && $_GET['action'] === 'creer_etudiant') {
                include 'php-crud/views/etudiant_form.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'modifier_etudiant'){
                include 'php-crud/views/etudiant_form.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'connect'){
                include 'php-crud/views/form_connect.php';

            } elseif (isset($_GET['action']) && $_GET['action'] === 'etudiant_list'){
                include 'php-crud/views/etudiant_list.php';

            } else {
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