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

    public function read_role()
    {
        $sql = "SELECT * FROM role";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

}

?>