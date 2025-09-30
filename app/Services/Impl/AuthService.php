<?php

namespace App\Services\Impl;

use App\Models\User;
use App\Repositories\Contracts\UsersRepositoryInterface;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService implements AuthServiceInterface
{
    // injection de l'interface repository pour la logique métier (enregistrement des données dans la BDD)
    private UsersRepositoryInterface $userRepository;

    public function __construct(UsersRepositoryInterface $userRepository) {
        $this->userRepository = $userRepository;
    }

     public function register(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        return $this->userRepository->create($data);
    }

    public function login(array $credentials): ?array
    {
        if (!$token = Auth::attempt($credentials)) {
            return null;
        }

        /** @var User $user */
        $user = Auth()->user()->load('roles.permissions');

        return [
            'access_token'=>$token,
            'token_type'=>'bearer',
            'expires_in'=> config('jwt.ttl')*60,
            'user'=>$user
        ];
    }

    public function logout(): void
    {
        Auth::logout();
    }

    public function refresh(): string
    {
        return Auth::refresh();
    }

    public function me(): ?User
    {
        return Auth::user();
    }
}
