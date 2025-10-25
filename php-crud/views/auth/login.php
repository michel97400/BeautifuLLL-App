

<?php
// Formulaire de connexion (login)
require_once __DIR__ . '/../../controllers/EtudiantController.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // echo($email .''. $password .'');

    $controller = new \Controllers\EtudiantController();
    $user = $controller->loginEtudiant($email, $password);
    if ($user) {
        $_SESSION['user'] = $user;
        header('Location: php-crud/views/success_connect.php');
        exit;
    } else {
        $message = "Identifiants invalides.";
    }
}
?>
<form action="" method="POST" class="etudiant-form">
    <h2>Connexion</h2>
    <?php if ($message): ?>
        <div style="color: red; text-align:center; margin-bottom:10px;"> <?= htmlspecialchars($message) ?> </div>
    <?php endif; ?>
    <label for="email">Email :</label>
    <input type="email" id="email" name="email" required>

    <label for="password">Mot de passe :</label>
    <input type="password" id="password" name="password" required>

    <button type="submit">Se connecter</button>
</form>
