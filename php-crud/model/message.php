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



    public function create($role_message, $contenu, $date_envoi, $id_session) {
        $sql = "INSERT INTO Message (role_message, contenu, date_envoi, id_session) VALUES (:role_message, :contenu, :date_envoi, :id_session)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':role_message', $role_message);
        $stmt->bindParam(':contenu', $contenu);
        $stmt->bindParam(':date_envoi', $date_envoi);
        $stmt->bindParam(':id_session', $id_session);
        return $stmt->execute();
    }

    public function read() {
        $sql = "SELECT * FROM Message";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function readSingle($id) {
        $sql = "SELECT * FROM Message WHERE id_message = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function update($id, $role_message, $contenu, $date_envoi, $id_session) {
        $sql = "UPDATE Message SET role_message = :role_message, contenu = :contenu, date_envoi = :date_envoi, id_session = :id_session WHERE id_message = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':role_message', $role_message);
        $stmt->bindParam(':contenu', $contenu);
        $stmt->bindParam(':date_envoi', $date_envoi);
        $stmt->bindParam(':id_session', $id_session);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $sql = "DELETE FROM Message WHERE id_message = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

}

?>