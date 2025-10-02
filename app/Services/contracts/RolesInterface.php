<?php

namespace App\Services\contracts;

use App\Models\Roles;

interface RolesInterface
{
    // déclaration des interfaces à utiliser
    public function saveRole(array $data) : Roles;
    public function getRole(int $id) : Roles;

}
