<!-- Page d'accueil BeautifuLLL AI -->
<div class="accueil-container">

    <!-- Section Hero -->
    <div class="hero-section">
        <h1 class="hero-title">Bienvenue sur BeautifuLLL AI</h1>
        <p class="hero-subtitle">
            Votre plateforme intelligente d'apprentissage propulsee par l'intelligence artificielle
        </p>
        <?php if (!$user): ?>
            <a href="?action=connect" class="hero-cta">
                Commencer maintenant
            </a>
        <?php endif; ?>
    </div>

    <!-- Section Fonctionnalites -->
    <div class="features-section">
        <h2 class="section-title">Fonctionnalites principales</h2>

        <div class="features-grid">
            <!-- Carte 1 -->
            <div class="feature-card">
                <div class="feature-icon icon-ai">IA</div>
                <h3 class="feature-title">Assistant IA Intelligent</h3>
                <p class="feature-description">
                    Beneficiez d'un assistant IA personnalise qui s'adapte a vos matieres et vous accompagne dans votre apprentissage.
                </p>
            </div>

            <!-- Carte 2 -->
            <div class="feature-card">
                <div class="feature-icon icon-manage">+</div>
                <h3 class="feature-title">Gestion des Matieres</h3>
                <p class="feature-description">
                    Organisez vos cours, matieres et niveaux de maniere intuitive pour un apprentissage structure et efficace.
                </p>
            </div>

            <!-- Carte 3 -->
            <div class="feature-card">
                <div class="feature-icon icon-history">...</div>
                <h3 class="feature-title">Conversations Sauvegardees</h3>
                <p class="feature-description">
                    Retrouvez l'historique de vos conversations avec l'IA pour reviser et progresser a votre rythme.
                </p>
            </div>
        </div>
    </div>

    <!-- Section Statistiques -->
    <?php if ($user): ?>
    <div class="stats-section">
        <h2 class="section-title">Votre espace personnalise</h2>
        <div class="stats-grid">
            <div>
                <div class="stat-value">
                    <?= htmlspecialchars($user['prenom'] ?? 'Etudiant') ?>
                </div>
                <div class="stat-label">Bienvenue</div>
            </div>
            <div>
                <div class="stat-value stat-success">
                    <?= htmlspecialchars($user['role'] ?? 'Utilisateur') ?>
                </div>
                <div class="stat-label">Votre role</div>
            </div>
        </div>

        <div class="stats-cta">
            <a href="?action=agent-ia">
                Acceder a l'Assistant IA
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Section A propos -->
    <div class="about-section">
        <h2 class="section-title">A propos de BeautifuLLL AI</h2>
        <p class="about-text">
            BeautifuLLL AI est une plateforme educative moderne qui combine la puissance de l'intelligence artificielle
            avec une interface intuitive pour offrir une experience d'apprentissage personnalisee et efficace.
            Notre mission est de rendre l'education accessible et engageante pour tous.
        </p>
    </div>

</div>
