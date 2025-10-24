<?php
require_once __DIR__ . '/../includes/check_admin.php';
require_once __DIR__ . '/../controllers/SessionConversationController.php';

use Controllers\SessionConversationController; // Assurez-vous que ce contrôleur existe

$controller = new SessionConversationController();
$id = $_GET['id'] ?? null;
$confirmed = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmer_suppression']));

// Appel de handleDelete
$result = $controller->handleDelete($id, $confirmed);

$errors = $result['errors'];
$message = $result['message'];
$session = $result['session'];

// Gestion de la redirection si succès
if ($result['success'] && $result['redirect']) {
    header("Location: ../../" . $result['redirect']);
    exit;
}

$id_column = 'id_session';
$entity_name = 'Session de Conversation';
$list_action = 'session_list';
?>

<?php if ($message): ?>
    <div style="color: red; text-align:center; margin-bottom:20px; padding:15px; border-radius:5px; background-color: #f8d7da;">
        <strong><?= htmlspecialchars($message) ?></strong>
        <br><br>
        <a href="index.php?action=<?= $list_action ?>" style="color: #0078d7; text-decoration: none;">Retour à la liste</a>
    </div>
<?php elseif ($session): ?>
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 2px solid #dc3545; border-radius: 10px; background-color: #fff3cd;">
        <h2 style="color: #dc3545; text-align: center;">Confirmer la suppression de la <?= $entity_name ?></h2>

        <div style="margin: 20px 0; padding: 15px; background-color: white; border-radius: 5px;">
            <p style="font-size: 16px; margin-bottom: 15px;">
                <strong>Vous êtes sur le point de supprimer la <?= $entity_name ?> suivante :</strong>
            </p>

            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px; font-weight: bold; width: 40%;">ID :</td>
                    <td style="padding: 8px;"><?= htmlspecialchars($session['id_session']) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: bold;">Début :</td>
                    <td style="padding: 8px;"><?= htmlspecialchars($session['date_heure_debut']) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: bold;">Durée :</td>
                    <td style="padding: 8px;"><?= htmlspecialchars($session['duree_session'] ?? 'N/A') ?></td>
                </tr>
            </table>
        </div>

        <p style="color: #856404; text-align: center; font-weight: bold; margin: 20px 0;">
            Cette action est irréversible ! Si des messages sont liés, la suppression échouera sauf configuration spéciale de la BDD.
        </p>

        <form method="POST" style="text-align: center;">
            <input type="hidden" name="<?= $id_column ?>" value="<?= htmlspecialchars($session[$id_column]) ?>">

            <button type="submit" name="confirmer_suppression"
                    style="background-color: #dc3545; color: white; padding: 12px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; margin-right: 10px;"
                    onclick="return confirm('Êtes-vous vraiment sûr de vouloir supprimer cette session ?');">
                Confirmer la suppression
            </button>

            <a href="index.php?action=<?= $list_action ?>"
               style="background-color: #6c757d; color: white; padding: 12px 30px; border-radius: 5px; font-size: 16px; text-decoration: none; display: inline-block;">
                Annuler
            </a>
        </form>
    </div>
<?php endif; ?>