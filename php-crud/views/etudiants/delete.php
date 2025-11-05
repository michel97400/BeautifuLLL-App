<?php
require_once __DIR__ . '/../../includes/check_admin.php';
require_once __DIR__ . '/../../controllers/EtudiantController.php';

use Controllers\EtudiantController;

$message = '';
$etudiant = null;
$controller = new EtudiantController();

// Récupérer l'ID depuis l'URL
$id = $_GET['id'] ?? null;

// Vérifier si c'est une confirmation de suppression
$confirmed = $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmer_suppression']);

// Traiter via le controller (la redirection est gérée dans index.php)
$result = $controller->handleDelete($id, $confirmed);

// Récupérer les données du résultat
$message = $result['message'];
$etudiant = $result['etudiant'];

// Note: La redirection est maintenant gérée dans index.php AVANT le rendu HTML
?>

<?php include __DIR__ . '/../../includes/crud_nav.php'; ?>

<?php if ($message): ?>
    <div class="alert alert-error" style="text-align: center;">
        <strong>✗ <?= htmlspecialchars($message) ?></strong>
        <br><br>
        <a href="index.php?action=etudiant_list" class="btn btn-secondary">Retour à la liste</a>
    </div>
<?php elseif ($etudiant): ?>
    <div class="crud-card">
        <h2 style="color: #dc3545; text-align: center;">⚠ Confirmer la suppression</h2>

        <div class="alert alert-warning">
            <strong>Vous êtes sur le point de supprimer l'étudiant suivant :</strong>
        </div>

        <table>
            <tr>
                <td>ID :</td>
                <td><?= htmlspecialchars($etudiant['id_etudiant']) ?></td>
            </tr>
            <tr>
                <td>Nom :</td>
                <td><?= htmlspecialchars($etudiant['nom']) ?></td>
            </tr>
            <tr>
                <td>Prénom :</td>
                <td><?= htmlspecialchars($etudiant['prenom']) ?></td>
            </tr>
            <tr>
                <td>Email :</td>
                <td><?= htmlspecialchars($etudiant['email']) ?></td>
            </tr>
        </table>

        <p style="color: #856404; text-align: center; font-weight: bold; margin: 20px 0;">
            ⚠ Cette action est irréversible !
        </p>

        <form method="POST">
            <input type="hidden" name="id_etudiant" value="<?= htmlspecialchars($etudiant['id_etudiant']) ?>">

            <div class="actions">
                <button type="submit" name="confirmer_suppression" class="btn btn-danger"
                        onclick="return confirm('Êtes-vous vraiment sûr de vouloir supprimer cet étudiant ?');">
                    Confirmer la suppression
                </button>

                <a href="index.php?action=etudiant_list" class="btn btn-secondary">
                    Annuler
                </a>
            </div>
        </form>
    </div>
<?php endif; ?>
