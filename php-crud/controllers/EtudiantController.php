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
    // ... create, login, update, delete restent identiques ...
    public function loginEtudiant($email, $password) { /* ... */ }
    public function createEtudiant($nom, $prenom, $email, $avatar, $password, $date_inscription, $consentement_rgpd, $id_role, $id_niveau) { /* ... */ }

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

    public function updateEtudiant($id_etudiant, $nom, $prenom, $email, $avatar, $passwordhash, $date_inscription, $consentement_rgpd, $id_role, $id_niveau) { /* ... */ }
    public function deleteEtudiant($id_etudiant) { /* ... */ }
}