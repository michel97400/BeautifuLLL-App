<?php

namespace Controllers;

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
}