<?php

namespace Controllers;

require_once __DIR__ . '/../model/message.php';
require_once __DIR__ . '/SessionConversationController.php';

use Models\Message;

class MessageController
{
    // ... create, read, update, delete restent identiques ...
    public function createMessage($role_message, $contenu, $date_envoi, $id_session)
    {
        $Message = new Message();
        return $Message->create($role_message, $contenu, $date_envoi, $id_session);
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

    public function updateMessage($id_message, $role_message, $contenu, $date_envoi, $id_session)
    {
        $Message = new Message();
        return $Message->update($id_message, $role_message, $contenu, $date_envoi, $id_session);
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
        $role_message = trim($post['role_message'] ?? '');
        $contenu = trim($post['contenu'] ?? '');
        $date_envoi = trim($post['date_envoi'] ?? '');
        $id_session = $post['id_session'] ?? '';

        // Validation du role_message
        $roles_valides = ['user', 'assistant'];
        if (empty($role_message)) {
            $errors[] = "Le rôle est requis.";
        } elseif (!in_array($role_message, $roles_valides)) {
            $errors[] = "Le rôle sélectionné n'est pas valide. Choisissez 'user' ou 'assistant'.";
        }

        // Validation du contenu
        if (empty($contenu)) {
            $errors[] = "Le contenu du message est requis.";
        } elseif (strlen($contenu) < 1) {
            $errors[] = "Le contenu doit contenir au moins 1 caractère.";
        } elseif (strlen($contenu) > 5000) {
            $errors[] = "Le contenu ne doit pas dépasser 5000 caractères.";
        }

        // Validation de date_envoi
        if (empty($date_envoi)) {
            $errors[] = "La date et heure d'envoi sont requises.";
        } else {
            // Valider le format DATETIME
            $date_obj = \DateTime::createFromFormat('Y-m-d\TH:i', $date_envoi);
            if (!$date_obj) {
                $errors[] = "Le format de la date et heure d'envoi est invalide.";
            } else {
                // Convertir au format MySQL DATETIME
                $date_envoi = $date_obj->format('Y-m-d H:i:s');
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
                $result = $this->updateMessage($id_message, $role_message, $contenu, $date_envoi, $id_session);

                if ($result) {
                    $message = "Message modifié avec succès !";
                    // Recharger les données mises à jour
                    $message_data = $this->getSingleMessage($id_message);
                } else {
                    $errors[] = "Erreur lors de la modification du message en base de données.";
                }
            } else {
                // Mode création
                $result = $this->createMessage($role_message, $contenu, $date_envoi, $id_session);

                if ($result) {
                    $message = "Message créé avec succès !";
                } else {
                    $errors[] = "Erreur lors de la création du message en base de données.";
                }
            }
        }

        $input = compact('role_message', 'contenu', 'date_envoi', 'id_session');
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