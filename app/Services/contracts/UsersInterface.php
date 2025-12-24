<?php

namespace App\Services\contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface UsersInterface
{
    // déclaration des interfaces à utiliser
    public function saveUser(Request $data): \Illuminate\Http\JsonResponse;

    public function getUsers(Request $request): LengthAwarePaginator;
    public function getUser(int $id): ?array;

    public function update(Request $data): \Illuminate\Http\JsonResponse;

    public function remove(int $idRole): \Illuminate\Http\JsonResponse;

}
