<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../model/matieres.php';
use Models\Matiere;

$matiereModel = new Matiere();
$matieres = $matiereModel->read();

?>
<form method="post" class="crud-card" style="max-width: 500px; margin: 40px auto 24px auto; padding: 32px; display: flex; flex-direction: column; gap: 18px; background: #fff; box-shadow: 0 4px 16px rgba(0,0,0,0.10); border-radius: 12px;">
    <h2 style="margin-bottom: 18px; font-size: 1.3rem; color: #0078d7;">Choisissez une matière pour discuter avec l’Agent IA</h2>
    <label for="matiere" style="font-weight:500;">Matière :</label>
    <select name="matiere" id="matiere" style="padding:10px 12px; border-radius:8px; border:1px solid #e0e0e0; font-size:1rem;">
        <option value="">-- Sélectionner --</option>
        <?php foreach ($matieres as $m): ?>
            <option value="<?= htmlspecialchars($m['nom_matieres']) ?>"><?= htmlspecialchars($m['nom_matieres']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary" style="margin-top:18px;">Générer Agent</button>
</form>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['matiere']) && $_POST['matiere'] !== '') {
    $_SESSION['agent_ia_matiere'] = $_POST['matiere'];
    echo '<script>window.location.href = "index.php?action=agent-ia";</script>';
    exit;
}
?>
