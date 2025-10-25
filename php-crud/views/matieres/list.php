<?php
// Vue pour afficher la liste des Matières.
require_once __DIR__ . '/../../includes/check_admin.php';
require_once __DIR__ . '/../../controllers/MatiereController.php';

$matiereController = new \Controllers\MatiereController();
$matieres = $matiereController->getMatiere();
?>

<?php include __DIR__ . '/../../includes/crud_nav.php'; ?>

<div class="crud-container">
    <div class="crud-header">
        <h1 class="page-title">Liste des Matières</h1>
        <a href="index.php?action=creer_matiere" class="btn btn-primary">+ Ajouter une matière</a>
    </div>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'supprime'): ?>
        <div class="alert alert-success">
            <strong>✓ Matière supprimée avec succès !</strong>
        </div>
    <?php endif; ?>

    <table class="crud-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($matieres as $matiere): ?>
                <tr>
                    <td><?= htmlspecialchars($matiere['id_matieres']) ?></td>
                    <td><?= htmlspecialchars($matiere['nom_matieres']) ?></td>
                    <td>
                        <?php
                        $description = $matiere['description_matiere'] ?? '';
                        echo htmlspecialchars(strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description);
                        ?>
                    </td>
                    <td>
                        <a href="index.php?action=modifier_matiere&id=<?= htmlspecialchars($matiere['id_matieres']) ?>" class="btn btn-primary btn-sm">Modifier</a>
                        <a href="index.php?action=supprimer_matiere&id=<?= htmlspecialchars($matiere['id_matieres']) ?>" class="btn btn-danger btn-sm">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
