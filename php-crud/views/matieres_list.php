<?php

require_once __DIR__ . '/../controllers/MatiereController.php';


$matiereController = new \Controllers\MatiereController();
$matieres = $matiereController->getMatieres(); 

echo "<h1>Liste des Matières</h1>";
echo "<table>";
echo "<tr><th>ID Matière</th><th>Nom</th><th>Description</th><th>Actions</th></tr>";

foreach ($matieres as $matiere) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($matiere['id_matieres']) . "</td>";
    echo "<td>" . htmlspecialchars($matiere['nom_matieres']) . "</td>";
    // Affiche une version courte de la description
    echo "<td>" . htmlspecialchars(substr($matiere['description_matiere'], 0, 50)) . (strlen($matiere['description_matiere']) > 50 ? '...' : '') . "</td>"; 
    echo "<td><a href='matiere_update.php?id=" . htmlspecialchars($matiere['id_matieres']) . "'>Modifier</a> | <a href='matiere_delete.php?id=" . htmlspecialchars($matiere['id_matieres']) . "'>Supprimer</a></td>";
    echo "</tr>";
}
echo "</table>";
?>