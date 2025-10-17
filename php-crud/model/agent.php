<?php
namespace Models;

use Config\Database;

class Agent
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function create($id_agents, $nom_agent, $type_agent, $avatar_agent, $est_actif, $description, $prompt_systeme)
    {
        $sql = "INSERT INTO agent (id_agents, nom_agent, type_agent, avatar_agent, est_actif, description,prompt_systeme) VALUES (:id_agents, :nom_agent, :type_agent, :avatar_agent, :est_actif, :description, :prompt_systeme)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(param':id_agents', var : $id_agents);
        $stmt->bindParam(':nom_agent', $nom_agent);
        $stmt->bindParam(':type_agent', $type_agent);
        $stmt->bindParam(':avatar_agent', $avatar_agent);
        $stmt->bindParam(':est_actif', $est_actif);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':prompt_systeme', $prompt_systeme);
        return $stmt->execute();
    }

    public function read()
    {
        $sql = "SELECT * FROM agent";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function update($id_agents, $nom_agent, $type_agent, $avatar_agent, $est_actif, $description, $prompt_systeme)
    {
        $sql = "UPDATE agent SET nom_agent = :nom_agent, type_agent = :type_agent, avatar_agent = :avatar_agent, est_actif = :est_actif, description = :description, prompt_systeme = :prompt_systeme   WHERE id_agents = :id_agents";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(param':id_user', var : $id_agents);
        $stmt->bindParam(':nom_agent', $nom_agent);
        $stmt->bindParam(':type_agent', $type_agent);
        $stmt->bindParam(':avatar_agent', $avatar_agent);
        $stmt->bindParam(':est_actif', $est_actif);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':prompt_systeme', $prompt_systeme);
        return $stmt->execute();
    }

    public function delete($id)
    {
        $sql = "DELETE FROM agent WHERE id_agents = :id_agents";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_agents', $id);
        return $stmt->execute();
    }
}

?>