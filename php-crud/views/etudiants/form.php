<?php
require_once __DIR__ . '/../../includes/check_admin.php';
require_once __DIR__ . '/../../controllers/EtudiantController.php';
require_once __DIR__ . '/../../controllers/RoleController.php';
require_once __DIR__ . '/../../controllers/NiveauController.php';

use Controllers\EtudiantController;
use Controllers\RoleController;
use Controllers\NiveauController;

$message = '';
$errors = [];
$etudiant = null;
$isEditMode = false;
$inputData = [];

// Détection du mode (ajout ou édition)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $isEditMode = true;
    $controller = new EtudiantController();
    $etudiant = $controller->getSingleEtudiant($_GET['id']);

    if (!$etudiant) {
        $message = "Étudiant introuvable.";
        $isEditMode = false;
    }
}

// Traitement du formulaire via le controller
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new EtudiantController();
    $result = $controller->handleSubmit($_POST, $_FILES, $isEditMode, $etudiant);

    // Récupérer les résultats du controller
    $errors = $result['errors'];
    $message = $result['message'];
    $inputData = $result['input'];

    // Si modification réussie, recharger les données
    if ($isEditMode && empty($errors) && $result['success']) {
        $etudiant = $result['etudiant'];
    }
}

// Récupérer les rôles et niveaux pour les selects
$roleController = new RoleController();
$niveauController = new NiveauController();
$roles = $roleController->getRoles();
$niveaux = $niveauController->getNiveaux();
?>

<?php include __DIR__ . '/../../includes/crud_nav.php'; ?>

<h2 class="page-title"><?= $isEditMode ? 'Modifier' : 'Ajouter' ?> un étudiant</h2>

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

<form action="" method="POST" enctype="multipart/form-data" class="etudiant-form">
    <?php if ($isEditMode): ?>
        <input type="hidden" name="id_etudiant" value="<?= htmlspecialchars($inputData['id_etudiant'] ?? $etudiant['id_etudiant'] ?? '') ?>">
    <?php endif; ?>

    <label for="nom">Nom :</label>
    <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($inputData['nom'] ?? $etudiant['nom'] ?? '') ?>" required>

    <label for="prenom">Prénom :</label>
    <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($inputData['prenom'] ?? $etudiant['prenom'] ?? '') ?>" required>

    <label for="email">Email :</label>
    <input type="email" id="email" name="email" value="<?= htmlspecialchars($inputData['email'] ?? $etudiant['email'] ?? '') ?>" required>

    <label for="avatar">Avatar :</label>
    <?php if ($isEditMode && !empty($etudiant['avatar'])): ?>
        <div style="margin-bottom: 10px;">
            <img src="uploads/<?= htmlspecialchars($etudiant['avatar']) ?>" alt="Avatar actuel" style="max-width: 100px; max-height: 100px; border-radius: 5px;">
            <p style="font-size: 12px; color: #666;">Avatar actuel : <?= htmlspecialchars($etudiant['avatar']) ?></p>
        </div>
    <?php endif; ?>
    <input type="file" id="avatar" name="avatar" accept="image/*">
    <?php if ($isEditMode): ?>
        <small style="color: #666;">Laissez vide pour conserver l'avatar actuel</small>
    <?php endif; ?>

    <label for="password">Mot de passe :</label>
    <input type="password" id="password" name="password" <?= $isEditMode ? '' : 'required' ?>>
    <?php if ($isEditMode): ?>
        <small style="color: #666;">Laissez vide pour conserver le mot de passe actuel</small>
    <?php endif; ?>

    <label for="consentement_rgpd">
        <input type="checkbox" id="consentement_rgpd" name="consentement_rgpd" value="1" <?= isset($etudiant['consentement_rgpd']) && $etudiant['consentement_rgpd'] ? 'checked' : '' ?> required>
        J'accepte la politique de confidentialité (RGPD)
    </label>

    <label for="id_role">Rôle :</label>
    <select id="id_role" name="id_role" required>
        <?php foreach ($roles as $role): ?>
            <option value="<?= htmlspecialchars($role['id_role']) ?>"
                <?= (isset($inputData['id_role']) && $inputData['id_role'] == $role['id_role']) || (isset($etudiant['id_role']) && $etudiant['id_role'] == $role['id_role']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($role['nom_role']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="id_niveau">Niveau :</label>
    <select id="id_niveau" name="id_niveau" required>
        <?php foreach ($niveaux as $niveau): ?>
            <option value="<?= htmlspecialchars($niveau['id_niveau']) ?>"
                <?= (isset($inputData['id_niveau']) && $inputData['id_niveau'] == $niveau['id_niveau']) || (isset($etudiant['id_niveau']) && $etudiant['id_niveau'] == $niveau['id_niveau']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($niveau['libelle_niveau']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit"><?= $isEditMode ? 'Modifier' : 'Créer' ?> l'étudiant</button>
    <a href="index.php?action=etudiant_list" class="btn btn-secondary">Retour à la liste</a>
</form>
