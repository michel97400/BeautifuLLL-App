<?php
namespace Models;

use Config\Database;
use PDO;

class SessionConversation
{
    private $conn;

    public function __construct($db = null)
    {
        if ($db) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->connect();
        }
    }

<<<<<<< HEAD
    public function readByEtudiant($id_etudiant)
{
    $sql = "SELECT sc.*, a.nom_agent, a.avatar_agent, 
            (SELECT COUNT(*) FROM Message m WHERE m.id_session = sc.id_session) as nb_messages
            FROM SESSION_CONVERSATION sc
            LEFT JOIN Agent a ON sc.id_agents = a.id_agents
            WHERE sc.id_etudiant = :id_etudiant
            ORDER BY sc.date_heure_debut DESC";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id_etudiant', $id_etudiant, \PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

    public function readSessionWithMessages($id_session)
{
    // Récupérer la session
    $session = $this->readSingle($id_session);
    
    if (!$session) {
        return null;
    }
    
    // Récupérer les messages
    $sql = "SELECT * FROM Message WHERE id_session = :id_session ORDER BY date_envoi ASC";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id_session', $id_session, \PDO::PARAM_INT);
    $stmt->execute();
    $session['messages'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
    return $session;
}
    public function createAndReturnId($duree_session, $date_heure_fin, $id_agents, $id_etudiant)
{
    $sql = "INSERT INTO session_conversation (duree_session, date_heure_fin, id_agents, id_etudiant)
            VALUES (:duree_session, :date_heure_fin, :id_agents, :id_etudiant)";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':duree_session', $duree_session);
    $stmt->bindParam(':date_heure_fin', $date_heure_fin);
    $stmt->bindParam(':id_agents', $id_agents, \PDO::PARAM_INT);
    $stmt->bindParam(':id_etudiant', $id_etudiant, \PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        return $this->conn->lastInsertId();
    }
    return false;
}

=======
    /**
     * Lire une session specifique
     */
>>>>>>> main
    public function readSingle($id)
    {
        $sql = "SELECT s.*, a.nom_agent, m.nom_matieres
                FROM SESSION_CONVERSATION s
                LEFT JOIN Agent a ON s.id_agents = a.id_agents
                LEFT JOIN Matieres m ON a.id_matieres = m.id_matieres
                WHERE s.id_session = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * MODIFIE: Creer une nouvelle session (au debut de la conversation)
     * @param int $id_agents ID de l'agent
     * @param int $id_etudiant ID de l'etudiant
     * @return int ID de la session creee
     */
    public function create($id_agents, $id_etudiant)
    {
        $sql = "INSERT INTO session_conversation (id_agents, id_etudiant, date_heure_debut)
                VALUES (:id_agents, :id_etudiant, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_agents', $id_agents, PDO::PARAM_INT);
        $stmt->bindParam(':id_etudiant', $id_etudiant, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Lire toutes les sessions
     */
    public function read()
    {
        $sql = "SELECT s.*, a.nom_agent, m.nom_matieres
                FROM SESSION_CONVERSATION s
                LEFT JOIN Agent a ON s.id_agents = a.id_agents
                LEFT JOIN Matieres m ON a.id_matieres = m.id_matieres
                ORDER BY s.date_heure_debut DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mettre a jour une session
     */
    public function update($id_session, $date_heure_debut, $duree_session, $date_heure_fin, $id_agents, $id_etudiant)
    {
        $sql = "UPDATE SESSION_CONVERSATION SET
                    date_heure_debut = :date_heure_debut,
                    duree_session = :duree_session,
                    date_heure_fin = :date_heure_fin,
                    id_agents = :id_agents,
                    id_etudiant = :id_etudiant
                WHERE id_session = :id_session";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':date_heure_debut', $date_heure_debut);
        $stmt->bindParam(':duree_session', $duree_session);
        $stmt->bindParam(':date_heure_fin', $date_heure_fin);
        $stmt->bindParam(':id_agents', $id_agents, PDO::PARAM_INT);
        $stmt->bindParam(':id_etudiant', $id_etudiant, PDO::PARAM_INT);
        $stmt->bindParam(':id_session', $id_session, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Supprimer une session
     */
    public function delete($id_session)
    {
        $sql = "DELETE FROM SESSION_CONVERSATION WHERE id_session = :id_session";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_session', $id_session, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * NOUVEAU: Terminer une session (calcul duree automatique)
     * @param int $id_session
     * @return bool
     */
    public function endSession($id_session)
    {
        $sql = "UPDATE SESSION_CONVERSATION
                SET date_heure_fin = NOW(),
                    duree_session = TIMEDIFF(NOW(), date_heure_debut)
                WHERE id_session = :id_session";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_session', $id_session, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * NOUVEAU: Recuperer les sessions d'un etudiant
     * @param int $id_etudiant
     * @param int $limit Nombre max de sessions a retourner
     * @return array
     */
    public function getSessionsByStudent($id_etudiant, $limit = 20)
    {
        $sql = "SELECT s.*, a.nom_agent, m.nom_matieres
                FROM SESSION_CONVERSATION s
                LEFT JOIN Agent a ON s.id_agents = a.id_agents
                LEFT JOIN Matieres m ON a.id_matieres = m.id_matieres
                WHERE s.id_etudiant = :id_etudiant
                ORDER BY s.date_heure_debut DESC
                LIMIT :limit";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_etudiant', $id_etudiant, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * NOUVEAU: Recuperer la session active d'un etudiant (non terminee)
     * @param int $id_etudiant
     * @return array|false
     */
    public function getActiveSession($id_etudiant)
    {
        $sql = "SELECT s.*, a.nom_agent, m.nom_matieres
                FROM SESSION_CONVERSATION s
                LEFT JOIN Agent a ON s.id_agents = a.id_agents
                LEFT JOIN Matieres m ON a.id_matieres = m.id_matieres
                WHERE s.id_etudiant = :id_etudiant
                AND s.date_heure_fin IS NULL
                ORDER BY s.date_heure_debut DESC
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_etudiant', $id_etudiant, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * NOUVEAU: Compter le nombre de messages dans une session
     * @param int $id_session
     * @return int
     */
    public function getMessageCount($id_session)
    {
        $sql = "SELECT COUNT(*) as count FROM Message WHERE id_session = :id_session";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_session', $id_session, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
}

?>
