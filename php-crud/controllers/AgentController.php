<?php

namespace Controllers;

require_once __DIR__ . '/../model/agent.php';
require_once __DIR__ . '/MatiereController.php';
require_once __DIR__ . '/EtudiantController.php';

use Models\Agent;
use Models\Matiere;
use Models\Etudiants;

class AgentController
{
    public function createAgent($nom_agent, $type_agent, $avatar_agent, $est_actif, $description, $prompt_systeme, $id_matieres, $id_etudiant)
    {
        $Agent = new Agent();
        return $Agent->create($nom_agent, $type_agent, $avatar_agent, $est_actif, $description, $prompt_systeme, $id_matieres, $id_etudiant);
    }

    public function getAgents()
    {
        $Agent = new Agent();
        return $Agent->read();
    }

    public function getAgentsWithDetails()
    {
        $agentModel = new Agent();
        $agents = $agentModel->read();
        $matiereModel = new \Models\Matiere();
        $etudiantModel = new Etudiants();

        foreach($agents as &$agent) {
            if ($agent['id_matieres']) {
                $matiere = $matiereModel->readSingle($agent['id_matieres']);
                $agent['nom_matieres'] = $matiere ? $matiere['nom_matieres'] : 'N/A';
            } else {
                $agent['nom_matieres'] = 'Général';
            }
            $etudiant = $etudiantModel->readSingle($agent['id_etudiant']);
            $agent['nom_complet_etudiant'] = $etudiant ? ($etudiant['prenom'] . ' ' . $etudiant['nom']) : 'Inconnu';
        }
        return $agents;
    }

    public function getSingleAgent($id_agents)
    {
        $Agent = new Agent();
        return $Agent->readSingle($id_agents);
    }

    public function getSingleAgentWithDetails($id_agents)
    {
        $agentModel = new Agent();
        $agent = $agentModel->readSingle($id_agents);

        if ($agent) {
            $matiereModel = new \Models\Matiere();
            $etudiantModel = new Etudiants();
            if ($agent['id_matieres']) {
                $matiere = $matiereModel->readSingle($agent['id_matieres']);
                $agent['nom_matieres'] = $matiere ? $matiere['nom_matieres'] : 'N/A';
            } else {
                $agent['nom_matieres'] = 'Général';
            }
            $etudiant = $etudiantModel->readSingle($agent['id_etudiant']);
            $agent['nom_complet_etudiant'] = $etudiant ? ($etudiant['prenom'] . ' ' . $etudiant['nom']) : 'Inconnu';
        }
        return $agent;
    }

    public function updateAgent($id_agents, $nom_agent, $type_agent, $avatar_agent, $est_actif, $description, $prompt_systeme, $id_matieres, $id_etudiant)
    {
        $Agent = new Agent();
        return $Agent->update($id_agents, $nom_agent, $type_agent, $avatar_agent, $est_actif, $description, $prompt_systeme, $id_matieres, $id_etudiant);
    }

    public function deleteAgent($id_agents)
    {
        $Agent = new Agent();
        return $Agent->delete($id_agents);
    }

