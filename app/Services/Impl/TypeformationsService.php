<?php

namespace App\Services\Impl;

use App\Models\Typeformations;
use App\Services\contracts\TypeformationsInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class TypeformationsService implements TypeformationsInterface
{

    public function saveTypeformation(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement saveTypeformation() method.
    }

    public function getTypeformation(int $id): Typeformations
    {
        // TODO: Implement getTypeformation() method.
    }

    public function getTypeformations(int $perPage): LengthAwarePaginator
    {
        // TODO: Implement getTypeformations() method.
        $queryData = Typeformations::with('getUserUpdated:id,name,email,code_user')
            ->select('typeformations.id', 'typeformations.libelle', 'typeformations.updated_at','typeformations.enable','typeformations.description','typeformations.updated_by')
            ->paginate($perPage);

        return $queryData;
    }

    public function update(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement update() method.
    }

    public function remove(int $idRole): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement remove() method.
    }
}
