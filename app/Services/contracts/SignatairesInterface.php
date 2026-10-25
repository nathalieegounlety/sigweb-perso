<?php

namespace App\Services\contracts;

use App\Models\Signataires;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface SignatairesInterface
{
    // déclaration des interfaces à utiliser
    public function saveSignataire(Request $data): \Illuminate\Http\JsonResponse;

    public function getSignataire(int $id): \Illuminate\Http\JsonResponse;

    public function getSignataires(Request $request): LengthAwarePaginator;

    public function update(Request $data): \Illuminate\Http\JsonResponse;

    public function remove(int $id): \Illuminate\Http\JsonResponse;

}
