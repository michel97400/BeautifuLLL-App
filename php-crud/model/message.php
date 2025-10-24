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



    public function create($emetteur, $contenu_message, $date_heure_message, $id_session) {
        $sql = "INSERT INTO message (emetteur, contenu_message, date_heure_message, id_session) VALUES (:emetteur, :contenu_message, :date_heure_message, :id_session)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':emetteur', $emetteur);
        $stmt->bindParam(':contenu_message', $contenu_message);
        $stmt->bindParam(':date_heure_message', $date_heure_message);
        $stmt->bindParam(':id_session', $id_session);
        return $stmt->execute();
    }

    public function read() {
        $sql = "SELECT * FROM message";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function readSingle($id) {
        $sql = "SELECT * FROM message WHERE id_message = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    public function readBySessionId($id_session) {
    $query = "SELECT * FROM message WHERE id_session = ? ORDER BY date_heure_message ASC";
    $stmt = $this->conn->prepare($query);
    $stmt->execute([$id_session]);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
    public function update($id, $emetteur, $contenu_message, $date_heure_message, $id_session) {
        $sql = "UPDATE message SET emetteur = :emetteur, contenu_message = :contenu_message, date_heure_message = :date_heure_message, id_session = :id_session WHERE id_message = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':emetteur', $emetteur);
        $stmt->bindParam(':contenu_message', $contenu_message);
        $stmt->bindParam(':date_heure_message', $date_heure_message);
        $stmt->bindParam(':id_session', $id_session);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $sql = "DELETE FROM message WHERE id_message = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

}

?>