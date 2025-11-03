<?php

namespace Controllers;

require_once __DIR__ . '/../model/SessionConversation.php';
require_once __DIR__ . '/../model/Agent.php';
require_once __DIR__ . '/../model/etudiant.php';
require_once __DIR__ . '/AgentController.php';
require_once __DIR__ . '/EtudiantController.php';

use Models\SessionConversation;
use Models\Agent;
use Models\Etudiants;

class SessionConversationController
{
    // ... create, update, delete restent identiques ...
    public function createSessionConversation($duree_session, $date_heure_fin, $id_agents, $id_etudiant)
    {
        $SessionConversation = new session_conversation();
        return $SessionConversation->create($duree_session, $date_heure_fin, $id_agents, $id_etudiant);
    }

    public function getSessionConversation()
    {
        $SessionConversation = new SessionConversation();
        return $SessionConversation->read();
    }
    
    public function getSessionsWithDetails()
    {
        $sessionModel = new SessionConversation();
        $sessions = $sessionModel->read();
        $agentModel = new Agent();
        $etudiantModel = new Etudiants();

        foreach ($sessions as &$session) {
            $agent = $agentModel->readSingle($session['id_agents']);
            $etudiant = $etudiantModel->readSingle($session['id_etudiant']);
            $session['nom_agent'] = $agent ? $agent['nom_agent'] : 'Inconnu';
            $session['nom_complet_etudiant'] = $etudiant ? ($etudiant['prenom'] . ' ' . $etudiant['nom']) : 'Inconnu';
        }
        return $sessions;
    }
    
    public function getSingleSessionWithDetails($id_session)
    {
        $sessionModel = new SessionConversation();
        $session = $sessionModel->readSingle($id_session);

        if ($session) {
            $agentModel = new Agent();
            $etudiantModel = new Etudiants();
            $agent = $agentModel->readSingle($session['id_agents']);
            $etudiant = $etudiantModel->readSingle($session['id_etudiant']);
            $session['nom_agent'] = $agent ? $agent['nom_agent'] : 'Inconnu';
            $session['nom_complet_etudiant'] = $etudiant ? ($etudiant['prenom'] . ' ' . $etudiant['nom']) : 'Inconnu';
        }
        return $session;
    }

    public function getSingleSessionConversation($id_session)
    {
        $SessionConversation = new SessionConversation();
        return $SessionConversation->readSingle($id_session);
    }

    public function updateSessionConversation($id_session, $date_heure_debut, $duree_session, $date_heure_fin, $id_agents, $id_etudiant)
    {
        $SessionConversation = new SessionConversation();
        return $SessionConversation->update($id_session, $date_heure_debut, $duree_session, $date_heure_fin, $id_agents, $id_etudiant);
    }

    public function deleteSessionConversation($id_Session)
    {
        $SessionConversation = new SessionConversation();
        return $SessionConversation->delete($id_Session);
    }

