<?php
require_once __DIR__ . '/../includes/check_admin.php';
require_once __DIR__ . '/../controllers/SessionConversationController.php';
require_once __DIR__ . '/../controllers/AgentController.php';
require_once __DIR__ . '/../controllers/EtudiantController.php';

use Controllers\SessionConversationController;
use Controllers\AgentController;
use Controllers\EtudiantController;

// Initialisation
$errors = [];
$message = '';
$inputData = [];
$session = null;
$isEditMode = false;

// Récupération de la session en mode édition
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $isEditMode = true;
    $controller = new SessionConversationController();
    $session = $controller->getSingleSessionConversation($_GET['id']);

    if (!$session) {
        $errors[] = "Session de conversation introuvable.";
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new SessionConversationController();
    $result = $controller->handleSubmit($_POST, $isEditMode, $session);

    $errors = $result['errors'];
    $message = $result['message'];
    $inputData = $result['input'];

    if ($result['success'] && isset($result['session'])) {
        $session = $result['session'];
    }
}

// Récupération des listes pour les selects
$agentController = new AgentController();
$etudiantController = new EtudiantController();
$agents = $agentController->getAgents();
$etudiants = $etudiantController->getEtudiant();
?>

<div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
    <h2 style="text-align: center; color: #fd7e14;">
        <?= $isEditMode ? 'Modifier la Session de Conversation' : 'Ajouter une Session de Conversation' ?>
    </h2>

    <?php if ($message): ?>
        <p style="color: green; text-align: center; background-color: #d4edda; padding: 10px; border-radius: 5px;">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div style="color: red; text-align: center; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            <strong>Erreurs :</strong>
            <ul style="list-style: none; padding: 0; margin: 5px 0 0 0;">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="">

        <?php if ($isEditMode): ?>
            <input type="hidden" name="id_session" value="<?= htmlspecialchars($session['id_session']) ?>">
        <?php endif; ?>

        <div style="display: flex; gap: 20px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label for="id_agents" style="display: block; margin-bottom: 5px; font-weight: bold;">Agent :</label>
                <select id="id_agents" name="id_agents" required
                        style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
                    <option value="">Sélectionner un agent</option>
                    <?php foreach ($agents as $agent): ?>
                        <option value="<?= htmlspecialchars($agent['id_agents']) ?>"
                                <?= ($inputData['id_agents'] ?? $session['id_agents'] ?? '') == $agent['id_agents'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($agent['nom_agent']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex: 1;">
                <label for="id_etudiant" style="display: block; margin-bottom: 5px; font-weight: bold;">Étudiant :</label>
                <select id="id_etudiant" name="id_etudiant" required
                        style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
                    <option value="">Sélectionner un étudiant</option>
                    <?php foreach ($etudiants as $etudiant): ?>
                        <option value="<?= htmlspecialchars($etudiant['id_etudiant']) ?>"
                                <?= ($inputData['id_etudiant'] ?? $session['id_etudiant'] ?? '') == $etudiant['id_etudiant'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($etudiant['prenom']) . ' ' . htmlspecialchars($etudiant['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="date_heure_debut" style="display: block; margin-bottom: 5px; font-weight: bold;">Date et Heure de Début :</label>
            <?php
            $defaultDateDebut = date('Y-m-d\TH:i');
            if ($isEditMode && isset($session['date_heure_debut'])) {
                $defaultDateDebut = date('Y-m-d\TH:i', strtotime($session['date_heure_debut']));
            }
            ?>
            <input type="datetime-local" id="date_heure_debut" name="date_heure_debut"
                   value="<?= htmlspecialchars($inputData['date_heure_debut'] ?? $defaultDateDebut) ?>" required
                   style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="date_heure_fin" style="display: block; margin-bottom: 5px; font-weight: bold;">Date et Heure de Fin (Optionnel) :</label>
            <?php
            $defaultDateFin = '';
            if ($isEditMode && isset($session['date_heure_fin']) && $session['date_heure_fin']) {
                $defaultDateFin = date('Y-m-d\TH:i', strtotime($session['date_heure_fin']));
            }
            ?>
            <input type="datetime-local" id="date_heure_fin" name="date_heure_fin"
                   value="<?= htmlspecialchars($inputData['date_heure_fin'] ?? $defaultDateFin) ?>"
                   style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 20px;">
            <label for="duree_session" style="display: block; margin-bottom: 5px; font-weight: bold;">Durée de la Session (HH:MM:SS) :</label>
            <input type="text" id="duree_session" name="duree_session"
                   value="<?= htmlspecialchars($inputData['duree_session'] ?? $session['duree_session'] ?? '') ?>"
                   placeholder="Ex: 00:30:00" required
                   style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="text-align: right;">
            <button type="submit"
                    style="background-color: #fd7e14; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                <?= $isEditMode ? 'Enregistrer les modifications' : 'Créer la Session' ?>
            </button>
            <a href="index.php?action=session_list"
               style="margin-left: 10px; padding: 10px 20px; border-radius: 5px; text-decoration: none; color: #6c757d;">
                Annuler
            </a>
        </div>
    </form>
</div>
