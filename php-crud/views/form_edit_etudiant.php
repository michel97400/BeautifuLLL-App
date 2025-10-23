<?php
// Formulaire de modification d'un étudiant
require_once __DIR__ . '/../controllers/EtudiantController.php';
use Controllers\EtudiantController;

// Récupération de l'ID depuis l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<p class="error">Aucun ID d\'étudiant spécifié.</p>';
    exit;
}

$id_etudiant = intval($_GET['id']);
$controller = new EtudiantController();
$etudiant = $controller->getSingleEtudiant($id_etudiant);

if (!$etudiant) {
    echo '<p class="error">Étudiant non trouvé.</p>';
    exit;
}
?>

<div class="form-container">
    <div class="form-header">
        <h2>Modifier l'étudiant</h2>
        <a href="?action=details_etudiant&id=<?php echo $etudiant['id_etudiant']; ?>" class="btn btn-secondary">← Retour aux détails</a>
    </div>

    <form action="../controllers/EtudiantController.php?action=update&id=<?php echo $etudiant['id_etudiant']; ?>" method="POST" enctype="multipart/form-data" class="etudiant-form">

        <input type="hidden" name="id_etudiant" value="<?php echo htmlspecialchars($etudiant['id_etudiant']); ?>">

        <div class="form-group">
            <label for="nom">Nom :</label>
            <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($etudiant['nom']); ?>" required>
        </div>

        <div class="form-group">
            <label for="prenom">Prénom :</label>
            <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($etudiant['prenom']); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email :</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($etudiant['email']); ?>" required>
        </div>

        <div class="form-group">
            <label for="avatar">Avatar actuel :</label>
            <?php if (!empty($etudiant['avatar'])): ?>
                <div class="current-avatar">
                    <img src="<?php echo htmlspecialchars($etudiant['avatar']); ?>" alt="Avatar actuel" class="avatar-preview">
                    <p class="avatar-path"><?php echo htmlspecialchars($etudiant['avatar']); ?></p>
                </div>
            <?php else: ?>
                <p class="no-avatar">Aucun avatar</p>
            <?php endif; ?>
            <label for="avatar">Changer l'avatar :</label>
            <input type="file" id="avatar" name="avatar" accept="image/*">
            <small>Laissez vide pour conserver l'avatar actuel</small>
        </div>

        <div class="form-group">
            <label for="password">Nouveau mot de passe :</label>
            <input type="password" id="password" name="password">
            <small>Laissez vide pour conserver le mot de passe actuel</small>
        </div>

        <div class="form-group">
            <label for="date_inscription">Date d'inscription :</label>
            <input type="date" id="date_inscription" name="date_inscription" value="<?php echo htmlspecialchars($etudiant['date_inscription']); ?>" required>
        </div>

        <div class="form-group checkbox-group">
            <label for="consentement_rgpd">
                <input type="checkbox" id="consentement_rgpd" name="consentement_rgpd" value="1" <?php echo $etudiant['consentement_rgpd'] ? 'checked' : ''; ?>>
                J'accepte la politique de confidentialité (RGPD)
            </label>
        </div>

        <div class="form-group">
            <label for="id_role">Rôle :</label>
            <select id="id_role" name="id_role" required>
                <option value="1" <?php echo $etudiant['id_role'] == 1 ? 'selected' : ''; ?>>Étudiant</option>
                <option value="2" <?php echo $etudiant['id_role'] == 2 ? 'selected' : ''; ?>>Autre</option>
            </select>
        </div>

        <div class="form-group">
            <label for="id_niveau">Niveau :</label>
            <select id="id_niveau" name="id_niveau" required>
                <option value="1" <?php echo $etudiant['id_niveau'] == 1 ? 'selected' : ''; ?>>6 ème</option>
                <option value="2" <?php echo $etudiant['id_niveau'] == 2 ? 'selected' : ''; ?>>5 ème</option>
                <option value="3" <?php echo $etudiant['id_niveau'] == 3 ? 'selected' : ''; ?>>4 ème</option>
                <option value="4" <?php echo $etudiant['id_niveau'] == 4 ? 'selected' : ''; ?>>3 ème</option>
                <option value="5" <?php echo $etudiant['id_niveau'] == 5 ? 'selected' : ''; ?>>Second</option>
                <option value="6" <?php echo $etudiant['id_niveau'] == 6 ? 'selected' : ''; ?>>Premiere</option>
                <option value="7" <?php echo $etudiant['id_niveau'] == 7 ? 'selected' : ''; ?>>Terminale</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">💾 Enregistrer les modifications</button>
            <a href="?action=details_etudiant&id=<?php echo $etudiant['id_etudiant']; ?>" class="btn btn-secondary">❌ Annuler</a>
        </div>
    </form>
</div>

<style>
.form-container {
    padding: 20px;
    max-width: 700px;
    margin: 0 auto;
}

.form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.form-header h2 {
    margin: 0;
    color: #333;
}

.etudiant-form {
    background-color: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #555;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="password"],
.form-group input[type="date"],
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    box-sizing: border-box;
}

.form-group input[type="file"] {
    display: block;
    margin-top: 10px;
    padding: 5px;
}

.form-group small {
    display: block;
    margin-top: 5px;
    color: #666;
    font-size: 12px;
    font-style: italic;
}

.checkbox-group {
    display: flex;
    align-items: center;
}

.checkbox-group label {
    display: flex;
    align-items: center;
    font-weight: normal;
    cursor: pointer;
}

.checkbox-group input[type="checkbox"] {
    margin-right: 10px;
    width: auto;
}

.current-avatar {
    margin-bottom: 15px;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 5px;
}

.avatar-preview {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #ddd;
    display: block;
    margin-bottom: 10px;
}

.avatar-path {
    font-size: 12px;
    color: #666;
    margin: 0;
}

.no-avatar {
    color: #999;
    font-style: italic;
    margin-bottom: 10px;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 30px;
}

.btn {
    padding: 12px 25px;
    text-decoration: none;
    border-radius: 5px;
    display: inline-block;
    cursor: pointer;
    border: none;
    font-size: 16px;
    font-weight: bold;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn:hover {
    opacity: 0.8;
}

.error {
    background-color: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid #f5c6cb;
    margin: 20px;
}
</style>
