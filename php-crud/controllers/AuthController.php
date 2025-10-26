<?php

namespace Controllers;

require_once __DIR__ . '/EtudiantController.php';

class AuthController
{
    /**
     * Gère la connexion d'un utilisateur
     * @param array $post Données POST du formulaire
     * @return array ['success' => bool, 'errors' => array, 'message' => string, 'redirect' => string|null]
     */
    public function handleLogin($post)
    {
        $errors = [];
        $email = trim($post['email'] ?? '');
        $password = $post['password'] ?? '';

        // Validation des champs
        if (empty($email)) {
            $errors[] = "L'email est requis.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Le format de l'email est invalide.";
        }

        if (empty($password)) {
            $errors[] = "Le mot de passe est requis.";
        }

        // Si erreurs de validation, retourner sans tenter l'authentification
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors,
                'message' => 'Veuillez corriger les erreurs de saisie.',
                'redirect' => null
            ];
        }

        // Tentative d'authentification
        $etudiantController = new EtudiantController();
        $user = $etudiantController->loginEtudiant($email, $password);

        if ($user) {
            // Démarrer la session si nécessaire
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Stocker l'utilisateur en session
            $_SESSION['user'] = $user;

            return [
                'success' => true,
                'errors' => [],
                'message' => 'Connexion réussie !',
                'redirect' => '../../index.php'
            ];
        } else {
            return [
                'success' => false,
                'errors' => ['Email ou mot de passe incorrect.'],
                'message' => 'Identifiants invalides.',
                'redirect' => null
            ];
        }
    }

    /**
     * Gère la déconnexion d'un utilisateur
     * @return array ['success' => bool, 'errors' => array, 'message' => string, 'redirect' => string]
     */
    public function handleLogout()
    {
        // Démarrer la session si nécessaire
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Détruire toutes les variables de session
        $_SESSION = array();

        // Détruire le cookie de session s'il existe
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Détruire la session
        session_destroy();

        return [
            'success' => true,
            'errors' => [],
            'message' => 'Déconnexion réussie.',
            'redirect' => '../../index.php?message=deconnecte'
        ];
    }

    /**
     * Vérifie si un utilisateur est connecté
     * @return bool
     */
    public function isAuthenticated()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user']);
    }

    /**
     * Récupère l'utilisateur connecté
     * @return array|null
     */
    public function getAuthenticatedUser()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user'] ?? null;
    }
}
