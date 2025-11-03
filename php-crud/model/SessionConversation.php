<?php
namespace Models;

use Config\Database;

class session_conversation
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function readByEtudiant($id_etudiant)
{
    $sql = "SELECT sc.*, a.nom_agent, a.avatar_agent, 
            (SELECT COUNT(*) FROM Message m WHERE m.id_session = sc.id_session) as nb_messages
            FROM SESSION_CONVERSATION sc
            LEFT JOIN Agent a ON sc.id_agents = a.id_agents
            WHERE sc.id_etudiant = :id_etudiant
            ORDER BY sc.date_heure_debut DESC";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id_etudiant', $id_etudiant, \PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

    public function readSessionWithMessages($id_session)
{
    // Récupérer la session
    $session = $this->readSingle($id_session);
    
    if (!$session) {
        return null;
    }
    
    // Récupérer les messages
    $sql = "SELECT * FROM Message WHERE id_session = :id_session ORDER BY date_envoi ASC";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id_session', $id_session, \PDO::PARAM_INT);
    $stmt->execute();
    $session['messages'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
    return $session;
}
    public function createAndReturnId($duree_session, $date_heure_fin, $id_agents, $id_etudiant)
{
    $sql = "INSERT INTO session_conversation (duree_session, date_heure_fin, id_agents, id_etudiant)
            VALUES (:duree_session, :date_heure_fin, :id_agents, :id_etudiant)";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':duree_session', $duree_session);
    $stmt->bindParam(':date_heure_fin', $date_heure_fin);
    $stmt->bindParam(':id_agents', $id_agents, \PDO::PARAM_INT);
    $stmt->bindParam(':id_etudiant', $id_etudiant, \PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        return $this->conn->lastInsertId();
    }
    return false;
}

    public function readSingle($id)
    {
        $sql = "SELECT * FROM SESSION_CONVERSATION WHERE id_session = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create($duree_session, $date_heure_fin, $id_agents, $id_etudiant)
    {
        $sql = "INSERT INTO session_conversation (duree_session, date_heure_fin, id_agents, id_etudiant)
                VALUES (:duree_session, :date_heure_fin, :id_agents, :id_etudiant)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':duree_session', $duree_session); // Type TIME
        $stmt->bindParam(':date_heure_fin', $date_heure_fin); // Type DATETIME
        $stmt->bindParam(':id_agents', $id_agents, \PDO::PARAM_INT);
        $stmt->bindParam(':id_etudiant', $id_etudiant, \PDO::PARAM_INT);
        return $stmt->execute();
    }
public function read()
    {
        $sql = "SELECT * FROM SESSION_CONVERSATION ORDER BY date_heure_debut DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

public function update($id_session, $date_heure_debut, $duree_session, $date_heure_fin, $id_agents, $id_etudiant)
    {
        $sql = "UPDATE SESSION_CONVERSATION SET 
                    date_heure_debut = :date_heure_debut, 
                    duree_session = :duree_session, 
                    date_heure_fin = :date_heure_fin, 
                    id_agents = :id_agents, 
                    id_etudiant = :id_etudiant
                WHERE id_session = :id_session";
        
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindParam(':date_heure_debut', $date_heure_debut);
        $stmt->bindParam(':duree_session', $duree_session);
        $stmt->bindParam(':date_heure_fin', $date_heure_fin);
        $stmt->bindParam(':id_agents', $id_agents, \PDO::PARAM_INT);
        $stmt->bindParam(':id_etudiant', $id_etudiant, \PDO::PARAM_INT);
        $stmt->bindParam(':id_session', $id_session, \PDO::PARAM_INT);
        
        return $stmt->execute();
}
public function delete($id_session)
    {
        $sql = "DELETE FROM SESSION_CONVERSATION WHERE id_session = :id_session";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_session', $id_session, \PDO::PARAM_INT);
        return $stmt->execute();
    }
}

?>