<?php
// Vue pour afficher la liste des Niveaux.
require_once __DIR__ . '/../includes/check_admin.php';
require_once __DIR__ . '/../controllers/NiveauController.php';

$niveauController = new \Controllers\NiveauController();
$niveaux = $niveauController->getNiveaux();
?>

<?php include __DIR__ . '/../includes/crud_nav.php'; ?>

<div class="crud-container">
    <div class="crud-header">
        <h1 class="page-title">Liste des Niveaux</h1>
        <a href="index.php?action=creer_niveau" class="btn btn-primary">+ Ajouter un niveau</a>
    </div>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'supprime'): ?>
        <div class="alert alert-success">
            <strong>✓ Niveau supprimé avec succès !</strong>
        </div>
    <?php endif; ?>

    <table class="crud-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Libellé</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($niveaux as $niveau): ?>
                <tr>
                    <td><?= htmlspecialchars($niveau['id_niveau']) ?></td>
                    <td><?= htmlspecialchars($niveau['libelle_niveau']) ?></td>
                    <td>
                        <a href="index.php?action=modifier_niveau&id=<?= htmlspecialchars($niveau['id_niveau']) ?>" class="btn btn-primary btn-sm">Modifier</a>
                        <a href="index.php?action=supprimer_niveau&id=<?= htmlspecialchars($niveau['id_niveau']) ?>" class="btn btn-danger btn-sm">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
