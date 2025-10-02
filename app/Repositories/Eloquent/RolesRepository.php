<?php

namespace App\Repositories\Eloquent;

use App\Models\Roles;
use App\Models\User;
use App\Repositories\Contracts\RolesRepositoryInterface;
use Couchbase\Role;

class RolesRepository implements  RolesRepositoryInterface
{

    public function create(array $data): Roles
    {
        // TODO: Implement create() method.
        return Roles::create($data);
    }

    public function findByLibelle(string $libelle): ?Roles
    {
        // TODO: Implement findByLibelle() method.
        return  Roles::whereLibelle($libelle)->first();
    }

    public function findById(int $id): ?Roles
    {
        // TODO: Implement findById() method.
        return  Roles::find($id);
    }

    public function update(int $id, array $data): Roles
    {
        // TODO: Implement update() method.
        $role = $this->findById($id);
        $role->setAttribute("libelle",$data['libelle']);
        $role->update();
        return  $role;
    }
}
