<?php
require_once __DIR__ . '/../../includes/check_admin.php';
require_once __DIR__ . '/../../controllers/AgentController.php';
require_once __DIR__ . '/../../controllers/MatiereController.php';

use Controllers\AgentController;
use Controllers\MatiereController;

// Initialisation
$errors = [];
$message = '';
$inputData = [];
$agent = null;
$isEditMode = false;

// Recuperation de l'agent en mode edition
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

// Recuperation des listes pour les selects
$matiereController = new MatiereController();
$matieres = $matiereController->getMatiere();
$types_agent = ['Assistant_Pedagogique', 'Tuteur_Prive', 'Agent_Test'];
$reasoning_efforts = ['low' => 'Low (Rapide)', 'medium' => 'Medium (Equilibre)', 'high' => 'High (Precis)'];
?>

<style>
.form-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #6f42c1;
}
.form-section h3 {
    margin-top: 0;
    color: #6f42c1;
    font-size: 1.2rem;
}
.slider-container {
    display: flex;
    align-items: center;
    gap: 10px;
}
.slider-value {
    min-width: 50px;
    text-align: center;
    font-weight: bold;
    color: #6f42c1;
    background: white;
    padding: 5px 10px;
    border-radius: 4px;
    border: 1px solid #ced4da;
}
input[type="range"] {
    flex: 1;
}
.char-counter {
    text-align: right;
    font-size: 0.85rem;
    color: #666;
    margin-top: 5px;
}
.char-counter.warning {
    color: #ff6b6b;
    font-weight: bold;
}
</style>

