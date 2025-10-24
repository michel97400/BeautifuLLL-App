<?php

namespace Controllers;

// Il est important d'inclure les modèles des tables liées
require_once __DIR__ . '/../model/SessionConversation.php';
require_once __DIR__ . '/../model/Agent.php';
require_once __DIR__ . '/../model/etudiant.php';

use Models\session_conversation;
use Models\Agent;
use Models\Etudiants;

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
    
    // NOUVELLE MÉTHODE
    public function getSingleSessionWithDetails($id_session)
    {
        $sessionModel = new session_conversation();
        $session = $sessionModel->readSingle($id_session);

        if ($session) {
            $agentModel = new Agent();
            $etudiantModel = new Etudiants();

            $agent = $agentModel->readSingle($session['id_agents']);
            $etudiant = $etudiantModel->readSingle($session['id_etudiant']);

            $session['nom_agent'] = $agent ? $agent['nom_agent'] : 'Inconnu';
            $session['nom_complet_etudiant'] = $etudiant ? ($etudiant['prenom'] . ' ' . $etudiant['nom']) : 'Inconnu';
        }

        return $session;
    }
    
    // NOUVELLE MÉTHODE
    public function getSessionsWithDetails()
    {
        $sessionModel = new session_conversation();
        $sessions = $sessionModel->read();

        $agentModel = new Agent();
        $etudiantModel = new Etudiants();

        foreach ($sessions as &$session) {
            $agent = $agentModel->readSingle($session['id_agents']);
            $etudiant = $etudiantModel->readSingle($session['id_etudiant']);

            $session['nom_agent'] = $agent ? $agent['nom_agent'] : 'Inconnu';
            $session['nom_complet_etudiant'] = $etudiant ? ($etudiant['prenom'] . ' ' . $etudiant['nom']) : 'Inconnu';
        }
        
        return $sessions;
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