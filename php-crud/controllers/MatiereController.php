<?php

namespace Controllers;

use Models\Matiere;

class MatiereController
{
    public function createMatiere($nom_matieres,
    $description_matiere)
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
}