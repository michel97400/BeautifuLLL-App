<?php

namespace Controllers;

require_once __DIR__ . '/../model/niveau.php';

use Models\Niveau;

class NiveauController
{
    public function createNiveau($libelle_niveau)
    {
        $Niveau = new Niveau();
        return $Niveau->create($libelle_niveau);
    }

    public function getNiveaux()
    {
        $Niveau = new Niveau();
        return $Niveau->read();
    }

    public function getSingleNiveau($id_niveau)
    {
        $Niveau = new Niveau();
        return $Niveau->readSingle($id_niveau);
    }

    public function updateNiveau($id_niveau, $libelle_niveau)
    {
        $Niveau = new Niveau();
        return $Niveau->update($id_niveau, $libelle_niveau);
    }

    public function deleteNiveau($id_niveau)
    {
        $Niveau = new Niveau();
        return $Niveau->delete($id_niveau);
    }

    /**
     * Gère la soumission du formulaire (création/modification)
     * @param array $post Données POST
     * @param bool $isEditMode true si modification, false si création
     * @param array|null $niveau Données du niveau en mode édition
     * @return array ['success' => bool, 'errors' => array, 'message' => string, 'niveau' => array|null, 'input' => array]
     */
    public function handleSubmit($post, $isEditMode, $niveau)
    {
        $errors = [];
        $message = '';

        // Récupération des données
        $libelle_niveau = trim($post['libelle_niveau'] ?? '');

        // Validation du libellé
        if (empty($libelle_niveau)) {
            $errors[] = "Le libellé du niveau est requis.";
        } elseif (strlen($libelle_niveau) < 2) {
            $errors[] = "Le libellé doit contenir au moins 2 caractères.";
        } elseif (strlen($libelle_niveau) > 50) {
            $errors[] = "Le libellé ne doit pas dépasser 50 caractères.";
        } else {
            // Vérifier l'unicité du libellé
            $niveaux = $this->getNiveaux();
            foreach ($niveaux as $niv) {
                if ($niv['libelle_niveau'] === $libelle_niveau) {
                    // Si en mode édition, vérifier que ce n'est pas le même niveau
                    if (!$isEditMode || $niv['id_niveau'] != $post['id_niveau']) {
                        $errors[] = "Ce niveau existe déjà.";
                        break;
                    }
                }
            }
        }

        // Si aucune erreur, procéder à l'enregistrement
        if (empty($errors)) {
            if ($isEditMode) {
                // Mode modification
                $id_niveau = $post['id_niveau'];
                $result = $this->updateNiveau($id_niveau, $libelle_niveau);

                if ($result) {
                    $message = "Niveau modifié avec succès !";
                    // Recharger les données mises à jour
                    $niveau = $this->getSingleNiveau($id_niveau);
                } else {
                    $errors[] = "Erreur lors de la modification du niveau en base de données.";
                }
            } else {
                // Mode création
                $result = $this->createNiveau($libelle_niveau);

                if ($result) {
                    $message = "Niveau créé avec succès !";
                } else {
                    $errors[] = "Erreur lors de la création du niveau en base de données.";
                }
            }
        }

        $input = compact('libelle_niveau');
        return [
            'success' => empty($errors),
            'errors' => $errors,
            'message' => $message,
            'niveau' => $niveau ?? null,
            'input' => $input
        ];
    }

    /**
     * Gère la suppression d'un niveau
     * @param string|null $id ID du niveau
     * @param bool $confirmed true si l'utilisateur a confirmé la suppression
     * @return array ['success' => bool, 'errors' => array, 'message' => string, 'niveau' => array|null, 'redirect' => string|null]
     */
    public function handleDelete($id, $confirmed = false)
    {
        $errors = [];
        $message = '';
        $niveau = null;
        $redirect = null;

        // Validation de l'ID
        if (!isset($id) || empty($id)) {
            $errors[] = "ID niveau manquant.";
            return [
                'success' => false,
                'errors' => $errors,
                'message' => "ID niveau manquant.",
                'niveau' => null,
                'redirect' => null
            ];
        }

        // Récupération du niveau
        $niveau = $this->getSingleNiveau($id);

        if (!$niveau) {
            $errors[] = "Niveau introuvable.";
            return [
                'success' => false,
                'errors' => $errors,
                'message' => "Niveau introuvable.",
                'niveau' => null,
                'redirect' => null
            ];
        }

        // Si confirmation, procéder à la suppression
        if ($confirmed) {
            $result = $this->deleteNiveau($id);

            if ($result) {
                return [
                    'success' => true,
                    'errors' => [],
                    'message' => "Niveau supprimé avec succès !",
                    'niveau' => $niveau,
                    'redirect' => 'index.php?action=niveau_list&message=supprime'
                ];
            } else {
                $errors[] = "Erreur lors de la suppression du niveau.";
                return [
                    'success' => false,
                    'errors' => $errors,
                    'message' => "Erreur lors de la suppression du niveau.",
                    'niveau' => $niveau,
                    'redirect' => null
                ];
            }
        }

        // Pas de confirmation, juste retourner le niveau pour afficher la page de confirmation
        return [
            'success' => false,
            'errors' => [],
            'message' => '',
            'niveau' => $niveau,
            'redirect' => null
        ];
    }
}