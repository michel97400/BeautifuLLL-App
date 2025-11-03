

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../model/etudiant.php';
require_once __DIR__ . '/../../model/niveau.php';
use Models\Etudiants;
use Models\Niveau;

$matiereChoisie = $_SESSION['agent_ia_matiere'] ?? null;
$user = $_SESSION['user'] ?? null;
$niveauLibelle = null;
if ($user && isset($user['email'])) {
    $etudiantModel = new Etudiants();
    $etudiant = $etudiantModel->readByEmail($user['email']);
    $niveauId = $etudiant['id_niveau'] ?? null;
    
    // Récupérer le libellé du niveau
    if ($niveauId) {
        $niveauModel = new Niveau();
        $niveauData = $niveauModel->readSingle($niveauId);
        $niveauLibelle = $niveauData['libelle_niveau'] ?? null;
    }
}

if ($matiereChoisie) {
    // Affiche la card du chat IA
    echo '<div class="chat-card-container">';
    echo '<div class="chat-header">';
    echo '<div class="chat-header-info">';
    echo '<h2 class="chat-title">Agent IA - Chat</h2>';
    echo '<div class="chat-subtitle">Matière : <strong>' . htmlspecialchars($matiereChoisie) . '</strong>';
    if ($niveauLibelle) echo ' | Niveau : <strong>' . htmlspecialchars($niveauLibelle) . '</strong>';
    echo '</div>';
    echo '</div>';
    echo '<form method="post" class="chat-header-form">';
    echo '<input type="hidden" name="reset_matiere" value="1">';
    echo '<button type="submit" class="btn btn-secondary">Changer de matière</button>';
    echo '</form>';
    echo '</div>';
    echo '<div class="chat-body">';
    echo '<div id="chat-history" class="chat-history"></div>';
    echo '<div id="chat-error" class="chat-error"></div>';
    echo '</div>';
    echo '<form id="chat-form" class="chat-form">';
    echo '<textarea id="message" name="message" rows="2" class="chat-input" placeholder="Écrivez votre message..."></textarea>';
    echo '<button type="submit" class="btn btn-primary chat-submit">Envoyer</button>';
    echo '</form>';
    echo '</div>';
    echo '<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>';
    echo '<script src="php-crud/public/chat.js"></script>';
    // Traitement du POST pour reset
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_matiere'])) {
        unset($_SESSION['agent_ia_matiere']);
        echo '<script>window.location.href = "index.php?action=agent-ia";</script>';
        exit;
    }
}
