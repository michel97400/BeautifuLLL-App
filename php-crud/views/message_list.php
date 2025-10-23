<?php
// Vue pour afficher la liste des Rôles.
require_once __DIR__ . '/../php-crud/controllers/MessageController.php';


// Récupération de l'ID de session depuis l'URL
$sessionId = $_GET['id'] ?? null;

if (!$sessionId) {
    echo "<p>ID de session manquant.</p>";
    exit;
}

$messageController = new \Controllers\MessageController();
// Cette méthode DOIT filtrer par id_session.
$messages = $messageController->getMessagesBySessionId($sessionId); 

echo "<h1>Messages de la Session # " . htmlspecialchars($sessionId) . "</h1>";
echo "<a href='sessions_list.php'>Retour aux Sessions</a>";
echo "<table>";
echo "<tr><th>Rôle</th><th>Contenu</th><th>Date d'Envoi</th><th>Actions</th></tr>";

foreach ($messages as $message) {
    // Utiliser une classe CSS pour différencier les rôles (user/assistant)
    $rowClass = $message['role'] === 'user' ? 'message-user' : 'message-assistant';
    
    echo "<tr class='" . $rowClass . "'>";
    echo "<td>" . htmlspecialchars($message['role']) . "</td>";
    echo "<td>" . nl2br(htmlspecialchars($message['contenu'])) . "</td>"; // nl2br pour les sauts de ligne
    echo "<td>" . htmlspecialchars(date('H:i:s', strtotime($message['date_envoi']))) . "</td>";
    echo "<td><a href='message_delete.php?id=" . htmlspecialchars($message['id_message']) . "'>Supprimer</a></td>";
    echo "</tr>";
}
echo "</table>";
?>
<style>
.message-user { background-color: #e0f7fa; }
.message-assistant { background-color: #fff3e0; }
</style>