<?php
require_once __DIR__ . '/../includes/check_admin.php';
require_once __DIR__ . '/../controllers/AgentController.php';
require_once __DIR__ . '/../controllers/MatiereController.php';
require_once __DIR__ . '/../controllers/EtudiantController.php';

use Controllers\AgentController;
use Controllers\MatiereController;
use Controllers\EtudiantController;

// Initialisation
$errors = [];
$message = '';
$inputData = [];
$agent = null;
$isEditMode = false;

// Récupération de l'agent en mode édition
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $isEditMode = true;
    $controller = new AgentController();
    $agent = $controller->getSingleAgent($_GET['id']);

    if (!$agent) {
        $errors[] = "Agent introuvable.";
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new AgentController();
    $result = $controller->handleSubmit($_POST, $_FILES, $isEditMode, $agent);

    $errors = $result['errors'];
    $message = $result['message'];
    $inputData = $result['input'];

    if ($result['success'] && isset($result['agent'])) {
        $agent = $result['agent'];
    }
}

// Récupération des listes pour les selects
$matiereController = new MatiereController();
$etudiantController = new EtudiantController();
$matieres = $matiereController->getMatiere();
$etudiants = $etudiantController->getEtudiant();
$types_agent = ['Assistant_Pédagogique', 'Tuteur_Privé', 'Agent_Test'];
?>

<div style="max-width: 700px; margin: 0 auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
    <h2 style="text-align: center; color: #6f42c1;">
        <?= $isEditMode ? 'Modifier l\'Agent' : 'Ajouter un nouvel Agent' ?>
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

    <form method="POST" action="" enctype="multipart/form-data">

        <?php if ($isEditMode): ?>
            <input type="hidden" name="id_agents" value="<?= htmlspecialchars($agent['id_agents']) ?>">
        <?php endif; ?>

        <div style="display: flex; gap: 20px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label for="nom_agent" style="display: block; margin-bottom: 5px; font-weight: bold;">Nom de l'Agent :</label>
                <input type="text" id="nom_agent" name="nom_agent"
                       value="<?= htmlspecialchars($inputData['nom_agent'] ?? $agent['nom_agent'] ?? '') ?>" required
                       style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
            </div>
            <div style="flex: 1;">
                <label for="type_agent" style="display: block; margin-bottom: 5px; font-weight: bold;">Type d'Agent :</label>
                <select id="type_agent" name="type_agent" required
                        style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
                    <option value="">Sélectionner un type</option>
                    <?php foreach ($types_agent as $type): ?>
                        <option value="<?= htmlspecialchars($type) ?>"
                                <?= ($inputData['type_agent'] ?? $agent['type_agent'] ?? '') == $type ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="description" style="display: block; margin-bottom: 5px; font-weight: bold;">Description :</label>
            <textarea id="description" name="description" rows="3"
                      style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;"><?= htmlspecialchars($inputData['description'] ?? $agent['description'] ?? '') ?></textarea>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="prompt_systeme" style="display: block; margin-bottom: 5px; font-weight: bold;">Prompt Système :</label>
            <textarea id="prompt_systeme" name="prompt_systeme" rows="5"
                      style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;"><?= htmlspecialchars($inputData['prompt_systeme'] ?? $agent['prompt_systeme'] ?? '') ?></textarea>
        </div>

        <div style="display: flex; gap: 20px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label for="id_matieres" style="display: block; margin-bottom: 5px; font-weight: bold;">Matière (Optionnel) :</label>
                <select id="id_matieres" name="id_matieres"
                        style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
                    <option value="">-- Aucune --</option>
                    <?php foreach ($matieres as $matiere): ?>
                        <option value="<?= htmlspecialchars($matiere['id_matieres']) ?>"
                                <?= ($inputData['id_matieres'] ?? $agent['id_matieres'] ?? '') == $matiere['id_matieres'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($matiere['nom_matieres']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex: 1;">
                <label for="id_etudiant" style="display: block; margin-bottom: 5px; font-weight: bold;">Étudiant Créateur :</label>
                <select id="id_etudiant" name="id_etudiant" required
                        style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
                    <option value="">Sélectionner un étudiant</option>
                    <?php foreach ($etudiants as $etudiant): ?>
                        <option value="<?= htmlspecialchars($etudiant['id_etudiant']) ?>"
                                <?= ($inputData['id_etudiant'] ?? $agent['id_etudiant'] ?? '') == $etudiant['id_etudiant'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($etudiant['prenom']) . ' ' . htmlspecialchars($etudiant['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 20px; align-items: center; margin-bottom: 20px;">
            <div style="flex: 1;">
                <label for="avatar_agent" style="display: block; margin-bottom: 5px; font-weight: bold;">Avatar (Fichier) :</label>
                <input type="file" id="avatar_agent" name="avatar_agent"
                       style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
                <?php if ($isEditMode && !empty($agent['avatar_agent'])): ?>
                    <small style="color: #666;">Avatar actuel : <?= htmlspecialchars($agent['avatar_agent']) ?></small>
                <?php endif; ?>
            </div>
            <div style="padding-top: 20px;">
                <input type="checkbox" id="est_actif" name="est_actif" value="1"
                       <?= ($inputData['est_actif'] ?? $agent['est_actif'] ?? 1) ? 'checked' : '' ?>
                       style="margin-right: 5px;">
                <label for="est_actif" style="font-weight: bold;">Est Actif</label>
            </div>
        </div>

        <div style="text-align: right;">
            <button type="submit"
                    style="background-color: #6f42c1; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                <?= $isEditMode ? 'Enregistrer les modifications' : 'Créer l\'Agent' ?>
            </button>
            <a href="index.php?action=agent_list"
               style="margin-left: 10px; padding: 10px 20px; border-radius: 5px; text-decoration: none; color: #6c757d;">
                Annuler
            </a>
        </div>
    </form>
</div>
