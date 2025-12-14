<?php

namespace App\Services\Impl;

use App\Models\Formations;
use App\Services\contracts\FormationsInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class FormationsService implements FormationsInterface
{

    public function saveFormation(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement saveFormation() method.
    }

    public function getFormation(int $id): Formations
    {
        // TODO: Implement getFormation() method.
    }

    public function getFormations(int $perPage): LengthAwarePaginator
    {
        // TODO: Implement getFormations() method.
        $queryFormations = Formations::select('formations.*')
            ->with('getUserUpdated:id,name,email,code_user')
            ->with('tarifications')
            ->paginate($perPage);
        return $queryFormations;
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
