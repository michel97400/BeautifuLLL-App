<?php
namespace Models;

use Config\Database;

class Etudiants
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function create($nom, $prenom, $email, $avatar, $passwordhash, $date_inscription, $consentement_rgpd, $id_role)
    {
        $sql = "INSERT INTO etudiants (nom, prenom, email, avatar, passwordhash, date_inscription, consentement_rgpd, id_role) VALUES (:nom, :prenom, :email, :avatar, :passwordhash, :date_inscription, :consentement_rgpd, :id_role, :id_niveau)";

        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':avatar', $avatar);
        $stmt->bindParam(':passwordhash', $passwordhash);
        $stmt->bindParam(':date_inscription', $date_inscription);
        $stmt->bindParam(':consentement_rgpd', $consentement_rgpd);
        $stmt->bindParam(':id_role', $id_role);
        $stmt->bindParam(':id_niveau', $id_niveau);
        return $stmt->execute();
    }

    public function read()
    {
        $sql = "SELECT * FROM etudiants";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function update($id, $name, $email)
    {
        $sql = "UPDATE etudiants SET name = :name, email = :email WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function delete($id)
    {
        $sql = "DELETE FROM etudiants WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}

?>