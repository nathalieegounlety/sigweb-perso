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

    public function getFormations(Request $request): LengthAwarePaginator
    {
        // TODO: Implement getFormations() method.
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Filtres spécifiques
        $filterTypeFormation = $request->input('filter_type_formation');
        $filterIsEnable = $request->input('filter_enable');
        $filterUpdatedAt = $request->input('filter_updated_at');
        $filterAuthor = $request->input('filter_author');
        $filterDateDebut = $request->input('filter_date_debut');
        $filterDateFin = $request->input('filter_date_fin');

        $queryFormations = Formations::select('formations.*')
            ->with('getUserUpdated:id,name,email,code_user')
            ->with('getTypeformation:id,libelle')
            ->with('tarifications');

        // On fait la jointure dès qu'on a besoin de users.name (recherche globale OU filtre auteur OU tri par auteur)
        $needsUserJoin = $search || $filterAuthor || $sortField === 'get_user_updated.name';

        if ($needsUserJoin) {
            $queryFormations->leftJoin('users', 'formations.updated_by', '=', 'users.id');
        }

        // Recherche globale sur plusieurs champs
        if ($search) {
            $queryFormations->where(function ($q) use ($search) {
                $q->where('formations.code_formation', 'like', "%{$search}%")
                    ->orWhere('formations.theme', 'like', "%{$search}%")
                ->orWhere('formations.id_webinaire', 'like', "%{$search}%"); // Recherche sur auteur
            });
        }

        // Filtres par colonne
        if ($filterTypeFormation) {
            $queryFormations->leftJoin('typeformations', 'formations.typeformation_id', '=', 'typeformations.id');
            $queryFormations->where('typeformations.libelle', 'like', "%{$filterTypeFormation}%");
        }

        if ($filterIsEnable !== null && $filterIsEnable !== '') {
            $queryFormations->where('formations.enable', $filterIsEnable); // 0 ou 1
        }
        if ($filterDateDebut) {
            $queryFormations->whereDate('formations.date_debut', $filterDateDebut);
        }
        if ($filterDateFin) {
            $queryFormations->whereDate('formations.date_fin', $filterDateFin);
        }
        if ($filterUpdatedAt) {
            $queryFormations->whereDate('formations.updated_at', $filterUpdatedAt);
        }
        if ($filterAuthor) {
            $queryFormations->where('users.name', 'like', "%{$filterAuthor}%");
        }

        // Tri
        if ($sortField === 'get_user_updated.name') {
            $queryFormations->orderBy('users.name', $sortOrder);
        } else {
            $queryFormations->orderBy("formations.{$sortField}", $sortOrder);
        }

       // $queryFormations->orderBy($sortField, $sortOrder);

        return $queryFormations->paginate($perPage);
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
