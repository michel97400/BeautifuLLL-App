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

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - BeautifuLLL AI</title>
    <link rel="stylesheet" href="../../../style.css">
    <style>
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            max-width: 1000px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 500px;
        }

        .welcome-section {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }

        .welcome-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .welcome-subtitle {
            font-size: 1.2rem;
            margin-bottom: 30px;
            opacity: 0.9;
            line-height: 1.6;
        }

        .welcome-icon {
            font-size: 4rem;
            margin-bottom: 30px;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
            animation: float 3s ease-in-out infinite;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        .feature-list li {
            padding: 8px 0;
            opacity: 0.9;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
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
            text-align: center;
        }

        .form-subtitle {
            color: #718096;
            margin-bottom: 30px;
            font-size: 1rem;
            text-align: center;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #4a5568;
        }

        .form-group input {
            width: 100%;
            padding: 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f7fafc;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4facfe;
            box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.1);
            background: white;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 16px;
            border: none;
            border-radius: 12px;
            width: 100%;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);
            margin-bottom: 20px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 172, 254, 0.4);
        }

        .register-link {
            text-align: center;
            color: #718096;
            margin-top: 20px;
        }

        .register-link a {
            color: #4facfe;
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border: 1px solid #fc8181;
        }

        .alert-error ul {
            margin: 5px 0 0 20px;
            padding: 0;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: #718096;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .divider span {
            padding: 0 15px;
            font-size: 0.9rem;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        @media (max-width: 768px) {
            .login-container {
                grid-template-columns: 1fr;
                margin: 20px;
            }

            .welcome-section {
                padding: 40px 30px;
            }

            .welcome-title {
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
    <div class="login-page">
        <div class="login-container">
            <!-- Section de bienvenue (gauche) -->
            <div class="welcome-section">
                <div class="welcome-icon">🎓</div>
                <h1 class="welcome-title">Bon retour !</h1>
                <p class="welcome-subtitle">
                    Connectez-vous pour continuer votre parcours d'apprentissage avec BeautifuLLL AI
                </p>
                
                <ul class="feature-list">
                    <li>🤖 Votre assistant IA vous attend</li>
                    <li>📚 Accédez à vos matières</li>
                    <li>💬 Retrouvez vos conversations</li>
                    <li>🚀 Continuez à progresser</li>
                </ul>
            </div>

            <!-- Section du formulaire (droite) -->
            <div class="form-section">
                <h2 class="form-title">Se connecter</h2>
                <p class="form-subtitle">Accédez à votre espace personnel</p>

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
                    <div class="alert alert-error">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="form-group">
                        <label for="email">Adresse email</label>
                        <input type="email" id="email" name="email" required 
                               placeholder="votre.email@exemple.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Votre mot de passe">
                    </div>

                    <button type="submit" class="btn-primary">🔐 Se connecter</button>
                </form>

                <div class="divider">
                    <span>Nouveau sur BeautifuLLL AI ?</span>
                </div>

                <p class="register-link">
                    <a href="?action=register">Créer un compte gratuitement</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
