<?php
// Vue pour afficher la liste des Agents.
require_once __DIR__ . '/../controllers/AgentController.php';

$agentController = new \Controllers\AgentController();
$agents = $agentController->getAgentsWithDetails(); 

echo "<h1>Liste des Agents</h1>";
echo "<table>";
echo "<tr><th>ID</th><th>Nom Agent</th><th>Type</th><th>Matière</th><th>Créateur</th><th>Actif</th><th>Date Création</th><th>Actions</th></tr>";

foreach ($agents as $agent) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($agent['id_agents']) . "</td>";
    echo "<td>" . htmlspecialchars($agent['nom_agent']) . "</td>";
    echo "<td>" . htmlspecialchars($agent['type_agent']) . "</td>";
    // Affichage des jointures
    echo "<td>" . htmlspecialchars($agent['nom_matieres'] ?? 'N/A') . "</td>"; 
    echo "<td>" . htmlspecialchars(($agent['prenom'] ?? '') . ' ' . ($agent['nom'] ?? '')) . "</td>";
    echo "<td>" . ($agent['est_actif'] ? 'Oui' : 'Non') . "</td>";
    echo "<td>" . htmlspecialchars(date('Y-m-d', strtotime($agent['date_creation']))) . "</td>";
    echo "<td><a href='agent_update.php?id=" . htmlspecialchars($agent['id_agents']) . "'>Modifier</a> | <a href='agent_delete.php?id=" . htmlspecialchars($agent['id_agents']) . "'>Supprimer</a></td>";
    echo "</tr>";
}
echo "</table>";
?>