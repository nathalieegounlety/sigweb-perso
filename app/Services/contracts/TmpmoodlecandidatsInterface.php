<?php

namespace App\Services\contracts;

use App\Models\Tmpmoodlecandidats;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface TmpmoodlecandidatsInterface
{
    // déclaration des interfaces à utiliser
    public function saveTmpmoodlecandidat(Request $data): \Illuminate\Http\JsonResponse;

    public function getTmpmoodlecandidat(int $id): Tmpmoodlecandidats;

    public function getTmpmoodlecandidats(int $perPage): LengthAwarePaginator;

    public function update(Request $data): \Illuminate\Http\JsonResponse;

    public function remove(int $idRole): \Illuminate\Http\JsonResponse;

}
