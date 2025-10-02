<?php
namespace App\Repositories\Contracts;

use App\Models\Roles;

interface RolesRepositoryInterface
{
    public function create(array $data): Roles;
    public function findByLibelle(string $libelle): ?Roles;
    public function findById(int $id): ?Roles;
    public function update(int $id, array $data): Roles;
}
