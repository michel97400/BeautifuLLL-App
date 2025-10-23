<?php
// Vue pour afficher la liste des Étudiants.
require_once __DIR__ . '/../includes/check_admin.php';
require_once __DIR__ . '/../controllers/EtudiantController.php';

$etudiantController = new \Controllers\EtudiantController();
$etudiants = $etudiantController->getEtudiantsWithDetails();

echo "<h1>Liste des Étudiants</h1>";

// Afficher message de succès si suppression
if (isset($_GET['message']) && $_GET['message'] === 'supprime') {
    echo '<div style="color: green; text-align:center; margin-bottom:20px; padding:15px; border-radius:5px; background-color: #d4edda;">';
    echo '<strong>Étudiant supprimé avec succès !</strong>';
    echo '</div>';
}

echo "<table>";
echo "<tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Niveau</th><th>Rôle</th><th>RGPD</th><th>Actions</th></tr>";

foreach ($etudiants as $etudiant) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($etudiant['id_etudiant']) . "</td>";
    echo "<td>" . htmlspecialchars($etudiant['nom']) . "</td>";
    echo "<td>" . htmlspecialchars($etudiant['prenom']) . "</td>";
    echo "<td>" . htmlspecialchars($etudiant['email']) . "</td>";
    // Affiche les libellés des jointures (si disponibles) ou les IDs par défaut
    echo "<td>" . htmlspecialchars($etudiant['niveau'] ?? $etudiant['id_niveau']) . "</td>";
    echo "<td>" . htmlspecialchars($etudiant['role'] ?? $etudiant['id_role']) . "</td>";
    echo "<td>" . ($etudiant['consentement_rgpd'] ? 'Oui' : 'Non') . "</td>";
    echo "<td><a href='../../index.php?action=modifier_etudiant&id=" . htmlspecialchars($etudiant['id_etudiant']) . "'>Modifier</a> | <a href='../../index.php?action=supprimer_etudiant&id=" . htmlspecialchars($etudiant['id_etudiant']) . "' style='color: #dc3545;'>Supprimer</a></td>";
    echo "</tr>";
}
echo "</table>";
?>