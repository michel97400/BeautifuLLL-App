<?php


namespace Controllers;

require_once __DIR__ . '/../model/SessionConversation.php';
use Models\session_conversation;

class SessionConversationController
{
    public function createSessionConversation($duree_session, $date_heure_fin, $id_agents, $id_etudiant)
    {
        $SessionConversation = new session_conversation();
        return $SessionConversation->create($duree_session, $date_heure_fin, $id_agents, $id_etudiant);
    }

    public function getSessionConversation()
    {
        $SessionConversation = new session_conversation();
        return $SessionConversation->read();
    }

    public function getSingleSessionConversation($id_session)
    {
        $SessionConversation = new session_conversation();
        return $SessionConversation->readSingle($id_session);
    }

    public function updateSessionConversation($id_session, $date_heure_debut, $duree_session, $date_heure_fin, $id_agents, $id_etudiant)
    {
        $SessionConversation = new session_conversation();
        return $SessionConversation->update($id_session, 
            $date_heure_debut, 
            $duree_session, 
            $date_heure_fin, 
            $id_agents, 
            $id_etudiant);
    }

    public function deleteSessionConversation($id_Session)
    {
        $SessionConversation = new session_conversation();
        return $SessionConversation->delete($id_Session);
    }
}