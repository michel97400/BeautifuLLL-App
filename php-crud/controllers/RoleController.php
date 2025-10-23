<?php

namespace Controllers;

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
}