

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../model/etudiant.php';
use Models\Etudiants;

$matiereChoisie = $_SESSION['agent_ia_matiere'] ?? null;
$user = $_SESSION['user'] ?? null;
$niveau = null;
if ($user && isset($user['email'])) {
    $etudiantModel = new Etudiants();
    $etudiant = $etudiantModel->readByEmail($user['email']);
    $niveau = $etudiant['id_niveau'] ?? null;
}

if ($matiereChoisie) {
    // Affiche la card du chat IA
    echo '<div class="crud-card chat-card" style="max-width: 600px; min-height: 500px; margin: 40px auto; display: flex; flex-direction: column; box-shadow: 0 4px 16px rgba(0,0,0,0.12);">';
    echo '<div class="chat-header" style="background: #0078d7; color: #fff; padding: 24px 32px; border-radius: 12px 12px 0 0; display: flex; justify-content: space-between; align-items: center;">';
    echo '<div>';
    echo '<h2 style="margin:0; font-size: 1.7rem; font-weight: 600; letter-spacing: 1px;">Agent IA - Chat</h2>';
    echo '<div style="margin-top:8px; font-size:1rem; color:#e7f3ff;">Matière : <strong>' . htmlspecialchars($matiereChoisie) . '</strong>';
    if ($niveau) echo ' | Niveau : <strong>' . htmlspecialchars($niveau) . '</strong>';
    echo '</div>';
    echo '</div>';
    echo '<form method="post" style="margin-left:auto;">';
    echo '<input type="hidden" name="reset_matiere" value="1">';
    echo '<button type="submit" class="btn btn-secondary" style="margin-left:12px;">Changer de matière</button>';
    echo '</form>';
    echo '</div>';
    echo '<div class="chat-body" style="flex:1; padding: 24px 32px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; background: #f8f9fa;">';
    echo '<div id="chat-history" class="chat-history" style="flex:1; display: flex; flex-direction: column; gap: 10px;"></div>';
    echo '<div id="chat-error" style="color: #dc3545; margin-top: 8px;"></div>';
    echo '</div>';
    echo '<form id="chat-form" style="padding: 20px 32px 24px 32px; background: #fff; border-radius: 0 0 12px 12px; box-shadow: 0 -2px 8px rgba(0,0,0,0.04); display: flex; gap: 12px; align-items: flex-end;">';
    echo '<textarea id="message" name="message" rows="2" placeholder="Écrivez votre message..." style="flex:1; border-radius: 8px; border: 1px solid #e0e0e0; font-size: 1rem; background: #fafbfc; padding: 10px 12px; resize: none;"></textarea>';
    echo '<button type="submit" class="btn btn-primary" style="padding: 12px 24px; font-size: 1.1rem; border-radius: 8px;">Envoyer</button>';
    echo '</form>';
    echo '</div>';
    echo '<script src="php-crud/public/chat.js"></script>';
    // Traitement du POST pour reset
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_matiere'])) {
        unset($_SESSION['agent_ia_matiere']);
        echo '<script>window.location.href = "index.php?action=agent-ia";</script>';
        exit;
    }
}
