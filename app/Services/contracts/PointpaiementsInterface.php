<?php

namespace App\Services\contracts;

use App\Models\PointDePaiements;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface PointDePaiementsInterface
{
    // déclaration des interfaces à utiliser
    public function savePointDePaiement(Request $data): \Illuminate\Http\JsonResponse;

    public function getPointDePaiement(int $id): PointDePaiements;

    public function getPointDePaiements(Request $request): LengthAwarePaginator;

    public function update(Request $data): \Illuminate\Http\JsonResponse;

    public function remove(int $id): \Illuminate\Http\JsonResponse;

}
