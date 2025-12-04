<?php

namespace App\Services\contracts;

use App\Models\InscritsTypePieces;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface TypepiecesInterface
{
    // déclaration des interfaces à utiliser
    public function saveTypepiece(Request $data): \Illuminate\Http\JsonResponse;

    public function getTypepiece(int $id): InscritsTypePieces;

    public function getTypepieces(int $perPage): LengthAwarePaginator;

    public function update(Request $data): \Illuminate\Http\JsonResponse;

    public function remove(int $idRole): \Illuminate\Http\JsonResponse;

}
