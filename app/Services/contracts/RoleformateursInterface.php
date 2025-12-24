<?php

namespace App\Services\contracts;

use App\Models\Roleformateurs;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface RoleformateursInterface
{
// déclaration des interfaces à utiliser
    public function saveRoleformateur(Request $data): \Illuminate\Http\JsonResponse;

    public function getRoleformateur(int $id): Roleformateurs;

    public function getRoleformateurs(Request $request): LengthAwarePaginator;

    public function update(Request $data): \Illuminate\Http\JsonResponse;

    public function remove(int $id): \Illuminate\Http\JsonResponse;
}