    /**
     * Gère la soumission du formulaire (création/modification)
     * @param array $post Données POST
     * @param bool $isEditMode true si modification, false si création
     * @param array|null $session Données de la session en mode édition
     * @return array ['success' => bool, 'errors' => array, 'message' => string, 'session' => array|null, 'input' => array]
     */
    public function handleSubmit($post, $isEditMode, $session)
    {
        $errors = [];
        $message = '';

        // Récupération des données
        $date_heure_debut = trim($post['date_heure_debut'] ?? '');
        $duree_session = trim($post['duree_session'] ?? '');
        $date_heure_fin = trim($post['date_heure_fin'] ?? '');
        $id_agents = $post['id_agents'] ?? '';
        $id_etudiant = $post['id_etudiant'] ?? '';

        // Validation de date_heure_debut
        if (empty($date_heure_debut)) {
            $errors[] = "La date et heure de début sont requises.";
        } else {
            // Valider le format DATETIME
            $date_obj = \DateTime::createFromFormat('Y-m-d\TH:i', $date_heure_debut);
            if (!$date_obj) {
                $errors[] = "Le format de la date et heure de début est invalide.";
            } else {
                // Convertir au format MySQL DATETIME
                $date_heure_debut = $date_obj->format('Y-m-d H:i:s');
            }
        }

        // Validation de duree_session
        if (empty($duree_session)) {
            $errors[] = "La durée de la session est requise.";
        } else {
            // Valider le format TIME (HH:MM:SS)
            if (!preg_match('/^([0-1]?[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/', $duree_session)) {
                $errors[] = "Le format de la durée doit être HH:MM:SS (ex: 01:30:00).";
            }
        }

        // Validation de date_heure_fin (optionnel)
        if (!empty($date_heure_fin)) {
            $date_fin_obj = \DateTime::createFromFormat('Y-m-d\TH:i', $date_heure_fin);
            if (!$date_fin_obj) {
                $errors[] = "Le format de la date et heure de fin est invalide.";
            } else {
                $date_heure_fin = $date_fin_obj->format('Y-m-d H:i:s');
            }
        }

        // Validation des relations (id_agents, id_etudiant requis)
        if (empty($id_agents)) {
            $errors[] = "L'agent est requis.";
        } else {
            $agentController = new AgentController();
            $agent = $agentController->getSingleAgent($id_agents);
            if (!$agent) {
                $errors[] = "L'agent sélectionné n'existe pas.";
            }
        }

        if (empty($id_etudiant)) {
            $errors[] = "L'étudiant est requis.";
        } else {
            $etudiantController = new EtudiantController();
            $etudiant = $etudiantController->getSingleEtudiant($id_etudiant);
            if (!$etudiant) {
                $errors[] = "L'étudiant sélectionné n'existe pas.";
            }
        }

        // Si aucune erreur, procéder à l'enregistrement
        if (empty($errors)) {
            if ($isEditMode) {
                // Mode modification
                $id_session = $post['id_session'];
                $result = $this->updateSessionConversation($id_session, $date_heure_debut, $duree_session, $date_heure_fin, $id_agents, $id_etudiant);

                if ($result) {
                    $message = "Session de conversation modifiée avec succès !";
                    // Recharger les données mises à jour
                    $session = $this->getSingleSessionConversation($id_session);
                } else {
                    $errors[] = "Erreur lors de la modification de la session en base de données.";
                }
            } else {
                // Mode création
                $result = $this->createSessionConversation($duree_session, $date_heure_fin, $id_agents, $id_etudiant);

                if ($result) {
                    $message = "Session de conversation créée avec succès !";
                } else {
                    $errors[] = "Erreur lors de la création de la session en base de données.";
                }
            }
        }

        $input = compact('date_heure_debut', 'duree_session', 'date_heure_fin', 'id_agents', 'id_etudiant');
        return [
            'success' => empty($errors),
            'errors' => $errors,
            'message' => $message,
            'session' => $session ?? null,
            'input' => $input
        ];
    }

    /**
     * Gère la suppression d'une session de conversation
     * @param string|null $id ID de la session
     * @param bool $confirmed true si l'utilisateur a confirmé la suppression
     * @return array ['success' => bool, 'errors' => array, 'message' => string, 'session' => array|null, 'redirect' => string|null]
     */
    public function handleDelete($id, $confirmed = false)
    {
        $errors = [];
        $message = '';
        $session = null;
        $redirect = null;

        // Validation de l'ID
        if (!isset($id) || empty($id)) {
            $errors[] = "ID session manquant.";
            return [
                'success' => false,
                'errors' => $errors,
                'message' => "ID session manquant.",
                'session' => null,
                'redirect' => null
            ];
        }

        // Récupération de la session
        $session = $this->getSingleSessionConversation($id);

        if (!$session) {
            $errors[] = "Session de conversation introuvable.";
            return [
                'success' => false,
                'errors' => $errors,
                'message' => "Session de conversation introuvable.",
                'session' => null,
                'redirect' => null
            ];
        }

        // Si confirmation, procéder à la suppression
        if ($confirmed) {
            $result = $this->deleteSessionConversation($id);

            if ($result) {
                return [
                    'success' => true,
                    'errors' => [],
                    'message' => "Session de conversation supprimée avec succès !",
                    'session' => $session,
                    'redirect' => 'index.php?action=session_list&message=supprime'
                ];
            } else {
                $errors[] = "Erreur lors de la suppression de la session. Vérifiez les dépendances (Messages).";
                return [
                    'success' => false,
                    'errors' => $errors,
                    'message' => "Erreur lors de la suppression de la session.",
                    'session' => $session,
                    'redirect' => null
                ];
            }
        }

        // Pas de confirmation, juste retourner la session pour afficher la page de confirmation
        return [
            'success' => false,
            'errors' => [],
            'message' => '',
            'session' => $session,
            'redirect' => null
        ];
    }
}