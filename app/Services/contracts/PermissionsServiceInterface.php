<?php

namespace App\Services\contracts;

use App\Models\Permissions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface PermissionsServiceInterface
{
    // déclaration des interfaces à utiliser
    public function savePermission(Request $request) :  \Illuminate\Http\JsonResponse;

    public function getPermission(int $id): Permissions;

    public function getPermissions(int $perPage): LengthAwarePaginator;

    public function update(Request $data): \Illuminate\Http\JsonResponse;

    public function remove(int $idPermission): \Illuminate\Http\JsonResponse;
}
