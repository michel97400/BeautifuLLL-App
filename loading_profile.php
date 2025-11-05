<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: index.php?action=connect');
    exit;
}

// Redirection automatique après 2 secondes vers le dashboard
$redirectUrl = 'index.php?action=dashboard';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chargement - BeautifuLLL AI</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            width: 100%;
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            overflow: hidden;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Animation des étoiles en arrière-plan */
        .stars {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .star {
            position: absolute;
            width: 2px;
            height: 2px;
            background: white;
            border-radius: 50%;
            animation: twinkle 2s infinite alternate;
        }

        @keyframes twinkle {
            0% { opacity: 0.3; transform: scale(0.8); }
            100% { opacity: 1; transform: scale(1.2); }
        }

        /* Container principal */
        .loading-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px 60px;
            display: flex;
            align-items: center;
            gap: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 700px;
            width: 90%;
            position: relative;
            z-index: 10;
            min-height: 280px;
            animation: slideInUp 0.8s ease-out;
        }

        /* Sections gauche et droite */
        .loading-left {
            flex: 0 0 200px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .loading-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: left;
            padding-left: 20px;
        }

        /* Logo animé */
        .loading-logo {
            font-size: 4rem;
            margin-bottom: 30px;
            animation: logoGlow 2s ease-in-out infinite alternate;
        }

        @keyframes logoGlow {
            0% { 
                transform: scale(1) rotate(0deg);
                text-shadow: 0 0 20px rgba(255, 215, 0, 0.6);
            }
            100% { 
                transform: scale(1.05) rotate(5deg);
                text-shadow: 0 0 30px rgba(255, 215, 0, 0.9), 0 0 40px rgba(255, 215, 0, 0.5);
            }
        }

        /* Titre principal */
        .loading-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
        }

        /* Sous-titre */
        .loading-subtitle {
            font-size: 1.2rem;
            color: #7f8c8d;
            margin-bottom: 30px;
            opacity: 0.8;
            line-height: 1.4;
        }

        /* Loader animé */
        .loader-wrapper {
            margin-bottom: 25px;
        }

        .loader {
            width: 70px;
            height: 70px;
            margin: 0 auto;
            position: relative;
        }

        .loader-ring {
            width: 100%;
            height: 100%;
            border: 4px solid rgba(102, 126, 234, 0.1);
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Points de chargement */
        .loading-dots {
            display: flex;
            justify-content: center;
            gap: 8px;
        }

        .dot {
            width: 12px;
            height: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            animation: bounce 1.4s ease-in-out infinite both;
        }

        .dot:nth-child(1) { animation-delay: -0.32s; }
        .dot:nth-child(2) { animation-delay: -0.16s; }
        .dot:nth-child(3) { animation-delay: 0s; }

        @keyframes bounce {
            0%, 80%, 100% { 
                transform: scale(0.8) translateY(0);
                opacity: 0.5;
            }
            40% { 
                transform: scale(1.2) translateY(-20px);
                opacity: 1;
            }
        }

        /* Texte de statut */
        .status-text {
            font-size: 1rem;
            color: #5a6c7d;
            margin-bottom: 20px;
            animation: fadeInOut 2s ease-in-out infinite;
        }

        @keyframes fadeInOut {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }

        /* Barre de progression */
        .progress-bar {
            width: 100%;
            height: 6px;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 3px;
            animation: fillProgress 2s ease-out forwards;
        }

        @keyframes fillProgress {
            0% { width: 0%; }
            20% { width: 30%; }
            50% { width: 65%; }
            80% { width: 90%; }
            100% { width: 100%; }
        }

        /* Version mobile */
        @media (max-width: 768px) {
            .loading-container {
                flex-direction: column;
                padding: 40px 30px;
                text-align: center;
                gap: 30px;
            }
            
            .loading-left {
                flex: none;
            }
            
            .loading-right {
                padding-left: 0;
                text-align: center;
            }
            
            .loading-title {
                font-size: 1.8rem;
            }
            
            .loading-subtitle {
                font-size: 1.1rem;
            }
            
            .loading-logo {
                font-size: 3rem;
                margin-bottom: 20px;
            }
        }

        @media (max-width: 480px) {
            .loading-container {
                padding: 30px 20px;
                margin: 20px;
            }
            
            .loading-title {
                font-size: 1.6rem;
            }
            
            .loading-subtitle {
                font-size: 1rem;
            }
            
            .loading-logo {
                font-size: 2.5rem;
            }
        }

        /* Animation d'entrée */
        @keyframes slideInUp {
            0% {
                opacity: 0;
                transform: translateY(50px) scale(0.9);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
    </style>
</head>
<body>
    <!-- Étoiles animées en arrière-plan -->
    <div class="stars" id="stars"></div>

    <div class="loading-container">
        <!-- Section gauche - Animations -->
        <div class="loading-left">
            <!-- Logo animé -->
            <div class="loading-logo">⭐</div>
            
            <!-- Loader circulaire -->
            <div class="loader-wrapper">
                <div class="loader">
                    <div class="loader-ring"></div>
                </div>
            </div>
            
            <!-- Points de chargement -->
            <div class="loading-dots">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
        </div>
        
        <!-- Section droite - Contenu -->
        <div class="loading-right">
            <!-- Titre et sous-titre -->
            <h1 class="loading-title">Chargement de votre profil</h1>
            <p class="loading-subtitle">Bienvenue <?php echo htmlspecialchars($_SESSION['user']['prenom']); ?> ! Préparation de votre espace personnel...</p>
            
            <!-- Barre de progression -->
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
            
            <!-- Texte de statut -->
            <div class="status-text" id="statusText">Connexion en cours...</div>
        </div>
    </div>

    <script>
        // Génération des étoiles
        function createStars() {
            const starsContainer = document.getElementById('stars');
            const numStars = 50;
            
            for (let i = 0; i < numStars; i++) {
                const star = document.createElement('div');
                star.className = 'star';
                star.style.left = Math.random() * 100 + '%';
                star.style.top = Math.random() * 100 + '%';
                star.style.animationDelay = Math.random() * 2 + 's';
                star.style.animationDuration = (Math.random() * 2 + 1) + 's';
                starsContainer.appendChild(star);
            }
        }

        // Changement des textes de statut
        const statusTexts = [
            'Connexion en cours...',
            'Chargement de vos données...',
            'Préparation de l\'interface...',
            'Finalisation...'
        ];

        let currentStatusIndex = 0;
        const statusElement = document.getElementById('statusText');

        function updateStatus() {
            if (currentStatusIndex < statusTexts.length - 1) {
                currentStatusIndex++;
                statusElement.textContent = statusTexts[currentStatusIndex];
            }
        }

        // Initialisation
        createStars();
        
        // Changement de statut toutes les 500ms
        const statusInterval = setInterval(updateStatus, 500);
        
        // Redirection après 2.5 secondes
        setTimeout(() => {
            clearInterval(statusInterval);
            window.location.href = '<?php echo $redirectUrl; ?>';
        }, 2500);
    </script>
</body>
</html>