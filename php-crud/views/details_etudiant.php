<?php
// Détails d'un étudiant
require_once __DIR__ . '/../controllers/EtudiantController.php';
use Controllers\EtudiantController;

// Récupération de l'ID depuis l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<p class="error">Aucun ID d\'étudiant spécifié.</p>';
    exit;
}

$id_etudiant = intval($_GET['id']);
$controller = new EtudiantController();
$etudiant = $controller->getSingleEtudiant($id_etudiant);

if (!$etudiant) {
    echo '<p class="error">Étudiant non trouvé.</p>';
    exit;
}
?>

<div class="details-etudiant">
    <div class="header-actions">
        <h2>Détails de l'étudiant</h2>
        <div>
            <a href="?action=liste_etudiants" class="btn btn-secondary">← Retour à la liste</a>
            <a href="?action=modifier_etudiant&id=<?php echo $etudiant['id_etudiant']; ?>" class="btn btn-warning">✏️ Modifier</a>
            <a href="?action=supprimer_etudiant&id=<?php echo $etudiant['id_etudiant']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet étudiant ?');">🗑️ Supprimer</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <?php if (!empty($etudiant['avatar'])): ?>
                <img src="<?php echo htmlspecialchars($etudiant['avatar']); ?>" alt="Avatar de <?php echo htmlspecialchars($etudiant['prenom']); ?>" class="avatar-large">
            <?php else: ?>
                <div class="avatar-placeholder">
                    <span><?php echo strtoupper(substr($etudiant['prenom'], 0, 1) . substr($etudiant['nom'], 0, 1)); ?></span>
                </div>
            <?php endif; ?>
            <h3><?php echo htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']); ?></h3>
        </div>

        <div class="card-body">
            <div class="info-group">
                <label>ID Étudiant :</label>
                <span><?php echo htmlspecialchars($etudiant['id_etudiant']); ?></span>
            </div>

            <div class="info-group">
                <label>Nom :</label>
                <span><?php echo htmlspecialchars($etudiant['nom']); ?></span>
            </div>

            <div class="info-group">
                <label>Prénom :</label>
                <span><?php echo htmlspecialchars($etudiant['prenom']); ?></span>
            </div>

            <div class="info-group">
                <label>Email :</label>
                <span><a href="mailto:<?php echo htmlspecialchars($etudiant['email']); ?>"><?php echo htmlspecialchars($etudiant['email']); ?></a></span>
            </div>

            <div class="info-group">
                <label>Date d'inscription :</label>
                <span><?php echo date('d/m/Y', strtotime($etudiant['date_inscription'])); ?></span>
            </div>

            <div class="info-group">
                <label>Consentement RGPD :</label>
                <span class="badge <?php echo $etudiant['consentement_rgpd'] ? 'badge-success' : 'badge-danger'; ?>">
                    <?php echo $etudiant['consentement_rgpd'] ? '✓ Accepté' : '✗ Non accepté'; ?>
                </span>
            </div>

            <div class="info-group">
                <label>Rôle ID :</label>
                <span><?php echo htmlspecialchars($etudiant['id_role']); ?></span>
            </div>

            <?php if (isset($etudiant['id_niveau'])): ?>
            <div class="info-group">
                <label>Niveau ID :</label>
                <span><?php echo htmlspecialchars($etudiant['id_niveau']); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.details-etudiant {
    padding: 20px;
    max-width: 800px;
    margin: 0 auto;
}

.header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.header-actions h2 {
    margin: 0;
    color: #333;
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

.btn-secondary {
    background-color: #6c757d;
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

.card {
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    text-align: center;
}

.avatar-large {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 4px solid white;
    object-fit: cover;
    margin-bottom: 15px;
}

.avatar-placeholder {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 4px solid white;
    background-color: rgba(255,255,255,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
}

.avatar-placeholder span {
    font-size: 48px;
    font-weight: bold;
    color: white;
}

.card-header h3 {
    margin: 0;
    font-size: 24px;
}

.card-body {
    padding: 30px;
}

.info-group {
    display: flex;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.info-group:last-child {
    border-bottom: none;
}

.info-group label {
    font-weight: bold;
    color: #555;
    width: 200px;
    flex-shrink: 0;
}

.info-group span {
    color: #333;
    flex-grow: 1;
}

.info-group a {
    color: #007bff;
    text-decoration: none;
}

.info-group a:hover {
    text-decoration: underline;
}

.badge {
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 12px;
    font-weight: bold;
}

.badge-success {
    background-color: #28a745;
    color: white;
}

.badge-danger {
    background-color: #dc3545;
    color: white;
}

.error {
    background-color: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid #f5c6cb;
}
</style>
