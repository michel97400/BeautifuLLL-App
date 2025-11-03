<!-- Page d'accueil BeautifuLLL AI -->
<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">

    <!-- Section Hero -->
    <div style="background: linear-gradient(135deg, #0078d7 0%, #005fa3 100%); border-radius: 16px; padding: 60px 40px; text-align: center; color: white; box-shadow: 0 4px 24px rgba(0, 120, 215, 0.2); margin-bottom: 40px;">
        <h1 style="font-size: 3rem; margin: 0 0 20px 0; font-weight: 700;">Bienvenue sur BeautifuLLL AI</h1>
        <p style="font-size: 1.3rem; margin: 0 0 30px 0; opacity: 0.95; max-width: 700px; margin-left: auto; margin-right: auto;">
            Votre plateforme intelligente d'apprentissage propulsee par l'intelligence artificielle
        </p>
        <?php if (!$user): ?>
            <a href="?action=connect" style="display: inline-block; background: white; color: #0078d7; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 1.1rem; transition: transform 0.2s; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                Commencer maintenant
            </a>
        <?php endif; ?>
    </div>

    <!-- Section Fonctionnalites -->
    <div style="margin-bottom: 50px;">
        <h2 style="text-align: center; font-size: 2rem; margin-bottom: 40px; color: #222;">Fonctionnalites principales</h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
            <!-- Carte 1 -->
            <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 24px rgba(0,0,0,0.12)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 16px rgba(0,0,0,0.08)';">
                <div style="width: 60px; height: 60px; background: #0078d7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; color: white; font-size: 1.8rem; font-weight: bold;">IA</div>
                <h3 style="color: #0078d7; margin: 0 0 12px 0; font-size: 1.4rem;">Assistant IA Intelligent</h3>
                <p style="color: #666; line-height: 1.6; margin: 0;">
                    Beneficiez d'un assistant IA personnalise qui s'adapte a vos matieres et vous accompagne dans votre apprentissage.
                </p>
            </div>

            <!-- Carte 2 -->
            <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 24px rgba(0,0,0,0.12)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 16px rgba(0,0,0,0.08)';">
                <div style="width: 60px; height: 60px; background: #28a745; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; color: white; font-size: 2rem; font-weight: bold;">+</div>
                <h3 style="color: #0078d7; margin: 0 0 12px 0; font-size: 1.4rem;">Gestion des Matieres</h3>
                <p style="color: #666; line-height: 1.6; margin: 0;">
                    Organisez vos cours, matieres et niveaux de maniere intuitive pour un apprentissage structure et efficace.
                </p>
            </div>

            <!-- Carte 3 -->
            <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 24px rgba(0,0,0,0.12)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 16px rgba(0,0,0,0.08)';">
                <div style="width: 60px; height: 60px; background: #17a2b8; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; color: white; font-size: 1.2rem; font-weight: bold;">...</div>
                <h3 style="color: #0078d7; margin: 0 0 12px 0; font-size: 1.4rem;">Conversations Sauvegardees</h3>
                <p style="color: #666; line-height: 1.6; margin: 0;">
                    Retrouvez l'historique de vos conversations avec l'IA pour reviser et progresser a votre rythme.
                </p>
            </div>
        </div>
    </div>

    <!-- Section Statistiques -->
    <?php if ($user): ?>
    <div style="background: white; border-radius: 12px; padding: 40px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); margin-bottom: 40px;">
        <h2 style="text-align: center; font-size: 1.8rem; margin-bottom: 30px; color: #222;">Votre espace personnalise</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; text-align: center;">
            <div>
                <div style="font-size: 2.5rem; font-weight: 700; color: #0078d7;">
                    <?= htmlspecialchars($user['prenom'] ?? 'Etudiant') ?>
                </div>
                <div style="color: #888; margin-top: 8px;">Bienvenue</div>
            </div>
            <div>
                <div style="font-size: 2.5rem; font-weight: 700; color: #28a745;">
                    <?= htmlspecialchars($user['role'] ?? 'Utilisateur') ?>
                </div>
                <div style="color: #888; margin-top: 8px;">Votre role</div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="?action=agent-ia" style="display: inline-block; background: #0078d7; color: white; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: background 0.2s;">
                Acceder a l'Assistant IA
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Section A propos -->
    <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 12px; padding: 40px; text-align: center;">
        <h2 style="font-size: 1.8rem; margin-bottom: 20px; color: #222;">A propos de BeautifuLLL AI</h2>
        <p style="color: #555; line-height: 1.8; max-width: 800px; margin: 0 auto; font-size: 1.1rem;">
            BeautifuLLL AI est une plateforme educative moderne qui combine la puissance de l'intelligence artificielle
            avec une interface intuitive pour offrir une experience d'apprentissage personnalisee et efficace.
            Notre mission est de rendre l'education accessible et engageante pour tous.
        </p>
    </div>

</div>
