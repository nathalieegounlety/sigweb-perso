<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Reductions;
use App\Services\contracts\ReductionsInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ReductionsService implements ReductionsInterface
{

    public function saveReduction(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement saveReduction() method.
        // vérifier si la modepaiement existe déjà
        $checkData = Reductions::whereLibelle($data->libelle)->wherePourcentage($data->pourcentage)->exists();
        if ($checkData) {
            return ResponseHandler::error("La données existe déjà", 409);
        }
        $dataCreate = new Reductions();
        $dataCreate->setAttribute('libelle', $data->libelle);
        $dataCreate->setAttribute('pourcentage', $data->pourcentage);
        $dataCreate->setAttribute('description', $data->description);

        if (auth('api')->user()) {
            $user = auth('api')->user();
            $dataCreate->setAttribute('created_by', $user->id);
            $dataCreate->setAttribute('updated_by', $user->id);
        }

        $dataCreate->save();
        return ResponseHandler::success("Création efféctuée");
    }

    public function getReduction(int $id): \App\Models\Reductions
    {
        // TODO: Implement getReduction() method.
        $data = Reductions::find($id);
        if (!$data) {
            return ResponseHandler::error("La données n'existe pas", 409);
        }
        return ResponseHandler::success("ok", $data);
    }

    public function getReductions(Request $request): LengthAwarePaginator
    {
        // TODO: Implement getReductions() method.
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Filtres spécifiques
        $filterLibelle = $request->input('filter_libelle');
        $filterIsEnable = $request->input('filter_is_enable');
        $filterUpdatedAt = $request->input('filter_updated_at');
        $filterAuthor = $request->input('filter_author');
        $filterPercent = $request->input('filter_percent');

        $query = Reductions::with(['getUserUpdated' => function($query) {
            $query->select('id', 'name', 'email', 'code_user');
        }])
        ->select('reductions.*'); // Sélectionnez toutes les colonnes de reductions
        
         //   \Log::info($query->toSql());

        // On fait la jointure
        $needsUserJoin = $search || $filterAuthor || $sortField === 'get_user_updated.name';

        if ($needsUserJoin) {
            $query->leftJoin('users', 'reductions.updated_by', '=', 'users.id');
        }

        // Recherche globale sur plusieurs champs
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reductions.libelle', 'like', "%{$search}%")
                    ->orWhere('reductions.pourcentage', 'like', "%{$search}%")
                    ->orWhere('reductions.description', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%"); // Recherche sur auteur
            });
        }

        // Filtres par colonne
        if ($filterLibelle) {
            $query->where('reductions.libelle', 'like', "%{$filterLibelle}%");
        }
        if ($filterPercent) {
            $query->where('reductions.pourcentage', 'like', "%{$filterPercent}%");
        }
        if ($filterIsEnable !== null && $filterIsEnable !== '') {
            $query->where('reductions.enable', $filterIsEnable); // 0 ou 1
        }
        if ($filterUpdatedAt) {
            $query->whereDate('reductions.updated_at', $filterUpdatedAt);
        }
        if ($filterAuthor) {
            $query->where('users.name', 'like', "%{$filterAuthor}%");
        }

        // Tri
        if ($sortField === 'get_user_updated.name') {
            $query->orderBy('users.name', $sortOrder);
        } else {
            $query->orderBy("reductions.{$sortField}", $sortOrder);
        }

        $query->orderBy($sortField, $sortOrder);

        return $query->paginate($perPage);
    }

    public function update(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement update() method.
        $dataUpdate = Reductions::find($data->id);

        if (!$dataUpdate) {
            return ResponseHandler::error("Données à modifier introuvable",404);
        }
        // check existing data
        $checkData = Reductions::whereLibelle($data->libelle)->wherePourcentage($data->pourcentage)->where("id", "!=", $dataUpdate->id)->exists();

        if ($checkData) {
            return ResponseHandler::error("Le données existe déjà");
        } else {
            // process update
            $dataUpdate->setAttribute('libelle', $data->libelle);
            $dataUpdate->setAttribute('pourcentage', $data->pourcentage);
            if(!is_null($data->description)){
                $dataUpdate->setAttribute('description', $data->description);
            }
            $dataUpdate->setAttribute('updated_by', auth('api')->id());
            $dataUpdate->update();
            return ResponseHandler::success("Modification effectuée avec succès");
        }
    }

    public function remove(int $id): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement remove() method.
        $dataDelete = Reductions::find($id);
        if (!$dataDelete) {
            return ResponseHandler::error("Données à supprimer introuvable",404);
        }

        $delete = $dataDelete->delete();

        if ($delete) {
            return ResponseHandler::success("Suppression effectuée.");
        } else {
            return ResponseHandler::error("Echec lors de la suppression, vous ne pouvez pas surpprimer cette données : $dataDelete->libelle.",401);
        }
    }

    public function getReductionObjet(): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getReductionObjet() method.
        $data = Reductions::whereEnable(true)
            ->select('reductions.id as code', 'reductions.libelle as name','reductions.pourcentage')
            ->get();

        return ResponseHandler::success("ok",$data);

    }
}
