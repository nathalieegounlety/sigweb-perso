<?php

namespace App\Services\contracts;

use App\Models\Roles;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface RolesServiceInterface
{
    // déclaration des interfaces à utiliser
    public function saveRole(Request $data): \Illuminate\Http\JsonResponse;

    public function getRole(int $id): Roles;

    public function getRoles(Request $request): LengthAwarePaginator;

    public function update(Request $data): \Illuminate\Http\JsonResponse;

    public function remove(int $idRole): \Illuminate\Http\JsonResponse;

}
