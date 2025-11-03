<?php

namespace Controllers;

require_once __DIR__ . '/../model/agent.php';
require_once __DIR__ . '/MatiereController.php';

use Models\Agent;
use Models\Matiere;

class AgentController
{
    /**
     * Creer un agent avec parametres LLM (MODIFIE: id_etudiant supprime)
     */
    public function createAgent($nom_agent, $type_agent, $avatar_agent, $est_actif, $description, $prompt_systeme, $id_matieres,
                                $model = 'openai/gpt-oss-20b', $temperature = 0.7, $max_tokens = 8192, $top_p = 1.0, $reasoning_effort = 'medium')
    {
        $Agent = new Agent();
        return $Agent->create($nom_agent, $type_agent, $avatar_agent, $est_actif, $description, $prompt_systeme, $id_matieres,
                             $model, $temperature, $max_tokens, $top_p, $reasoning_effort);
    }

    /**
     * Recuperer tous les agents
     */
    public function getAgents()
    {
        $Agent = new Agent();
        return $Agent->read();
    }

    /**
     * Recuperer tous les agents avec details (SIMPLIFIE: plus besoin de charger etudiant)
     */
    public function getAgentsWithDetails()
    {
        // Maintenant read() inclut deja nom_matieres via JOIN
        $agentModel = new Agent();
        return $agentModel->read();
    }

    /**
     * Recuperer un agent specifique
     */
    public function getSingleAgent($id_agents)
    {
        $Agent = new Agent();
        return $Agent->readSingle($id_agents);
    }

    /**
     * Recuperer un agent avec details (SIMPLIFIE: plus besoin de charger etudiant)
     */
    public function getSingleAgentWithDetails($id_agents)
    {
        // Maintenant readSingle() inclut deja nom_matieres via JOIN
        $agentModel = new Agent();
        return $agentModel->readSingle($id_agents);
    }

    /**
     * Mettre a jour un agent (MODIFIE: id_etudiant supprime, parametres LLM ajoutes)
     */
    public function updateAgent($id_agents, $nom_agent, $type_agent, $avatar_agent, $est_actif, $description, $prompt_systeme, $id_matieres,
                                $model = 'openai/gpt-oss-20b', $temperature = 0.7, $max_tokens = 8192, $top_p = 1.0, $reasoning_effort = 'medium')
    {
        $Agent = new Agent();
        return $Agent->update($id_agents, $nom_agent, $type_agent, $avatar_agent, $est_actif, $description, $prompt_systeme, $id_matieres,
                             $model, $temperature, $max_tokens, $top_p, $reasoning_effort);
    }

    /**
     * Supprimer un agent
     */
    public function deleteAgent($id_agents)
    {
        $Agent = new Agent();
        return $Agent->delete($id_agents);
    }

