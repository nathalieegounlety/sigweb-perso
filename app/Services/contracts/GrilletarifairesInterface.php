<?php

namespace App\Services\contracts;

use App\Models\Grilletarifaires;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface GrilletarifairesInterface
{
    // déclaration des interfaces à utiliser
    public function saveGrilletarifaire(Request $data): \Illuminate\Http\JsonResponse;

    public function getGrilletarifaire(int $id): Grilletarifaires;

    public function getGrilletarifaires(int $perPage): LengthAwarePaginator;

    public function getGrilletarifairesObj(int $perPage): LengthAwarePaginator;

    public function update(Request $data): \Illuminate\Http\JsonResponse;

    public function remove(int $idRole): \Illuminate\Http\JsonResponse;

}