<div style="max-width: 800px; margin: 0 auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
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

        <!-- SECTION: Informations de base -->
        <div class="form-section">
            <h3>Informations de base</h3>

            <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label for="nom_agent" style="display: block; margin-bottom: 5px; font-weight: bold;">Nom de l'Agent * :</label>
                    <input type="text" id="nom_agent" name="nom_agent"
                           value="<?= htmlspecialchars($inputData['nom_agent'] ?? $agent['nom_agent'] ?? '') ?>" required
                           style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
                </div>
                <div style="flex: 1;">
                    <label for="type_agent" style="display: block; margin-bottom: 5px; font-weight: bold;">Type d'Agent * :</label>
                    <select id="type_agent" name="type_agent" required
                            style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
                        <option value="">Selectionner un type</option>
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
                <label for="id_matieres" style="display: block; margin-bottom: 5px; font-weight: bold;">Matiere * :</label>
                <select id="id_matieres" name="id_matieres" required
                        style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
                    <option value="">-- Selectionner une matiere --</option>
                    <?php foreach ($matieres as $matiere): ?>
                        <option value="<?= htmlspecialchars($matiere['id_matieres']) ?>"
                                <?= ($inputData['id_matieres'] ?? $agent['id_matieres'] ?? '') == $matiere['id_matieres'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($matiere['nom_matieres']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small style="color: #666; font-style: italic;">Un seul agent par matiere est autorise</small>
            </div>

            <div style="margin-bottom: 15px;">
                <label for="description" style="display: block; margin-bottom: 5px; font-weight: bold;">Description :</label>
                <textarea id="description" name="description" rows="3" maxlength="500"
                          style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;"><?= htmlspecialchars($inputData['description'] ?? $agent['description'] ?? '') ?></textarea>
                <div class="char-counter" id="desc-counter">0 / 500 caracteres</div>
            </div>
        </div>

        <!-- SECTION: Prompt Systeme -->
        <div class="form-section">
            <h3>Prompt Systeme</h3>
            <div style="margin-bottom: 15px;">
                <label for="prompt_systeme" style="display: block; margin-bottom: 5px; font-weight: bold;">Prompt Systeme * :</label>
                <textarea id="prompt_systeme" name="prompt_systeme" rows="6" maxlength="2000" required
                          style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;"><?= htmlspecialchars($inputData['prompt_systeme'] ?? $agent['prompt_systeme'] ?? '') ?></textarea>
                <div class="char-counter" id="prompt-counter">0 / 2000 caracteres</div>
                <small style="color: #666; display: block; margin-top: 5px;">
                    Ce prompt definit le comportement de base de l'agent. Il sera completé automatiquement avec le niveau de l'etudiant.
                </small>
            </div>
        </div>

        <!-- SECTION: Parametres LLM (NOUVEAU) -->
        <div class="form-section">
            <h3>Parametres LLM</h3>

            <div style="margin-bottom: 15px;">
                <label for="model" style="display: block; margin-bottom: 5px; font-weight: bold;">Modele LLM * :</label>
                <input type="text" id="model" name="model"
                       value="<?= htmlspecialchars($inputData['model'] ?? $agent['model'] ?? 'openai/gpt-oss-20b') ?>" required
                       style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
                <small style="color: #666;">Exemple: openai/gpt-oss-20b, llama3-70b-8192, etc.</small>
            </div>

            <div style="margin-bottom: 15px;">
                <label for="temperature" style="display: block; margin-bottom: 5px; font-weight: bold;">Temperature (0 = Precis, 2 = Creatif) :</label>
                <div class="slider-container">
                    <input type="range" id="temperature" name="temperature"
                           min="0" max="2" step="0.1"
                           value="<?= htmlspecialchars($inputData['temperature'] ?? $agent['temperature'] ?? '0.7') ?>"
                           style="width: 100%;">
                    <span class="slider-value" id="temp-value">0.7</span>
                </div>
                <small style="color: #666; display: block; margin-top: 5px;">
                    Recommandé: 0.5 pour maths/physique, 0.7 general, 0.8-1.0 pour histoire/langues
                </small>
            </div>

            <div style="margin-bottom: 15px;">
                <label for="max_tokens" style="display: block; margin-bottom: 5px; font-weight: bold;">Max Tokens :</label>
                <input type="number" id="max_tokens" name="max_tokens"
                       min="1" max="100000"
                       value="<?= htmlspecialchars($inputData['max_tokens'] ?? $agent['max_tokens'] ?? '8192') ?>"
                       style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
                <small style="color: #666;">Nombre maximum de tokens dans la reponse (1-100000)</small>
            </div>

            <div style="margin-bottom: 15px;">
                <label for="top_p" style="display: block; margin-bottom: 5px; font-weight: bold;">Top P (Diversite) :</label>
                <div class="slider-container">
                    <input type="range" id="top_p" name="top_p"
                           min="0" max="1" step="0.05"
                           value="<?= htmlspecialchars($inputData['top_p'] ?? $agent['top_p'] ?? '1.0') ?>"
                           style="width: 100%;">
                    <span class="slider-value" id="topp-value">1.0</span>
                </div>
                <small style="color: #666; display: block; margin-top: 5px;">
                    Controle la diversite des reponses (generalement laisser a 1.0)
                </small>
            </div>

            <div style="margin-bottom: 15px;">
                <label for="reasoning_effort" style="display: block; margin-bottom: 5px; font-weight: bold;">Reasoning Effort (Effort de raisonnement) :</label>
                <select id="reasoning_effort" name="reasoning_effort"
                        style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
                    <?php foreach ($reasoning_efforts as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value) ?>"
                                <?= ($inputData['reasoning_effort'] ?? $agent['reasoning_effort'] ?? 'medium') == $value ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small style="color: #666;">Recommandé: High pour maths/physique, Medium pour le reste</small>
            </div>
        </div>

        <!-- SECTION: Autres options -->
        <div class="form-section">
            <h3>Autres options</h3>

            <div style="display: flex; gap: 20px; align-items: center; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label for="avatar_agent" style="display: block; margin-bottom: 5px; font-weight: bold;">Avatar (Fichier) :</label>
                    <input type="file" id="avatar_agent" name="avatar_agent" accept="image/jpeg,image/png,image/gif"
                           style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
                    <?php if ($isEditMode && !empty($agent['avatar_agent'])): ?>
                        <small style="color: #666;">Avatar actuel : <?= htmlspecialchars($agent['avatar_agent']) ?></small>
                    <?php endif; ?>
                </div>
                <div style="padding-top: 20px;">
                    <input type="checkbox" id="est_actif" name="est_actif" value="1"
                           <?= ($inputData['est_actif'] ?? $agent['est_actif'] ?? 1) ? 'checked' : '' ?>
                           style="margin-right: 5px;">
                    <label for="est_actif" style="font-weight: bold;">Agent Actif</label>
                </div>
            </div>
        </div>

        <div style="text-align: right; margin-top: 20px;">
            <button type="submit"
                    style="background-color: #6f42c1; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold;">
                <?= $isEditMode ? 'Enregistrer les modifications' : 'Creer l\'Agent' ?>
            </button>
            <a href="index.php?action=agent_list"
               style="margin-left: 10px; padding: 12px 30px; border-radius: 5px; text-decoration: none; color: #6c757d; background: #e9ecef; display: inline-block;">
                Annuler
            </a>
        </div>
    </form>
</div>

<script>
// Mise a jour des valeurs des sliders en temps reel
document.getElementById('temperature').addEventListener('input', function() {
    document.getElementById('temp-value').textContent = this.value;
});

document.getElementById('top_p').addEventListener('input', function() {
    document.getElementById('topp-value').textContent = this.value;
});

// Compteur de caracteres pour description
const descTextarea = document.getElementById('description');
const descCounter = document.getElementById('desc-counter');
function updateDescCounter() {
    const length = descTextarea.value.length;
    descCounter.textContent = length + ' / 500 caracteres';
    if (length > 450) {
        descCounter.classList.add('warning');
    } else {
        descCounter.classList.remove('warning');
    }
}
descTextarea.addEventListener('input', updateDescCounter);
updateDescCounter(); // Init

// Compteur de caracteres pour prompt systeme
const promptTextarea = document.getElementById('prompt_systeme');
const promptCounter = document.getElementById('prompt-counter');
function updatePromptCounter() {
    const length = promptTextarea.value.length;
    promptCounter.textContent = length + ' / 2000 caracteres';
    if (length > 1800) {
        promptCounter.classList.add('warning');
    } else {
        promptCounter.classList.remove('warning');
    }
}
promptTextarea.addEventListener('input', updatePromptCounter);
updatePromptCounter(); // Init

// Initialiser les valeurs des sliders au chargement
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('temp-value').textContent = document.getElementById('temperature').value;
    document.getElementById('topp-value').textContent = document.getElementById('top_p').value;
});
</script>
