<?php
// Formulaire de création d'un étudiant
?>
<form action="../controllers/EtudiantController.php?action=create" method="POST" enctype="multipart/form-data" class="etudiant-form">
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
        <option value="1">Étudiant</option>
        <option value="2">Autre</option>
    </select>

    <label for="id_niveau">Niveau :</label>
    <select id="id_niveau" name="id_niveau" required>
        <option value="1">6 ème</option>
        <option value="2">5 ème</option>
        <option value="3">4 ème</option>
        <option value="3">3 ème</option>
        <option value="3">Second</option>
        <option value="3">Premiere</option>
        <option value="3">Terminale</option>
        ('6 eme'), ('5 eme'), ('4 eme'), ('3 eme'), ('Second'), ('Premiere'), ('Terminale')
    </select>

    <button type="submit">Créer l'étudiant</button>
</form>
