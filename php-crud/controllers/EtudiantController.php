<?php

namespace Controllers;

require_once __DIR__ . '/../model/etudiant.php';
require_once __DIR__ . '/../model/role.php';
use Models\Etudiants;
use Models\Role;

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

    public function updateEtudiant($id_etudiant, $nom, $prenom, $email, $avatar, $passwordhash, $date_inscription, $consentement_rgpd, $id_role)
    {
        $Etudiant = new Etudiants(); 
        return $Etudiant->update(id: $id_etudiant,nom: $nom, prenom: $prenom, email: $email, avatar: $avatar, passwordhash: $passwordhash);
    }

    public function deleteEtudiant($id_etudiant)
    {
        $Etudiant = new Etudiants();
        return $Etudiant->delete($id_etudiant);
    }
}