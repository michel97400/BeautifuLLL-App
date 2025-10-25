<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;
?>

<div style="max-width: 600px; margin: 50px auto; padding: 30px; border: 2px solid #dc3545; border-radius: 10px; background-color: #f8d7da; text-align: center;">
    <h1 style="color: #dc3545; font-size: 48px; margin-bottom: 20px;">Accès refusé</h1>

    <p style="font-size: 18px; color: #721c24; margin-bottom: 20px;">
        Vous n'avez pas les droits nécessaires pour accéder à cette page.
    </p>

    <?php if ($user): ?>
        <p style="font-size: 16px; color: #721c24; margin-bottom: 30px;">
            Vous êtes connecté en tant que <strong><?= htmlspecialchars($user['prenom'] ?? $user['nom'] ?? $user['email']) ?></strong><br>
            Rôle : <strong><?= htmlspecialchars($user['role'] ?? 'Non défini') ?></strong>
        </p>
        <p style="font-size: 14px; color: #856404; margin-bottom: 30px;">
            Cette section est réservée aux administrateurs.
        </p>
    <?php else: ?>
        <p style="font-size: 16px; color: #721c24; margin-bottom: 30px;">
            Vous devez être connecté avec un compte administrateur pour accéder à cette section.
        </p>
    <?php endif; ?>

    <div style="margin-top: 30px;">
        <a href="index.php"
           style="display: inline-block; background-color: #0078d7; color: white; padding: 12px 30px; border-radius: 5px; text-decoration: none; font-size: 16px; margin-right: 10px;">
            Retour à l'accueil
        </a>

        <?php if (!$user): ?>
            <a href="index.php?action=connect"
               style="display: inline-block; background-color: #28a745; color: white; padding: 12px 30px; border-radius: 5px; text-decoration: none; font-size: 16px;">
                Se connecter
            </a>
        <?php endif; ?>
    </div>
</div>
