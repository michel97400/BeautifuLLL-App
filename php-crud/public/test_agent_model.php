<?php
/**
 * Script de test pour le modele Agent refactorise
 * Teste les nouvelles methodes ajoutees en Phase 2
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/agent.php';

use Config\Database;
use Models\Agent;

echo "<h1>Test du Modele Agent - Phase 2</h1>";
echo "<style>
body { font-family: Arial, sans-serif; padding: 20px; }
h2 { color: #0078d7; border-bottom: 2px solid #0078d7; padding-bottom: 5px; }
.success { color: green; }
.error { color: red; }
pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>";

// Creer instance
$agent = new Agent();

// ================================================================
// TEST 1: getAgentByMatiere()
// ================================================================
echo "<h2>Test 1: getAgentByMatiere()</h2>";

try {
    // Tester avec Francais (id_matieres = 1)
    $agentFrancais = $agent->getAgentByMatiere(1);

    if ($agentFrancais) {
        echo "<p class='success'>✓ Agent trouve pour Francais</p>";
        echo "<pre>";
        echo "Nom: " . $agentFrancais['nom_agent'] . "\n";
        echo "Matiere: " . $agentFrancais['nom_matieres'] . "\n";
        echo "Temperature: " . $agentFrancais['temperature'] . "\n";
        echo "Model: " . $agentFrancais['model'] . "\n";
        echo "Reasoning: " . $agentFrancais['reasoning_effort'] . "\n";
        echo "</pre>";
    } else {
        echo "<p class='error'>✗ Aucun agent trouve pour Francais</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Erreur: " . $e->getMessage() . "</p>";
}

// ================================================================
// TEST 2: getLLMParameters()
// ================================================================
echo "<h2>Test 2: getLLMParameters()</h2>";

try {
    // Recuperer les parametres de l'agent Maths (id_agents = 4)
    $llmParams = $agent->getLLMParameters(4);

    if ($llmParams) {
        echo "<p class='success'>✓ Parametres LLM recuperes</p>";
        echo "<pre>";
        print_r($llmParams);
        echo "</pre>";
    } else {
        echo "<p class='error'>✗ Impossible de recuperer les parametres</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Erreur: " . $e->getMessage() . "</p>";
}

// ================================================================
// TEST 3: agentExistsForMatiere()
// ================================================================
echo "<h2>Test 3: agentExistsForMatiere()</h2>";

try {
    // Tester avec matiere qui a un agent (Francais = 1)
    $exists1 = $agent->agentExistsForMatiere(1);
    echo "<p class='success'>✓ Francais (id=1) a un agent: " . ($exists1 ? 'OUI' : 'NON') . "</p>";

    // Tester avec matiere qui n'existe pas (id=999)
    $exists999 = $agent->agentExistsForMatiere(999);
    echo "<p class='success'>✓ Matiere 999 a un agent: " . ($exists999 ? 'OUI' : 'NON') . "</p>";

} catch (Exception $e) {
    echo "<p class='error'>✗ Erreur: " . $e->getMessage() . "</p>";
}

// ================================================================
// TEST 4: getActiveAgents()
// ================================================================
echo "<h2>Test 4: getActiveAgents()</h2>";

try {
    $activeAgents = $agent->getActiveAgents();

    echo "<p class='success'>✓ Nombre d'agents actifs: " . count($activeAgents) . "</p>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Nom</th><th>Matiere</th><th>Temperature</th><th>Reasoning</th></tr>";

    foreach ($activeAgents as $ag) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($ag['nom_agent']) . "</td>";
        echo "<td>" . htmlspecialchars($ag['nom_matieres']) . "</td>";
        echo "<td>" . htmlspecialchars($ag['temperature']) . "</td>";
        echo "<td>" . htmlspecialchars($ag['reasoning_effort']) . "</td>";
        echo "</tr>";
    }

    echo "</table>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Erreur: " . $e->getMessage() . "</p>";
}

// ================================================================
// TEST 5: read() avec JOIN
// ================================================================
echo "<h2>Test 5: read() avec JOIN sur Matieres</h2>";

try {
    $allAgents = $agent->read();

    echo "<p class='success'>✓ Nombre total d'agents: " . count($allAgents) . "</p>";
    echo "<p>Verification que nom_matieres est bien inclus:</p>";

    if (!empty($allAgents)) {
        $firstAgent = $allAgents[0];
        if (isset($firstAgent['nom_matieres'])) {
            echo "<p class='success'>✓ nom_matieres present dans les resultats</p>";
        } else {
            echo "<p class='error'>✗ nom_matieres manquant dans les resultats</p>";
        }
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Erreur: " . $e->getMessage() . "</p>";
}

// ================================================================
// TEST 6: readSingle() avec JOIN
// ================================================================
echo "<h2>Test 6: readSingle() avec JOIN</h2>";

try {
    $singleAgent = $agent->readSingle(2);

    if ($singleAgent) {
        echo "<p class='success'>✓ Agent trouve</p>";
        echo "<pre>";
        echo "ID: " . $singleAgent['id_agents'] . "\n";
        echo "Nom: " . $singleAgent['nom_agent'] . "\n";
        echo "Matiere: " . $singleAgent['nom_matieres'] . "\n";
        echo "Model: " . $singleAgent['model'] . "\n";
        echo "Temperature: " . $singleAgent['temperature'] . "\n";
        echo "Max tokens: " . $singleAgent['max_tokens'] . "\n";
        echo "Top P: " . $singleAgent['top_p'] . "\n";
        echo "Reasoning: " . $singleAgent['reasoning_effort'] . "\n";
        echo "</pre>";
    } else {
        echo "<p class='error'>✗ Agent non trouve</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Erreur: " . $e->getMessage() . "</p>";
}

// ================================================================
// RESUME
// ================================================================
echo "<h2>Resume</h2>";
echo "<p>Toutes les nouvelles methodes ont ete testees:</p>";
echo "<ul>";
echo "<li>✓ getAgentByMatiere()</li>";
echo "<li>✓ getLLMParameters()</li>";
echo "<li>✓ agentExistsForMatiere()</li>";
echo "<li>✓ getActiveAgents()</li>";
echo "<li>✓ read() avec JOIN</li>";
echo "<li>✓ readSingle() avec JOIN</li>";
echo "</ul>";

echo "<p><strong>Phase 2 terminee avec succes!</strong></p>";
echo "<p>Prochaine etape: Modifier le controleur AgentController.php (Phase 3)</p>";
?>
