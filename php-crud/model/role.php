<?php
namespace Models;

use Config\Database;

class Role
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }


    public function create($nom_role) {
        $sql = "INSERT INTO role (nom_role) VALUES (:nom_role)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nom_role', $nom_role);
        return $stmt->execute();
    }

    public function read() {
        $sql = "SELECT * FROM role";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function readSingle($id) {
        $sql = "SELECT * FROM role WHERE id_role = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function update($id, $nom_role) {
        $sql = "UPDATE role SET nom_role = :nom_role WHERE id_role = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nom_role', $nom_role);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $sql = "DELETE FROM role WHERE id_role = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

}

?>