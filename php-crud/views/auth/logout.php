<?php
// Page de déconnexion
require_once __DIR__ . '/../../controllers/AuthController.php';

use Controllers\AuthController;

$controller = new AuthController();
$result = $controller->handleLogout();

if ($result['success']) {
    header('Location: ' . $result['redirect']);
    exit;
}
?>
