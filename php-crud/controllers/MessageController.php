<?php

namespace Controllers;

require_once __DIR__ . '/../model/message.php';
require_once __DIR__ . '/SessionConversationController.php';

use Models\Message;

class MessageController
{
    // ... create, read, update, delete restent identiques ...
    public function createMessage($emetteur, $contenu_message, $date_heure_message, $id_session)
    {
        $Message = new Message();
        return $Message->create($emetteur, $contenu_message, $date_heure_message, $id_session);
    }

    public function getMessages()
    {
        $Message = new Message();
        return $Message->read();
    }
    
    // NOUVELLE MÉTHODE CORRIGÉE - Essentielle pour votre vue
    /**
     * Récupère tous les messages pour un ID de session spécifique.
     *
     * @param int $id_session L'ID de la session de conversation.
     * @return array La liste des messages pour cette session.
     */
    public function getMessagesBySessionId(int $id_session): array
    {
        $messageModel = new Message();
        // Cette méthode doit être créée dans votre modèle Models\Message
        // Elle exécutera une requête comme : "SELECT * FROM Message WHERE id_session = ? ORDER BY date_envoi ASC"
        return $messageModel->readBySessionId($id_session); 
    }

    public function getSingleMessage($id_message)
    {
        $Message = new Message();
        return $Message->readSingle($id_message);
    }

    public function updateMessage($id_message, $emetteur, $contenu_message, $date_heure_message, $id_session)
    {
        $Message = new Message();
        return $Message->update($id_message, $emetteur, $contenu_message, $date_heure_message, $id_session);
    }

    public function deleteMessage($id_message)
    {
        $Message = new Message();
        return $Message->delete($id_message);
    }

    /**
     * Gère la soumission du formulaire (création/modification)
     * @param array $post Données POST
     * @param bool $isEditMode true si modification, false si création
     * @param array|null $message_data Données du message en mode édition
     * @return array ['success' => bool, 'errors' => array, 'message' => string, 'message_data' => array|null, 'input' => array]
     */
    public function handleSubmit($post, $isEditMode, $message_data)
    {
        $errors = [];
        $message = '';

        // Récupération des données
        $emetteur = trim($post['emetteur'] ?? '');
        $contenu_message = trim($post['contenu_message'] ?? '');
        $date_heure_message = trim($post['date_heure_message'] ?? '');
        $id_session = $post['id_session'] ?? '';

        // Validation du emetteur
        $roles_valides = ['user', 'agent'];
        if (empty($emetteur)) {
            $errors[] = "L'émetteur est requis.";
        } elseif (!in_array($emetteur, $roles_valides)) {
            $errors[] = "L'émetteur sélectionné n'est pas valide. Choisissez 'user' ou 'agent'.";
        }

        // Validation du contenu
        if (empty($contenu_message)) {
            $errors[] = "Le contenu du message est requis.";
        } elseif (strlen($contenu_message) < 1) {
            $errors[] = "Le contenu doit contenir au moins 1 caractère.";
        } elseif (strlen($contenu_message) > 5000) {
            $errors[] = "Le contenu ne doit pas dépasser 5000 caractères.";
        }

        // Validation de date_heure_message
        if (empty($date_heure_message)) {
            $errors[] = "La date et heure d'envoi sont requises.";
        } else {
            // Valider le format DATETIME
            $date_obj = \DateTime::createFromFormat('Y-m-d\TH:i', $date_heure_message);
            if (!$date_obj) {
                $errors[] = "Le format de la date et heure d'envoi est invalide.";
            } else {
                // Convertir au format MySQL DATETIME
                $date_heure_message = $date_obj->format('Y-m-d H:i:s');
            }
        }

        // Validation de la session (id_session requis)
        if (empty($id_session)) {
            $errors[] = "La session de conversation est requise.";
        } else {
            $sessionController = new SessionConversationController();
            $session = $sessionController->getSingleSessionConversation($id_session);
            if (!$session) {
                $errors[] = "La session de conversation sélectionnée n'existe pas.";
            }
        }

        // Si aucune erreur, procéder à l'enregistrement
        if (empty($errors)) {
            if ($isEditMode) {
                // Mode modification
                $id_message = $post['id_message'];
                $result = $this->updateMessage($id_message, $emetteur, $contenu_message, $date_heure_message, $id_session);

                if ($result) {
                    $message = "Message modifié avec succès !";
                    // Recharger les données mises à jour
                    $message_data = $this->getSingleMessage($id_message);
                } else {
                    $errors[] = "Erreur lors de la modification du message en base de données.";
                }
            } else {
                // Mode création
                $result = $this->createMessage($emetteur, $contenu_message, $date_heure_message, $id_session);

                if ($result) {
                    $message = "Message créé avec succès !";
                } else {
                    $errors[] = "Erreur lors de la création du message en base de données.";
                }
            }
        }

        $input = compact('emetteur', 'contenu_message', 'date_heure_message', 'id_session');
        return [
            'success' => empty($errors),
            'errors' => $errors,
            'message' => $message,
            'message_data' => $message_data ?? null,
            'input' => $input
        ];
    }

    /**
     * Gère la suppression d'un message
     * @param string|null $id ID du message
     * @param bool $confirmed true si l'utilisateur a confirmé la suppression
     * @return array ['success' => bool, 'errors' => array, 'message' => string, 'message_data' => array|null, 'redirect' => string|null]
     */
    public function handleDelete($id, $confirmed = false)
    {
        $errors = [];
        $message = '';
        $message_data = null;
        $redirect = null;

        // Validation de l'ID
        if (!isset($id) || empty($id)) {
            $errors[] = "ID message manquant.";
            return [
                'success' => false,
                'errors' => $errors,
                'message' => "ID message manquant.",
                'message_data' => null,
                'redirect' => null
            ];
        }

        // Récupération du message
        $message_data = $this->getSingleMessage($id);

        if (!$message_data) {
            $errors[] = "Message introuvable.";
            return [
                'success' => false,
                'errors' => $errors,
                'message' => "Message introuvable.",
                'message_data' => null,
                'redirect' => null
            ];
        }

        // Si confirmation, procéder à la suppression
        if ($confirmed) {
            $result = $this->deleteMessage($id);

            if ($result) {
                return [
                    'success' => true,
                    'errors' => [],
                    'message' => "Message supprimé avec succès !",
                    'message_data' => $message_data,
                    'redirect' => 'index.php?action=message_list&message=supprime'
                ];
            } else {
                $errors[] = "Erreur lors de la suppression du message.";
                return [
                    'success' => false,
                    'errors' => $errors,
                    'message' => "Erreur lors de la suppression du message.",
                    'message_data' => $message_data,
                    'redirect' => null
                ];
            }
        }

        // Pas de confirmation, juste retourner le message pour afficher la page de confirmation
        return [
            'success' => false,
            'errors' => [],
            'message' => '',
            'message_data' => $message_data,
            'redirect' => null
        ];
    }
}