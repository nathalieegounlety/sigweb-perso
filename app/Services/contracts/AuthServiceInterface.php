<?php

namespace App\Services\Contracts;

use App\Models\User;
use Illuminate\Http\Request;

interface AuthServiceInterface
{
    // enregistrer un compte utilisateur
    public function register(Request $data): User;

    // gestion de la connexion
    public function login(array $credentials): ?array;

    // gestion de la déconnexion
    public function logout(): \Illuminate\Http\JsonResponse;

    // rafraîchir le token
    public function refresh(): string;

    // retourne les informations d'un utilisateur
    public function me(): ?User;
}
