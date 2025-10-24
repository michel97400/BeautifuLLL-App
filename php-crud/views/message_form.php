<?php
// Inclure les contrôleurs/modèles nécessaires et charger la liste pour la clé étrangère (Sessions)
// Exemple: $sessions = $sessionController->getAllSessions();

$isEditMode = isset($message_data) && is_array($message_data); // Renommé $message_data pour éviter le conflit avec $message d'erreur
$id_message = $isEditMode ? htmlspecialchars($message_data['id_message']) : '';
$role = $isEditMode ? htmlspecialchars($message_data['role']) : '';
$contenu = $isEditMode ? htmlspecialchars($message_data['contenu']) : '';
$date_envoi = $isEditMode ? date('Y-m-d\TH:i', strtotime($message_data['date_envoi'])) : date('Y-m-d\TH:i');
$id_session_selected = $isEditMode ? $message_data['id_session'] : '';

$message = $message ?? '';

// Variables mock pour l'exemple
$sessions = [
    ['id_session' => 1, 'description' => 'Session 1 - Jean/Tuteur Math'], 
    ['id_session' => 2, 'description' => 'Session 2 - Marie/Assistant Français']
]; 
$roles_message = ['user', 'assistant'];
?>

<div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
    <h2 style="text-align: center; color: #dc3545;">
        <?= $isEditMode ? 'Modifier le Message' : 'Ajouter un nouveau Message' ?>
    </h2>

    <?php if ($message): ?>
        <p style="color: red; text-align: center;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" action="index.php?action=message_save">
        
        <?php if ($isEditMode): ?>
            <input type="hidden" name="id_message" value="<?= $id_message ?>">
        <?php endif; ?>

        <div style="margin-bottom: 15px;">
            <label for="id_session" style="display: block; margin-bottom: 5px; font-weight: bold;">Session de Conversation :</label>
            <select id="id_session" name="id_session" required
                    style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
                <option value="">Sélectionner une session</option>
                <?php foreach ($sessions as $session_item): ?>
                    <option value="<?= htmlspecialchars($session_item['id_session']) ?>" 
                            <?= $id_session_selected == $session_item['id_session'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($session_item['description']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="role" style="display: block; margin-bottom: 5px; font-weight: bold;">Rôle (user/assistant) :</label>
            <select id="role" name="role" required
                    style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
                <option value="">Sélectionner un rôle</option>
                <?php foreach ($roles_message as $role_item): ?>
                    <option value="<?= htmlspecialchars($role_item) ?>" 
                            <?= $role == $role_item ? 'selected' : '' ?>>
                        <?= ucfirst(htmlspecialchars($role_item)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="contenu" style="display: block; margin-bottom: 5px; font-weight: bold;">Contenu :</label>
            <textarea id="contenu" name="contenu" rows="6" required
                      style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;"><?= $contenu ?></textarea>
        </div>

        <div style="margin-bottom: 20px;">
            <label for="date_envoi" style="display: block; margin-bottom: 5px; font-weight: bold;">Date et Heure d'Envoi :</label>
            <input type="datetime-local" id="date_envoi" name="date_envoi" value="<?= $date_envoi ?>"
                   style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
        </div>
        
        <div style="text-align: right;">
            <button type="submit"
                    style="background-color: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                <?= $isEditMode ? 'Enregistrer les modifications' : 'Créer le Message' ?>
            </button>
            <a href="index.php?action=message_list" 
               style="margin-left: 10px; padding: 10px 20px; border-radius: 5px; text-decoration: none; color: #6c757d;">
                Annuler
            </a>
        </div>
    </form>
</div>