<?php

namespace Controllers;

require_once __DIR__ . '/../model/role.php';

use Models\Role;

class RoleController
{
    public function createRole($nom_role)
    {
        $Role = new Role();
        return $Role->create($nom_role);
    }

    public function getRoles()
    {
        $Role = new Role();
        return $Role->read();
    }

    public function getSingleRole($id_role)
    {
        $Role = new Role();
        return $Role->readSingle($id_role);
    }

    public function updateRole($id_role, $nom_role)
    {
        $Role = new Role();
        return $Role->update($id_role, $nom_role);
    }

    public function deleteRole($id_role)
    {
        $Role = new Role();
        return $Role->delete($id_role);
    }

    /**
     * Gère la soumission du formulaire (création/modification)
     * @param array $post Données POST
     * @param bool $isEditMode true si modification, false si création
     * @param array|null $role Données du rôle en mode édition
     * @return array ['success' => bool, 'errors' => array, 'message' => string, 'role' => array|null, 'input' => array]
     */
    public function handleSubmit($post, $isEditMode, $role)
    {
        $errors = [];
        $message = '';

        // Récupération des données
        $nom_role = trim($post['nom_role'] ?? '');

        // Validation du nom
        if (empty($nom_role)) {
            $errors[] = "Le nom du rôle est requis.";
        } elseif (strlen($nom_role) < 3) {
            $errors[] = "Le nom doit contenir au moins 3 caractères.";
        } elseif (strlen($nom_role) > 50) {
            $errors[] = "Le nom ne doit pas dépasser 50 caractères.";
        } else {
            // Vérifier l'unicité du nom
            $roles = $this->getRoles();
            foreach ($roles as $r) {
                if ($r['nom_role'] === $nom_role) {
                    // Si en mode édition, vérifier que ce n'est pas le même rôle
                    if (!$isEditMode || $r['id_role'] != $post['id_role']) {
                        $errors[] = "Ce rôle existe déjà.";
                        break;
                    }
                }
            }
        }

        // Si aucune erreur, procéder à l'enregistrement
        if (empty($errors)) {
            if ($isEditMode) {
                // Mode modification
                $id_role = $post['id_role'];
                $result = $this->updateRole($id_role, $nom_role);

                if ($result) {
                    $message = "Rôle modifié avec succès !";
                    // Recharger les données mises à jour
                    $role = $this->getSingleRole($id_role);
                } else {
                    $errors[] = "Erreur lors de la modification du rôle en base de données.";
                }
            } else {
                // Mode création
                $result = $this->createRole($nom_role);

                if ($result) {
                    $message = "Rôle créé avec succès !";
                } else {
                    $errors[] = "Erreur lors de la création du rôle en base de données.";
                }
            }
        }

        $input = compact('nom_role');
        return [
            'success' => empty($errors),
            'errors' => $errors,
            'message' => $message,
            'role' => $role ?? null,
            'input' => $input
        ];
    }

    /**
     * Gère la suppression d'un rôle
     * @param string|null $id ID du rôle
     * @param bool $confirmed true si l'utilisateur a confirmé la suppression
     * @return array ['success' => bool, 'errors' => array, 'message' => string, 'role' => array|null, 'redirect' => string|null]
     */
    public function handleDelete($id, $confirmed = false)
    {
        $errors = [];
        $message = '';
        $role = null;
        $redirect = null;

        // Validation de l'ID
        if (!isset($id) || empty($id)) {
            $errors[] = "ID rôle manquant.";
            return [
                'success' => false,
                'errors' => $errors,
                'message' => "ID rôle manquant.",
                'role' => null,
                'redirect' => null
            ];
        }

        // Récupération du rôle
        $role = $this->getSingleRole($id);

        if (!$role) {
            $errors[] = "Rôle introuvable.";
            return [
                'success' => false,
                'errors' => $errors,
                'message' => "Rôle introuvable.",
                'role' => null,
                'redirect' => null
            ];
        }

        // Si confirmation, procéder à la suppression
        if ($confirmed) {
            $result = $this->deleteRole($id);

            if ($result) {
                return [
                    'success' => true,
                    'errors' => [],
                    'message' => "Rôle supprimé avec succès !",
                    'role' => $role,
                    'redirect' => 'index.php?action=role_list&message=supprime'
                ];
            } else {
                $errors[] = "Erreur lors de la suppression du rôle.";
                return [
                    'success' => false,
                    'errors' => $errors,
                    'message' => "Erreur lors de la suppression du rôle.",
                    'role' => $role,
                    'redirect' => null
                ];
            }
        }

        // Pas de confirmation, juste retourner le rôle pour afficher la page de confirmation
        return [
            'success' => false,
            'errors' => [],
            'message' => '',
            'role' => $role,
            'redirect' => null
        ];
    }
}