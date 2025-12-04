<?php

namespace App\Services\contracts;

use App\Models\Reductions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface ReductionsInterface
{
    // déclaration des interfaces à utiliser
    public function saveReduction(Request $data): \Illuminate\Http\JsonResponse;

    public function getReduction(int $id): Reductions;

    public function getReductions(int $perPage): LengthAwarePaginator;

    public function update(Request $data): \Illuminate\Http\JsonResponse;

    public function remove(int $idRole): \Illuminate\Http\JsonResponse;

}
