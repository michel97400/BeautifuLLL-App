<?php
namespace Models;

use Config\Database;
use PDO;

class Agent
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Creer un nouvel agent avec parametres LLM
     * @param string $nom_agent Nom de l'agent
     * @param string $type_agent Type d'agent
     * @param string $avatar_agent Chemin vers l'avatar
     * @param bool $est_actif Agent actif ou non
     * @param string $description Description de l'agent
     * @param string $prompt_systeme Prompt systeme de base
     * @param int $id_matieres ID de la matiere (obligatoire)
     * @param string $model Modele LLM a utiliser
     * @param float $temperature Temperature (0-2)
     * @param int $max_tokens Nombre max de tokens
     * @param float $top_p Top_p (0-1)
     * @param string $reasoning_effort Effort de raisonnement (low/medium/high)
     * @return bool
     */
    public function create($nom_agent, $type_agent, $avatar_agent, $est_actif, $description, $prompt_systeme, $id_matieres,
                          $model = 'openai/gpt-oss-20b', $temperature = 0.7, $max_tokens = 8192, $top_p = 1.0, $reasoning_effort = 'medium')
    {
        $sql = "INSERT INTO agent
                (nom_agent, type_agent, avatar_agent, est_actif, description, prompt_systeme, id_matieres,
                 model, temperature, max_tokens, top_p, reasoning_effort)
                VALUES
                (:nom_agent, :type_agent, :avatar_agent, :est_actif, :description, :prompt_systeme, :id_matieres,
                 :model, :temperature, :max_tokens, :top_p, :reasoning_effort)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nom_agent', $nom_agent);
        $stmt->bindParam(':type_agent', $type_agent);
        $stmt->bindParam(':avatar_agent', $avatar_agent);
        $stmt->bindParam(':est_actif', $est_actif, PDO::PARAM_INT);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':prompt_systeme', $prompt_systeme);
        $stmt->bindParam(':id_matieres', $id_matieres, PDO::PARAM_INT);
        $stmt->bindParam(':model', $model);
        $stmt->bindParam(':temperature', $temperature);
        $stmt->bindParam(':max_tokens', $max_tokens, PDO::PARAM_INT);
        $stmt->bindParam(':top_p', $top_p);
        $stmt->bindParam(':reasoning_effort', $reasoning_effort);

        return $stmt->execute();
    }

    /**
     * Lire tous les agents
     * @return array
     */
    public function read()
    {
        $sql = "SELECT a.*, m.nom_matieres
                FROM agent a
                LEFT JOIN Matieres m ON a.id_matieres = m.id_matieres
                ORDER BY m.nom_matieres";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lire un agent specifique
     * @param int $id_agents
     * @return array|false
     */
    public function readSingle($id_agents)
    {
        $sql = "SELECT a.*, m.nom_matieres
                FROM agent a
                LEFT JOIN Matieres m ON a.id_matieres = m.id_matieres
                WHERE a.id_agents = :id_agents
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_agents', $id_agents, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * NOUVELLE METHODE: Recuperer l'agent d'une matiere specifique
     * @param int $id_matieres ID de la matiere
     * @return array|false Agent ou false si non trouve
     */
    public function getAgentByMatiere($id_matieres)
    {
        $sql = "SELECT a.*, m.nom_matieres
                FROM agent a
                LEFT JOIN Matieres m ON a.id_matieres = m.id_matieres
                WHERE a.id_matieres = :id_matieres
                AND a.est_actif = 1
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_matieres', $id_matieres, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * NOUVELLE METHODE: Recuperer les parametres LLM d'un agent
     * @param int $id_agents ID de l'agent
     * @return array|false Parametres LLM
     */
    public function getLLMParameters($id_agents)
    {
        $sql = "SELECT model, temperature, max_tokens, top_p, reasoning_effort, prompt_systeme
                FROM agent
                WHERE id_agents = :id_agents";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_agents', $id_agents, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * NOUVELLE METHODE: Mettre a jour uniquement les parametres LLM
     * @param int $id_agents
     * @param string $model
     * @param float $temperature
     * @param int $max_tokens
     * @param float $top_p
     * @param string $reasoning_effort
     * @return bool
     */
    public function updateLLMParameters($id_agents, $model, $temperature, $max_tokens, $top_p, $reasoning_effort)
    {
        $sql = "UPDATE agent
                SET model = :model,
                    temperature = :temperature,
                    max_tokens = :max_tokens,
                    top_p = :top_p,
                    reasoning_effort = :reasoning_effort
                WHERE id_agents = :id_agents";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_agents', $id_agents, PDO::PARAM_INT);
        $stmt->bindParam(':model', $model);
        $stmt->bindParam(':temperature', $temperature);
        $stmt->bindParam(':max_tokens', $max_tokens, PDO::PARAM_INT);
        $stmt->bindParam(':top_p', $top_p);
        $stmt->bindParam(':reasoning_effort', $reasoning_effort);

        return $stmt->execute();
    }

    /**
     * Mettre a jour un agent (MODIFIE: id_etudiant supprime, parametres LLM ajoutes)
     * @param int $id_agents
     * @param string $nom_agent
     * @param string $type_agent
     * @param string $avatar_agent
     * @param bool $est_actif
     * @param string $description
     * @param string $prompt_systeme
     * @param int $id_matieres
     * @param string $model
     * @param float $temperature
     * @param int $max_tokens
     * @param float $top_p
     * @param string $reasoning_effort
     * @return bool
     */
    public function update($id_agents, $nom_agent, $type_agent, $avatar_agent, $est_actif, $description, $prompt_systeme, $id_matieres,
                          $model = 'openai/gpt-oss-20b', $temperature = 0.7, $max_tokens = 8192, $top_p = 1.0, $reasoning_effort = 'medium')
    {
        $sql = "UPDATE agent SET
                    nom_agent = :nom_agent,
                    type_agent = :type_agent,
                    avatar_agent = :avatar_agent,
                    est_actif = :est_actif,
                    description = :description,
                    prompt_systeme = :prompt_systeme,
                    id_matieres = :id_matieres,
                    model = :model,
                    temperature = :temperature,
                    max_tokens = :max_tokens,
                    top_p = :top_p,
                    reasoning_effort = :reasoning_effort
                WHERE id_agents = :id_agents";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_agents', $id_agents, PDO::PARAM_INT);
        $stmt->bindParam(':nom_agent', $nom_agent);
        $stmt->bindParam(':type_agent', $type_agent);
        $stmt->bindParam(':avatar_agent', $avatar_agent);
        $stmt->bindParam(':est_actif', $est_actif, PDO::PARAM_INT);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':prompt_systeme', $prompt_systeme);
        $stmt->bindParam(':id_matieres', $id_matieres, PDO::PARAM_INT);
        $stmt->bindParam(':model', $model);
        $stmt->bindParam(':temperature', $temperature);
        $stmt->bindParam(':max_tokens', $max_tokens, PDO::PARAM_INT);
        $stmt->bindParam(':top_p', $top_p);
        $stmt->bindParam(':reasoning_effort', $reasoning_effort);

        return $stmt->execute();
    }

    /**
     * Supprimer un agent
     * @param int $id_agents
     * @return bool
     */
    public function delete($id_agents)
    {
        $sql = "DELETE FROM agent WHERE id_agents = :id_agents";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_agents', $id_agents, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * NOUVELLE METHODE: Verifier si un agent existe deja pour une matiere
     * @param int $id_matieres
     * @param int|null $exclude_id ID de l'agent a exclure (pour les updates)
     * @return bool
     */
    public function agentExistsForMatiere($id_matieres, $exclude_id = null)
    {
        $sql = "SELECT COUNT(*) as count FROM agent WHERE id_matieres = :id_matieres";

        if ($exclude_id !== null) {
            $sql .= " AND id_agents != :exclude_id";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_matieres', $id_matieres, PDO::PARAM_INT);

        if ($exclude_id !== null) {
            $stmt->bindParam(':exclude_id', $exclude_id, PDO::PARAM_INT);
        }

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['count'] > 0;
    }

    /**
     * NOUVELLE METHODE: Recuperer tous les agents actifs
     * @return array
     */
    public function getActiveAgents()
    {
        $sql = "SELECT a.*, m.nom_matieres
                FROM agent a
                LEFT JOIN Matieres m ON a.id_matieres = m.id_matieres
                WHERE a.est_actif = 1
                ORDER BY m.nom_matieres";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