    /**
     * Gère la soumission du formulaire (création/modification)
     * @param array $post Données POST
     * @param array $files Fichiers uploadés
     * @param bool $isEditMode true si modification, false si création
     * @param array|null $agent Données de l'agent en mode édition
     * @return array ['success' => bool, 'errors' => array, 'message' => string, 'agent' => array|null, 'input' => array]
     */
    public function handleSubmit($post, $files, $isEditMode, $agent)
    {
        $errors = [];
        $message = '';

        // Récupération des données
        $nom_agent = trim($post['nom_agent'] ?? '');
        $type_agent = trim($post['type_agent'] ?? '');
        $description = trim($post['description'] ?? '');
        $prompt_systeme = trim($post['prompt_systeme'] ?? '');
        $est_actif = isset($post['est_actif']) ? 1 : 0;
        $id_matieres = !empty($post['id_matieres']) ? $post['id_matieres'] : null;
        $id_etudiant = $post['id_etudiant'] ?? '';

        // Validation du nom de l'agent
        if (empty($nom_agent)) {
            $errors[] = "Le nom de l'agent est requis.";
        } elseif (strlen($nom_agent) < 3) {
            $errors[] = "Le nom doit contenir au moins 3 caractères.";
        } elseif (strlen($nom_agent) > 100) {
            $errors[] = "Le nom ne doit pas dépasser 100 caractères.";
        } else {
            // Vérifier l'unicité du nom
            $agents = $this->getAgents();
            foreach ($agents as $ag) {
                if ($ag['nom_agent'] === $nom_agent) {
                    // Si en mode édition, vérifier que ce n'est pas le même agent
                    if (!$isEditMode || $ag['id_agents'] != $post['id_agents']) {
                        $errors[] = "Ce nom d'agent existe déjà.";
                        break;
                    }
                }
            }
        }

        // Validation du type d'agent
        $types_agent_valides = ['Assistant_Pédagogique', 'Tuteur_Privé', 'Agent_Test'];
        if (empty($type_agent)) {
            $errors[] = "Le type d'agent est requis.";
        } elseif (!in_array($type_agent, $types_agent_valides)) {
            $errors[] = "Le type d'agent sélectionné n'est pas valide.";
        }

        // Validation de la description
        if (!empty($description) && strlen($description) > 500) {
            $errors[] = "La description ne doit pas dépasser 500 caractères.";
        }

        // Validation du prompt système
        if (!empty($prompt_systeme) && strlen($prompt_systeme) > 1000) {
            $errors[] = "Le prompt système ne doit pas dépasser 1000 caractères.";
        }

        // Validation des relations (id_matieres optionnel, id_etudiant requis)
        if (!empty($id_matieres)) {
            $matiereController = new MatiereController();
            $matiere = $matiereController->getSingleMatiere($id_matieres);
            if (!$matiere) {
                $errors[] = "La matière sélectionnée n'existe pas.";
            }
        }

        if (empty($id_etudiant)) {
            $errors[] = "L'étudiant créateur est requis.";
        } else {
            $etudiantController = new EtudiantController();
            $etudiant = $etudiantController->getSingleEtudiant($id_etudiant);
            if (!$etudiant) {
                $errors[] = "L'étudiant sélectionné n'existe pas.";
            }
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
                $errors[] = "Le format de l'image n'est pas autorisé. Formats acceptés : JPEG, PNG, GIF.";
            }

            // Validation de la taille (2MB max)
            if ($fileInfo['size'] > 2 * 1024 * 1024) {
                $errors[] = "L'image est trop volumineuse. Taille maximale : 2MB.";
            }

            // Si pas d'erreur, traiter l'upload
            if (empty($errors)) {
                // Créer un nom de fichier sécurisé et unique
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
                    $errors[] = "Le fichier n'a été que partiellement téléchargé.";
                    break;
                default:
                    $errors[] = "Erreur lors de l'upload du fichier.";
            }
        }

        // Si aucune erreur, procéder à l'enregistrement
        if (empty($errors)) {
            if ($isEditMode) {
                // Mode modification
                $id_agents = $post['id_agents'];
                $result = $this->updateAgent($id_agents, $nom_agent, $type_agent, $avatar_agent, $est_actif, $description, $prompt_systeme, $id_matieres, $id_etudiant);

                if ($result) {
                    $message = "Agent modifié avec succès !";
                    // Recharger les données mises à jour
                    $agent = $this->getSingleAgent($id_agents);
                } else {
                    $errors[] = "Erreur lors de la modification de l'agent en base de données.";
                }
            } else {
                // Mode création
                $result = $this->createAgent($nom_agent, $type_agent, $avatar_agent, $est_actif, $description, $prompt_systeme, $id_matieres, $id_etudiant);

                if ($result) {
                    $message = "Agent créé avec succès !";
                } else {
                    $errors[] = "Erreur lors de la création de l'agent en base de données.";
                }
            }
        }

        $input = compact('nom_agent', 'type_agent', 'description', 'prompt_systeme', 'est_actif', 'id_matieres', 'id_etudiant');
        return [
            'success' => empty($errors),
            'errors' => $errors,
            'message' => $message,
            'agent' => $agent ?? null,
            'input' => $input
        ];
    }

    /**
     * Gère la suppression d'un agent
     * @param string|null $id ID de l'agent
     * @param bool $confirmed true si l'utilisateur a confirmé la suppression
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

        // Récupération de l'agent
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

        // Si confirmation, procéder à la suppression
        if ($confirmed) {
            $result = $this->deleteAgent($id);

            if ($result) {
                return [
                    'success' => true,
                    'errors' => [],
                    'message' => "Agent supprimé avec succès !",
                    'agent' => $agent,
                    'redirect' => 'index.php?action=agent_list&message=supprime'
                ];
            } else {
                $errors[] = "Erreur lors de la suppression de l'agent. Vérifiez les dépendances (Sessions de conversation).";
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
