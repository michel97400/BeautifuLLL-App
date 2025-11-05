<?php


// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit;
}
$user = $_SESSION['user'] ?? null;
$isAdmin = $user && isset($user['role']) && $user['role'] === 'Administrateur';

require_once __DIR__ . '/../../controllers/EtudiantController.php';
require_once __DIR__ . '/../../controllers/NiveauController.php';
require_once __DIR__ . '/../../controllers/RoleController.php';

use Controllers\EtudiantController;
use Controllers\NiveauController;
use Controllers\RoleController;

$controller = new EtudiantController();
$niveauController = new NiveauController();
$roleController = new RoleController();

// Récupérer l'ID de l'étudiant connecté
$userId = $_SESSION['user']['id_etudiant'];
$etudiant = $controller->getSingleEtudiantWithDetails($userId);

// Gérer la modification du profil
$errors = [];
$message = '';
$editMode = false;
$successUpdate = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_profile') {
            $result = $controller->handleSubmit($_POST, $_FILES, true, $etudiant);
            
            if ($result['success'] && empty($result['errors'])) {
                // Recharger les données de l'étudiant
                $etudiant = $controller->getSingleEtudiantWithDetails($userId);
                // Mettre à jour la session
                $_SESSION['user'] = array_merge($_SESSION['user'], [
                    'nom' => $etudiant['nom'],
                    'prenom' => $etudiant['prenom'],
                    'email' => $etudiant['email'],
                    'avatar' => $etudiant['avatar']
                ]);
                // Marquer le succès pour redirection JavaScript
                $successUpdate = true;
                $message = $result['message'];
            } else {
                $errors = $result['errors'];
                $editMode = true; // Rester en mode édition si erreurs
            }
        } elseif ($_POST['action'] === 'cancel_edit') {
            $editMode = false;
        }
    }
}

// Gérer le message de succès après redirection
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $message = "Profil modifié avec succès !";
}

if (isset($_GET['edit'])) {
    $editMode = true;
}

