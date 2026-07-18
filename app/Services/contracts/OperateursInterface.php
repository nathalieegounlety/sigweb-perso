<?php

namespace App\Services\contracts;

use App\Models\Operateurs;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface OperateursInterface
{
    // déclaration des interfaces à utiliser
    public function saveOperateurs(Request $data): \Illuminate\Http\JsonResponse;

    public function getOneOperateurs(int $id);

    public function getOperateurs(Request $request): LengthAwarePaginator;

    public function update(Request $data): \Illuminate\Http\JsonResponse;

    public function remove(int $id): \Illuminate\Http\JsonResponse;

    public function getOperateursObj():  \Illuminate\Http\JsonResponse;

}
