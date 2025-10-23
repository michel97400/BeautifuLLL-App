<?php

require_once __DIR__ . '/../php-crud/controllers/NiveauController.php';

$niveauController = new \Controllers\NiveauController();
$niveaux = $niveauController->getNiveaux(); 

echo "<h1>Liste des Niveaux</h1>";
echo "<table>";
echo "<tr><th>ID Niveau</th><th>Libellé</th><th>Actions</th></tr>";

foreach ($niveaux as $niveau) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($niveau['id_niveau']) . "</td>";
    echo "<td>" . htmlspecialchars($niveau['libelle_niveau']) . "</td>";
    echo "<td><a href='niveau_update.php?id=" . htmlspecialchars($niveau['id_niveau']) . "'>Modifier</a> | <a href='niveau_delete.php?id=" . htmlspecialchars($niveau['id_niveau']) . "'>Supprimer</a></td>";
    echo "</tr>";
}
echo "</table>";
?>