<?php
// Vue pour afficher la liste des Agents.
require_once __DIR__ . '/../includes/check_admin.php';
require_once __DIR__ . '/../controllers/AgentController.php';

$agentController = new \Controllers\AgentController();
$agents = $agentController->getAgentsWithDetails();
?>

<?php include __DIR__ . '/../includes/crud_nav.php'; ?>

<div class="crud-container">
    <div class="crud-header">
        <h1 class="page-title">Liste des Agents IA</h1>
        <a href="index.php?action=creer_agent" class="btn btn-primary">+ Ajouter un agent</a>
    </div>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'supprime'): ?>
        <div class="alert alert-success">
            <strong>✓ Agent supprimé avec succès !</strong>
        </div>
    <?php endif; ?>

    <table class="crud-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom Agent</th>
                <th>Type</th>
                <th>Matière</th>
                <th>Créateur</th>
                <th>Actif</th>
                <th>Date Création</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($agents as $agent): ?>
                <tr>
                    <td><?= htmlspecialchars($agent['id_agents']) ?></td>
                    <td><?= htmlspecialchars($agent['nom_agent']) ?></td>
                    <td><?= htmlspecialchars($agent['type_agent']) ?></td>
                    <td><?= htmlspecialchars($agent['nom_matieres'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars(($agent['prenom'] ?? '') . ' ' . ($agent['nom'] ?? '')) ?></td>
                    <td>
                        <?php if ($agent['est_actif']): ?>
                            <span class="badge badge-success">Oui</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Non</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars(date('Y-m-d', strtotime($agent['date_creation']))) ?></td>
                    <td>
                        <a href="index.php?action=modifier_agent&id=<?= htmlspecialchars($agent['id_agents']) ?>" class="btn btn-primary btn-sm">Modifier</a>
                        <a href="index.php?action=supprimer_agent&id=<?= htmlspecialchars($agent['id_agents']) ?>" class="btn btn-danger btn-sm">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
