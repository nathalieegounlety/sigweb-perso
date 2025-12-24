<?php

namespace App\Services\contracts;

use App\Models\Ouvrages;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface OuvragesInterface
{
    // déclaration des interfaces à utiliser
    public function saveOuvrage(Request $data): \Illuminate\Http\JsonResponse;

    public function getOuvrage(int $id): Ouvrages;

    public function getOuvrages(Request $request): LengthAwarePaginator;

    public function update(Request $data): \Illuminate\Http\JsonResponse;

    public function remove(int $id): \Illuminate\Http\JsonResponse;

}
