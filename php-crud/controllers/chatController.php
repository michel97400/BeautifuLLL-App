<?php
session_start();
require_once __DIR__ . '/../model/ChatModel.php';

class ChatController {
    
    /**
     * Affiche la vue du chat
     */
    public static function index() {
    require_once __DIR__ . '/../views/chat_card.php';
    }
    
    /**
     * Gère l'envoi d'un message
     */
    public static function sendMessage() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
            exit;
        }
        
        $userMessage = $_POST['message'] ?? '';
        
        if (empty(trim($userMessage))) {
            echo json_encode(['success' => false, 'error' => 'Message vide']);
            exit;
        }
        
        // Ajouter le message utilisateur à l'historique
        ChatModel::addMessage('user', $userMessage);
        
        // Limiter l'historique pour ne pas dépasser le contexte
        ChatModel::limitHistory(20);
        
        // Récupérer l'historique complet
        $history = ChatModel::getConversationHistory();
        
        // Envoyer à Groq
        $result = ChatModel::sendToGroq($history);
        
        if ($result['success']) {
            // Ajouter la réponse à l'historique
            ChatModel::addMessage('assistant', $result['response']);
            
            echo json_encode([
                'success' => true,
                'response' => $result['response']
            ]);
        } else {
            echo json_encode($result);
        }
        
        exit;
    }
    
    /**
     * Réinitialise la conversation
     */
    public static function reset() {
        header('Content-Type: application/json');
        
        ChatModel::resetHistory();
        
        echo json_encode(['success' => true, 'message' => 'Conversation réinitialisée']);
        exit;
    }
    
    /**
     * Route les requêtes selon l'action
     */
    public static function handleRequest() {
        $action = $_GET['action'] ?? 'index';
        
        switch ($action) {
            case 'send':
                self::sendMessage();
                break;
            case 'reset':
                self::reset();
                break;
            default:
                self::index();
                break;
        }
    }
}

// Point d'entrée si ce fichier est appelé directement
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    ChatController::handleRequest();
}
?>