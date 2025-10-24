<?php
require_once __DIR__ . '/../includes/check_admin.php';
require_once __DIR__ . '/../controllers/EtudiantController.php';
require_once __DIR__ . '/../controllers/RoleController.php';
require_once __DIR__ . '/../controllers/NiveauController.php';

use Controllers\EtudiantController;
use Controllers\RoleController;
use Controllers\NiveauController;

$message = '';
$errors = []; // Tableau pour stocker les erreurs de validation
$etudiant = null;
$isEditMode = false;

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

$inputData = [];
// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données avec nettoyage
    $etudiantController = new EtudiantController();
    $result = $etudiantController->handleSubmit($_POST,$_FILES,$isEditMode,$etudiant);
    $errors = $result['errors'];
    $message = $result['message'];
    $etudiant = $result['etudiant'];
    $inputData = $result['input'];
}

// Récupérer les rôles et niveaux pour les selects
$roleController = new RoleController();
$niveauController = new NiveauController();
$roles = $roleController->getRoles();
$niveaux = $niveauController->getNiveaus();
?>

<h2><?= $isEditMode ? 'Modifier' : 'Ajouter' ?> un étudiant</h2>

<?php if (!empty($errors)): ?>
    <div style="color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; text-align:left; margin-bottom:15px; padding:15px; border-radius:5px;">
        <strong>Erreurs de validation :</strong>
        <ul style="margin: 10px 0 0 0; padding-left: 20px;">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($message): ?>
    <div style="color: <?= strpos($message, 'succès') !== false ? 'green' : 'red' ?>; text-align:center; margin-bottom:10px; padding:10px; border-radius:5px; background-color: <?= strpos($message, 'succès') !== false ? '#d4edda' : '#f8d7da' ?>;">
        <?= htmlspecialchars($message) ?>
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
                <?= (isset($id_role) && $id_role == $role['id_role']) || (isset($etudiant['id_role']) && $etudiant['id_role'] == $role['id_role']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($role['nom_role']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="id_niveau">Niveau :</label>
    <select id="id_niveau" name="id_niveau" required>
        <?php foreach ($niveaux as $niveau): ?>
            <option value="<?= htmlspecialchars($niveau['id_niveau']) ?>"
                <?= (isset($id_niveau) && $id_niveau == $niveau['id_niveau']) || (isset($etudiant['id_niveau']) && $etudiant['id_niveau'] == $niveau['id_niveau']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($niveau['libelle_niveau']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit"><?= $isEditMode ? 'Modifier' : 'Créer' ?> l'étudiant</button>
    <a href="index.php?action=etudiant_list" style="display: inline-block; margin-top: 10px; text-align: center; color: #0078d7; text-decoration: none;">Retour à la liste</a>
</form>
