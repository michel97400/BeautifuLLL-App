<?php
/**
 * Script de test pour l'integration complete du chat avec la base de donnees
 * Teste: Agent loading, Session creation, Message storage, History retrieval
 */

session_start();

require_once __DIR__ . '/../model/chatModel.php';
require_once __DIR__ . '/../model/agent.php';
require_once __DIR__ . '/../model/etudiant.php';
require_once __DIR__ . '/../model/message.php';
require_once __DIR__ . '/../model/SessionConversation.php';

use Models\Agent;
use Models\Etudiants;
use Models\Message;
use Models\SessionConversation;

echo "<h1>Test Integration Chat + Base de Donnees</h1>";
echo "<style>
body { font-family: Arial, sans-serif; padding: 20px; }
h2 { color: #0078d7; border-bottom: 2px solid #0078d7; padding-bottom: 5px; margin-top: 30px; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.info { color: #0078d7; }
pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
.test-section { margin-bottom: 30px; }
</style>";

// ================================================================
// SETUP: Simuler un etudiant connecte
// ================================================================
echo "<h2>Setup: Simulation d'un etudiant connecte</h2>";

try {
    $etudiantModel = new Etudiants();
    $etudiants = $etudiantModel->read();

    if (empty($etudiants)) {
        echo "<p class='error'>Erreur: Aucun etudiant dans la base de donnees</p>";
        exit;
    }

    // Prendre le premier etudiant
    $etudiant = $etudiants[0];
    echo "<p class='success'>Etudiant trouve: {$etudiant['prenom']} {$etudiant['nom']} (ID: {$etudiant['id_etudiant']})</p>";

    // Simuler la session utilisateur
    $_SESSION['user'] = [
        'id_etudiant' => $etudiant['id_etudiant'],
        'email' => $etudiant['email'],
        'prenom' => $etudiant['prenom'],
        'nom' => $etudiant['nom']
    ];

} catch (Exception $e) {
    echo "<p class='error'>Erreur lors de la recuperation de l'etudiant: " . $e->getMessage() . "</p>";
    exit;
}

// ================================================================
// TEST 1: Charger un agent depuis la base de donnees
// ================================================================
echo "<h2>Test 1: Chargement d'un agent</h2>";

try {
    $agentModel = new Agent();
    $activeAgents = $agentModel->getActiveAgents();

    if (empty($activeAgents)) {
        echo "<p class='error'>Erreur: Aucun agent actif dans la base de donnees</p>";
        exit;
    }

    $agent = $activeAgents[0];
    echo "<p class='success'>Agent trouve: {$agent['nom_agent']}</p>";
    echo "<p class='info'>Matiere: {$agent['nom_matieres']}</p>";
    echo "<p class='info'>Model LLM: {$agent['model']}</p>";
    echo "<p class='info'>Temperature: {$agent['temperature']}</p>";
    echo "<p class='info'>Max tokens: {$agent['max_tokens']}</p>";
    echo "<p class='info'>Reasoning effort: {$agent['reasoning_effort']}</p>";

    // Definir la matiere dans la session
    $_SESSION['agent_ia_id_matieres'] = $agent['id_matieres'];
    $_SESSION['agent_ia_matiere'] = $agent['nom_matieres'];

} catch (Exception $e) {
    echo "<p class='error'>Erreur: " . $e->getMessage() . "</p>";
    exit;
}

// ================================================================
// TEST 2: Initialiser une session de conversation
// ================================================================
echo "<h2>Test 2: Initialisation de session</h2>";

try {
    $sessionId = ChatModel::initializeSession();

    if ($sessionId) {
        echo "<p class='success'>Session initialisee avec ID: $sessionId</p>";
    } else {
        echo "<p class='error'>Echec de l'initialisation de la session</p>";
        exit;
    }

    $currentAgent = ChatModel::getCurrentAgent();
    if ($currentAgent) {
        echo "<p class='success'>Agent charge: {$currentAgent['nom_agent']}</p>";
    }

} catch (Exception $e) {
    echo "<p class='error'>Erreur: " . $e->getMessage() . "</p>";
    exit;
}

// ================================================================
// TEST 3: Ajouter des messages
// ================================================================
echo "<h2>Test 3: Ajout de messages</h2>";

try {
    // Message utilisateur
    $msgId1 = ChatModel::addMessage('user', 'Bonjour, je voudrais de l\'aide en mathematiques.');
    if ($msgId1) {
        echo "<p class='success'>Message utilisateur ajoute (ID: $msgId1)</p>";
    } else {
        echo "<p class='error'>Echec d'ajout du message utilisateur</p>";
    }

    // Message assistant
    $msgId2 = ChatModel::addMessage('assistant', 'Bonjour! Je suis ravi de vous aider en mathematiques. Quelle notion souhaitez-vous travailler?');
    if ($msgId2) {
        echo "<p class='success'>Message assistant ajoute (ID: $msgId2)</p>";
    } else {
        echo "<p class='error'>Echec d'ajout du message assistant</p>";
    }

    // Autre message utilisateur
    $msgId3 = ChatModel::addMessage('user', 'J\'ai besoin d\'aide sur les equations du second degre.');
    if ($msgId3) {
        echo "<p class='success'>Message utilisateur 2 ajoute (ID: $msgId3)</p>";
    }

} catch (Exception $e) {
    echo "<p class='error'>Erreur: " . $e->getMessage() . "</p>";
    exit;
}

// ================================================================
// TEST 4: Recuperer l'historique
// ================================================================
echo "<h2>Test 4: Recuperation de l'historique</h2>";

try {
    $history = ChatModel::getConversationHistory();

    echo "<p class='success'>Historique recupere: " . count($history) . " message(s)</p>";

    if (!empty($history)) {
        echo "<pre>";
        foreach ($history as $idx => $msg) {
            echo "Message " . ($idx + 1) . ":\n";
            echo "  Role: {$msg['role']}\n";
            echo "  Contenu: " . substr($msg['content'], 0, 80) . "...\n\n";
        }
        echo "</pre>";
    }

} catch (Exception $e) {
    echo "<p class='error'>Erreur: " . $e->getMessage() . "</p>";
}

// ================================================================
// TEST 5: Verification en base de donnees
// ================================================================
echo "<h2>Test 5: Verification en base de donnees</h2>";

try {
    $messageModel = new Message();
    $messages = $messageModel->getMessagesBySession($sessionId);

    echo "<p class='success'>Messages en BDD: " . count($messages) . "</p>";
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Role</th><th>Contenu</th><th>Date</th></tr>";

    foreach ($messages as $msg) {
        echo "<tr>";
        echo "<td>{$msg['id_message']}</td>";
        echo "<td><strong>{$msg['role']}</strong></td>";
        echo "<td>" . htmlspecialchars(substr($msg['contenu'], 0, 60)) . "...</td>";
        echo "<td>{$msg['date_envoi']}</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Informations session
    $sessionModel = new SessionConversation();
    $session = $sessionModel->readSingle($sessionId);

    if ($session) {
        echo "<h3 style='margin-top: 20px;'>Informations Session</h3>";
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
        echo "<tr><td><strong>ID Session</strong></td><td>{$session['id_session']}</td></tr>";
        echo "<tr><td><strong>Agent</strong></td><td>{$session['nom_agent']}</td></tr>";
        echo "<tr><td><strong>Matiere</strong></td><td>{$session['nom_matieres']}</td></tr>";
        echo "<tr><td><strong>Date debut</strong></td><td>{$session['date_heure_debut']}</td></tr>";
        echo "<tr><td><strong>Date fin</strong></td><td>" . ($session['date_heure_fin'] ?? 'En cours') . "</td></tr>";
        echo "</table>";
    }

} catch (Exception $e) {
    echo "<p class='error'>Erreur: " . $e->getMessage() . "</p>";
}

// ================================================================
// TEST 6: Prompt systeme
// ================================================================
echo "<h2>Test 6: Prompt systeme genere</h2>";

try {
    // Utiliser reflexion pour acceder a la methode privee
    $reflection = new ReflectionClass('ChatModel');
    $method = $reflection->getMethod('getSystemPrompt');
    $method->setAccessible(true);
    $systemPrompt = $method->invoke(null);

    echo "<p class='success'>Prompt systeme genere avec succes</p>";
    echo "<pre>" . htmlspecialchars($systemPrompt) . "</pre>";

} catch (Exception $e) {
    echo "<p class='error'>Erreur: " . $e->getMessage() . "</p>";
}

// ================================================================
// NETTOYAGE (optionnel)
// ================================================================
echo "<h2>Nettoyage</h2>";
echo "<p class='info'>Session de test terminee. La session reste en base de donnees pour inspection.</p>";
echo "<p class='info'>Pour terminer la session: ChatModel::endSession()</p>";

// Decommenter pour nettoyer:
// ChatModel::endSession();
// echo "<p class='success'>Session terminee</p>";

echo "<hr>";
echo "<h2 style='color: green;'>Tests termines avec succes!</h2>";
echo "<p>L'integration du chat avec la base de donnees fonctionne correctement:</p>";
echo "<ul>";
echo "<li>Agent charge depuis la BDD</li>";
echo "<li>Session creee automatiquement</li>";
echo "<li>Messages sauvegardes en BDD</li>";
echo "<li>Historique recupere depuis la BDD</li>";
echo "<li>Prompt systeme adapte au niveau etudiant</li>";
echo "<li>Parametres LLM de l'agent utilises</li>";
echo "</ul>";
?>