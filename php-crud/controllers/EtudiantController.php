<?php

namespace Controllers;

require_once __DIR__ . '/../model/etudiant.php';
require_once __DIR__ . '/../model/role.php';
require_once __DIR__ . '/../model/niveau.php';
require_once __DIR__ . '/RoleController.php';
require_once __DIR__ . '/NiveauController.php';
use Models\Etudiants;
use Models\Role;
use Models\Niveau;

class EtudiantController
{
    public function loginEtudiant($email, $password)
    {
        // Utiliser l'import en tête de fichier (use Models\Etudiants;)
        $Etudiant = new Etudiants();
        $etudiant = $Etudiant->readByEmail($email);
        if ($etudiant && password_verify($password, $etudiant['passwordhash'])) {
            // Récupérer le rôle pour l'ajouter aux données de session/login
            $roleModel = new Role();
            $role = $roleModel->readSingle($etudiant['id_role']);
            $etudiant['role'] = $role ? $role['nom_role'] : null;
            return $etudiant;
        }
        // Connexion échouée
        return false;
    }
    
    public function createEtudiant($nom, $prenom, $email, $avatar, $password, $date_inscription, $consentement_rgpd, $id_role, $id_niveau)
    {
        $Etudiant = new Etudiants();
        // Le modèle doit gérer le hachage du mot de passe
        return $Etudiant->create($nom, $prenom, $email, $avatar, $password, $date_inscription, $consentement_rgpd, $id_role, $id_niveau);
    }

    public function getEtudiant()
    {
        $Etudiant = new Etudiants();
        return $Etudiant->read();
    }

    public function getSingleEtudiant($id_etudiant)
    {
        $Etudiant = new Etudiants();
        return $Etudiant->readSingle($id_etudiant);
    }

    // MÉTHODE POUR UN SEUL ÉTUDIANT AVEC DÉTAILS (utilisée pour modification/suppression)
    public function getSingleEtudiantWithDetails($id_etudiant)
    {
        $etudiantModel = new Etudiants();
        $etudiant = $etudiantModel->readSingle($id_etudiant);

        if ($etudiant) {
            $roleModel = new Role();
            $niveauModel = new Niveau();
            
            $role = $roleModel->readSingle($etudiant['id_role']);
            $etudiant['nom_role'] = $role ? $role['nom_role'] : 'Non défini';

            $niveau = $niveauModel->readSingle($etudiant['id_niveau']);
            $etudiant['libelle_niveau'] = $niveau ? $niveau['libelle_niveau'] : 'Non défini';
        }
        
        return $etudiant;
    }

    // MÉTHODE POUR LA LISTE DES ÉTUDIANTS AVEC DÉTAILS
    public function getEtudiantsWithDetails()
    {
        $etudiantModel = new Etudiants();
        $etudiants = $etudiantModel->read();
        $roleModel = new Role();
        $niveauModel = new Niveau();

        foreach ($etudiants as &$etudiant) {
            $role = $roleModel->readSingle($etudiant['id_role']);
            $etudiant['role'] = $role ? $role['nom_role'] : null;

            $niveau = $niveauModel->readSingle($etudiant['id_niveau']);
            $etudiant['niveau'] = $niveau ? $niveau['libelle_niveau'] : null;
        }
        return $etudiants;
    }

    public function updateEtudiant($id_etudiant, $nom, $prenom, $email, $avatar, $passwordhash, $date_inscription, $consentement_rgpd, $id_role, $id_niveau)
    {
        $Etudiant = new Etudiants();
        return $Etudiant->update($id_etudiant, $nom, $prenom, $email, $avatar, $passwordhash, $date_inscription, $consentement_rgpd, $id_role, $id_niveau);
    }

    public function deleteEtudiant($id_etudiant)
    {
        $Etudiant = new Etudiants();
        return $Etudiant->delete($id_etudiant);
    }



