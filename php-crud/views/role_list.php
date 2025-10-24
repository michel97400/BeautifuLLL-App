<?php
// Vue pour afficher la liste des Rôles.
require_once __DIR__ . '/../includes/check_admin.php';
require_once __DIR__ . '/../controllers/RoleController.php';

$roleController = new \Controllers\RoleController();
$roles = $roleController->getRoles();
?>

<?php include __DIR__ . '/../includes/crud_nav.php'; ?>

<div class="crud-container">
    <div class="crud-header">
        <h1 class="page-title">Liste des Rôles</h1>
        <a href="index.php?action=creer_role" class="btn btn-primary">+ Ajouter un rôle</a>
    </div>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'supprime'): ?>
        <div class="alert alert-success">
            <strong>✓ Rôle supprimé avec succès !</strong>
        </div>
    <?php endif; ?>

    <table class="crud-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom du rôle</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($roles as $role): ?>
                <tr>
                    <td><?= htmlspecialchars($role['id_role']) ?></td>
                    <td><?= htmlspecialchars($role['nom_role']) ?></td>
                    <td>
                        <a href="index.php?action=modifier_role&id=<?= htmlspecialchars($role['id_role']) ?>" class="btn btn-primary btn-sm">Modifier</a>
                        <a href="index.php?action=supprimer_role&id=<?= htmlspecialchars($role['id_role']) ?>" class="btn btn-danger btn-sm">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
