<?php

namespace Controllers;

use Models\Message;

class MessageController
{
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