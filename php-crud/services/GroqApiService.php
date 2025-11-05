<?php
namespace Services;



class GroqApiService {
    private $apiKey;
    private $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct() {
        $this->loadApiKey();
    }

    private function loadApiKey() {
        $envPath = __DIR__ . '/../../.env';
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                list($key, $value) = explode('=', $line, 2);
                if (trim($key) === 'GROQ_API_KEY') {
                    $this->apiKey = trim($value);
                    break;
                }
            }
        }
    }

    /**
     * Envoie une requête à l'API Groq
     * @param array $messages - Tableau de messages au format OpenAI
     * @param string $model - Modèle à utiliser (défaut: llama3-8b-8192)
     * @return array - Réponse de l'API avec ['success', 'message', 'error']
     */
    public function sendChatRequest($messages, $model = 'llama3-8b-8192',$temperature) {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => '',
                'error' => 'Clé API Groq non configurée',
            ];
        }

        $data = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => 8192
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return [
                'success' => false,
                'message' => '',
                'error' => 'Erreur cURL: ' . $curlError
            ];
        }

        if ($httpCode !== 200) {
            return [
                'success' => false,
                'message' => '',
                'error' => 'Erreur API (Code: ' . $httpCode . ')'
            ];
        }

        $result = json_decode($response, true);
        
        if (isset($result['choices'][0]['message']['content'])) {
            return [
                'success' => true,
                'message' => $result['choices'][0]['message']['content'],
                'error' => ''
            ];
        }

        return [
            'success' => false,
            'message' => '',
            'error' => 'Réponse API invalide'
        ];
    }
}