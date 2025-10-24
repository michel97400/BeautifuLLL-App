<?php

namespace Controllers;

require_once __DIR__ . '/../model/matieres.php';

use Models\Matiere;

class MatiereController
{
    public function createMatiere($nom_matieres, $description_matiere)
    {
        $Matiere = new Matiere();
        return $Matiere->create($nom_matieres, $description_matiere);
    }

    public function getMatiere()
    {
        $Matiere = new Matiere();
        return $Matiere->read();
    }

    public function getSingleMatiere($id_matieres)
    {
        $Matiere = new Matiere();
        return $Matiere->readSingle($id_matieres);
    }

    public function updateMatiere($id_matieres, $nom_matieres, $description_matiere)
    {
        $Matiere = new Matiere();
        return $Matiere->update($id_matieres, $nom_matieres, $description_matiere);
    }

    public function deleteMatiere($id_matieres)
    {
        $Matiere = new Matiere();
        return $Matiere->delete($id_matieres);
    }

    /**
     * Gère la soumission du formulaire (création/modification)
     * @param array $post Données POST
     * @param bool $isEditMode true si modification, false si création
     * @param array|null $matiere Données de la matière en mode édition
     * @return array ['success' => bool, 'errors' => array, 'message' => string, 'matiere' => array|null, 'input' => array]
     */
    public function handleSubmit($post, $isEditMode, $matiere)
    {
        $errors = [];
        $message = '';

        // Récupération des données
        $nom_matieres = trim($post['nom_matieres'] ?? '');
        $description_matiere = trim($post['description_matiere'] ?? '');

        // Validation du nom
        if (empty($nom_matieres)) {
            $errors[] = "Le nom de la matière est requis.";
        } elseif (strlen($nom_matieres) < 3) {
            $errors[] = "Le nom doit contenir au moins 3 caractères.";
        } elseif (strlen($nom_matieres) > 100) {
            $errors[] = "Le nom ne doit pas dépasser 100 caractères.";
        } else {
            // Vérifier l'unicité du nom
            $matieres = $this->getMatiere();
            foreach ($matieres as $mat) {
                if ($mat['nom_matieres'] === $nom_matieres) {
                    // Si en mode édition, vérifier que ce n'est pas la même matière
                    if (!$isEditMode || $mat['id_matieres'] != $post['id_matieres']) {
                        $errors[] = "Ce nom de matière existe déjà.";
                        break;
                    }
                }
            }
        }

        // Validation de la description
        if (!empty($description_matiere) && strlen($description_matiere) > 500) {
            $errors[] = "La description ne doit pas dépasser 500 caractères.";
        }

        // Si aucune erreur, procéder à l'enregistrement
        if (empty($errors)) {
            if ($isEditMode) {
                // Mode modification
                $id_matieres = $post['id_matieres'];
                $result = $this->updateMatiere($id_matieres, $nom_matieres, $description_matiere);

                if ($result) {
                    $message = "Matière modifiée avec succès !";
                    // Recharger les données mises à jour
                    $matiere = $this->getSingleMatiere($id_matieres);
                } else {
                    $errors[] = "Erreur lors de la modification de la matière en base de données.";
                }
            } else {
                // Mode création
                $result = $this->createMatiere($nom_matieres, $description_matiere);

                if ($result) {
                    $message = "Matière créée avec succès !";
                } else {
                    $errors[] = "Erreur lors de la création de la matière en base de données.";
                }
            }
        }

        $input = compact('nom_matieres', 'description_matiere');
        return [
            'success' => empty($errors),
            'errors' => $errors,
            'message' => $message,
            'matiere' => $matiere ?? null,
            'input' => $input
        ];
    }

    /**
     * Gère la suppression d'une matière
     * @param string|null $id ID de la matière
     * @param bool $confirmed true si l'utilisateur a confirmé la suppression
     * @return array ['success' => bool, 'errors' => array, 'message' => string, 'matiere' => array|null, 'redirect' => string|null]
     */
    public function handleDelete($id, $confirmed = false)
    {
        $errors = [];
        $message = '';
        $matiere = null;
        $redirect = null;

        // Validation de l'ID
        if (!isset($id) || empty($id)) {
            $errors[] = "ID matière manquant.";
            return [
                'success' => false,
                'errors' => $errors,
                'message' => "ID matière manquant.",
                'matiere' => null,
                'redirect' => null
            ];
        }

        // Récupération de la matière
        $matiere = $this->getSingleMatiere($id);

        if (!$matiere) {
            $errors[] = "Matière introuvable.";
            return [
                'success' => false,
                'errors' => $errors,
                'message' => "Matière introuvable.",
                'matiere' => null,
                'redirect' => null
            ];
        }

        // Si confirmation, procéder à la suppression
        if ($confirmed) {
            $result = $this->deleteMatiere($id);

            if ($result) {
                return [
                    'success' => true,
                    'errors' => [],
                    'message' => "Matière supprimée avec succès !",
                    'matiere' => $matiere,
                    'redirect' => 'index.php?action=matiere_list&message=supprime'
                ];
            } else {
                $errors[] = "Erreur lors de la suppression de la matière.";
                return [
                    'success' => false,
                    'errors' => $errors,
                    'message' => "Erreur lors de la suppression de la matière.",
                    'matiere' => $matiere,
                    'redirect' => null
                ];
            }
        }

        // Pas de confirmation, juste retourner la matière pour afficher la page de confirmation
        return [
            'success' => false,
            'errors' => [],
            'message' => '',
            'matiere' => $matiere,
            'redirect' => null
        ];
    }
}