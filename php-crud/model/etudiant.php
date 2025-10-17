<?php
namespace Models;

use Config\Database;

class User
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function create($id_user, $nom, $prenom, $email, $avatar, $passwordhash, $date_inscription, $consentement_rgpd, $id_role)
    {
        $sql = "INSERT INTO etudiants (id_user, nom, prenom, email, avatar, passwordhash, date_inscription, consentement_rgpd, id_role) VALUES (:id_user, :nom, :prenom, :email, :avatar, :passwordhash, :date_inscription, :consentement_rgpd, :id_role)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':avatar', $avatar);
        $stmt->bindParam(':passwordhash', $passwordhash);
        $stmt->bindParam(':date_inscription', $date_inscription);
        $stmt->bindParam(':consentement_rgpd', $consentement_rgpd);
        $stmt->bindParam(':id_role', $id_role);
        return $stmt->execute();
    }

    public function read()
    {
        $sql = "SELECT * FROM users";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function update($id, $name, $email)
    {
        $sql = "UPDATE users SET name = :name, email = :email WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function delete($id)
    {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}

?>