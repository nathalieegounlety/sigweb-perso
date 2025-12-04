<?php

namespace App\Services\contracts;

use App\Models\Modepaiements;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface ModepaiementsInterface
{
    // déclaration des interfaces à utiliser
    public function saveModepaiement(Request $data): \Illuminate\Http\JsonResponse;

    public function getModepaiement(int $id): Modepaiements;

    public function getModepaiements(int $perPage): LengthAwarePaginator;

    public function update(Request $data): \Illuminate\Http\JsonResponse;

    public function remove(int $idRole): \Illuminate\Http\JsonResponse;

}
