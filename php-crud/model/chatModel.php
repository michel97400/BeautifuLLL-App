<?php


class ChatModel {
    
    /**
     * Initialise ou récupère l'historique de conversation
     */
    public static function getConversationHistory() {
        if (!isset($_SESSION['chat_messages'])) {
            $_SESSION['chat_messages'] = [];
        }
        return $_SESSION['chat_messages'];
    }
    
    /**
     * Ajoute un message à l'historique
     */
    public static function addMessage($role, $content) {
        if (!isset($_SESSION['chat_messages'])) {
            $_SESSION['chat_messages'] = [];
        }
        
        $_SESSION['chat_messages'][] = [
            'role' => $role,
            'content' => $content
        ];
    }
    
    /**
     * Réinitialise l'historique
     */
    public static function resetHistory() {
        $_SESSION['chat_messages'] = [];
    }
    
    /**
     * Obtenir le prompt système selon le rôle de l'utilisateur
     */
    private static function getSystemPrompt() {
        $user = $_SESSION['user'] ?? null;
        $isAdmin = $user && isset($user['role']) && $user['role'] === 'Administrateur';
        $isUser = $user && $user !== null;

        // Ajout : prompt personnalisé si l'utilisateur a choisi une matière
        if ($isAdmin) {
            $matiere = $_SESSION['agent_ia_matiere'] ?? null;
            $niveau = $_SESSION['agent_ia_id_niveau'] ?? null;
            return "Tu es un assistant IA pour BeautifuLLL AI, parlant à un Administrateur. Tu as accès à toutes les fonctionnalités et peux donner des conseils sur la gestion des étudiants, l'administration du système, et les statistiques. Sois professionnel et précis.";
        } elseif ($isUser) {
            $matiere = $_SESSION['agent_ia_matiere'] ?? null;
            $niveau = $_SESSION['agent_ia_id_niveau'] ?? null;
            $userName = $user['prenom'] ?? $user['nom'] ?? 'l\'utilisateur';
            if ($matiere && $niveau) {
                return "Tu es un agent IA expert dans la matière '$matiere' pour le niveau $niveau. Tu aides '$userName' à générer des cours, des conseils pédagogiques. Sois précis, pédagogique et propose des cours adaptées. Ne tutoie pas, utilise 'vous' au lieu de 'tu'";
            }
        } else {
            return "Tu es un assistant IA pour BeautifuLLL AI, parlant à un visiteur non connecté. Tu peux donner des informations générales sur le système et encourager la connexion pour plus de fonctionnalités. Sois accueillant.";
        }
    }
    
    /**
     * Envoie une requête à l'API Groq
     */
    public static function sendToGroq($messages) {
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

        // Ajouter le prompt système au début si pas déjà présent
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
            'model' => 'openai/gpt-oss-20b',
            'temperature' => 1,
            'max_completion_tokens' => 8192,
            'top_p' => 1,
            'reasoning_effort' => 'medium',
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
     * Limite l'historique aux N derniers messages (pour éviter de dépasser le contexte)
     */
    public static function limitHistory($maxMessages = 20) {
        if (isset($_SESSION['chat_messages']) && count($_SESSION['chat_messages']) > $maxMessages) {
            $_SESSION['chat_messages'] = array_slice($_SESSION['chat_messages'], -$maxMessages);
        }
    }
}
?>