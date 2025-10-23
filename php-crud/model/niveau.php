<?php

namespace Models;

use Config\Database;

class Niveau{

    private $conn;

    public function __construct(){
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function read(){

        $sql = "SELECT * FROM niveau";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);

    }


}

?>