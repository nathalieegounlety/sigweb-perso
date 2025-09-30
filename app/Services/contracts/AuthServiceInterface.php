<?php

namespace App\Services\Contracts;

use App\Models\User;

interface AuthServiceInterface
{
    // enregistrer un compte utilisateur
    public function register(array $data): User;
    // gestion de la connexion
    public function login(array $credentials): ?array;
    // gestion de la déconnexion
    public function logout(): void;
    // rafraîchir le token
    public function refresh(): string;
    // retourne les informations d'un utilisateur
    public function me(): ?User;
}   