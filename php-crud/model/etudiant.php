<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';
use Config\Database;

class Etudiants
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function readByEmail($email)
    {
        $sql = "SELECT * FROM Etudiants WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create($nom, $prenom, $email, $avatar, $password, $date_inscription, $consentement_rgpd, $id_role, $id_niveau)
    {
        // Hash du mot de passe
        $passwordhash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO Etudiants (nom, prenom, email, avatar, passwordhash, date_inscription, consentement_rgpd, id_role, id_niveau) VALUES (:nom, :prenom, :email, :avatar, :passwordhash, :date_inscription, :consentement_rgpd, :id_role, :id_niveau)";

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


    public function readSingle($id)
    {
        $sql = "SELECT * FROM Etudiants WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function update($id, $nom, $prenom, $email, $avatar, $passwordhash)
    {
        $sql = "UPDATE Etudiants SET nom = :nom, prenom = :prenom, email = :email, avatar = :avatar, passwordhash = :passwordhash WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':avatar', $avatar);
        $stmt->bindParam(':passwordhash', $passwordhash);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function delete($id_etudiant)
    {
        $sql = "DELETE FROM Etudiants WHERE id_etudiant = :id_etudiant";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_etudiant', $id);
        return $stmt->execute();
    }
}

?>