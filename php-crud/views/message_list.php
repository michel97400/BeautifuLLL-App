<?php
// Vue pour afficher la liste des Messages.
require_once __DIR__ . '/../includes/check_admin.php';
require_once __DIR__ . '/../controllers/MessageController.php';

$messageController = new \Controllers\MessageController();
$messages = $messageController->getMessages();
?>

<?php include __DIR__ . '/../includes/crud_nav.php'; ?>

<div class="crud-container">
    <div class="crud-header">
        <h1 class="page-title">Liste des Messages</h1>
        <a href="index.php?action=creer_message" class="btn btn-primary">+ Ajouter un message</a>
    </div>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'supprime'): ?>
        <div class="alert alert-success">
            <strong>✓ Message supprimé avec succès !</strong>
        </div>
    <?php endif; ?>

    <table class="crud-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Session</th>
                <th>Rôle</th>
                <th>Contenu</th>
                <th>Date Envoi</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($messages as $message): ?>
                <?php
                // Utiliser une classe CSS pour différencier les rôles (user/assistant)
                $rowClass = $message['role_message'] === 'user' ? 'message-user' : 'message-assistant';
                ?>
                <tr class="<?= $rowClass ?>">
                    <td><?= htmlspecialchars($message['id_message']) ?></td>
                    <td><?= htmlspecialchars($message['id_session']) ?></td>
                    <td>
                        <?php if ($message['role_message'] === 'user'): ?>
                            <span class="badge badge-primary">User</span>
                        <?php else: ?>
                            <span class="badge badge-success">Assistant</span>
                        <?php endif; ?>
                    </td>
                    <td><?= nl2br(htmlspecialchars(substr($message['contenu'], 0, 100))) ?><?= strlen($message['contenu']) > 100 ? '...' : '' ?></td>
                    <td><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($message['date_envoi']))) ?></td>
                    <td>
                        <a href="index.php?action=modifier_message&id=<?= htmlspecialchars($message['id_message']) ?>" class="btn btn-primary btn-sm">Modifier</a>
                        <a href="index.php?action=supprimer_message&id=<?= htmlspecialchars($message['id_message']) ?>" class="btn btn-danger btn-sm">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
.message-user { background-color: #e0f7fa; }
.message-assistant { background-color: #fff3e0; }
</style>
