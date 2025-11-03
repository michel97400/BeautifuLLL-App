<?php

require_once __DIR__ . '/../model/agent.php';
require_once __DIR__ . '/../model/message.php';
require_once __DIR__ . '/../model/SessionConversation.php';
require_once __DIR__ . '/../model/etudiant.php';
require_once __DIR__ . '/../model/niveau.php';

use Models\Agent;
use Models\Message;
use Models\SessionConversation;
use Models\Etudiants;
use Models\Niveau;

class ChatModel {

    private static $currentSession = null;
    private static $currentAgent = null;

    /**
     * MODIFIE: Initialise ou recupere la session de conversation en base de donnees
     * @return int|null ID de la session ou null si echec
     */
    public static function initializeSession() {
        // Verifier si une session est deja en cours
        if (self::$currentSession !== null) {
            return self::$currentSession;
        }

        // Recuperer l'ID de la matiere depuis la session
        $id_matieres = $_SESSION['agent_ia_id_matieres'] ?? null;
        if (!$id_matieres) {
            error_log("ChatModel: id_matieres manquant dans la session");
            return null;
        }

        // Recuperer l'ID de l'etudiant depuis la session utilisateur
        $user = $_SESSION['user'] ?? null;
        if (!$user || !isset($user['id_etudiant'])) {
            error_log("ChatModel: Utilisateur non connecte ou id_etudiant manquant");
            return null;
        }
        $id_etudiant = $user['id_etudiant'];

        // Charger l'agent pour cette matiere
        $agentModel = new Agent();
        $agent = $agentModel->getAgentByMatiere($id_matieres);
        if (!$agent) {
            error_log("ChatModel: Aucun agent trouve pour id_matieres=$id_matieres");
            return null;
        }
        self::$currentAgent = $agent;

        // Verifier s'il existe une session active
        $sessionModel = new SessionConversation();
        $activeSession = $sessionModel->getActiveSession($id_etudiant);

        if ($activeSession && $activeSession['id_agents'] == $agent['id_agents']) {
            // Session active existante avec le bon agent
            self::$currentSession = $activeSession['id_session'];
        } else {
            // Creer une nouvelle session
            $newSessionId = $sessionModel->create($agent['id_agents'], $id_etudiant);
            if ($newSessionId) {
                self::$currentSession = $newSessionId;
            } else {
                error_log("ChatModel: Echec de creation de session");
                return null;
            }
        }

        return self::$currentSession;
    }

    /**
     * NOUVEAU: Terminer la session actuelle
     */
    public static function endSession() {
        if (self::$currentSession) {
            $sessionModel = new SessionConversation();
            $sessionModel->endSession(self::$currentSession);
            self::$currentSession = null;
            self::$currentAgent = null;
        }
    }

    /**
     * MODIFIE: Recupere l'historique de conversation depuis la base de donnees
     * @param int $limit Nombre max de messages a recuperer
     * @return array Messages au format [['role' => '...', 'content' => '...'], ...]
     */
    public static function getConversationHistory($limit = 20) {
        $sessionId = self::initializeSession();
        if (!$sessionId) {
            return [];
        }

        $messageModel = new Message();
        $messages = $messageModel->getRecentMessages($sessionId, $limit);

        // Convertir format DB vers format API
        $history = [];
        foreach ($messages as $msg) {
            $history[] = [
                'role' => $msg['role'],
                'content' => $msg['contenu']
            ];
        }

        return $history;
    }

    /**
     * MODIFIE: Ajoute un message a la base de donnees
     * @param string $role 'user' ou 'assistant'
     * @param string $content Contenu du message
     * @return int|false ID du message cree ou false
     */
    public static function addMessage($role, $content) {
        $sessionId = self::initializeSession();
        if (!$sessionId) {
            error_log("ChatModel::addMessage - Session non initialisee");
            return false;
        }

        $messageModel = new Message();
        return $messageModel->create($sessionId, $role, $content);
    }

    /**
     * MODIFIE: Reinitialise l'historique (termine la session actuelle)
     */
    public static function resetHistory() {
        self::endSession();
        unset($_SESSION['agent_ia_id_matieres']);
        unset($_SESSION['agent_ia_matiere']);
    }
    
    /**
     * MODIFIE: Obtenir le prompt systeme depuis l'agent en base de donnees
     * Le prompt est adapte au niveau de l'etudiant
     */
    private static function getSystemPrompt() {
        // Si pas d'agent charge, utiliser prompt generique
        if (self::$currentAgent === null) {
            self::initializeSession();
        }

        if (self::$currentAgent === null) {
            return "Tu es un assistant IA pour BeautifuLLL AI. Sois utile et precis.";
        }

        // Recuperer le prompt de base de l'agent
        $basePrompt = self::$currentAgent['prompt_systeme'];

        // Recuperer les informations de l'etudiant
        $user = $_SESSION['user'] ?? null;
        if (!$user || !isset($user['id_etudiant'])) {
            return $basePrompt;
        }

        // Charger le niveau de l'etudiant
        $etudiantModel = new Etudiants();
        $etudiant = $etudiantModel->readSingle($user['id_etudiant']);

        if ($etudiant && isset($etudiant['id_niveau'])) {
            $niveauModel = new Niveau();
            $niveau = $niveauModel->readSingle($etudiant['id_niveau']);

            if ($niveau && isset($niveau['libelle_niveau'])) {
                // Completer le prompt avec le niveau
                $niveauLibelle = $niveau['libelle_niveau'];
                $prenom = $etudiant['prenom'] ?? 'l\'etudiant';

                $finalPrompt = $basePrompt . "\n\n";
                $finalPrompt .= "CONTEXTE ETUDIANT:\n";
                $finalPrompt .= "- Nom: $prenom\n";
                $finalPrompt .= "- Niveau scolaire: $niveauLibelle\n";
                $finalPrompt .= "- Matiere: " . self::$currentAgent['nom_matieres'] . "\n\n";
                $finalPrompt .= "Adapte tes reponses au niveau $niveauLibelle. ";
                $finalPrompt .= "Sois pedagogique et encourage l'apprentissage.";

                return $finalPrompt;
            }
        }

        return $basePrompt;
    }
    
