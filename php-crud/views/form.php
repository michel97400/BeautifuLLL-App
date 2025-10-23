<?php
require_once __DIR__ . '/../controllers/EtudiantController.php';
use Controllers\EtudiantController;
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $date_inscription = $_POST['date_inscription'] ?? date('Y-m-d');
    $consentement_rgpd = isset($_POST['consentement_rgpd']) ? 1 : 0;
    $id_role = $_POST['id_role'] ?? 1;
    $id_niveau = $_POST['id_niveau'] ?? 1;
    // Gestion de l'avatar
    $avatar = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $avatar = basename($_FILES['avatar']['name']);
        move_uploaded_file($_FILES['avatar']['tmp_name'], __DIR__ . '/../../uploads/' . $avatar);
    }
    $controller = new EtudiantController();
    $result = $controller->createEtudiant($nom, $prenom, $email, $avatar, $password, $date_inscription, $consentement_rgpd, $id_role, $id_niveau);
    if ($result) {
        header('Location: php-crud/views/success_create.php');
        exit;
    } else {
        $message = "Erreur lors de la création de l'étudiant.";
    }
}
?>
<form action="" method="POST" enctype="multipart/form-data" class="etudiant-form">
    <?php if ($message): ?>
        <div style="color: <?= $message === 'Étudiant créé avec succès !' ? 'green' : 'red' ?>; text-align:center; margin-bottom:10px;"> <?= htmlspecialchars($message) ?> </div>
    <?php endif; ?>
    <label for="nom">Nom :</label>
    <input type="text" id="nom" name="nom" required>

    <label for="prenom">Prénom :</label>
    <input type="text" id="prenom" name="prenom" required>

    <label for="email">Email :</label>
    <input type="email" id="email" name="email" required>

    <label for="avatar">Avatar :</label>
    <input type="file" id="avatar" name="avatar" accept="image/*">

    <label for="password">Mot de passe :</label>
    <input type="password" id="password" name="password" required>

    <label for="date_inscription">Date d'inscription :</label>
    <input type="date" id="date_inscription" name="date_inscription" value="<?php echo date('Y-m-d'); ?>" required>

    <label for="consentement_rgpd">
        <input type="checkbox" id="consentement_rgpd" name="consentement_rgpd" value="1" required>
        J'accepte la politique de confidentialité (RGPD)
    </label>

    <label for="id_role">Rôle :</label>
    <select id="id_role" name="id_role" required>
        <option value="2">Étudiant</option>
    </select>

    <label for="id_niveau">Niveau :</label>
    <select id="id_niveau" name="id_niveau" required>
        <option value="1">6 ème</option>
        <option value="2">5 ème</option>
        <option value="3">4 ème</option>
        <option value="4">3 ème</option>
        <option value="5">Second</option>
        <option value="6">Premiere</option>
        <option value="7">Terminale</option>
    </select>

    <button type="submit">Créer l'étudiant</button>
</form>