    public function handleSubmit($post, $files, $isEditMode, $etudiant){
        $errors = []; // Tableau pour stocker les erreurs de validation
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $date_inscription = $isEditMode ? ($etudiant['date_inscription'] ?? date('Y-m-d')) : date('Y-m-d');
        $consentement_rgpd = isset($_POST['consentement_rgpd']) ? 1 : 0;
        $id_role = $_POST['id_role'] ?? 1;
        $id_niveau = $_POST['id_niveau'] ?? 1;

        // Validation du nom
        if (empty($nom)) {
            $errors[] = "Le nom est requis.";
        } elseif (strlen($nom) > 50) {
            $errors[] = "Le nom ne doit pas dépasser 50 caractères.";
        } elseif (!preg_match("/^[a-zA-ZÀ-ÿ\s'-]+$/u", $nom)) {
            $errors[] = "Le nom contient des caractères non autorisés.";
        }

        // Validation du prénom
        if (empty($prenom)) {
            $errors[] = "Le prénom est requis.";
        } elseif (strlen($prenom) > 50) {
            $errors[] = "Le prénom ne doit pas dépasser 50 caractères.";
        } elseif (!preg_match("/^[a-zA-ZÀ-ÿ\s'-]+$/u", $prenom)) {
            $errors[] = "Le prénom contient des caractères non autorisés.";
        }

        // Validation de l'email
        if (empty($email)) {
            $errors[] = "L'email est requis.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Le format de l'email est invalide.";
        } else {
            // Vérifier que l'email n'existe pas déjà
            
            $etudiants = $this->getEtudiant();
            foreach ($etudiants as $etud) {
                if ($etud['email'] === $email) {
                    // Si on est en mode édition, vérifier que ce n'est pas le même étudiant
                    if (!$isEditMode || $etud['id_etudiant'] != $_POST['id_etudiant']) {
                        $errors[] = "Cet email est déjà utilisé par un autre étudiant.";
                        break;
                    }
                }
            }
        }

        // Validation du mot de passe
        if (!$isEditMode) {
            // En création, le mot de passe est obligatoire
            if (empty($password)) {
                $errors[] = "Le mot de passe est requis.";
            } elseif (strlen($password) < 8) {
                $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
            }
        } else {
            // En modification, valider seulement si un nouveau mot de passe est fourni
            if (!empty($password) && strlen($password) < 8) {
                $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
            }
        }

        // Validation du consentement RGPD
        if ($consentement_rgpd != 1) {
            $errors[] = "Vous devez accepter la politique de confidentialité (RGPD).";
        }

        // Validation des relations (id_role, id_niveau)
        $roleController = new RoleController();
        $niveauController = new NiveauController();
        $roles = $roleController->getRoles();
        $niveaux = $niveauController->getNiveaux();

        $roleExists = false;
        foreach ($roles as $role) {
            if ($role['id_role'] == $id_role) {
                $roleExists = true;
                break;
            }
        }
        if (!$roleExists) {
            $errors[] = "Le rôle sélectionné n'existe pas.";
        }

        $niveauExists = false;
        foreach ($niveaux as $niveau) {
            if ($niveau['id_niveau'] == $id_niveau) {
                $niveauExists = true;
                break;
            }
        }
        if (!$niveauExists) {
            $errors[] = "Le niveau sélectionné n'existe pas.";
        }
        
        // Gestion de l'avatar avec validation
    $avatar = $isEditMode ? ($etudiant['avatar'] ?? null) : null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $fileInfo = $_FILES['avatar'];

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
            $avatar = uniqid('avatar_', true) . '.' . $extension;
            $uploadPath = __DIR__ . '/../views/../../uploads/' . $avatar;

            if (!move_uploaded_file($fileInfo['tmp_name'], $uploadPath)) {
                $errors[] = "Erreur lors de l'upload de l'avatar.";
                $avatar = $isEditMode ? ($etudiant['avatar'] ?? null) : null;
            }
        }
        } elseif (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Gestion des erreurs d'upload
            switch ($_FILES['avatar']['error']) {
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
            $controller = new EtudiantController();

            if ($isEditMode) {
                // Mode modification
                $id_etudiant = $_POST['id_etudiant'];
                // Si pas de nouveau mot de passe, garder l'ancien
                $passwordhash = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : $etudiant['passwordhash'];

                $result = $this->updateEtudiant($id_etudiant, $nom, $prenom, $email, $avatar, $passwordhash, $date_inscription, $consentement_rgpd, $id_role, $id_niveau);

                if ($result) {
                    $message = "Étudiant modifié avec succès !";
                    // Recharger les données mises à jour
                    $etudiant = $this->getSingleEtudiant($id_etudiant);
                } else {
                    $errors[] = "Erreur lors de la modification de l'étudiant en base de données.";
                }
            } else {
                // Mode création
                $result = $this->createEtudiant($nom, $prenom, $email, $avatar, $password, $date_inscription, $consentement_rgpd, $id_role, $id_niveau);

                if ($result) {
                    $message = "Étudiant créé avec succès !";
                    // Optionnel : rediriger vers la liste
                    // header('Location: etudiant_list.php');
                    // exit;
                } else {
                    $errors[] = "Erreur lors de la création de l'étudiant en base de données.";
                }
            }
        }
        $input = compact('nom', 'prenom', 'email', 'id_role', 'id_niveau');
        return ["success" => TRUE, "errors" => $errors, 'message'=> $message ?? "", "etudiant"=> $etudiant ?? null, "input" => $input] ;
    }

    /**
     * Gère la suppression d'un étudiant
     * @param string|null $id ID de l'étudiant
     * @param bool $confirmed true si l'utilisateur a confirmé la suppression
     * @return array ['success' => bool, 'errors' => array, 'message' => string, 'etudiant' => array|null, 'redirect' => string|null]
     */
    public function handleDelete($id, $confirmed = false)
    {
        $errors = [];
        $message = '';
        $etudiant = null;
        $redirect = null;

        // Validation de l'ID
        if (!isset($id) || empty($id)) {
            $errors[] = "ID étudiant manquant.";
            return [
                'success' => false,
                'errors' => $errors,
                'message' => "ID étudiant manquant.",
                'etudiant' => null,
                'redirect' => null
            ];
        }

        // Récupération de l'étudiant
        $etudiant = $this->getSingleEtudiant($id);

        if (!$etudiant) {
            $errors[] = "Étudiant introuvable.";
            return [
                'success' => false,
                'errors' => $errors,
                'message' => "Étudiant introuvable.",
                'etudiant' => null,
                'redirect' => null
            ];
        }

        // Si confirmation, procéder à la suppression
        if ($confirmed) {
            $result = $this->deleteEtudiant($id);

            if ($result) {
                return [
                    'success' => true,
                    'errors' => [],
                    'message' => "Étudiant supprimé avec succès !",
                    'etudiant' => $etudiant,
                    'redirect' => 'index.php?action=etudiant_list&message=supprime'
                ];
            } else {
                $errors[] = "Erreur lors de la suppression de l'étudiant.";
                return [
                    'success' => false,
                    'errors' => $errors,
                    'message' => "Erreur lors de la suppression de l'étudiant.",
                    'etudiant' => $etudiant,
                    'redirect' => null
                ];
            }
        }

        // Pas de confirmation, juste retourner l'étudiant pour afficher la page de confirmation
        return [
            'success' => false,
            'errors' => [],
            'message' => '',
            'etudiant' => $etudiant,
            'redirect' => null
        ];
    }
}