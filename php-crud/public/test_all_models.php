<?php
/**
 * Fichier de test complet pour tous les modèles
 * Tests CRUD sur tous les modèles de l'application
 * Base de données: db_app_educia
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/role.php';
require_once __DIR__ . '/../model/niveau.php';
require_once __DIR__ . '/../model/matieres.php';
require_once __DIR__ . '/../model/etudiant.php';
require_once __DIR__ . '/../model/agent.php';
require_once __DIR__ . '/../model/session_conversation.php';
require_once __DIR__ . '/../model/message.php';

use Models\Role;
use Models\Niveau;
use Models\Matiere;
use Models\Etudiants;
use Models\Agent;
use Models\session_conversation;
use Models\Message;

// Configuration HTML
header('Content-Type: text/html; charset=utf-8');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Complet - Tous les Modèles</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 2.5em; margin-bottom: 10px; }
        .header p { font-size: 1.1em; opacity: 0.9; }

        .content { padding: 30px; }

        .test-section {
            margin-bottom: 40px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
        }
        .test-section-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            font-size: 1.4em;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .test-section-body { padding: 20px; }

        .status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
        }
        .status.success { background: #4caf50; color: white; }
        .status.error { background: #f44336; color: white; }
        .status.info { background: #2196f3; color: white; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table tbody tr:hover { background: #f5f5f5; }
        table tbody tr:nth-child(even) { background: #fafafa; }

        .test-result {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            border-left: 4px solid #667eea;
        }
        .test-result h4 {
            color: #667eea;
            margin-bottom: 10px;
        }
        .test-result pre {
            background: white;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 0.9em;
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #f44336;
            margin-top: 10px;
        }

        .summary {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .summary-item {
            text-align: center;
            padding: 15px;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
        }
        .summary-item h3 { font-size: 2em; margin-bottom: 5px; }
        .summary-item p { font-size: 1em; opacity: 0.9; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Test Complet des Modèles</h1>
            <p>Application BeautifuLLL - Tests CRUD sur tous les modèles</p>
            <p style="font-size: 0.9em; margin-top: 10px;">Base de données: db_app_educia</p>
        </div>

        <div class="content">

<?php

// Compteurs de tests
$total_tests = 0;
$tests_reussis = 0;
$tests_echoues = 0;

// Fonction utilitaire pour afficher les résultats
function displayResult($title, $success, $data = null, $error = null) {
    global $total_tests, $tests_reussis, $tests_echoues;
    $total_tests++;

    if ($success) {
        $tests_reussis++;
        echo "<div class='test-result'>";
        echo "<h4>✅ $title</h4>";
        if ($data !== null) {
            echo "<pre>" . print_r($data, true) . "</pre>";
        }
        echo "</div>";
    } else {
        $tests_echoues++;
        echo "<div class='error-message'>";
        echo "<h4>❌ $title</h4>";
        echo "<p><strong>Erreur:</strong> " . htmlspecialchars($error) . "</p>";
        echo "</div>";
    }
}

// ========================================
// TEST 1: MODÈLE ROLE
// ========================================
echo "<div class='test-section'>";
echo "<div class='test-section-header'>";
echo "<span>1. Test du Modèle ROLE</span>";
echo "</div>";
echo "<div class='test-section-body'>";

try {
    $roleModel = new Role();
    $roles = $roleModel->read_role();

    if ($roles && count($roles) > 0) {
        displayResult("Lecture des rôles", true);
        echo "<table>";
        echo "<thead><tr><th>ID</th><th>Nom du Rôle</th></tr></thead>";
        echo "<tbody>";
        foreach ($roles as $role) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($role['id_role']) . "</td>";
            echo "<td>" . htmlspecialchars($role['nom_role']) . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        displayResult("Lecture des rôles", false, null, "Aucun rôle trouvé dans la base de données");
    }
} catch (Exception $e) {
    displayResult("Test du modèle Role", false, null, $e->getMessage());
}

echo "</div></div>";

// ========================================
// TEST 2: MODÈLE NIVEAU
// ========================================
echo "<div class='test-section'>";
echo "<div class='test-section-header'>";
echo "<span>2. Test du Modèle NIVEAU</span>";
echo "</div>";
echo "<div class='test-section-body'>";

try {
    $niveauModel = new Niveau();
    $niveaux = $niveauModel->read();

    if ($niveaux && count($niveaux) > 0) {
        displayResult("Lecture des niveaux scolaires", true);
        echo "<table>";
        echo "<thead><tr><th>ID</th><th>Libellé</th></tr></thead>";
        echo "<tbody>";
        foreach ($niveaux as $niveau) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($niveau['id_niveau']) . "</td>";
            echo "<td>" . htmlspecialchars($niveau['libelle_niveau']) . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        displayResult("Lecture des niveaux", false, null, "Aucun niveau trouvé");
    }
} catch (Exception $e) {
    displayResult("Test du modèle Niveau", false, null, $e->getMessage());
}

echo "</div></div>";

// ========================================
// TEST 3: MODÈLE MATIERES
// ========================================
echo "<div class='test-section'>";
echo "<div class='test-section-header'>";
echo "<span>3. Test du Modèle MATIERES</span>";
echo "</div>";
echo "<div class='test-section-body'>";

try {
    $matiereModel = new Matiere();
    $matieres = $matiereModel->read();

    if ($matieres && count($matieres) > 0) {
        displayResult("Lecture des matières", true);
        echo "<table>";
        echo "<thead><tr><th>ID</th><th>Nom</th><th>Description</th></tr></thead>";
        echo "<tbody>";
        foreach ($matieres as $matiere) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($matiere['id_matieres']) . "</td>";
            echo "<td>" . htmlspecialchars($matiere['nom_matieres']) . "</td>";
            echo "<td>" . htmlspecialchars($matiere['description_matiere']) . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        displayResult("Lecture des matières", false, null, "Aucune matière trouvée");
    }
} catch (Exception $e) {
    displayResult("Test du modèle Matiere", false, null, $e->getMessage());
}

echo "</div></div>";

// ========================================
// TEST 4: MODÈLE ETUDIANTS
// ========================================
echo "<div class='test-section'>";
echo "<div class='test-section-header'>";
echo "<span>4. Test du Modèle ETUDIANTS</span>";
echo "</div>";
echo "<div class='test-section-body'>";

try {
    $etudiantModel = new Etudiants();
    $etudiants = $etudiantModel->read();

    if ($etudiants && count($etudiants) > 0) {
        displayResult("Lecture des étudiants", true);
        echo "<table>";
        echo "<thead><tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Date Inscription</th><th>RGPD</th></tr></thead>";
        echo "<tbody>";
        foreach ($etudiants as $etudiant) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($etudiant['id_etudiant']) . "</td>";
            echo "<td>" . htmlspecialchars($etudiant['nom']) . "</td>";
            echo "<td>" . htmlspecialchars($etudiant['prenom']) . "</td>";
            echo "<td>" . htmlspecialchars($etudiant['email']) . "</td>";
            echo "<td>" . htmlspecialchars($etudiant['date_inscription']) . "</td>";
            echo "<td>" . ($etudiant['consentement_rgpd'] ? '✅' : '❌') . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        displayResult("Lecture des étudiants", false, null, "Aucun étudiant trouvé");
    }
} catch (Exception $e) {
    displayResult("Test du modèle Etudiants", false, null, $e->getMessage());
}

echo "</div></div>";

// ========================================
// TEST 5: MODÈLE AGENT
// ========================================
echo "<div class='test-section'>";
echo "<div class='test-section-header'>";
echo "<span>5. Test du Modèle AGENT</span>";
echo "</div>";
echo "<div class='test-section-body'>";

try {
    $agentModel = new Agent();
    $agents = $agentModel->read();

    if ($agents && count($agents) > 0) {
        displayResult("Lecture des agents IA", true);
        echo "<table>";
        echo "<thead><tr><th>ID</th><th>Nom</th><th>Type</th><th>Description</th><th>Actif</th><th>Date Création</th></tr></thead>";
        echo "<tbody>";
        foreach ($agents as $agent) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($agent['id_agents']) . "</td>";
            echo "<td>" . htmlspecialchars($agent['nom_agent']) . "</td>";
            echo "<td>" . htmlspecialchars($agent['type_agent']) . "</td>";
            echo "<td>" . htmlspecialchars(substr($agent['description'], 0, 50)) . "...</td>";
            echo "<td>" . ($agent['est_actif'] ? '✅' : '❌') . "</td>";
            echo "<td>" . htmlspecialchars($agent['date_creation']) . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        displayResult("Lecture des agents", false, null, "Aucun agent trouvé");
    }

    // Test de lecture d'un agent spécifique
    if ($agents && count($agents) > 0) {
        $firstAgentId = $agents[0]['id_agents'];
        $singleAgent = $agentModel->readSingle($firstAgentId);
        if ($singleAgent) {
            displayResult("Lecture d'un agent spécifique (ID: $firstAgentId)", true, $singleAgent);
        }
    }

} catch (Exception $e) {
    displayResult("Test du modèle Agent", false, null, $e->getMessage());
}

echo "</div></div>";

// ========================================
// TEST 6: MODÈLE SESSION_CONVERSATION
// ========================================
echo "<div class='test-section'>";
echo "<div class='test-section-header'>";
echo "<span>6. Test du Modèle SESSION_CONVERSATION</span>";
echo "</div>";
echo "<div class='test-section-body'>";

try {
    $sessionModel = new session_conversation();
    $sessions = $sessionModel->read();

    if ($sessions && count($sessions) > 0) {
        displayResult("Lecture des sessions de conversation", true);
        echo "<table>";
        echo "<thead><tr><th>ID Session</th><th>Début</th><th>Fin</th><th>Durée</th><th>ID Agent</th><th>ID Étudiant</th></tr></thead>";
        echo "<tbody>";
        foreach ($sessions as $session) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($session['id_session']) . "</td>";
            echo "<td>" . htmlspecialchars($session['date_heure_debut']) . "</td>";
            echo "<td>" . htmlspecialchars($session['date_heure_fin'] ?? 'En cours') . "</td>";
            echo "<td>" . htmlspecialchars($session['duree_session'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($session['id_agents']) . "</td>";
            echo "<td>" . htmlspecialchars($session['id_etudiant']) . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        displayResult("Lecture des sessions", false, null, "Aucune session trouvée");
    }
} catch (Exception $e) {
    displayResult("Test du modèle Session_conversation", false, null, $e->getMessage());
}

echo "</div></div>";

// ========================================
// TEST 7: MODÈLE MESSAGE
// ========================================
echo "<div class='test-section'>";
echo "<div class='test-section-header'>";
echo "<span>7. Test du Modèle MESSAGE</span>";
echo "</div>";
echo "<div class='test-section-body'>";

try {
    $messageModel = new Message();
    $messages = $messageModel->read_role();

    if ($messages && count($messages) > 0) {
        displayResult("Lecture des messages", true);
        echo "<table>";
        echo "<thead><tr><th>ID</th><th>Rôle</th><th>Contenu</th><th>Date Envoi</th><th>ID Session</th></tr></thead>";
        echo "<tbody>";
        foreach ($messages as $message) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($message['id_message']) . "</td>";
            echo "<td><span class='status " . ($message['role_message'] == 'user' ? 'info' : 'success') . "'>" .
                 htmlspecialchars($message['role_message']) . "</span></td>";
            echo "<td>" . htmlspecialchars(substr($message['contenu'], 0, 100)) . "...</td>";
            echo "<td>" . htmlspecialchars($message['date_envoi']) . "</td>";
            echo "<td>" . htmlspecialchars($message['id_session']) . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        displayResult("Lecture des messages", false, null, "Aucun message trouvé");
    }
} catch (Exception $e) {
    displayResult("Test du modèle Message", false, null, $e->getMessage());
}

echo "</div></div>";

// ========================================
// RÉSUMÉ DES TESTS
// ========================================
echo "<div class='summary'>";
echo "<div class='summary-item'>";
echo "<h3>$total_tests</h3>";
echo "<p>Tests Total</p>";
echo "</div>";
echo "<div class='summary-item'>";
echo "<h3>$tests_reussis</h3>";
echo "<p>Tests Réussis</p>";
echo "</div>";
echo "<div class='summary-item'>";
echo "<h3>$tests_echoues</h3>";
echo "<p>Tests Échoués</p>";
echo "</div>";
echo "<div class='summary-item'>";
$pourcentage = $total_tests > 0 ? round(($tests_reussis / $total_tests) * 100, 2) : 0;
echo "<h3>$pourcentage%</h3>";
echo "<p>Taux de Réussite</p>";
echo "</div>";
echo "</div>";

?>

        </div>
    </div>
</body>
</html>
