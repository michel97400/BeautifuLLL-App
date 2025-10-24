<?php
// Vue pour afficher la liste des Étudiants.
require_once __DIR__ . '/../includes/check_admin.php';
require_once __DIR__ . '/../controllers/EtudiantController.php';

$etudiantController = new \Controllers\EtudiantController();
$etudiants = $etudiantController->getEtudiantsWithDetails();
?>

<?php include __DIR__ . '/../includes/crud_nav.php'; ?>

<div class="crud-container">
    <div class="crud-header">
        <h1 class="page-title">Liste des Étudiants</h1>
        <a href="index.php?action=creer_etudiant" class="btn btn-primary">+ Ajouter un étudiant</a>
    </div>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'supprime'): ?>
        <div class="alert alert-success">
            <strong>✓ Étudiant supprimé avec succès !</strong>
        </div>
    <?php endif; ?>

    <table class="crud-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Niveau</th>
                <th>Rôle</th>
                <th>RGPD</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($etudiants as $etudiant): ?>
                <tr>
                    <td><?= htmlspecialchars($etudiant['id_etudiant']) ?></td>
                    <td><?= htmlspecialchars($etudiant['nom']) ?></td>
                    <td><?= htmlspecialchars($etudiant['prenom']) ?></td>
                    <td><?= htmlspecialchars($etudiant['email']) ?></td>
                    <td><?= htmlspecialchars($etudiant['niveau'] ?? $etudiant['id_niveau']) ?></td>
                    <td><?= htmlspecialchars($etudiant['role'] ?? $etudiant['id_role']) ?></td>
                    <td>
                        <?php if ($etudiant['consentement_rgpd']): ?>
                            <span class="badge badge-success">Oui</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Non</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="index.php?action=modifier_etudiant&id=<?= htmlspecialchars($etudiant['id_etudiant']) ?>" class="btn btn-primary btn-sm">Modifier</a>
                        <a href="index.php?action=supprimer_etudiant&id=<?= htmlspecialchars($etudiant['id_etudiant']) ?>" class="btn btn-danger btn-sm">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>