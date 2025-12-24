<?php

namespace App\Services\Impl;

use App\Models\Auditeurs;
use App\Models\User;
use App\Services\contracts\AuditeursInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class AuditeursService implements AuditeursInterface
{

    public function saveAuditeur(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement saveAuditeur() method.
    }

    public function getAuditeur(int $id): Auditeurs
    {
        // TODO: Implement getAuditeur() method.
    }

    public function getAuditeurs(Request $request): LengthAwarePaginator
    {
        // TODO: Implement getAuditeurs() method.
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Filtres spécifiques
        $filterActif = $request->input('filter_actif');
        $filterUpdatedAt = $request->input('filter_updated_at');

        $queryData = Auditeurs::select(["auditeurs.id", "auditeurs.nom", "auditeurs.prenoms", "auditeurs.email", "auditeurs.telephone", "auditeurs.pays", "auditeurs.created_at"])
            ->selectRaw(
                '(EXISTS (
                    SELECT 1 FROM auditeurs_pieces
                    WHERE auditeurs_pieces.auditeur_id = auditeurs.id
                )) as check_dossier'
            );


        // Recherche globale sur plusieurs champs
        if ($search) {
            $queryData->where(function ($q) use ($search) {
                $q->where('auditeurs.nom', 'like', "%{$search}%")
                    ->orWhere('auditeurs.prenoms', 'like', "%{$search}%")
                    ->orWhere('auditeurs.telephone', 'like', "%{$search}%")
                    ->orWhere('auditeurs.pays', 'like', "%{$search}%")
                    ->orWhere('auditeurs.email', 'like', "%{$search}%"); // Recherche sur auteur
            });
        }

        // Filtres par colonne
        if ($filterUpdatedAt) {
            $queryData->whereDate('auditeurs.updated_at', $filterUpdatedAt);
        }
        // Tri
        $queryData->orderBy("auditeurs.{$sortField}", $sortOrder);

        return $queryData->paginate($perPage);
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
