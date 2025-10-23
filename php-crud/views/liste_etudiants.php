<?php
// Liste de tous les étudiants
require_once __DIR__ . '/../controllers/EtudiantController.php';
use Controllers\EtudiantController;

$controller = new EtudiantController();
$etudiants = $controller->getEtudiant();
?>

<div class="liste-etudiants">
    <h2>Liste des étudiants</h2>

    <div class="actions-header">
        <a href="?action=creer_etudiant" class="btn btn-primary">➕ Créer un nouvel étudiant</a>
    </div>

    <?php if (empty($etudiants)): ?>
        <p class="no-data">Aucun étudiant enregistré pour le moment.</p>
    <?php else: ?>
        <table class="table-etudiants">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Avatar</th>
                    <th>Date d'inscription</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($etudiants as $etudiant): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($etudiant['id_etudiant']); ?></td>
                        <td><?php echo htmlspecialchars($etudiant['nom']); ?></td>
                        <td><?php echo htmlspecialchars($etudiant['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($etudiant['email']); ?></td>
                        <td>
                            <?php if (!empty($etudiant['avatar'])): ?>
                                <img src="<?php echo htmlspecialchars($etudiant['avatar']); ?>" alt="Avatar" class="avatar-mini">
                            <?php else: ?>
                                <span class="no-avatar">Aucun</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($etudiant['date_inscription']); ?></td>
                        <td class="actions">
                            <a href="?action=details_etudiant&id=<?php echo $etudiant['id_etudiant']; ?>" class="btn btn-info" title="Voir les détails">👁️</a>
                            <a href="?action=modifier_etudiant&id=<?php echo $etudiant['id_etudiant']; ?>" class="btn btn-warning" title="Modifier">✏️</a>
                            <a href="?action=supprimer_etudiant&id=<?php echo $etudiant['id_etudiant']; ?>" class="btn btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet étudiant ?');">🗑️</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
.liste-etudiants {
    padding: 20px;
}

.liste-etudiants h2 {
    margin-bottom: 20px;
    color: #333;
}

.actions-header {
    margin-bottom: 20px;
}

.btn {
    padding: 8px 15px;
    text-decoration: none;
    border-radius: 5px;
    display: inline-block;
    margin: 0 5px;
    cursor: pointer;
    border: none;
    font-size: 14px;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-info {
    background-color: #17a2b8;
    color: white;
}

.btn-warning {
    background-color: #ffc107;
    color: black;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn:hover {
    opacity: 0.8;
}

.table-etudiants {
    width: 100%;
    border-collapse: collapse;
    background-color: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.table-etudiants th,
.table-etudiants td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.table-etudiants th {
    background-color: #f8f9fa;
    font-weight: bold;
    color: #333;
}

.table-etudiants tr:hover {
    background-color: #f5f5f5;
}

.avatar-mini {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.no-avatar {
    color: #999;
    font-style: italic;
}

.no-data {
    text-align: center;
    padding: 40px;
    color: #666;
    font-style: italic;
}

.actions {
    white-space: nowrap;
}
</style>
