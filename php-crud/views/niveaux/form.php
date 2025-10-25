<?php
require_once __DIR__ . '/../../includes/check_admin.php';
require_once __DIR__ . '/../../controllers/NiveauController.php';

use Controllers\NiveauController;

$message = '';
$errors = [];
$niveau = null;
$isEditMode = false;

// Détection du mode (ajout ou édition)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $isEditMode = true;
    $controller = new NiveauController();
    $niveau = $controller->getSingleNiveau($_GET['id']);

    if (!$niveau) {
        $message = "Niveau introuvable.";
        $isEditMode = false;
    }
}

$inputData = [];
// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new NiveauController();
    $result = $controller->handleSubmit($_POST, $isEditMode, $niveau);

    $errors = $result['errors'];
    $message = $result['message'];
    $niveau = $result['niveau'];
    $inputData = $result['input'];
}
?>

<?php include __DIR__ . '/../../includes/crud_nav.php'; ?>

<h2 class="page-title"><?= $isEditMode ? 'Modifier' : 'Ajouter' ?> un niveau</h2>

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
        <input type="hidden" name="id_niveau" value="<?= htmlspecialchars($niveau['id_niveau'] ?? '') ?>">
    <?php endif; ?>

    <label for="libelle_niveau">Libellé du niveau :</label>
    <input type="text" id="libelle_niveau" name="libelle_niveau" value="<?= htmlspecialchars($inputData['libelle_niveau'] ?? $niveau['libelle_niveau'] ?? '') ?>" required>

    <button type="submit"><?= $isEditMode ? 'Modifier' : 'Créer' ?> le niveau</button>
    <a href="index.php?action=niveau_list" class="btn btn-secondary">Retour à la liste</a>
</form>
