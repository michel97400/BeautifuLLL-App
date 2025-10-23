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


    public function create($nom_matieres, $description_matiere) {
        $sql = "INSERT INTO matieres (nom_matieres, description_matiere) VALUES (:nom_matieres, :description_matiere)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nom_matieres', $nom_matieres);
        $stmt->bindParam(':description_matiere', $description_matiere);
        return $stmt->execute();
    }

    public function read() {
        $sql = "SELECT * FROM matieres;";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function readSingle($id) {
        $sql = "SELECT * FROM matieres WHERE id_matieres = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function update($id, $nom_matieres, $description_matiere) {
        $sql = "UPDATE matieres SET nom_matieres = :nom_matieres, description_matiere = :description_matiere WHERE id_matieres = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nom_matieres', $nom_matieres);
        $stmt->bindParam(':description_matiere', $description_matiere);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $sql = "DELETE FROM matieres WHERE id_matieres = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }


}

?>