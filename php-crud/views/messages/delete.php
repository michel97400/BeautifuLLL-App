<?php
require_once __DIR__ . '/../../includes/check_admin.php';
require_once __DIR__ . '/../../controllers/MessageController.php';

use Controllers\MessageController; // Assurez-vous que ce contrôleur existe

$controller = new MessageController();
$id = $_GET['id'] ?? null;
$confirmed = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmer_suppression']));

// Appel de handleDelete (la redirection est gérée dans index.php)
$result = $controller->handleDelete($id, $confirmed);

$errors = $result['errors'];
$message = $result['message'];
$message_data = $result['message_data'];

// Note: La redirection est maintenant gérée dans index.php AVANT le rendu HTML

$id_column = 'id_message';
$entity_name = 'Message';
$list_action = 'message_list';
?>

<?php if ($message): ?>
    <div style="color: red; text-align:center; margin-bottom:20px; padding:15px; border-radius:5px; background-color: #f8d7da;">
        <strong><?= htmlspecialchars($message) ?></strong>
        <br><br>
        <a href="index.php?action=<?= $list_action ?>" style="color: #0078d7; text-decoration: none;">Retour à la liste</a>
    </div>
<?php elseif ($message_data): ?>
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 2px solid #dc3545; border-radius: 10px; background-color: #fff3cd;">
        <h2 style="color: #dc3545; text-align: center;">Confirmer la suppression du <?= $entity_name ?></h2>

        <div style="margin: 20px 0; padding: 15px; background-color: white; border-radius: 5px;">
            <p style="font-size: 16px; margin-bottom: 15px;">
                <strong>Vous êtes sur le point de supprimer le <?= $entity_name ?> suivant :</strong>
            </p>

            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px; font-weight: bold; width: 40%;">ID :</td>
                    <td style="padding: 8px;"><?= htmlspecialchars($message_data['id_message']) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: bold;">Rôle :</td>
                    <td style="padding: 8px;"><?= htmlspecialchars($message_data['role']) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: bold;">Contenu (extrait) :</td>
                    <td style="padding: 8px;"><?= htmlspecialchars(substr($message_data['contenu'], 0, 50)) ?>...</td>
                </tr>
            </table>
        </div>

        <p style="color: #856404; text-align: center; font-weight: bold; margin: 20px 0;">
            Cette action est irréversible !
        </p>

        <form method="POST" style="text-align: center;">
            <input type="hidden" name="<?= $id_column ?>" value="<?= htmlspecialchars($message_data[$id_column]) ?>">

            <button type="submit" name="confirmer_suppression"
                    style="background-color: #dc3545; color: white; padding: 12px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; margin-right: 10px;"
                    onclick="return confirm('Êtes-vous vraiment sûr de vouloir supprimer ce message ?');">
                Confirmer la suppression
            </button>

            <a href="index.php?action=<?= $list_action ?>"
               style="background-color: #6c757d; color: white; padding: 12px 30px; border-radius: 5px; font-size: 16px; text-decoration: none; display: inline-block;">
                Annuler
            </a>
        </form>
    </div>
<?php endif; ?>