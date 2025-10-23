<?php

namespace Controllers;

use Models\Agent;

class AgentController
{
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

    public function getSingleAgent($id_agents)
    {
        $Agent = new Agent();
        return $Agent->readSingle($id_agents);
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