    /**
     * Gere la soumission du formulaire (creation/modification)
     * MODIFIE: id_etudiant supprime, parametres LLM ajoutes, validation matiere obligatoire et unique
     * @param array $post Donnees POST
     * @param array $files Fichiers uploades
     * @param bool $isEditMode true si modification, false si creation
     * @param array|null $agent Donnees de l'agent en mode edition
     * @return array ['success' => bool, 'errors' => array, 'message' => string, 'agent' => array|null, 'input' => array]
     */
    public function handleSubmit($post, $files, $isEditMode, $agent)
    {
        $errors = [];
        $message = '';

        // Recuperation des donnees de base
        $nom_agent = trim($post['nom_agent'] ?? '');
        $type_agent = trim($post['type_agent'] ?? '');
        $description = trim($post['description'] ?? '');
        $prompt_systeme = trim($post['prompt_systeme'] ?? '');
        $est_actif = isset($post['est_actif']) ? 1 : 0;
        $id_matieres = !empty($post['id_matieres']) ? $post['id_matieres'] : null;

        // NOUVEAU: Recuperation des parametres LLM
        $model = trim($post['model'] ?? 'openai/gpt-oss-20b');
        $temperature = isset($post['temperature']) ? floatval($post['temperature']) : 0.7;
        $max_tokens = isset($post['max_tokens']) ? intval($post['max_tokens']) : 8192;
        $top_p = isset($post['top_p']) ? floatval($post['top_p']) : 1.0;
        $reasoning_effort = $post['reasoning_effort'] ?? 'medium';

        // ===== VALIDATIONS =====

        // Validation du nom de l'agent
        if (empty($nom_agent)) {
            $errors[] = "Le nom de l'agent est requis.";
        } elseif (strlen($nom_agent) < 3) {
            $errors[] = "Le nom doit contenir au moins 3 caracteres.";
        } elseif (strlen($nom_agent) > 100) {
            $errors[] = "Le nom ne doit pas depasser 100 caracteres.";
        } else {
            // Verifier l'unicite du nom
            $agents = $this->getAgents();
            foreach ($agents as $ag) {
                if ($ag['nom_agent'] === $nom_agent) {
                    // Si en mode edition, verifier que ce n'est pas le meme agent
                    if (!$isEditMode || $ag['id_agents'] != $post['id_agents']) {
                        $errors[] = "Ce nom d'agent existe deja.";
                        break;
                    }
                }
            }
        }

        // Validation du type d'agent
        $types_agent_valides = ['Assistant_Pedagogique', 'Tuteur_Prive', 'Agent_Test'];
        if (empty($type_agent)) {
            $errors[] = "Le type d'agent est requis.";
        } elseif (!in_array($type_agent, $types_agent_valides)) {
            $errors[] = "Le type d'agent selectionne n'est pas valide.";
        }

        // Validation de la description
        if (!empty($description) && strlen($description) > 500) {
            $errors[] = "La description ne doit pas depasser 500 caracteres.";
        }

        // Validation du prompt systeme (OBLIGATOIRE maintenant)
        if (empty($prompt_systeme)) {
            $errors[] = "Le prompt systeme est requis.";
        } elseif (strlen($prompt_systeme) > 2000) {
            $errors[] = "Le prompt systeme ne doit pas depasser 2000 caracteres.";
        }

        // NOUVEAU: Validation de la matiere (OBLIGATOIRE et UNIQUE)
        if (empty($id_matieres)) {
            $errors[] = "La matiere est obligatoire.";
        } else {
            // Verifier que la matiere existe
            $matiereController = new MatiereController();
            $matiere = $matiereController->getSingleMatiere($id_matieres);
            if (!$matiere) {
                $errors[] = "La matiere selectionnee n'existe pas.";
            } else {
                // Verifier qu'il n'existe pas deja un agent pour cette matiere
                $agentModel = new Agent();
                $exclude_id = $isEditMode ? $post['id_agents'] : null;
                if ($agentModel->agentExistsForMatiere($id_matieres, $exclude_id)) {
                    $errors[] = "Un agent existe deja pour cette matiere. Vous ne pouvez avoir qu'un seul agent par matiere.";
                }
            }
        }

        // NOUVEAU: Validation des parametres LLM
        if (empty($model)) {
            $errors[] = "Le modele LLM est requis.";
        }

        if ($temperature < 0 || $temperature > 2) {
            $errors[] = "La temperature doit etre entre 0 et 2.";
        }

        if ($max_tokens < 1 || $max_tokens > 100000) {
            $errors[] = "Le nombre de tokens doit etre entre 1 et 100000.";
        }

        if ($top_p < 0 || $top_p > 1) {
            $errors[] = "Top_p doit etre entre 0 et 1.";
        }

        $reasoning_efforts_valides = ['low', 'medium', 'high'];
        if (!in_array($reasoning_effort, $reasoning_efforts_valides)) {
            $errors[] = "Le reasoning effort doit etre 'low', 'medium' ou 'high'.";
        }

        // Gestion de l'avatar avec validation
        $avatar_agent = $isEditMode ? ($agent['avatar_agent'] ?? null) : null;
        if (isset($files['avatar_agent']) && $files['avatar_agent']['error'] === UPLOAD_ERR_OK) {
            $fileInfo = $files['avatar_agent'];

            // Validation du type MIME
            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detectedMime = finfo_file($finfo, $fileInfo['tmp_name']);
            finfo_close($finfo);

            if (!in_array($detectedMime, $allowedMimes)) {
                $errors[] = "Le format de l'image n'est pas autorise. Formats acceptes : JPEG, PNG, GIF.";
            }

            // Validation de la taille (2MB max)
            if ($fileInfo['size'] > 2 * 1024 * 1024) {
                $errors[] = "L'image est trop volumineuse. Taille maximale : 2MB.";
            }

            // Si pas d'erreur, traiter l'upload
            if (empty($errors)) {
                // Creer un nom de fichier securise et unique
                $extension = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);
                $avatar_agent = uniqid('avatar_agent_', true) . '.' . $extension;
                $uploadPath = __DIR__ . '/../views/../../uploads/' . $avatar_agent;

                if (!move_uploaded_file($fileInfo['tmp_name'], $uploadPath)) {
                    $errors[] = "Erreur lors de l'upload de l'avatar.";
                    $avatar_agent = $isEditMode ? ($agent['avatar_agent'] ?? null) : null;
                }
            }
        } elseif (isset($files['avatar_agent']) && $files['avatar_agent']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Gestion des erreurs d'upload
            switch ($files['avatar_agent']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errors[] = "Le fichier est trop volumineux.";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errors[] = "Le fichier n'a ete que partiellement telecharge.";
                    break;
                default:
                    $errors[] = "Erreur lors de l'upload du fichier.";
            }
        }

