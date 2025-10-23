<?php

namespace Controllers;

use Models\Etudiants;

class EtudiantController
{
    public function createEtudiant($nom, $prenom, $email, $avatar, $passwordhash, $date_inscription, $consentement_rgpd, $id_role)
    {
        $Etudiant = new Etudiants();
        return $Etudiant->create(nom: $nom, prenom: $prenom, email: $email, avatar: $avatar, passwordhash: $passwordhash, date_inscription: $date_inscription, consentement_rgpd: $consentement_rgpd, id_role: $id_role);
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
        return $Etudiant->update(id: $id_etudiant,nom: $nom, prenom: $prenom, email: $email, avatar: $avatar, passwordhash: $passwordhash, date_inscription: $date_inscription, consentement_rgpd: $consentement_rgpd, id_role: $id_role);
    }

    public function deleteEtudiant($id_etudiant)
    {
        $Etudiant = new Etudiants();
        return $Etudiant->delete($id_etudiant);
    }
}