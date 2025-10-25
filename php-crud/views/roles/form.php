<?php
require_once __DIR__ . '/../../includes/check_admin.php';
require_once __DIR__ . '/../../controllers/RoleController.php';

use Controllers\RoleController;

$message = '';
$errors = [];
$role = null;
$isEditMode = false;

// Détection du mode (ajout ou édition)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $isEditMode = true;
    $controller = new RoleController();
    $role = $controller->getSingleRole($_GET['id']);

    if (!$role) {
        $message = "Rôle introuvable.";
        $isEditMode = false;
    }
}

$inputData = [];
// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new RoleController();
    $result = $controller->handleSubmit($_POST, $isEditMode, $role);

    $errors = $result['errors'];
    $message = $result['message'];
    $role = $result['role'];
    $inputData = $result['input'];
}
?>

<?php include __DIR__ . '/../../includes/crud_nav.php'; ?>

<h2 class="page-title"><?= $isEditMode ? 'Modifier' : 'Ajouter' ?> un rôle</h2>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <strong>⚠ Erreurs de validation :</strong>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($message): ?>
    <div class="alert <?= strpos($message, 'succès') !== false ? 'alert-success' : 'alert-error' ?>">
        <strong><?= strpos($message, 'succès') !== false ? '✓' : '✗' ?> <?= htmlspecialchars($message) ?></strong>
    </div>
<?php endif; ?>

<form action="" method="POST" class="etudiant-form">
    <?php if ($isEditMode): ?>
        <input type="hidden" name="id_role" value="<?= htmlspecialchars($role['id_role'] ?? '') ?>">
    <?php endif; ?>

    <label for="nom_role">Nom du rôle :</label>
    <input type="text" id="nom_role" name="nom_role" value="<?= htmlspecialchars($inputData['nom_role'] ?? $role['nom_role'] ?? '') ?>" required>

    <button type="submit"><?= $isEditMode ? 'Modifier' : 'Créer' ?> le rôle</button>
    <a href="index.php?action=role_list" class="btn btn-secondary">Retour à la liste</a>
</form>
