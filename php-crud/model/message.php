<?php
namespace Models;

use Config\Database;

class Message
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }


    public function create_message() {
        $sql = "INSERT INTO Message (role_message, contenu, date_envoi, id_session) VALUES (:role_message, :contenu, :date_envoi, :id_session)";

        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':role_message', $role;
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

    public function read_role()
    {
        $sql = "SELECT * FROM Message";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

}

?>