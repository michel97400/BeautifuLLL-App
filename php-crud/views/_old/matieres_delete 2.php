<?php
require_once __DIR__ . '/../includes/check_admin.php';
require_once __DIR__ . '/../controllers/MatiereController.php';

use Controllers\MatiereController; // Assurez-vous que ce contrôleur existe

$message = '';
$matiere = null;
$controller = new MatiereController();

$id_column = 'id_matieres';
$entity_name = 'Matière';
$list_action = 'matiere_list';

// Vérifier si l'ID est présent
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $message = "ID de la {$entity_name} manquant.";
} else {
    $id_entity = $_GET['id'];
    $matiere = $controller->getSingleMatiere($id_entity); // Assurez-vous d'avoir la méthode

    if (!$matiere) {
        $message = "{$entity_name} introuvable.";
    }
}

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmer_suppression'])) {
    $id_entity = $_POST[$id_column];
    
    // NOTE IMPORTANTE: Le contrôleur doit gérer la vérification des clés étrangères !
    // Une matière ne peut pas être supprimée si elle est utilisée par un Agent.
    $result = $controller->deleteMatiere($id_entity);

    if ($result) {
        header("Location: ../../index.php?action={$list_action}&message=supprime");
        exit;
    } else {
        $message = "Erreur lors de la suppression de la {$entity_name}. Vérifiez les dépendances (Agents).";
    }
}
?>

<?php if ($message): ?>
    <div style="color: red; text-align:center; margin-bottom:20px; padding:15px; border-radius:5px; background-color: #f8d7da;">
        <strong><?= htmlspecialchars($message) ?></strong>
        <br><br>
        <a href="index.php?action=<?= $list_action ?>" style="color: #0078d7; text-decoration: none;">Retour à la liste</a>
    </div>
<?php elseif ($matiere): ?>
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 2px solid #dc3545; border-radius: 10px; background-color: #fff3cd;">
        <h2 style="color: #dc3545; text-align: center;">Confirmer la suppression de la <?= $entity_name ?></h2>

        <div style="margin: 20px 0; padding: 15px; background-color: white; border-radius: 5px;">
            <p style="font-size: 16px; margin-bottom: 15px;">
                <strong>Vous êtes sur le point de supprimer la <?= $entity_name ?> suivante :</strong>
            </p>

            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px; font-weight: bold; width: 40%;">ID :</td>
                    <td style="padding: 8px;"><?= htmlspecialchars($matiere['id_matieres']) ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: bold;">Nom :</td>
                    <td style="padding: 8px;"><?= htmlspecialchars($matiere['nom_matieres']) ?></td>
                </tr>
            </table>
        </div>

        <p style="color: #856404; text-align: center; font-weight: bold; margin: 20px 0;">
            Cette action est irréversible ! Si des agents sont liés, la suppression échouera sauf configuration spéciale de la BDD.
        </p>

        <form method="POST" style="text-align: center;">
            <input type="hidden" name="<?= $id_column ?>" value="<?= htmlspecialchars($matiere[$id_column]) ?>">

            <button type="submit" name="confirmer_suppression"
                    style="background-color: #dc3545; color: white; padding: 12px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; margin-right: 10px;"
                    onclick="return confirm('Êtes-vous vraiment sûr de vouloir supprimer cette matière ?');">
                Confirmer la suppression
            </button>

            <a href="index.php?action=<?= $list_action ?>"
               style="background-color: #6c757d; color: white; padding: 12px 30px; border-radius: 5px; font-size: 16px; text-decoration: none; display: inline-block;">
                Annuler
            </a>
        </form>
    </div>
<?php endif; ?>