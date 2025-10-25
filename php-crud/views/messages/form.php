<?php
require_once __DIR__ . '/../../includes/check_admin.php';
require_once __DIR__ . '/../../controllers/MessageController.php';
require_once __DIR__ . '/../../controllers/SessionConversationController.php';

use Controllers\MessageController;
use Controllers\SessionConversationController;

// Initialisation
$errors = [];
$message = '';
$inputData = [];
$message_data = null;
$isEditMode = false;

// Récupération du message en mode édition
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $isEditMode = true;
    $controller = new MessageController();
    $message_data = $controller->getSingleMessage($_GET['id']);

    if (!$message_data) {
        $errors[] = "Message introuvable.";
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new MessageController();
    $result = $controller->handleSubmit($_POST, $isEditMode, $message_data);

    $errors = $result['errors'];
    $message = $result['message'];
    $inputData = $result['input'];

    if ($result['success'] && isset($result['message_data'])) {
        $message_data = $result['message_data'];
    }
}

// Récupération des listes pour les selects
$sessionController = new SessionConversationController();
$sessions = $sessionController->getSessionConversation();
$emetteurs_possibles = ['user', 'agent'];
?>

<div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
    <h2 style="text-align: center; color: #dc3545;">
        <?= $isEditMode ? 'Modifier le Message' : 'Ajouter un nouveau Message' ?>
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
            <input type="hidden" name="id_message" value="<?= htmlspecialchars($message_data['id_message']) ?>">
        <?php endif; ?>

        <div style="margin-bottom: 15px;">
            <label for="id_session" style="display: block; margin-bottom: 5px; font-weight: bold;">Session de Conversation :</label>
            <select id="id_session" name="id_session" required
                    style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
                <option value="">Sélectionner une session</option>
                <?php foreach ($sessions as $session_item): ?>
                    <option value="<?= htmlspecialchars($session_item['id_session']) ?>"
                            <?= ($inputData['id_session'] ?? $message_data['id_session'] ?? '') == $session_item['id_session'] ? 'selected' : '' ?>>
                        Session <?= htmlspecialchars($session_item['id_session']) ?> - <?= htmlspecialchars($session_item['date_heure_debut']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="emetteur" style="display: block; margin-bottom: 5px; font-weight: bold;">Émetteur (user/agent) :</label>
            <select id="emetteur" name="emetteur" required
                    style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
                <option value="">Sélectionner un émetteur</option>
                <?php foreach ($emetteurs_possibles as $emetteur_item): ?>
                    <option value="<?= htmlspecialchars($emetteur_item) ?>"
                            <?= ($inputData['emetteur'] ?? $message_data['emetteur'] ?? '') == $emetteur_item ? 'selected' : '' ?>>
                        <?= ucfirst(htmlspecialchars($emetteur_item)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="contenu_message" style="display: block; margin-bottom: 5px; font-weight: bold;">Contenu :</label>
            <textarea id="contenu_message" name="contenu_message" rows="6" required
                      style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;"><?= htmlspecialchars($inputData['contenu_message'] ?? $message_data['contenu_message'] ?? '') ?></textarea>
        </div>

        <div style="margin-bottom: 20px;">
            <label for="date_heure_message" style="display: block; margin-bottom: 5px; font-weight: bold;">Date et Heure d'Envoi :</label>
            <?php
            $defaultDate = date('Y-m-d\TH:i');
            if ($isEditMode && isset($message_data['date_heure_message'])) {
                $defaultDate = date('Y-m-d\TH:i', strtotime($message_data['date_heure_message']));
            }
            ?>
            <input type="datetime-local" id="date_heure_message" name="date_heure_message"
                   value="<?= htmlspecialchars($inputData['date_heure_message'] ?? $defaultDate) ?>"
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
