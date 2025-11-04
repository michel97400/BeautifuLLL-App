<?php
namespace Models;

use Config\Database;
use PDO;

class Message
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * MODIFIE: Creer un message (chat moderne)
     * @param int $id_session ID de la session de conversation
     * @param string $role Role du message ('user' ou 'assistant')
     * @param string $contenu Contenu du message
     * @return int|false ID du message cree ou false si echec
     */
    public function create($id_session, $role, $contenu)
    {
        $sql = "INSERT INTO Message (id_session, role, contenu, date_envoi)
                VALUES (:id_session, :role, :contenu, NOW())";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_session', $id_session, PDO::PARAM_INT);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        $stmt->bindParam(':contenu', $contenu, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Lire tous les messages
     * @return array
     */
    public function read()
    {
        $sql = "SELECT * FROM Message ORDER BY date_envoi ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lire un message specifique
     * @param int $id_message
     * @return array|false
     */
    public function readSingle($id_message)
    {
        $sql = "SELECT * FROM Message WHERE id_message = :id_message";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_message', $id_message, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * NOUVEAU: Recuperer tous les messages d'une session
     * @param int $id_session ID de la session
     * @return array Messages tries par date d'envoi
     */
    public function getMessagesBySession($id_session)
    {
        $sql = "SELECT * FROM Message
                WHERE id_session = :id_session
                ORDER BY date_envoi ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_session', $id_session, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * DEPRECATED: Ancienne methode - utiliser getMessagesBySession() a la place
     * Conserve pour compatibilite avec ancien code
     */
    public function readBySessionId($id_session)
    {
        return $this->getMessagesBySession($id_session);
    }

    /**
     * NOUVEAU: Recuperer les N derniers messages d'une session
     * Utile pour construire le contexte de conversation
     * @param int $id_session
     * @param int $limit Nombre de messages a recuperer
     * @return array
     */
    public function getRecentMessages($id_session, $limit = 10)
    {
        $sql = "SELECT * FROM Message
                WHERE id_session = :id_session
                ORDER BY date_envoi DESC
                LIMIT :limit";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_session', $id_session, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        // Inverser l'ordre pour avoir chronologique
        return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * NOUVEAU: Compter les messages d'une session par role
     * @param int $id_session
     * @return array ['user' => count, 'assistant' => count]
     */
    public function countMessagesByRole($id_session)
    {
        $sql = "SELECT role, COUNT(*) as count
                FROM Message
                WHERE id_session = :id_session
                GROUP BY role";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_session', $id_session, PDO::PARAM_INT);
        $stmt->execute();

        $result = ['user' => 0, 'assistant' => 0];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['role']] = (int)$row['count'];
        }

        return $result;
    }

    /**
     * Mettre a jour un message
     * @param int $id_message
     * @param string $role
     * @param string $contenu
     * @param int $id_session
     * @return bool
     */
    public function update($id_message, $role, $contenu, $id_session)
    {
        $sql = "UPDATE Message SET
                    role = :role,
                    contenu = :contenu,
                    id_session = :id_session
                WHERE id_message = :id_message";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        $stmt->bindParam(':contenu', $contenu, PDO::PARAM_STR);
        $stmt->bindParam(':id_session', $id_session, PDO::PARAM_INT);
        $stmt->bindParam(':id_message', $id_message, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Supprimer un message
     * @param int $id_message
     * @return bool
     */
    public function delete($id_message)
    {
        $sql = "DELETE FROM Message WHERE id_message = :id_message";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_message', $id_message, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * NOUVEAU: Supprimer tous les messages d'une session
     * @param int $id_session
     * @return bool
     */
    public function deleteBySession($id_session)
    {
        $sql = "DELETE FROM Message WHERE id_session = :id_session";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_session', $id_session, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>