<?php
// Inclure les contrôleurs/modèles nécessaires si non inclus globalement

$isEditMode = isset($niveau) && is_array($niveau); // Assume $niveau est chargé si en mode édition
$id_niveau = $isEditMode ? htmlspecialchars($niveau['id_niveau']) : '';
$libelle_niveau = $isEditMode ? htmlspecialchars($niveau['libelle_niveau']) : '';

$message = $message ?? '';
?>

<div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
    <h2 style="text-align: center; color: #ffc107;">
        <?= $isEditMode ? 'Modifier le Niveau' : 'Ajouter un nouveau Niveau' ?>
    </h2>

    <?php if ($message): ?>
        <p style="color: red; text-align: center;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" action="index.php?action=niveau_save">
        
        <?php if ($isEditMode): ?>
            <input type="hidden" name="id_niveau" value="<?= $id_niveau ?>">
        <?php endif; ?>

        <div style="margin-bottom: 15px;">
            <label for="libelle_niveau" style="display: block; margin-bottom: 5px; font-weight: bold;">Libellé du Niveau :</label>
            <input type="text" id="libelle_niveau" name="libelle_niveau" value="<?= $libelle_niveau ?>" required
                   style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
        </div>
        
        <div style="text-align: right;">
            <button type="submit"
                    style="background-color: #ffc107; color: black; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                <?= $isEditMode ? 'Enregistrer les modifications' : 'Créer le Niveau' ?>
            </button>
            <a href="index.php?action=niveau_list" 
               style="margin-left: 10px; padding: 10px 20px; border-radius: 5px; text-decoration: none; color: #6c757d;">
                Annuler
            </a>
        </div>
    </form>
</div>