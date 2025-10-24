<?php

namespace Controllers;

// Il est important d'inclure les modèles des tables liées
use Models\Agent;
use Models\Matiere;
use Models\Etudiants;

class AgentController
{
    // ... (méthodes create, delete, etc. restent inchangées)
    public function createAgent($nom_agent, $type_agent, $avatar_agent, $est_actif, $description, $prompt_systeme, $id_matieres, $id_etudiant)
    {
        $Agent = new Agent();
        return $Agent->create($nom_agent, 
            $type_agent, 
            $avatar_agent, 
            $est_actif, 
            $description, 
            $prompt_systeme, 
            $id_matieres, 
            $id_etudiant
        );
    }

    public function getAgents()
    {
        $Agent = new Agent();
        return $Agent->read();
    }
    
    // NOUVELLE MÉTHODE
    public function getAgentsWithDetails()
    {
        $agentModel = new Agent();
        $agents = $agentModel->read();
        
        $matiereModel = new \Models\Matiere();
        $etudiantModel = new Etudiants();

        foreach($agents as &$agent) {
            if ($agent['id_matieres']) {
                $matiere = $matiereModel->readSingle($agent['id_matieres']);
                $agent['nom_matieres'] = $matiere ? $matiere['nom_matieres'] : 'N/A';
            } else {
                $agent['nom_matieres'] = 'Général';
            }

            $etudiant = $etudiantModel->readSingle($agent['id_etudiant']);
            $agent['nom_complet_etudiant'] = $etudiant ? ($etudiant['prenom'] . ' ' . $etudiant['nom']) : 'Inconnu';
        }
        
        return $agents;
    }

    public function getSingleAgent($id_agents)
    {
        $Agent = new Agent();
        return $Agent->readSingle($id_agents);
    }

    // NOUVELLE MÉTHODE
    public function getSingleAgentWithDetails($id_agents)
    {
        $agentModel = new Agent();
        $agent = $agentModel->readSingle($id_agents);
        
        if ($agent) {
            $matiereModel = new \Models\Matiere();
            $etudiantModel = new Etudiants();
            
            if ($agent['id_matieres']) {
                $matiere = $matiereModel->readSingle($agent['id_matieres']);
                $agent['nom_matieres'] = $matiere ? $matiere['nom_matieres'] : 'N/A';
            } else {
                $agent['nom_matieres'] = 'Général';
            }

            $etudiant = $etudiantModel->readSingle($agent['id_etudiant']);
            $agent['nom_complet_etudiant'] = $etudiant ? ($etudiant['prenom'] . ' ' . $etudiant['nom']) : 'Inconnu';
        }
        
        return $agent;
    }


    public function updateAgent($id_agents, $nom_agent, $type_agent, $avatar_agent, $est_actif, $description, $prompt_systeme, $id_matieres, $id_etudiant)
    {
        $Agent = new Agent();
        return $Agent->update($id_agents, 
            $nom_agent, 
            $type_agent, 
            $avatar_agent, 
            $est_actif, 
            $description, 
            $prompt_systeme, 
            $id_matieres, 
            $id_etudiant);
    }

    public function deleteAgent($id_agents)
    {
        $Agent = new Agent();
        return $Agent->delete($id_agents);
    }
}