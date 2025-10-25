<?php
require_once __DIR__ . '/../../includes/check_admin.php';
require_once __DIR__ . '/../../controllers/EtudiantController.php';
require_once __DIR__ . '/../../controllers/RoleController.php';
require_once __DIR__ . '/../../controllers/NiveauController.php';

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
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $date_inscription = $isEditMode ? ($etudiant['date_inscription'] ?? date('Y-m-d')) : date('Y-m-d');
    $consentement_rgpd = isset($_POST['consentement_rgpd']) ? 1 : 0;
    $id_role = $_POST['id_role'] ?? 1;
    $id_niveau = $_POST['id_niveau'] ?? 1;

    // VALIDATION DES CHAMPS

    // Validation du nom
    if (empty($nom)) {
        $errors[] = "Le nom est requis.";
    } elseif (strlen($nom) > 50) {
        $errors[] = "Le nom ne doit pas dépasser 50 caractères.";
    } elseif (!preg_match("/^[a-zA-ZÀ-ÿ\s'-]+$/u", $nom)) {
        $errors[] = "Le nom contient des caractères non autorisés.";
    }

    // Validation du prénom
    if (empty($prenom)) {
        $errors[] = "Le prénom est requis.";
    } elseif (strlen($prenom) > 50) {
        $errors[] = "Le prénom ne doit pas dépasser 50 caractères.";
    } elseif (!preg_match("/^[a-zA-ZÀ-ÿ\s'-]+$/u", $prenom)) {
        $errors[] = "Le prénom contient des caractères non autorisés.";
    }

    // Validation de l'email
    if (empty($email)) {
        $errors[] = "L'email est requis.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Le format de l'email est invalide.";
    } else {
        // Vérifier que l'email n'existe pas déjà
        $controller = new EtudiantController();
        $etudiants = $controller->getEtudiant();
        foreach ($etudiants as $etud) {
            if ($etud['email'] === $email) {
                // Si on est en mode édition, vérifier que ce n'est pas le même étudiant
                if (!$isEditMode || $etud['id_etudiant'] != $_POST['id_etudiant']) {
                    $errors[] = "Cet email est déjà utilisé par un autre étudiant.";
                    break;
                }
            }
        }
    }

    // Validation du mot de passe
    if (!$isEditMode) {
        // En création, le mot de passe est obligatoire
        if (empty($password)) {
            $errors[] = "Le mot de passe est requis.";
        } elseif (strlen($password) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
        }
    } else {
        // En modification, valider seulement si un nouveau mot de passe est fourni
        if (!empty($password) && strlen($password) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
        }
    }

    // Validation du consentement RGPD
    if ($consentement_rgpd != 1) {
        $errors[] = "Vous devez accepter la politique de confidentialité (RGPD).";
    }

    // Validation des relations (id_role, id_niveau)
    $roleController = new RoleController();
    $niveauController = new NiveauController();
    $roles = $roleController->getRoles();
    $niveaux = $niveauController->getNiveaux();

    $roleExists = false;
    foreach ($roles as $role) {
        if ($role['id_role'] == $id_role) {
            $roleExists = true;
            break;
        }
    }
    if (!$roleExists) {
        $errors[] = "Le rôle sélectionné n'existe pas.";
    }

    $niveauExists = false;
    foreach ($niveaux as $niveau) {
        if ($niveau['id_niveau'] == $id_niveau) {
            $niveauExists = true;
            break;
        }
    }
    if (!$niveauExists) {
        $errors[] = "Le niveau sélectionné n'existe pas.";
    }

    // Gestion de l'avatar avec validation
    $avatar = $isEditMode ? ($etudiant['avatar'] ?? null) : null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $fileInfo = $_FILES['avatar'];

        // Validation du type MIME
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = finfo_file($finfo, $fileInfo['tmp_name']);
        finfo_close($finfo);

        if (!in_array($detectedMime, $allowedMimes)) {
            $errors[] = "Le format de l'image n'est pas autorisé. Formats acceptés : JPEG, PNG, GIF.";
        }

        // Validation de la taille (2MB max)
        if ($fileInfo['size'] > 2 * 1024 * 1024) {
            $errors[] = "L'image est trop volumineuse. Taille maximale : 2MB.";
        }

        // Si pas d'erreur, traiter l'upload
        if (empty($errors)) {
            // Créer un nom de fichier sécurisé et unique
            $extension = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);
            $avatar = uniqid('avatar_', true) . '.' . $extension;
            $uploadPath = __DIR__ . '/../../uploads/' . $avatar;

            if (!move_uploaded_file($fileInfo['tmp_name'], $uploadPath)) {
                $errors[] = "Erreur lors de l'upload de l'avatar.";
                $avatar = $isEditMode ? ($etudiant['avatar'] ?? null) : null;
            }
        }
    } elseif (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Gestion des erreurs d'upload
        switch ($_FILES['avatar']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = "Le fichier est trop volumineux.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $errors[] = "Le fichier n'a été que partiellement téléchargé.";
                break;
            default:
                $errors[] = "Erreur lors de l'upload du fichier.";
        }
    }

    // Si aucune erreur, procéder à l'enregistrement
    if (empty($errors)) {
        $controller = new EtudiantController();

        if ($isEditMode) {
            // Mode modification
            $id_etudiant = $_POST['id_etudiant'];
            // Si pas de nouveau mot de passe, garder l'ancien
            $passwordhash = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : $etudiant['passwordhash'];

            $result = $controller->updateEtudiant($id_etudiant, $nom, $prenom, $email, $avatar, $passwordhash, $date_inscription, $consentement_rgpd, $id_role, $id_niveau);

            if ($result) {
                $message = "Étudiant modifié avec succès !";
                // Recharger les données mises à jour
                $etudiant = $controller->getSingleEtudiant($id_etudiant);
            } else {
                $errors[] = "Erreur lors de la modification de l'étudiant en base de données.";
            }
        } else {
            // Mode création
            $result = $controller->createEtudiant($nom, $prenom, $email, $avatar, $password, $date_inscription, $consentement_rgpd, $id_role, $id_niveau);

            if ($result) {
                $message = "Étudiant créé avec succès !";
                // Optionnel : rediriger vers la liste
                // header('Location: etudiant_list.php');
                // exit;
            } else {
                $errors[] = "Erreur lors de la création de l'étudiant en base de données.";
            }
        }
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
    <a href="index.php?action=etudiant_list" class="btn btn-secondary">Retour à la liste</a>
</form>
