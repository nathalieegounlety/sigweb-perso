<?php

namespace App\Services\contracts;

use App\Models\Signataires;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface SignatairesInterface
{
    // déclaration des interfaces à utiliser
    public function saveSignataire(Request $data): \Illuminate\Http\JsonResponse;

    public function getSignataire(int $id): Signataires;

    public function getSignataires(int $perPage): LengthAwarePaginator;

    public function update(Request $data): \Illuminate\Http\JsonResponse;

    public function remove(int $idRole): \Illuminate\Http\JsonResponse;

}
