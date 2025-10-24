<?php
// Vue pour afficher la liste des Sessions de Conversation.
require_once __DIR__ . '/../controllers/SessionConversationController.php';


$sessionController = new \Controllers\SessionConversationController();
// Cette méthode devrait joindre Agent et Etudiants.
$sessions = $sessionController->getSessionsWithDetails(); 

echo "<h1>Liste des Sessions de Conversation</h1>";
echo "<table>";
echo "<tr><th>ID</th><th>Début</th><th>Fin</th><th>Durée</th><th>Agent</th><th>Étudiant</th><th>Actions</th></tr>";

foreach ($sessions as $session) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($session['id_session']) . "</td>";
    echo "<td>" . htmlspecialchars(date('Y-m-d H:i:s', strtotime($session['date_heure_debut']))) . "</td>";
    echo "<td>" . htmlspecialchars($session['date_heure_fin'] ? date('Y-m-d H:i:s', strtotime($session['date_heure_fin'])) : 'En cours') . "</td>";
    echo "<td>" . htmlspecialchars($session['duree_session'] ?? 'N/A') . "</td>";
    // Affichage des jointures
    echo "<td>" . htmlspecialchars($session['nom_agent'] ?? $session['id_agents']) . "</td>"; 
    echo "<td>" . htmlspecialchars(($session['prenom'] ?? '') . ' ' . ($session['nom'] ?? $session['id_etudiant'])) . "</td>";
    echo "<td><a href='messages_session.php?id=" . htmlspecialchars($session['id_session']) . "'>Voir Messages</a> | <a href='session_delete.php?id=" . htmlspecialchars($session['id_session']) . "'>Supprimer</a></td>";
    echo "</tr>";
}
echo "</table>";
?>