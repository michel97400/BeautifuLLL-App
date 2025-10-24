<?php

namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;

class Niveau{

    private $conn;

    public function __construct(){
        $database = new Database();
        $this->conn = $database->connect();
    }


    public function create($libelle_niveau) {
        $sql = "INSERT INTO niveau (libelle_niveau) VALUES (:libelle_niveau)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':libelle_niveau', $libelle_niveau);
        return $stmt->execute();
    }

    public function read() {
        $sql = "SELECT * FROM niveau";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function readSingle($id) {
        $sql = "SELECT * FROM niveau WHERE id_niveau = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function update($id, $libelle_niveau) {
        $sql = "UPDATE niveau SET libelle_niveau = :libelle_niveau WHERE id_niveau = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':libelle_niveau', $libelle_niveau);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $sql = "DELETE FROM niveau WHERE id_niveau = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }


}

?>