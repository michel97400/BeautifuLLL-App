<?php
namespace Models;

use Config\Database;

class Message
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function create($emetteur, $contenu_message, $date_heure_message, $id_session) {
        // Compatible avec les deux structures de DB
        // Si votre table utilise 'role', 'contenu', 'date_envoi'
        // OU 'emetteur', 'contenu_message', 'date_heure_message'
        
        $sql = "INSERT INTO message (emetteur, contenu_message, date_heure_message, id_session) 
                VALUES (:emetteur, :contenu_message, :date_heure_message, :id_session)";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':emetteur', $emetteur);
            $stmt->bindParam(':contenu_message', $contenu_message);
            $stmt->bindParam(':date_heure_message', $date_heure_message);
            $stmt->bindParam(':id_session', $id_session);
            return $stmt->execute();
        } catch (\PDOException $e) {
            // Si erreur, essayer avec les nouveaux noms de colonnes
            $sql = "INSERT INTO message (role, contenu, date_envoi, id_session) 
                    VALUES (:role, :contenu, :date_envoi, :id_session)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':role', $emetteur);
            $stmt->bindParam(':contenu', $contenu_message);
            $stmt->bindParam(':date_envoi', $date_heure_message);
            $stmt->bindParam(':id_session', $id_session);
            return $stmt->execute();
        }
    }

    public function read() {
        $sql = "SELECT * FROM message ORDER BY date_heure_message ASC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Essayer avec date_envoi
            $sql = "SELECT * FROM message ORDER BY date_envoi ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
    }

    public function readSingle($id) {
        $sql = "SELECT * FROM message WHERE id_message = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function readBySessionId($id_session) {
        $sql = "SELECT * FROM message WHERE id_session = ? ORDER BY date_heure_message ASC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id_session]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Essayer avec date_envoi
            $sql = "SELECT * FROM message WHERE id_session = ? ORDER BY date_envoi ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id_session]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
    }
    
    public function update($id, $emetteur, $contenu_message, $date_heure_message, $id_session) {
        $sql = "UPDATE message SET emetteur = :emetteur, contenu_message = :contenu_message, 
                date_heure_message = :date_heure_message, id_session = :id_session 
                WHERE id_message = :id";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':emetteur', $emetteur);
            $stmt->bindParam(':contenu_message', $contenu_message);
            $stmt->bindParam(':date_heure_message', $date_heure_message);
            $stmt->bindParam(':id_session', $id_session);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (\PDOException $e) {
            // Essayer avec les nouveaux noms
            $sql = "UPDATE message SET role = :role, contenu = :contenu, 
                    date_envoi = :date_envoi, id_session = :id_session 
                    WHERE id_message = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':role', $emetteur);
            $stmt->bindParam(':contenu', $contenu_message);
            $stmt->bindParam(':date_envoi', $date_heure_message);
            $stmt->bindParam(':id_session', $id_session);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        }
    }

    public function delete($id) {
        $sql = "DELETE FROM message WHERE id_message = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>