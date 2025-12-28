<?php

namespace App\Services\contracts;

use App\Models\Formations;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface FormationsInterface
{
    // déclaration des interfaces à utiliser
    public function saveFormation(Request $data): \Illuminate\Http\JsonResponse;

    public function getFormation(int $id): Formations;

    public function getFormations(Request $request): LengthAwarePaginator;

    public function update(Request $data): \Illuminate\Http\JsonResponse;

    public function remove(int $id): \Illuminate\Http\JsonResponse;

    public function getRecentFormations(Request $request);

}
