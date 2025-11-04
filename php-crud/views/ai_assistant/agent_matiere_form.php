<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../model/matieres.php';
require_once __DIR__ . '/../../model/agent.php';

use Models\Matiere;
use Models\Agent;

$matiereModel = new Matiere();
$agentModel = new Agent();

// Recuperer toutes les matieres
$matieres = $matiereModel->read();

// Filtrer pour ne garder que celles qui ont un agent actif
$matieresAvecAgent = [];
foreach ($matieres as $matiere) {
    $agent = $agentModel->getAgentByMatiere($matiere['id_matieres']);
    if ($agent) {
        $matieresAvecAgent[] = [
            'id_matieres' => $matiere['id_matieres'],
            'nom_matieres' => $matiere['nom_matieres'],
            'agent_nom' => $agent['nom_agent']
        ];
    }
}

$errorMessage = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_matieres']) && $_POST['id_matieres'] !== '') {
    $id_matieres = intval($_POST['id_matieres']);

    // Verifier qu'un agent existe pour cette matiere
    $agent = $agentModel->getAgentByMatiere($id_matieres);

    if ($agent) {
        // Recuperer le nom de la matiere pour affichage
        $matiereChoisie = null;
        foreach ($matieresAvecAgent as $m) {
            if ($m['id_matieres'] == $id_matieres) {
                $matiereChoisie = $m['nom_matieres'];
                break;
            }
        }

        // Stocker l'ID ET le nom dans la session
        $_SESSION['agent_ia_id_matieres'] = $id_matieres;
        $_SESSION['agent_ia_matiere'] = $matiereChoisie;

        echo '<script>window.location.href = "index.php?action=agent-ia";</script>';
        exit;
    } else {
        $errorMessage = 'Aucun agent disponible pour cette matiere.';
    }
}
?>

<form method="post" class="crud-card" style="max-width: 500px; margin: 40px auto 24px auto; padding: 32px; display: flex; flex-direction: column; gap: 18px; background: #fff; box-shadow: 0 4px 16px rgba(0,0,0,0.10); border-radius: 12px;">
    <h2 style="margin-bottom: 18px; font-size: 1.3rem; color: #0078d7;">Choisissez une matiere pour discuter avec l'Agent IA</h2>

    <?php if ($errorMessage): ?>
        <div style="background: #ffebee; color: #c62828; padding: 12px; border-radius: 6px; border-left: 4px solid #c62828;">
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($matieresAvecAgent)): ?>
        <div style="background: #fff3cd; color: #856404; padding: 12px; border-radius: 6px; border-left: 4px solid #ffc107;">
            <strong>Aucun agent disponible</strong><br>
            Aucune matiere n'a d'agent IA configure. Veuillez contacter l'administrateur.
        </div>
    <?php else: ?>
        <label for="id_matieres" style="font-weight:500;">Matiere :</label>
        <select name="id_matieres" id="id_matieres" required style="padding:10px 12px; border-radius:8px; border:1px solid #e0e0e0; font-size:1rem;">
            <option value="">-- Selectionner --</option>
            <?php foreach ($matieresAvecAgent as $m): ?>
                <option value="<?= htmlspecialchars($m['id_matieres']) ?>">
                    <?= htmlspecialchars($m['nom_matieres']) ?> - <?= htmlspecialchars($m['agent_nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div style="background: #e3f2fd; color: #0277bd; padding: 12px; border-radius: 6px; font-size: 0.9rem;">
            <strong>Information :</strong> Seules les matieres disposant d'un agent IA sont affichées.
            <br>Total : <?= count($matieresAvecAgent) ?> agent(s) disponible(s).
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top:18px;">Demarrer la conversation</button>
    <?php endif; ?>
</form>
