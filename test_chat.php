<?php
// Placez ce fichier à la racine
session_start();

// Simuler un utilisateur connecté si nécessaire
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'id_etudiant' => 3,
        'nom' => 'Dubois',
        'prenom' => 'Pierre',
        'email' => 'pierre.dubois@test.com',
        'role' => 'Administrateur'
    ];
}

// Simuler une matière choisie
if (!isset($_SESSION['agent_ia_matiere'])) {
    $_SESSION['agent_ia_matiere'] = 'Français';
}

echo "<h2>Test d'envoi de message</h2>";

// Test 1 : Vérifier que le contrôleur existe
$controllerPath = __DIR__ . '/php-crud/controllers/chatController.php';
if (file_exists($controllerPath)) {
    echo "✅ chatController.php existe<br>";
} else {
    echo "❌ chatController.php n'existe pas au chemin: $controllerPath<br>";
    exit;
}

// Test 2 : Inclure le contrôleur
require_once $controllerPath;

if (class_exists('ChatController')) {
    echo "✅ Classe ChatController trouvée<br>";
} else {
    echo "❌ Classe ChatController non trouvée<br>";
    exit;
}

// Test 3 : Vérifier les méthodes
$methods = get_class_methods('ChatController');
echo "<h3>Méthodes disponibles :</h3>";
echo "<ul>";
foreach ($methods as $method) {
    echo "<li>$method</li>";
}
echo "</ul>";

// Test 4 : Tester l'envoi d'un message
echo "<h3>Test d'envoi de message</h3>";

// Simuler une requête POST
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['message'] = 'Test de message';

ob_start();
ChatController::sendMessage();
$response = ob_get_clean();

echo "<h4>Réponse brute :</h4>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Essayer de décoder la réponse
$data = json_decode($response, true);
if ($data) {
    echo "<h4>Réponse décodée :</h4>";
    echo "<pre>" . print_r($data, true) . "</pre>";
    
    if (isset($data['success']) && $data['success']) {
        echo "<p style='color: green; font-weight: bold;'>✅ Message envoyé avec succès !</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Erreur : " . ($data['error'] ?? 'Inconnue') . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ La réponse n'est pas du JSON valide</p>";
}

echo "<hr>";
echo "<p><a href='index.php?action=agent-ia'>Retour au chat IA</a></p>";
?>