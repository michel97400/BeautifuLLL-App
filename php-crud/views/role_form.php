<?php
// Inclure les contrôleurs/modèles nécessaires si non inclus globalement

$isEditMode = isset($role) && is_array($role); // Assume $role est chargé si en mode édition
$id_role = $isEditMode ? htmlspecialchars($role['id_role']) : '';
$nom_role = $isEditMode ? htmlspecialchars($role['nom_role']) : '';

// $message est supposé contenir un message d'erreur ou de succès si applicable
$message = $message ?? '';

// Dans un vrai projet, il faudrait charger la liste des rôles si besoin, mais cette table est simple

?>

<div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
    <h2 style="text-align: center; color: #007bff;">
        <?= $isEditMode ? 'Modifier le Rôle' : 'Ajouter un nouveau Rôle' ?>
    </h2>

    <?php if ($message): ?>
        <p style="color: red; text-align: center;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" action="index.php?action=role_save"> <?php if ($isEditMode): ?>
            <input type="hidden" name="id_role" value="<?= $id_role ?>">
        <?php endif; ?>

        <div style="margin-bottom: 15px;">
            <label for="nom_role" style="display: block; margin-bottom: 5px; font-weight: bold;">Nom du Rôle :</label>
            <input type="text" id="nom_role" name="nom_role" value="<?= $nom_role ?>" required
                   style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
        </div>
        
        <div style="text-align: right;">
            <button type="submit"
                    style="background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                <?= $isEditMode ? 'Enregistrer les modifications' : 'Créer le Rôle' ?>
            </button>
            <a href="index.php?action=role_list" 
               style="margin-left: 10px; padding: 10px 20px; border-radius: 5px; text-decoration: none; color: #6c757d;">
                Annuler
            </a>
        </div>
    </form>
</div>