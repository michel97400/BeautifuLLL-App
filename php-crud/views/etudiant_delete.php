<?php
require_once __DIR__ . '/../includes/check_admin.php';
require_once __DIR__ . '/../controllers/EtudiantController.php';

use Controllers\EtudiantController;

$message = '';
$etudiant = null;
$controller = new EtudiantController();

// Vérifier si l'ID est présent
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $message = "ID étudiant manquant.";
} else {
    $id_etudiant = $_GET['id'];
    $etudiant = $controller->getSingleEtudiant($id_etudiant);

    if (!$etudiant) {
        $message = "Étudiant introuvable.";
    }
}

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmer_suppression'])) {
    $id_etudiant = $_POST['id_etudiant'];
    $result = $controller->deleteEtudiant($id_etudiant);

    if ($result) {
        // Redirection vers la liste avec message de succès
        header('Location: ../../index.php?action=etudiant_list&message=supprime');
        exit;
    } else {
        $message = "Erreur lors de la suppression de l'étudiant.";
    }
}
?>

<?php if ($message): ?>
    <div style="color: red; text-align:center; margin-bottom:20px; padding:15px; border-radius:5px; background-color: #f8d7da;">
        <strong><?= htmlspecialchars($message) ?></strong>
        <br><br>
        <a href="index.php?action=etudiant_list" style="color: #0078d7; text-decoration: none;">Retour à la liste</a>
    </div>
<?php elseif ($etudiant): ?>
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 2px solid #dc3545; border-radius: 10px; background-color: #fff3cd;">
        <h2 style="color: #dc3545; text-align: center;">Confirmer la suppression</h2>

        <div style="margin: 20px 0; padding: 15px; background-color: white; border-radius: 5px;">
            <p style="font-size: 16px; margin-bottom: 15px;">
                <strong>Vous êtes sur le point de supprimer l'étudiant suivant :</strong>
            </p>

            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px; font-weight: bold; width: 40%;">ID :</td>
                    <td style="padding: 8px;"><?= htmlspecialchars($etudiant['id_etudiant']) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: bold;">Nom :</td>
                    <td style="padding: 8px;"><?= htmlspecialchars($etudiant['nom']) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: bold;">Prénom :</td>
                    <td style="padding: 8px;"><?= htmlspecialchars($etudiant['prenom']) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: bold;">Email :</td>
                    <td style="padding: 8px;"><?= htmlspecialchars($etudiant['email']) ?></td>
                </tr>
            </table>
        </div>

        <p style="color: #856404; text-align: center; font-weight: bold; margin: 20px 0;">
            Cette action est irréversible !
        </p>

        <form method="POST" style="text-align: center;">
            <input type="hidden" name="id_etudiant" value="<?= htmlspecialchars($etudiant['id_etudiant']) ?>">

            <button type="submit" name="confirmer_suppression"
                    style="background-color: #dc3545; color: white; padding: 12px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; margin-right: 10px;"
                    onclick="return confirm('Êtes-vous vraiment sûr de vouloir supprimer cet étudiant ?');">
                Confirmer la suppression
            </button>

            <a href="index.php?action=etudiant_list"
               style="background-color: #6c757d; color: white; padding: 12px 30px; border-radius: 5px; font-size: 16px; text-decoration: none; display: inline-block;">
                Annuler
            </a>
        </form>
    </div>
<?php endif; ?>
