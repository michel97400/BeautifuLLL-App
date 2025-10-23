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


    public function create_message($role_message, $contenu, $date_envoi, $id_session) {
        $sql = "INSERT INTO Message (role_message, contenu, date_envoi, id_session) VALUES (:role_message, :contenu, :date_envoi, :id_session)";

        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':role_message', $role_message);
        $stmt->bindParam(':contenu', $contenu);
        $stmt->bindParam(':date_envoi', $date_envoi);
        $stmt->bindParam(':id_session', $id_session);
        return $stmt->execute();
    }

    public function read_role()
    {
        $sql = "SELECT * FROM Message";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

}

?>