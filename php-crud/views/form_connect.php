<?php
// Formulaire de connexion (login)
?>
<form action="../php-crud/controllers/EtudiantController.php?action=login" method="POST" class="etudiant-form">
    <h2>Connexion</h2>
    <label for="email">Email :</label>
    <input type="email" id="email" name="email" required>

    <label for="password">Mot de passe :</label>
    <input type="password" id="password" name="password" required>

    <button type="submit">Se connecter</button>
</form>
