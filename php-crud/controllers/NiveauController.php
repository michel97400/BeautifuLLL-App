<?php

namespace Controllers;

use Models\Niveau;

class NiveauController
{
    public function createNiveau($libellé_niveau)
    {
        $Niveau = new Niveau();
        return $Niveau->create($libellé_niveau);
    }

    public function getNiveaus()
    {
        $Niveau = new Niveau();
        return $Niveau->read();
    }

    public function getSingleNiveau($id_niveau)
    {
        $Niveau = new Niveau();
        return $Niveau->readSingle($id_niveau);
    }

    public function updateNiveau($id_niveau, $libellé_niveau)
    {
        $Niveau = new Niveau();
        return $Niveau->update($id_niveau, 
            $libellé_niveau);
    }

    public function deleteNiveau($id_niveau)
    {
        $Niveau = new Niveau();
        return $Niveau->delete($id_niveau);
    }
}