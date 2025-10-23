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
                <a href="?action=liste_etudiants">Liste des étudiants</a>
                <a href="?action=creer_etudiant">Créer un étudiant</a>
                <a href="?action=connect">Connexion</a>
                <a href="#">Contact</a>

            </nav>
        </header>
        <div class="main-content">
            <?php
            $action = $_GET['action'] ?? 'accueil';

            switch ($action) {
                // Créer un étudiant
                case 'creer_etudiant':
                    include 'php-crud/views/form_etudiant.php';
                    break;

                // Lister tous les étudiants
                case 'liste_etudiants':
                    include 'php-crud/views/liste_etudiants.php';
                    break;

                // Voir les détails d'un étudiant
                case 'details_etudiant':
                    if (isset($_GET['id'])) {
                        include 'php-crud/views/details_etudiant.php';
                    } else {
                        echo '<p class="error">ID manquant.</p>';
                    }
                    break;

                // Modifier un étudiant
                case 'modifier_etudiant':
                    if (isset($_GET['id'])) {
                        include 'php-crud/views/form_edit_etudiant.php';
                    } else {
                        echo '<p class="error">ID manquant.</p>';
                    }
                    break;

                // Supprimer un étudiant
                case 'supprimer_etudiant':
                    if (isset($_GET['id'])) {
                        require_once 'php-crud/controllers/EtudiantController.php';
                        use Controllers\EtudiantController;

                        $controller = new EtudiantController();
                        $result = $controller->deleteEtudiant($_GET['id']);

                        if ($result) {
                            echo '<p class="success">Étudiant supprimé avec succès.</p>';
                            echo '<a href="?action=liste_etudiants" class="btn btn-primary">Retour à la liste</a>';
                        } else {
                            echo '<p class="error">Erreur lors de la suppression.</p>';
                        }
                    } else {
                        echo '<p class="error">ID manquant.</p>';
                    }
                    break;

                // Connexion
                case 'connect':
                    include 'php-crud/views/form_connect.php';
                    break;

                // Accueil par défaut
                default:
                    echo '<p>Bienvenue sur BeautifuLLL AI. Sélectionnez une action dans le menu.</p>';
                    break;
            }
            ?>
        </div>
        <footer>
            <p>&copy; 2025 BeautifuLLL AI. Tous droits réservés.</p>
        </footer>
    </main>
</body>
</html>