<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Grilletarifaires;
use App\Services\contracts\GrilletarifairesInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class GrilletarifairesService implements GrilletarifairesInterface
{

    public function saveGrilletarifaire(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement saveGrilletarifaire() method.
        $checkData = Grilletarifaires::whereLibelle($data->libelle)
            ->whereTypeTarif($data->type_tarif)
            ->whereMontant($data->montant)
            ->exists();
        if ($checkData) {
            return ResponseHandler::error("La données existe déjà", 409);
        }

        $dataCreate = new Grilletarifaires();
        $dataCreate->setAttribute('libelle', $data->libelle);
        $dataCreate->setAttribute('type_tarif', $data->type_tarif);
        $dataCreate->setAttribute('montant', $data->montant);

        if (auth('api')->user()) {
            $user = auth('api')->user();
            $dataCreate->setAttribute('created_by', $user->id);
            $dataCreate->setAttribute('updated_by', $user->id);
        }

        $dataCreate->save();
        return ResponseHandler::success("Création efféctuée");
    }

    public function getGrilletarifaire(int $id) 
    {
        // TODO: Implement getGrilletarifaire() method.
        $data = Grilletarifaires::find($id);
        if (!$data) {
            return ResponseHandler::error("La données n'existe pas", 409);
        }
        return ResponseHandler::success("ok", $data);
    }

    public function getGrilletarifaires(Request $request): LengthAwarePaginator
    {
        // TODO: Implement getGrilletarifaires() method.
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Filtres spécifiques
        $filterLibelle = $request->input('filter_libelle');
        $filterIsEnable = $request->input('filter_is_enable');
        $filterUpdatedAt = $request->input('filter_updated_at');
        $filterAuthor = $request->input('filter_author');
        $filterMontant = $request->input('montant');

        $query = Grilletarifaires::with('getUserUpdated:id,name,email,code_user')
            ->select('grilletarifaires.id','grilletarifaires.type_tarif', 'grilletarifaires.libelle','grilletarifaires.is_enable','grilletarifaires.montant', 'grilletarifaires.updated_by', 'grilletarifaires.updated_at');

        // On fait la jointure
        $needsUserJoin = $search || $filterAuthor || $sortField === 'get_user_updated.name';

        if ($needsUserJoin) {
            $query->leftJoin('users', 'grilletarifaires.updated_by', '=', 'users.id');
        }

        // Recherche globale sur plusieurs champs
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('grilletarifaires.libelle', 'like', "%{$search}%")
                    ->orWhere('grilletarifaires.montant', 'like', "%{$search}%")
                    ->orWhere('grilletarifaires.type_tarif', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%"); // Recherche sur auteur
            });
        }

        // Filtres par colonne
        if ($filterLibelle) {
            $query->where('grilletarifaires.libelle', 'like', "%{$filterLibelle}%");
        }
        if ($filterMontant) {
            $query->where('grilletarifaires.montant', 'like', "%{$filterMontant}%");
        }
        if ($filterIsEnable !== null && $filterIsEnable !== '') {
            $query->where('grilletarifaires.is_enable', $filterIsEnable); // 0 ou 1
        }
        if ($filterUpdatedAt) {
            $query->whereDate('grilletarifaires.updated_at', $filterUpdatedAt);
        }
        if ($filterAuthor) {
            $query->where('users.name', 'like', "%{$filterAuthor}%");
        }

        // Tri
        if ($sortField === 'get_user_updated.name') {
            $query->orderBy('users.name', $sortOrder);
        } else {
            $query->orderBy("grilletarifaires.{$sortField}", $sortOrder);
        }

        $query->orderBy($sortField, $sortOrder);

        return $query->paginate($perPage);
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
        $dataUpdate = Grilletarifaires::find($data->id);

        if (!$dataUpdate) {
            return ResponseHandler::error("Données à modifier introuvable",404);
        }
        // check existing data
        $checkData = Grilletarifaires::whereLibelle($data->libelle)
            ->whereMontant($data->montant)
            ->whereTypeTarif($data->type_tarif)
            ->where("id", "!=", $dataUpdate->id)->exists();

        if ($checkData) {
            return ResponseHandler::error("Le données existe déjà");
        } else {
            // process update
            $dataUpdate->setAttribute('libelle', $data->libelle);
            $dataUpdate->setAttribute('type_tarif', $data->type_tarif);
            $dataUpdate->setAttribute('montant', $data->montant);
            $dataUpdate->setAttribute('updated_by', auth('api')->id());
            $dataUpdate->update();
            return ResponseHandler::success("Modification effectuée avec succès");
        }
    }

    public function remove(int $id): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement remove() method.
        $dataDelete = Grilletarifaires::find($id);
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
}
