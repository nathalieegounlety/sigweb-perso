<?php 

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UsersRepositoryInterface;

class UsersRepository implements UsersRepositoryInterface
{
    public function create(array $data): User
    {
        return User::create($data);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email',$email)->first();
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }
}
