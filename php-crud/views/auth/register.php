<?php
require_once __DIR__ . '/../../controllers/EtudiantController.php';
use Controllers\EtudiantController;
$niveauController = new \Controllers\NiveauController();
$niveaux = $niveauController->getNiveaux();
$controller = new EtudiantController();
$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->handleSubmit($_POST, $_FILES, false, null); // false = création

    $errors = $result['errors'] ?? [];
    $message = $result['message'] ?? '';

    if (empty($errors) && $result['success']) {
        // Connexion automatique après inscription
        $etudiant = $controller->loginEtudiant($_POST['email'], $_POST['password']);
        if ($etudiant) {
            $_SESSION['user'] = [
                'id_etudiant' => $etudiant['id_etudiant'],
                'nom' => $etudiant['nom'],
                'prenom' => $etudiant['prenom'],
                'email' => $etudiant['email'],
                'avatar' => $etudiant['avatar'] ?? null,
                'role' => $etudiant['role']
            ];
            header('Location: index.php?action=dashboard');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - BeautifuLLL AI</title>
    <link rel="stylesheet" href="../../../style.css">
    <style>
        .auth-container {
            max-width: 500px;
            margin: 100px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .auth-title {
            text-align: center;
            color: #667eea;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        .btn-primary {
            background: #667eea;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            width: 100%;
            font-size: 1.1rem;
            cursor: pointer;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <h1 class="auth-title">Créer votre compte étudiant</h1>

        <?php if ($errors): ?>
            <div style="background:#f8d7da; color:#721c24; padding:10px; border-radius:6px; margin-bottom:20px;">
                <ul style="margin:5px 0;">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($message && empty($errors)): ?>
            <div style="background:#d4edda; color:#155724; padding:10px; border-radius:6px; margin-bottom:20px;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nom">Nom *</label>
                <input type="text" name="nom" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="prenom">Prénom *</label>
                <input type="text" name="prenom" required value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe *</label>
                <input type="password" name="password" required minlength="8">
                <small>Minimum 8 caractères</small>
            </div>

            <div class="form-group">
                <label for="id_niveau">Votre niveau d'études *</label>
                <select name="id_niveau" id="id_niveau" required>
                    <option value="">-- Choisissez votre niveau --</option>
                    <?php foreach ($niveaux as $niveau): ?>
                        <option value="<?= $niveau['id_niveau'] ?>" 
                            <?= (isset($_POST['id_niveau']) && $_POST['id_niveau'] == $niveau['id_niveau']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($niveau['libelle_niveau']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 8px; font-size: 0.95rem;">
                <label style="display: flex; align-items: center; cursor: pointer; margin: 0;">
                    <input type="checkbox" name="consentement_rgpd" value="1" required style="margin: 0; width: 16px; height: 16px;">
                    <span style="margin-left: 8px;">J'accepte la politique de confidentialité (RGPD) *</span>
                </label>
            </div>

            <!-- Valeurs par défaut pour étudiant -->
            <input type="hidden" name="id_role" value="2"> <!-- Rôle "Étudiant" -->

            <button type="submit" class="btn-primary">S'inscrire</button>
        </form>

        <p class="login-link">
            Déjà un compte ? <a href="?action=connect">Se connecter</a>
        </p>
    </div>
</body>
</html>