<?php
// Vue pour afficher la liste des Rôles.
require_once __DIR__ . '/../php-crud/controllers/RoleController.php';


$roleController = new \Controllers\RoleController();
$roles = $roleController->getRoles(); 

echo "<h1>Liste des Rôles</h1>";
echo "<table>";
echo "<tr><th>ID Rôle</th><th>Nom du Rôle</th><th>Actions</th></tr>";

foreach ($roles as $role) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($role['id_role']) . "</td>";
    echo "<td>" . htmlspecialchars($role['nom_role']) . "</td>";
    // Les actions à définir (ex: modifier, supprimer)
    echo "<td><a href='role_update.php?id=" . htmlspecialchars($role['id_role']) . "'>Modifier</a> | <a href='role_delete.php?id=" . htmlspecialchars($role['id_role']) . "'>Supprimer</a></td>";
    echo "</tr>";
}
echo "</table>";
?>