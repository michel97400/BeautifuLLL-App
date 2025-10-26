<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php?action=connect');
    exit;
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion réussie</title>
    <link rel="stylesheet" href="../../style.css">
</head>
<body>
    <main>
        <div class="main-content">
            <div class="etudiant-form" style="text-align:center;">
                <h2>Bienvenue, <?php echo htmlspecialchars($user['prenom'] ?? $user['nom'] ?? $user['email']); ?> !</h2>
                <p>Vous êtes connecté avec le rôle : <strong><?php echo htmlspecialchars($user['role']); ?></strong></p>
                <a href="../../index.php" style="color:#0078d7;">Retour à l'accueil</a>
            </div>
        </div>
    </main>
</body>
</html>
