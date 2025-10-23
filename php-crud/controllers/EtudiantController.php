<?php

namespace Controllers;

require_once __DIR__ . '/../model/etudiant.php';
require_once __DIR__ . '/../model/role.php';
require_once __DIR__ . '/../model/niveau.php';
use Models\Etudiants;
use Models\Role;
use Models\Niveau;

class EtudiantController
{
    public function loginEtudiant($email, $password)
    {
        $Etudiant = new \Models\Etudiants();
        $etudiant = $Etudiant->readByEmail($email);
        if ($etudiant && password_verify($password, $etudiant['passwordhash'])) {
            // Récupérer le rôle
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

    public function getEtudiantsWithDetails(){
        $etudiantModel = new Etudiants();
        $roleModel = new Role();
        $niveauModel = new Niveau();

        // Récupérer tous les étudiants
        $etudiants = $etudiantModel->read();

        // Enrichir chaque étudiant avec les détails du rôle et du niveau
        foreach ($etudiants as &$etudiant) {
            // Récupérer le rôle
            if (isset($etudiant['id_role'])) {
                $role = $roleModel->readSingle($etudiant['id_role']);
                $etudiant['role'] = $role ? $role['nom_role'] : null;
            }

            // Récupérer le niveau
            if (isset($etudiant['id_niveau'])) {
                $niveau = $niveauModel->readSingle($etudiant['id_niveau']);
                $etudiant['niveau'] = $niveau ? $niveau['libelle_niveau'] : null;
            }
        }

        return $etudiants;
    }
}