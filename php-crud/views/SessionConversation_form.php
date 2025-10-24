<?php
// Inclure les contrôleurs/modèles nécessaires et charger les listes pour les clés étrangères (Agents et Étudiants)
// Exemple: $agents = $agentController->getAllAgents();
// Exemple: $etudiants = $etudiantController->getAllEtudiants();

$isEditMode = isset($session) && is_array($session);
$id_session = $isEditMode ? htmlspecialchars($session['id_session']) : '';
$date_heure_debut = $isEditMode ? date('Y-m-d\TH:i', strtotime($session['date_heure_debut'])) : date('Y-m-d\TH:i');
$duree_session = $isEditMode ? htmlspecialchars($session['duree_session']) : ''; // Format TIME 'HH:MM:SS'
$date_heure_fin = $isEditMode && $session['date_heure_fin'] ? date('Y-m-d\TH:i', strtotime($session['date_heure_fin'])) : '';
$id_agents_selected = $isEditMode ? $session['id_agents'] : '';
$id_etudiant_selected = $isEditMode ? $session['id_etudiant'] : '';

$message = $message ?? '';

// Variables mock pour l'exemple
$agents = [
    ['id_agents' => 1, 'nom_agent' => 'Tuteur Math'], 
    ['id_agents' => 2, 'nom_agent' => 'Assistant Français']
]; 
$etudiants = [
    ['id_etudiant' => 1, 'nom' => 'Dupont', 'prenom' => 'Jean'], 
    ['id_etudiant' => 2, 'nom' => 'Martin', 'prenom' => 'Marie']
]; 
?>

<div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
    <h2 style="text-align: center; color: #fd7e14;">
        <?= $isEditMode ? 'Modifier la Session de Conversation' : 'Ajouter une Session de Conversation' ?>
    </h2>

    <?php if ($message): ?>
        <p style="color: red; text-align: center;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" action="index.php?action=session_save">
        
        <?php if ($isEditMode): ?>
            <input type="hidden" name="id_session" value="<?= $id_session ?>">
        <?php endif; ?>

        <div style="display: flex; gap: 20px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label for="id_agents" style="display: block; margin-bottom: 5px; font-weight: bold;">Agent :</label>
                <select id="id_agents" name="id_agents" required
                        style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
                    <option value="">Sélectionner un agent</option>
                    <?php foreach ($agents as $agent): ?>
                        <option value="<?= htmlspecialchars($agent['id_agents']) ?>" 
                                <?= $id_agents_selected == $agent['id_agents'] ? 'selected' : '' ?>>
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
                                <?= $id_etudiant_selected == $etudiant['id_etudiant'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($etudiant['prenom']) . ' ' . htmlspecialchars($etudiant['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="date_heure_debut" style="display: block; margin-bottom: 5px; font-weight: bold;">Date et Heure de Début :</label>
            <input type="datetime-local" id="date_heure_debut" name="date_heure_debut" value="<?= $date_heure_debut ?>" required
                   style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="date_heure_fin" style="display: block; margin-bottom: 5px; font-weight: bold;">Date et Heure de Fin (Optionnel) :</label>
            <input type="datetime-local" id="date_heure_fin" name="date_heure_fin" value="<?= $date_heure_fin ?>"
                   style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
        </div>
        
        <div style="margin-bottom: 20px;">
            <label for="duree_session" style="display: block; margin-bottom: 5px; font-weight: bold;">Durée de la Session (HH:MM:SS) :</label>
            <input type="text" id="duree_session" name="duree_session" value="<?= $duree_session ?>" placeholder="Ex: 00:30:00"
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