    /**
     * MODIFIE: Envoie une requete a l'API Groq avec parametres de l'agent
     */
    public static function sendToGroq($messages) {
        // Initialiser la session et charger l'agent
        self::initializeSession();

        // Parametres LLM par defaut (si pas d'agent charge)
        $model = 'openai/gpt-oss-20b';
        $temperature = 0.7;
        $max_tokens = 8192;
        $top_p = 1.0;
        $reasoning_effort = 'medium';

        // Utiliser les parametres de l'agent si disponible
        if (self::$currentAgent !== null) {
            $model = self::$currentAgent['model'] ?? $model;
            $temperature = floatval(self::$currentAgent['temperature'] ?? $temperature);
            $max_tokens = intval(self::$currentAgent['max_tokens'] ?? $max_tokens);
            $top_p = floatval(self::$currentAgent['top_p'] ?? $top_p);
            $reasoning_effort = self::$currentAgent['reasoning_effort'] ?? $reasoning_effort;
        }

        // Charger les variables d'environnement
        $envPath = __DIR__ . '/../../.env';
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '=') !== false) {
                    list($key, $value) = explode('=', trim($line), 2);
                    $_ENV[$key] = $value;
                }
            }
        }
        $apiKey = $_ENV['GROQ_API_KEY'] ?? getenv('GROQ_API_KEY');
        $apiUrl = $_ENV['GROQ_API_URL'] ?? getenv('GROQ_API_URL');

        // Ajouter le prompt systeme au debut si pas deja present
        $systemPrompt = trim(self::getSystemPrompt() ?? '');
        $hasSystemPrompt = false;
        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $hasSystemPrompt = true;
                break;
            }
        }
        if (!$hasSystemPrompt && $systemPrompt !== '') {
            array_unshift($messages, [
                'role' => 'system',
                'content' => $systemPrompt
            ]);
        }

        // Adapter le format des messages pour l'API Groq
        $groqMessages = [];
        foreach ($messages as $msg) {
            // Adapter 'role' et 'content' pour Groq
            $role = $msg['role'];
            if ($role === 'user' || $role === 'assistant' || $role === 'system') {
                $groqMessages[] = [
                    'role' => $role,
                    'content' => $msg['content']
                ];
            }
        }

        $data = [
            'messages' => $groqMessages,
            'model' => $model,
            'temperature' => $temperature,
            'max_completion_tokens' => $max_tokens,
            'top_p' => $top_p,
            'reasoning_effort' => $reasoning_effort,
            'stream' => false,
            'stop' => null
        ];

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        // SSL options for Windows/WAMP environments
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return [
                'success' => false,
                'error' => 'Erreur de connexion: ' . $curlError
            ];
        }

        if ($httpCode !== 200) {
            $apiError = 'Erreur API (HTTP ' . $httpCode . ')';
            $details = $response;
            // Essayer d'extraire le message d'erreur JSON
            $jsonDetails = json_decode($response, true);
            if (is_array($jsonDetails) && isset($jsonDetails['error']['message'])) {
                $apiError .= ': ' . $jsonDetails['error']['message'];
            }
            return [
                'success' => false,
                'error' => $apiError,
                'details' => $details
            ];
        }

        $result = json_decode($response, true);

        if (isset($result['choices'][0]['message']['content'])) {
            return [
                'success' => true,
                'response' => $result['choices'][0]['message']['content']
            ];
        }

        return [
            'success' => false,
            'error' => 'Réponse invalide de l\'API'
        ];
    }
    
    /**
     * DEPRECATED: Limite l'historique aux N derniers messages
     * Cette methode est maintenant obsolete car la limitation est geree
     * automatiquement par getConversationHistory($limit) et getRecentMessages()
     * Conservee pour compatibilite avec l'ancien code
     */
    public static function limitHistory($maxMessages = 20) {
        // Plus necessaire - la limitation est geree par getConversationHistory()
        // qui utilise getRecentMessages() de la BDD
        return;
    }

    /**
     * NOUVEAU: Obtenir les informations de l'agent actuel
     * @return array|null Agent ou null si pas initialise
     */
    public static function getCurrentAgent() {
        self::initializeSession();
        return self::$currentAgent;
    }

    /**
     * NOUVEAU: Obtenir l'ID de la session actuelle
     * @return int|null ID session ou null si pas initialisee
     */
    public static function getCurrentSessionId() {
        return self::$currentSession;
    }
}
?>