// Récupérer les niveaux et rôles pour les selects
$niveaux = $niveauController->getNiveaux();
$roles = $roleController->getRoles();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - BeautifuLLL AI</title>
    <link rel="stylesheet" href="../../../style.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 120px auto 40px;
            padding: 0 20px;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }

        .dashboard-header h1 {
            margin: 0 0 10px 0;
            font-size: 2rem;
        }

        .dashboard-header p {
            margin: 0;
            opacity: 0.9;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .profile-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 2px solid #cc1010ff;
        }

        .profile-avatar {
            text-align: center;
            margin-bottom: 20px;
        }

        .avatar-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #667eea;
            margin: 0 auto;
            display: block;
        }

        .avatar-placeholder {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            margin: 0 auto;
        }

        .profile-info {
            text-align: center;
            border: 2px solid #330fabff;
            border-radius: 50px;
        }

        .profile-name {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 15px 0 5px;
            color: #2d3748;
        }

        .profile-email {
            color: #718096;
            margin-bottom: 15px;
        }

        .profile-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            margin: 5px;
        }

        .badge-role {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .badge-niveau {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .info-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 2px solid #12ce76ff;
        }

        .info-card h2 {
            margin: 0 0 20px 0;
            font-size: 1.5rem;
            color: #2d3748;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        .info-row {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #4a5568;
            min-width: 200px;
        }

        .info-value {
            color: #2d3748;
            flex: 1;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #4a5568;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group input[type="file"] {
            padding: 8px;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn-secondary:hover {
            background: #cbd5e0;
        }

        .btn-edit {
            background: #48bb78;
            color: white;
        }

        .btn-edit:hover {
            background: #38a169;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }

        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border: 1px solid #fc8181;
        }

        .alert ul {
            margin: 10px 0 0 20px;
            padding: 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 2px 2px 8px 2px  rgba(42, 7, 157, 0.1);
            text-align: center;
            border: 2px solid #101010ff;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
            margin: 10px 0;
        }

        .stat-label {
            color: #718096;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-header {
                padding: 25px;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="dashboard-header">
            <h1>👋 Bienvenue, <?= htmlspecialchars($etudiant['prenom']) ?> !</h1>
            <p>Gérez votre profil et consultez vos informations</p>
        </div>

        <!-- Messages -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <strong>Erreurs détectées :</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Membre depuis</div>
                <div class="stat-value">
                    <?php
                    $date = new DateTime($etudiant['date_inscription']);
                    $now = new DateTime();
                    $diff = $now->diff($date);
                    echo $diff->days;
                    ?>
                </div>
                <div class="stat-label">Jours</div>
            </div>
            <?php if (!$isAdmin): ?>
            <div class="stat-card">
                <div class="stat-label">Niveau actuel</div>
                <div class="stat-value" style="font-size: 1.5rem;">
                    <?= htmlspecialchars($etudiant['libelle_niveau']) ?>
                </div>
            </div>
            <?php endif; ?>
            <div class="stat-card" style="display:flex; justify-content:center; align-items:center;">
                <div class="stat-value" style="font-size: 1.5rem; margin:0;">
                    <?= htmlspecialchars($etudiant['nom_role']) ?>
                </div>
            </div>
        </div>

        <!-- Grille principale -->
        <div class="dashboard-grid">
            <!-- Card Avatar -->
            <div class="profile-card">
                <div class="profile-avatar">
                    <?php if (!empty($etudiant['avatar'])): ?>
                        <img src="../../../uploads/<?= htmlspecialchars($etudiant['avatar']) ?>" 
                             alt="Avatar" class="avatar-circle">
                    <?php else: ?>
                        <div class="avatar-placeholder">
                            <?= strtoupper(substr($etudiant['prenom'], 0, 1) . substr($etudiant['nom'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="profile-info">
                    <h2 class="profile-name">
                        <?= htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']) ?>
                    </h2>
                    <p class="profile-email"><?= htmlspecialchars($etudiant['email']) ?></p>
                    
                    <div>
                        <span class="profile-badge badge-role">
                            <?= htmlspecialchars($etudiant['nom_role']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card Informations -->
            <div class="info-card">
                <?php if (!$editMode): ?>
                    <!-- Mode Lecture -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap:50px;">
                        <h2 style="margin: 0;">Informations du profil</h2>
                        <a href="?action=dashboard&edit=1" class="btn btn-edit">
                            ✏️ Modifier mon profil
                        </a>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Nom complet :</span>
                        <span class="info-value"><?= htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']) ?></span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Email :</span>
                        <span class="info-value"><?= htmlspecialchars($etudiant['email']) ?></span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Rôle :</span>
                        <span class="info-value"><?= htmlspecialchars($etudiant['nom_role']) ?></span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Date d'inscription :</span>
                        <span class="info-value">
                            <?php
                            $date = new DateTime($etudiant['date_inscription']);
                            echo $date->format('d/m/Y');
                            ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Niveau :</span>
                        <span class="info-value"><?= htmlspecialchars($etudiant['libelle_niveau']) ?></span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Consentement RGPD :</span>
                        <span class="info-value">
                            <?= $etudiant['consentement_rgpd'] ? '✅ Accepté' : '❌ Non accepté' ?>
                        </span>
                    </div>

                <?php else: ?>
                    <!-- Mode Édition -->
                    <h2 id="edit-form">Modifier mon profil</h2>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_profile">
                        <input type="hidden" name="id_etudiant" value="<?= $etudiant['id_etudiant'] ?>">

                        <div class="form-group">
                            <label for="nom">Nom *</label>
                            <input type="text" id="nom" name="nom" 
                                   value="<?= htmlspecialchars($etudiant['nom']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="prenom">Prénom *</label>
                            <input type="text" id="prenom" name="prenom" 
                                   value="<?= htmlspecialchars($etudiant['prenom']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" 
                                   value="<?= htmlspecialchars($etudiant['email']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="password">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                            <input type="password" id="password" name="password" 
                                   placeholder="Minimum 8 caractères">
                        </div>

                        <div class="form-group">
                            <label for="id_niveau">Niveau *</label>
                            <select id="id_niveau" name="id_niveau" required>
                                <option value="">Sélectionner un niveau</option>
                                <?php foreach ($niveaux as $niveau): ?>
                                    <option value="<?= $niveau['id_niveau'] ?>" 
                                            <?= $niveau['id_niveau'] == $etudiant['id_niveau'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($niveau['libelle_niveau']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="avatar">Photo de profil</label>
                            <input type="file" id="avatar" name="avatar" accept="image/*">
                            <small style="color: #718096;">Formats acceptés : JPEG, PNG, GIF (max 2MB)</small>
                        </div>
                        
                        <!-- Consentement RGPD : non modifiable, conservé de l'inscription -->
                        <input type="hidden" name="consentement_rgpd" value="<?= $etudiant['consentement_rgpd'] ?>">

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">💾 Enregistrer les modifications</button>
                            <a href="?action=dashboard" class="btn btn-secondary">❌ Annuler</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bouton retour -->
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="btn btn-secondary">← Retour à l'accueil</a>
        </div>
    </div>

    <script>
        // Redirection après succès de la modification
        <?php if ($successUpdate): ?>
            window.location.href = '?action=dashboard&success=1';
        <?php endif; ?>

        // Si on est en mode édition (paramètre edit=1), défiler vers le formulaire
        <?php if ($editMode && !$successUpdate): ?>
            window.addEventListener('load', function() {
                const infoCard = document.querySelector('.info-card');
                if (infoCard) {
                    infoCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        <?php endif; ?>

        // Si message de succès via URL, défiler vers le haut pour voir le message
        <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
            window.addEventListener('load', function() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        <?php endif; ?>
    </script>
</body>
</html>
