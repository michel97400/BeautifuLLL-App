<?php
// Inclure les contrôleurs/modèles nécessaires si non inclus globalement

$isEditMode = isset($matiere) && is_array($matiere); // Assume $matiere est chargé si en mode édition
$id_matieres = $isEditMode ? htmlspecialchars($matiere['id_matieres']) : '';
$nom_matieres = $isEditMode ? htmlspecialchars($matiere['nom_matieres']) : '';
$description_matiere = $isEditMode ? htmlspecialchars($matiere['description_matiere']) : '';

$message = $message ?? '';
?>

<div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
    <h2 style="text-align: center; color: #28a745;">
        <?= $isEditMode ? 'Modifier la Matière' : 'Ajouter une nouvelle Matière' ?>
    </h2>

    <?php if ($message): ?>
        <p style="color: red; text-align: center;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" action="index.php?action=matiere_save">
        
        <?php if ($isEditMode): ?>
            <input type="hidden" name="id_matieres" value="<?= $id_matieres ?>">
        <?php endif; ?>

        <div style="margin-bottom: 15px;">
            <label for="nom_matieres" style="display: block; margin-bottom: 5px; font-weight: bold;">Nom de la Matière :</label>
            <input type="text" id="nom_matieres" name="nom_matieres" value="<?= $nom_matieres ?>" required
                   style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="description_matiere" style="display: block; margin-bottom: 5px; font-weight: bold;">Description :</label>
            <textarea id="description_matiere" name="description_matiere" rows="4"
                      style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;"><?= $description_matiere ?></textarea>
        </div>
        
        <div style="text-align: right;">
            <button type="submit"
                    style="background-color: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                <?= $isEditMode ? 'Enregistrer les modifications' : 'Créer la Matière' ?>
            </button>
            <a href="index.php?action=matiere_list" 
               style="margin-left: 10px; padding: 10px 20px; border-radius: 5px; text-decoration: none; color: #6c757d;">
                Annuler
            </a>
        </div>
    </form>
</div>