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
        .register-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            max-width: 1200px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 600px;
        }

        .benefits-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .benefits-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .benefits-subtitle {
            font-size: 1.1rem;
            margin-bottom: 40px;
            opacity: 0.9;
            line-height: 1.6;
        }

        .benefit-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 25px;
            animation: slideInLeft 0.6s ease-out;
        }

        .benefit-icon {
            font-size: 1.5rem;
            margin-right: 15px;
            margin-top: 2px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
        }

        .benefit-content h3 {
            margin: 0 0 8px 0;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .benefit-content p {
            margin: 0;
            opacity: 0.9;
            line-height: 1.5;
        }

        .form-section {
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-title {
            font-size: 2rem;
            color: #2d3748;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .form-subtitle {
            color: #718096;
            margin-bottom: 30px;
            font-size: 1.1rem;
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
            padding: 14px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f7fafc;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }

        .form-group small {
            color: #718096;
            font-size: 0.9rem;
            margin-top: 5px;
            display: block;
        }

        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 25px;
            font-size: 0.95rem;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin: 0;
            accent-color: #667eea;
        }

        .checkbox-group label {
            margin: 0;
            cursor: pointer;
            line-height: 1.4;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 16px;
            border: none;
            border-radius: 10px;
            width: 100%;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            color: #718096;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border: 1px solid #fc8181;
        }

        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }

        .alert ul {
            margin: 5px 0 0 20px;
            padding: 0;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @media (max-width: 768px) {
            .register-container {
                grid-template-columns: 1fr;
                margin: 20px;
            }

            .benefits-section {
                padding: 40px 30px;
            }

            .benefits-title {
                font-size: 2rem;
            }

            .form-section {
                padding: 40px 30px;
            }

            .form-title {
                font-size: 1.7rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-page">
        <div class="register-container">
            <!-- Section des avantages (gauche) -->
            <div class="benefits-section">
                <h1 class="benefits-title">Pourquoi s'enregistrer ?</h1>
                <p class="benefits-subtitle">
                    Rejoignez BeautifuLLL AI et découvrez une nouvelle façon d'apprendre avec l'intelligence artificielle.
                </p>

                <div class="benefit-item">
                    <div class="benefit-icon">🤖</div>
                    <div class="benefit-content">
                        <h3>Assistant IA personnalisé</h3>
                        <p>Bénéficiez d'un assistant IA qui s'adapte à votre niveau et vos matières pour un apprentissage sur mesure.</p>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">📚</div>
                    <div class="benefit-content">
                        <h3>Suivi personnalisé</h3>
                        <p>Suivez vos progrès, organisez vos matières et accédez à vos conversations sauvegardées.</p>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">💬</div>
                    <div class="benefit-content">
                        <h3>Conversations illimitées</h3>
                        <p>Posez toutes vos questions à l'IA et retrouvez l'historique de vos échanges à tout moment.</p>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">🚀</div>
                    <div class="benefit-content">
                        <h3>Apprentissage accéléré</h3>
                        <p>Apprenez plus efficacement grâce à des explications adaptées à votre rythme et votre style. Génerer vos cours reçus en PDF et téléchargés les !</p>
                    </div>
                </div>
            </div>

            <!-- Section du formulaire (droite) -->
            <div class="form-section">
                <h2 class="form-title">Créer votre compte</h2>
                <p class="form-subtitle">Commencez votre parcours d'apprentissage intelligent</p>

                <?php if ($errors): ?>
                    <div class="alert alert-error">
                        <ul>
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($message && empty($errors)): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nom">Nom *</label>
                        <input type="text" name="nom" id="nom" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" placeholder="Votre nom de famille">
                    </div>

                    <div class="form-group">
                        <label for="prenom">Prénom *</label>
                        <input type="text" name="prenom" id="prenom" required value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" placeholder="Votre prénom">
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" name="email" id="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="votre.email@exemple.com">
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe *</label>
                        <input type="password" name="password" id="password" required minlength="8" placeholder="Minimum 8 caractères">
                        <small>Choisissez un mot de passe sécurisé d'au moins 8 caractères</small>
                    </div>

                    <div class="form-group">
                        <label for="id_niveau">Votre niveau d'études *</label>
                        <select name="id_niveau" id="id_niveau" required>
                            <option value="">-- Sélectionnez votre niveau --</option>
                            <?php foreach ($niveaux as $niveau): ?>
                                <option value="<?= $niveau['id_niveau'] ?>" 
                                    <?= (isset($_POST['id_niveau']) && $_POST['id_niveau'] == $niveau['id_niveau']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($niveau['libelle_niveau']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" name="consentement_rgpd" id="rgpd" value="1" required>
                        <label for="rgpd">J'accepte la politique de confidentialité et le traitement de mes données personnelles (RGPD) *</label>
                    </div>

                    <!-- Valeurs par défaut pour étudiant -->
                    <input type="hidden" name="id_role" value="2">

                    <button type="submit" class="btn-primary">🚀 Créer mon compte</button>
                </form>

                <p class="login-link">
                    Vous avez déjà un compte ? <a href="?action=connect">Se connecter ici</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>