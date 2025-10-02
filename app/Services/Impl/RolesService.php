<?php

namespace App\Services\Impl;

use App\Models\Roles;
use App\Repositories\Contracts\RolesRepositoryInterface;
use App\Services\contracts\RolesInterface;

class RolesService implements RolesInterface
{
    private RolesRepositoryInterface $rolesRepository;

    public function __construct(RolesRepositoryInterface $rolesRepository)
    {
        $this->rolesRepository = $rolesRepository;
    }

    public function saveRole(array $data): Roles
    {
        // TODO: Implement saveRole() method.
        $saveRole = $this->rolesRepository->create($data);
        return  $saveRole;
    }

    public function getRole(int $id): Roles
    {
        // TODO: Implement getRole() method.
        return $this->rolesRepository->findById($id);
    }
}
