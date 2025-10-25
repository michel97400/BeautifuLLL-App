<?php
require_once __DIR__ . '/../../includes/check_admin.php';
require_once __DIR__ . '/../../controllers/NiveauController.php';

use Controllers\NiveauController;

$message = '';
$niveau = null;
$controller = new NiveauController();

// Récupérer l'ID depuis l'URL
$id = $_GET['id'] ?? null;

// Vérifier si c'est une confirmation de suppression
$confirmed = $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmer_suppression']);

// Traiter via le controller
$result = $controller->handleDelete($id, $confirmed);

// Récupérer les données du résultat
$message = $result['message'];
$niveau = $result['niveau'];

// Si redirection nécessaire (suppression réussie)
if (!empty($result['redirect'])) {
    header('Location: ../../' . $result['redirect']);
    exit;
}
?>

<?php include __DIR__ . '/../../includes/crud_nav.php'; ?>

<?php if ($message): ?>
    <div class="alert alert-error" style="text-align: center;">
        <strong>✗ <?= htmlspecialchars($message) ?></strong>
        <br><br>
        <a href="index.php?action=niveau_list" class="btn btn-secondary">Retour à la liste</a>
    </div>
<?php elseif ($niveau): ?>
    <div class="crud-card">
        <h2 style="color: #dc3545; text-align: center;">⚠ Confirmer la suppression</h2>

        <div class="alert alert-warning">
            <strong>Vous êtes sur le point de supprimer le niveau suivant :</strong>
        </div>

        <table>
            <tr>
                <td>ID :</td>
                <td><?= htmlspecialchars($niveau['id_niveau']) ?></td>
            </tr>
            <tr>
                <td>Libellé :</td>
                <td><?= htmlspecialchars($niveau['libelle_niveau']) ?></td>
            </tr>
        </table>

        <p style="color: #856404; text-align: center; font-weight: bold; margin: 20px 0;">
            ⚠ Cette action est irréversible !
        </p>

        <form method="POST">
            <input type="hidden" name="id_niveau" value="<?= htmlspecialchars($niveau['id_niveau']) ?>">

            <div class="actions">
                <button type="submit" name="confirmer_suppression" class="btn btn-danger"
                        onclick="return confirm('Êtes-vous vraiment sûr de vouloir supprimer ce niveau ?');">
                    Confirmer la suppression
                </button>

                <a href="index.php?action=niveau_list" class="btn btn-secondary">
                    Annuler
                </a>
            </div>
        </form>
    </div>
<?php endif; ?>
