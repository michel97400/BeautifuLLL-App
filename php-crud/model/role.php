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

    public function create($nomRole)
    {
        $sql = "INSERT INTO role (nom_role) VALUES (:nomRole)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nomRole', $nomRole);
        return $stmt->execute();
    }

}


?>