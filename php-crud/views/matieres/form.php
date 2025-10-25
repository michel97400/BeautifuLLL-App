<?php
require_once __DIR__ . '/../../includes/check_admin.php';
require_once __DIR__ . '/../../controllers/MatiereController.php';

use Controllers\MatiereController;

$message = '';
$errors = [];
$matiere = null;
$isEditMode = false;

// Détection du mode (ajout ou édition)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $isEditMode = true;
    $controller = new MatiereController();
    $matiere = $controller->getSingleMatiere($_GET['id']);

    if (!$matiere) {
        $message = "Matière introuvable.";
        $isEditMode = false;
    }
}

$inputData = [];
// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new MatiereController();
    $result = $controller->handleSubmit($_POST, $isEditMode, $matiere);

    $errors = $result['errors'];
    $message = $result['message'];
    $matiere = $result['matiere'];
    $inputData = $result['input'];
}
?>

<?php include __DIR__ . '/../../includes/crud_nav.php'; ?>

<h2 class="page-title"><?= $isEditMode ? 'Modifier' : 'Ajouter' ?> une matière</h2>

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
        <input type="hidden" name="id_matieres" value="<?= htmlspecialchars($matiere['id_matieres'] ?? '') ?>">
    <?php endif; ?>

    <label for="nom_matieres">Nom de la matière :</label>
    <input type="text" id="nom_matieres" name="nom_matieres" value="<?= htmlspecialchars($inputData['nom_matieres'] ?? $matiere['nom_matieres'] ?? '') ?>" required>

    <label for="description_matiere">Description :</label>
    <textarea id="description_matiere" name="description_matiere" rows="5" style="padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; background: #fafbfc; font-family: inherit; resize: vertical;"><?= htmlspecialchars($inputData['description_matiere'] ?? $matiere['description_matiere'] ?? '') ?></textarea>

    <button type="submit"><?= $isEditMode ? 'Modifier' : 'Créer' ?> la matière</button>
    <a href="index.php?action=matiere_list" class="btn btn-secondary">Retour à la liste</a>
</form>