        // Si aucune erreur, proceder a l'enregistrement
        if (empty($errors)) {
            if ($isEditMode) {
                // Mode modification
                $id_agents = $post['id_agents'];
                $result = $this->updateAgent(
                    $id_agents, $nom_agent, $type_agent, $avatar_agent, $est_actif,
                    $description, $prompt_systeme, $id_matieres,
                    $model, $temperature, $max_tokens, $top_p, $reasoning_effort
                );

                if ($result) {
                    $message = "Agent modifie avec succes !";
                    // Recharger les donnees mises a jour
                    $agent = $this->getSingleAgent($id_agents);
                } else {
                    $errors[] = "Erreur lors de la modification de l'agent en base de donnees.";
                }
            } else {
                // Mode creation
                $result = $this->createAgent(
                    $nom_agent, $type_agent, $avatar_agent, $est_actif,
                    $description, $prompt_systeme, $id_matieres,
                    $model, $temperature, $max_tokens, $top_p, $reasoning_effort
                );

                if ($result) {
                    $message = "Agent cree avec succes !";
                } else {
                    $errors[] = "Erreur lors de la creation de l'agent en base de donnees.";
                }
            }
        }

        $input = compact('nom_agent', 'type_agent', 'description', 'prompt_systeme', 'est_actif', 'id_matieres',
                        'model', 'temperature', 'max_tokens', 'top_p', 'reasoning_effort');
        return [
            'success' => empty($errors),
            'errors' => $errors,
            'message' => $message,
            'agent' => $agent ?? null,
            'input' => $input
        ];
    }

    /**
     * Gere la suppression d'un agent
     * @param string|null $id ID de l'agent
     * @param bool $confirmed true si l'utilisateur a confirme la suppression
     * @return array ['success' => bool, 'errors' => array, 'message' => string, 'agent' => array|null, 'redirect' => string|null]
     */
    public function handleDelete($id, $confirmed = false)
    {
        $errors = [];
        $message = '';
        $agent = null;
        $redirect = null;

        // Validation de l'ID
        if (!isset($id) || empty($id)) {
            $errors[] = "ID agent manquant.";
            return [
                'success' => false,
                'errors' => $errors,
                'message' => "ID agent manquant.",
                'agent' => null,
                'redirect' => null
            ];
        }

        // Recuperation de l'agent
        $agent = $this->getSingleAgent($id);

        if (!$agent) {
            $errors[] = "Agent introuvable.";
            return [
                'success' => false,
                'errors' => $errors,
                'message' => "Agent introuvable.",
                'agent' => null,
                'redirect' => null
            ];
        }

        // Si confirmation, proceder a la suppression
        if ($confirmed) {
            $result = $this->deleteAgent($id);

            if ($result) {
                return [
                    'success' => true,
                    'errors' => [],
                    'message' => "Agent supprime avec succes !",
                    'agent' => $agent,
                    'redirect' => 'index.php?action=agent_list&message=supprime'
                ];
            } else {
                $errors[] = "Erreur lors de la suppression de l'agent. Verifiez les dependances (Sessions de conversation).";
                return [
                    'success' => false,
                    'errors' => $errors,
                    'message' => "Erreur lors de la suppression de l'agent.",
                    'agent' => $agent,
                    'redirect' => null
                ];
            }
        }

        // Pas de confirmation, juste retourner l'agent pour afficher la page de confirmation
        return [
            'success' => false,
            'errors' => [],
            'message' => '',
            'agent' => $agent,
            'redirect' => null
        ];
    }
}
