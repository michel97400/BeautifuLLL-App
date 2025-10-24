<?php
// Vue pour afficher la liste des Sessions de Conversation.
require_once __DIR__ . '/../includes/check_admin.php';
require_once __DIR__ . '/../controllers/SessionConversationController.php';

$sessionController = new \Controllers\SessionConversationController();
$sessions = $sessionController->getSessionsWithDetails();
?>

<?php include __DIR__ . '/../includes/crud_nav.php'; ?>

<div class="crud-container">
    <div class="crud-header">
        <h1 class="page-title">Liste des Sessions de Conversation</h1>
        <a href="index.php?action=creer_session" class="btn btn-primary">+ Ajouter une session</a>
    </div>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'supprime'): ?>
        <div class="alert alert-success">
            <strong>✓ Session supprimée avec succès !</strong>
        </div>
    <?php endif; ?>

    <table class="crud-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Début</th>
                <th>Fin</th>
                <th>Durée</th>
                <th>Agent</th>
                <th>Étudiant</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sessions as $session): ?>
                <tr>
                    <td><?= htmlspecialchars($session['id_session']) ?></td>
                    <td><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($session['date_heure_debut']))) ?></td>
                    <td>
                        <?php if ($session['date_heure_fin']): ?>
                            <?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($session['date_heure_fin']))) ?>
                        <?php else: ?>
                            <span class="badge badge-primary">En cours</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($session['duree_session'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($session['nom_agent'] ?? $session['id_agents']) ?></td>
                    <td><?= htmlspecialchars(($session['prenom'] ?? '') . ' ' . ($session['nom'] ?? $session['id_etudiant'])) ?></td>
                    <td>
                        <a href="index.php?action=modifier_session&id=<?= htmlspecialchars($session['id_session']) ?>" class="btn btn-primary btn-sm">Modifier</a>
                        <a href="index.php?action=supprimer_session&id=<?= htmlspecialchars($session['id_session']) ?>" class="btn btn-danger btn-sm">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
