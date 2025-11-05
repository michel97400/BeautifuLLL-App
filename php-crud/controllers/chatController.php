<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/ChatModel.php';
require_once __DIR__ . '/../model/SessionConversation.php';
require_once __DIR__ . '/../model/message.php';
require_once __DIR__ . '/../model/Agent.php';

use Config\Database;
use Models\SessionConversation;
use Models\Message;
use Models\Agent;

class ChatController {
    
    /**
     * Affiche la vue du chat
     */
    public static function index() {
        require_once __DIR__ . '/../views/chat_card.php';
    }
    
    /**
     * Récupère l'historique des sessions de l'utilisateur
     */
    public static function getHistory() {
        header('Content-Type: application/json');
        
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['id_etudiant'])) {
                echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté']);
                exit;
            }
            
            $sessionModel = new SessionConversation();
            $sessions = $sessionModel->readByEtudiant($user['id_etudiant']);
            
            echo json_encode(['success' => true, 'sessions' => $sessions]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Erreur: ' . $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Charge une session existante
     */
    public static function loadSession() {
        header('Content-Type: application/json');
        
        try {
            $id_session = $_POST['id_session'] ?? null;
            
            if (!$id_session) {
                echo json_encode(['success' => false, 'error' => 'ID session manquant']);
                exit;
            }
            
            $sessionModel = new SessionConversation();
            $messageModel = new Message();
            
            $session = $sessionModel->readSingle($id_session);
            if (!$session) {
                echo json_encode(['success' => false, 'error' => 'Session introuvable']);
                exit;
            }
            
            // Récupérer les messages
            $messages = $messageModel->readBySessionId($id_session);
            
            // Stocker la session active
            $_SESSION['current_session_id'] = $id_session;
            $_SESSION['chat_messages'] = [];
            
            // Convertir les messages DB en format chat
            // Utiliser 'emetteur' ou 'role' selon votre DB
            foreach ($messages as $msg) {
                $role = $msg['role'] ?? $msg['emetteur'] ?? 'user';
                $content = $msg['contenu'] ?? $msg['contenu_message'] ?? '';
                
                $_SESSION['chat_messages'][] = [
                    'role' => $role,
                    'content' => $content
                ];
            }
            
            echo json_encode([
                'success' => true,
                'session' => $session,
                'messages' => $messages
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Erreur: ' . $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Crée ou récupère la session active
     */
    private static function getOrCreateSession() {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user || !isset($user['id_etudiant'])) {
                return null;
            }

            // Si une session existe déjà
            if (isset($_SESSION['current_session_id'])) {
                return $_SESSION['current_session_id'];
            }

            // Récupérer la matière choisie par l'utilisateur
            $id_matieres = $_SESSION['agent_ia_id_matieres'] ?? null;
            
            // Si pas d'ID matière en session, essayer de récupérer depuis la dernière session
            if (!$id_matieres) {
                $sessionModel = new SessionConversation();
                $lastSession = $sessionModel->getActiveSession($user['id_etudiant']);
                
                if ($lastSession && isset($lastSession['id_agents'])) {
                    // Récupérer l'agent de la dernière session
                    $agentModel = new Agent();
                    $agent = $agentModel->readSingle($lastSession['id_agents']);
                    
                    if ($agent && isset($agent['id_matieres'])) {
                        $id_matieres = $agent['id_matieres'];
                        // Mettre à jour la session avec cette info
                        $_SESSION['agent_ia_id_matieres'] = $id_matieres;
                        $_SESSION['agent_ia_matiere'] = $agent['nom_matieres'] ?? '';
                    }
                }
                
                // Si toujours pas d'ID matière, on ne peut pas continuer
                if (!$id_matieres) {
                    error_log("chatController: Matieres manquant et aucune session précédente");
                    return null;
                }
            }

            // Créer une nouvelle session
            $sessionModel = new SessionConversation();
            $agentModel = new Agent();

            // Trouver l'agent correspondant à la matière choisie
            $agent = $agentModel->getAgentByMatiere($id_matieres);
            if (!$agent) {
                error_log("chatController: Aucun agent trouvé pour id_matieres=$id_matieres");
                return null;
            }

            $id_agent = $agent['id_agents'];

            // Toujours créer une nouvelle session (ne pas réutiliser l'ancienne)
            // Cela permet d'avoir un historique distinct pour chaque conversation
            $id_session = $sessionModel->createAndReturnId(
                '00:00:00',
                null,
                $id_agent,
                $user['id_etudiant']
            );

            $_SESSION['current_session_id'] = $id_session;
            return $id_session;
        } catch (Exception $e) {
            error_log("Erreur création session: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Gère l'envoi d'un message
     */
    public static function sendMessage() {
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
                exit;
            }
            
            $userMessage = $_POST['message'] ?? '';
            
            if (empty(trim($userMessage))) {
                echo json_encode(['success' => false, 'error' => 'Message vide']);
                exit;
            }
            
            // Obtenir ou créer la session
            $id_session = self::getOrCreateSession();
            if (!$id_session) {
                echo json_encode(['success' => false, 'error' => 'Impossible de créer une session. Vérifiez qu\'il existe au moins un agent.']);
                exit;
            }
            
            // Ajouter le message utilisateur à l'historique
            ChatModel::addMessage('user', $userMessage);
            
            // Sauvegarder le message dans la DB
            $messageModel = new Message();
            
            // Utiliser la méthode create avec les bons paramètres
            $messageModel->create(
                'user',
                $userMessage,
                date('Y-m-d H:i:s'),
                $id_session
            );
            
            // Limiter l'historique
            ChatModel::limitHistory(20);
            
            // Récupérer l'historique complet
            $history = ChatModel::getConversationHistory();
            
            // Envoyer à Groq
            $result = ChatModel::sendToGroq($history);
            
            if ($result['success']) {
                // Ajouter la réponse à l'historique
                ChatModel::addMessage('assistant', $result['response']);
                $assistantMessage = $result['response'];
                // Sauvegarder la réponse dans la DB
                $messageModel->create(
                    'assistant',
                    $assistantMessage,
                    date('Y-m-d H:i:s'),
                    $id_session
                );
                
                $sessionModel = new SessionConversation();
                if(count($messageModel->getMessagesBySession($id_session)) == 2){
                    $intelligentTitle = ChatModel::createIntelligentTitle($userMessage,$assistantMessage);
                    if ($intelligentTitle['success'] && isset($intelligentTitle['response'])) {
                        $sessionModel->updateTitleById($id_session, $intelligentTitle['response']);
                    }
                }
                echo json_encode([
                    'success' => true,
                    'response' => $result['response'],
                    'session_id' => $id_session
                ]);



            } else {
                echo json_encode($result);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()]);
        }
        
        exit;
    }
    
    /**
     * Réinitialise la conversation
     */
    public static function reset() {
        header('Content-Type: application/json');
        
        try {
            ChatModel::resetHistory();
            unset($_SESSION['current_session_id']);
            // Ne pas supprimer agent_ia_id_matieres et agent_ia_matiere
            // pour permettre la création d'une nouvelle session avec le même agent
            
            echo json_encode(['success' => true, 'message' => 'Conversation réinitialisée']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Erreur: ' . $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Supprime une session
     */
    public static function deleteSession() {
        header('Content-Type: application/json');
        
        try {
            $id_session = $_POST['id_session'] ?? null;
            
            if (!$id_session) {
                echo json_encode(['success' => false, 'error' => 'ID session manquant']);
                exit;
            }
            
            $sessionModel = new SessionConversation();
            $result = $sessionModel->delete($id_session);
            
            if ($result) {
                // Si c'est la session active, la réinitialiser
                if (isset($_SESSION['current_session_id']) && $_SESSION['current_session_id'] == $id_session) {
                    unset($_SESSION['current_session_id']);
                    ChatModel::resetHistory();
                }
                
                echo json_encode(['success' => true, 'message' => 'Session supprimée']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Erreur lors de la suppression']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Erreur: ' . $e->getMessage()]);
        }
        
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
            case 'history':
                self::getHistory();
                break;
            case 'load':
                self::loadSession();
                break;
            case 'delete':
                self::deleteSession();
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