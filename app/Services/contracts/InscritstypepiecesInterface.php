<?php

namespace App\Services\contracts;

use App\Models\InscritsTypePieces;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface InscritstypepiecesInterface
{
    // déclaration des interfaces à utiliser
    public function saveInscritstypepiece(Request $data): \Illuminate\Http\JsonResponse;

    public function getInscritstypepiece(int $id): InscritsTypePieces;

    public function getInscritstypepieces(Request $request): LengthAwarePaginator;

    public function update(Request $data): \Illuminate\Http\JsonResponse;

    public function remove(int $id): \Illuminate\Http\JsonResponse;

}
