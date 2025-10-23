<?php
namespace Models;

require_once "../config/Database.php";
use Config\Database;

class Etudiants
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function create($nom, $prenom, $email, $avatar, $passwordhash, $date_inscription, $consentement_rgpd, $id_role,$id_niveau)
    {
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

    public function readSingle($id_etudiant)
    {
        $sql = "SELECT * FROM etudiants WHERE id_etudiant = :id_etudiant LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_etudiant', $id_etudiant, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    public function update($id_etudiant, $nom, $prenom, $email, $avatar, $passwordhash, $date_inscription, $consentement_rgpd, $id_role, $id_niveau)
    {
        $sql = "UPDATE Etudiants SET nom = :nom, prenom = :prenom, email = :email, avatar = :avatar, passwordhash = :passwordhash, date_inscription = :date_inscription, consentement_rgpd = :consentement_rgpd, id_role = :id_role, id_niveau = :id_niveau WHERE id_etudiant = :id_etudiant";
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
        $stmt->bindParam(':id_etudiant', $id_etudiant);
        return $stmt->execute();
    }

    public function delete($id_etudiant)
    {
        $sql = "DELETE FROM Etudiants WHERE id_etudiant = :id_etudiant";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_etudiant', $id_etudiant);
        return $stmt->execute();
    }
}

?>