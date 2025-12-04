<?php

namespace App\Services\contracts;

use App\Models\Typeformations;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface TypeformationsInterface
{
    // déclaration des interfaces à utiliser
    public function saveTypeformation(Request $data): \Illuminate\Http\JsonResponse;

    public function getTypeformation(int $id): Typeformations;

    public function getTypeformations(int $perPage): LengthAwarePaginator;

    public function update(Request $data): \Illuminate\Http\JsonResponse;

    public function remove(int $idRole): \Illuminate\Http\JsonResponse;

}
