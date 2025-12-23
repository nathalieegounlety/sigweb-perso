<?php

namespace App\Services\Impl;

use App\Models\Grilletarifaires;
use App\Services\contracts\GrilletarifairesInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class GrilletarifairesService implements GrilletarifairesInterface
{

    public function saveGrilletarifaire(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement saveGrilletarifaire() method.
    }

    public function getGrilletarifaire(int $id): Grilletarifaires
    {
        // TODO: Implement getGrilletarifaire() method.
    }

    public function getGrilletarifaires(int $perPage): LengthAwarePaginator
    {
        // TODO: Implement getGrilletarifaires() method.
    }

    public function getGrilletarifairesObj(int $perPage): LengthAwarePaginator
    {
        // TODO: Implement getGrilletarifaires() method.
        $queryData = Grilletarifaires::select('grilletarifaires.id', 'grilletarifaires.libelle', 'grilletarifaires.montant','grilletarifaires.is_enable','grilletarifaires.type_tarif')
            ->whereNotIn('grilletarifaires.libelle',['ATTESTATION'])
            ->where('grilletarifaires.is_enable',true)
            ->orderBy('grilletarifaires.type_tarif', 'ASC')
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
