<?php
// Inclure les contrôleurs/modèles nécessaires et charger les listes pour les clés étrangères (Matières et Étudiants)
// Exemple: $matieres = $matiereController->getAllMatieres();
// Exemple: $etudiants = $etudiantController->getAllEtudiants();

$isEditMode = isset($agent) && is_array($agent);
$id_agents = $isEditMode ? htmlspecialchars($agent['id_agents']) : '';
$nom_agent = $isEditMode ? htmlspecialchars($agent['nom_agent']) : '';
$type_agent = $isEditMode ? htmlspecialchars($agent['type_agent']) : '';
$avatar_agent = $isEditMode ? htmlspecialchars($agent['avatar_agent']) : '';
$est_actif = $isEditMode ? $agent['est_actif'] : true;
$description = $isEditMode ? htmlspecialchars($agent['description']) : '';
$prompt_systeme = $isEditMode ? htmlspecialchars($agent['prompt_systeme']) : '';
$id_matieres_selected = $isEditMode ? $agent['id_matieres'] : '';
$id_etudiant_selected = $isEditMode ? $agent['id_etudiant'] : ''; // L'étudiant qui a créé/gère cet agent

$message = $message ?? '';

// Variables mock pour l'exemple
$matieres = [
    ['id_matieres' => 1, 'nom_matieres' => 'Français'], 
    ['id_matieres' => 2, 'nom_matieres' => 'Anglais']
]; 
$etudiants = [
    ['id_etudiant' => 1, 'nom' => 'Dupont', 'prenom' => 'Jean'], 
    ['id_etudiant' => 3, 'nom' => 'Dubois', 'prenom' => 'Pierre']
]; 
$types_agent = ['Assistant_Pédagogique', 'Tuteur_Privé', 'Agent_Test'];
?>

<div style="max-width: 700px; margin: 0 auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
    <h2 style="text-align: center; color: #6f42c1;">
        <?= $isEditMode ? 'Modifier l\'Agent' : 'Ajouter un nouvel Agent' ?>
    </h2>

    <?php if ($message): ?>
        <p style="color: red; text-align: center;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" action="index.php?action=agent_save" enctype="multipart/form-data">
        
        <?php if ($isEditMode): ?>
            <input type="hidden" name="id_agents" value="<?= $id_agents ?>">
            <input type="hidden" name="date_creation" value="<?= htmlspecialchars($agent['date_creation']) ?>">
        <?php endif; ?>

        <div style="display: flex; gap: 20px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label for="nom_agent" style="display: block; margin-bottom: 5px; font-weight: bold;">Nom de l'Agent :</label>
                <input type="text" id="nom_agent" name="nom_agent" value="<?= $nom_agent ?>" required
                       style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
            </div>
            <div style="flex: 1;">
                <label for="type_agent" style="display: block; margin-bottom: 5px; font-weight: bold;">Type d'Agent :</label>
                <select id="type_agent" name="type_agent" required
                        style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
                    <option value="">Sélectionner un type</option>
                    <?php foreach ($types_agent as $type): ?>
                        <option value="<?= htmlspecialchars($type) ?>" 
                                <?= $type_agent == $type ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="description" style="display: block; margin-bottom: 5px; font-weight: bold;">Description :</label>
            <textarea id="description" name="description" rows="3"
                      style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;"><?= $description ?></textarea>
        </div>
        <div style="margin-bottom: 15px;">
            <label for="prompt_systeme" style="display: block; margin-bottom: 5px; font-weight: bold;">Prompt Système :</label>
            <textarea id="prompt_systeme" name="prompt_systeme" rows="5"
                      style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;"><?= $prompt_systeme ?></textarea>
        </div>

        <div style="display: flex; gap: 20px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label for="id_matieres" style="display: block; margin-bottom: 5px; font-weight: bold;">Matière (Optionnel) :</label>
                <select id="id_matieres" name="id_matieres" 
                        style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
                    <option value="">-- Aucune --</option>
                    <?php foreach ($matieres as $matiere): ?>
                        <option value="<?= htmlspecialchars($matiere['id_matieres']) ?>" 
                                <?= $id_matieres_selected == $matiere['id_matieres'] ? 'selected' : '' ?>>
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
                                <?= $id_etudiant_selected == $etudiant['id_etudiant'] ? 'selected' : '' ?>>
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
            </div>
            <div style="padding-top: 20px;">
                <input type="checkbox" id="est_actif" name="est_actif" value="1" <?= $est_actif ? 'checked' : '' ?>
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