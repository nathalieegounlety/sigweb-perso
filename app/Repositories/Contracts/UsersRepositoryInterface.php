<?php 

namespace App\Repositories\Contracts;

use App\Models\User;

interface UsersRepositoryInterface
{
    public function create(array $data): User;
    public function findByEmail(string $email): ?User;
    public function findById(int $id): ?User;
}
