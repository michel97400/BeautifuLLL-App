<?php
// Formulaire de connexion (login)
require_once __DIR__ . '/../../controllers/AuthController.php';

use Controllers\AuthController;

$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new AuthController();
    $result = $controller->handleLogin($_POST);

    $errors = $result['errors'];
    $message = $result['message'];

    // Si succès, rediriger
    if ($result['success'] && !empty($result['redirect'])) {
        header('Location: ' . $result['redirect']);
        exit;
    }
}
?>
<form action="" method="POST" class="etudiant-form">
    <h2>Connexion</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <strong>⚠ Erreurs :</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($message && empty($errors)): ?>
        <div style="color: red; text-align:center; margin-bottom:10px;"> <?= htmlspecialchars($message) ?> </div>
    <?php endif; ?>
    <label for="email">Email :</label>
    <input type="email" id="email" name="email" required>

    <label for="password">Mot de passe :</label>
    <input type="password" id="password" name="password" required>

    <button type="submit">Se connecter</button>
</form>
