<?php
namespace Models;

use Config\Database;
use PDO; // Ajout pour utiliser PDO::FETCH_ASSOC sans le namespace global

class Agent
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function create($nom_agent, $type_agent, $avatar_agent, $est_actif, $description, $prompt_systeme, $id_matieres, $id_etudiant)
    {
        $sql = "INSERT INTO agent (nom_agent, type_agent, avatar_agent, est_actif, description, prompt_systeme, id_matieres, $id_etudiant) VALUES (:nom_agent, :type_agent, :avatar_agent, :est_actif, :description, :prompt_systeme, :id_matieres, $id_etudiant)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nom_agent', $nom_agent);
        $stmt->bindParam(':type_agent', $type_agent);
        $stmt->bindParam(':avatar_agent', $avatar_agent);
        $stmt->bindParam(':est_actif', $est_actif, PDO::PARAM_INT);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':prompt_systeme', $prompt_systeme);
        $stmt->bindParam(':id_matieres', $id_matieres, PDO::PARAM_INT);
        $stmt->bindParam(':id_etudiants', $id_etudiant, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function read()
    {
        $sql = "SELECT a.* FROM agent a";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readSingle($id_agents)
    {
        $sql = "SELECT * FROM agent WHERE id_agents = :id_agents LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_agents', $id_agents, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id_agents, $nom_agent, $type_agent, $avatar_agent, $est_actif, $description, $prompt_systeme, $id_matieres, $id_etudiant)
    {
        $sql = "UPDATE agent SET 
                    nom_agent = :nom_agent, 
                    type_agent = :type_agent, 
                    avatar_agent = :avatar_agent, 
                    est_actif = :est_actif, 
                    description = :description, 
                    prompt_systeme = :prompt_systeme, 
                    id_matieres = :id_matieres, 
                    id_etudiant = :id_etudiant
                WHERE id_agents = :id_agents";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_agents', $id_agents, PDO::PARAM_INT);
        $stmt->bindParam(':nom_agent', $nom_agent);
        $stmt->bindParam(':type_agent', $type_agent);
        $stmt->bindParam(':avatar_agent', $avatar_agent);
        $stmt->bindParam(':est_actif', $est_actif, PDO::PARAM_INT);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':prompt_systeme', $prompt_systeme);
        $stmt->bindParam(':id_matieres', $id_matieres, PDO::PARAM_INT);
        $stmt->bindParam(':id_etudiant', $id_etudiant, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function delete($id_agents)
    {
        $sql = "DELETE FROM agent WHERE id_agents = :id_agents";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_agents', $id_agents, PDO::PARAM_INT);
        return $stmt->execute();
    }
}

?>