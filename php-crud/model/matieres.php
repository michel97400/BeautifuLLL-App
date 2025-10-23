<?php

namespace Models;
require "php-crud/config/Database.php";
use Config\Database;

class Matiere{
    // private $id_matieres;
    // private $nom_matieres;
    // private $description_matiere;
    private $conn;

    public function __construct(){
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function read(){

        $sql = "SELECT * FROM matieres;";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


}

?>