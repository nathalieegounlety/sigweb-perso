<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Modepays;
use App\Services\contracts\ModepaysInterface; 
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ModepaysService implements ModepaysInterface
{

    public function saveModepays(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement saveModepays() method.
        // vérifier si la données existe déjà
        $checkData = Modepays::wherePaysId($data->pays_id)->whereModepaiementId($data->modepaiement_id)->exists();
        if ($checkData) {
            return ResponseHandler::error("La données existe déjà", 409);
        }
        $saveData = new Modepays();
        $saveData->setAttribute('pays_id', $data->pays_id);
        $saveData->setAttribute('modepaiement_id', $data->modepaiement_id);

        if (auth('api')->user()) {
            $user = auth('api')->user();
            $saveData->setAttribute('created_by', $user->id);
            $saveData->setAttribute('updated_by', $user->id);
        }

        $saveData->save();
        return ResponseHandler::success("Création efféctuée");

    }

    public function getOneModepays(int $id)
    {
        // TODO: Implement getOneModepays() method.
        $data = Modepays::join("pays","pays.id","=","modepays.pays_id")
        ->join("modepaiements","modepaiements.id","=","modepays.modepaiement_id")
        ->select(["modepays.pays_id","modepays.modepaiement_id","pays.nom_pays","pays.code_iso",
        "modepaiements.libelle"])
        ->whereId($id)->first();
        if (!$data) {
            return ResponseHandler::error("La données n'existe pas", 409);
        }
        return ResponseHandler::success("ok", $data);
    }

    public function getModepays(Request $request): LengthAwarePaginator
    {
        // TODO: Implement getModepays() method.

        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Filtres spécifiques
        $filterLibelle = $request->input('filter_libelle');
        $filterIsEnable = $request->input('filter_is_enable');
        $filterUpdatedAt = $request->input('filter_updated_at');
        $filterAuthor = $request->input('filter_author');
 
            $query = Modepays::with(['getUserUpdated' => function($query) {
                $query->select('id', 'name', 'email', 'code_user');
            }])
            ->innerJoin('pays', 'pays.id', '=', 'modepays.pays_id')
            ->innerJoin('modepaiements', 'modepaiements.id', '=', 'modepays.modepaiement_id')
            ->select('modepays.*','modepaiements.libelle','pays.nom_pays'); // Sélectionnez toutes les colonnes de modepays

        // On fait la jointure
        $needsUserJoin = $search || $filterAuthor || $sortField === 'get_user_updated.name';

        if ($needsUserJoin) {
            $query->leftJoin('users', 'modepays.updated_by', '=', 'users.id');
        }

        // Recherche globale sur plusieurs champs
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('pays.nom_pays', 'like', "%{$search}%")
                    ->orWhere('modepaiements.libelle', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%"); // Recherche sur auteur
            });
        }

        // Filtres par colonne 
        if ($filterIsEnable !== null && $filterIsEnable !== '') {
            $query->where('modepays.is_enable', $filterIsEnable); // 0 ou 1
        }
        if ($filterUpdatedAt) {
            $query->whereDate('modepays.updated_at', $filterUpdatedAt);
        }
        if ($filterAuthor) {
            $query->where('users.name', 'like', "%{$filterAuthor}%");
        }

        // Tri
        if ($sortField === 'get_user_updated.name') {
            $query->orderBy('users.name', $sortOrder);
        } else {
            $query->orderBy("modepays.{$sortField}", $sortOrder);
        }

        $query->orderBy($sortField, $sortOrder);

        return $query->paginate($perPage);
    }

    public function update(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement update() method.
        $data = Modepays::find($data->id);

        if (!$data) {
            return ResponseHandler::error("Mode paiement par pays à modifier introuvable",404);
        }
        // check existing data
        $checkData = Modepays::wherePaysId($data->pays_id)
        ->whereModepaiementId($data->modepaiement_id)
        ->where("id", "!=", $data->id)->exists();

        if ($checkData) {
            return ResponseHandler::error("La données existe déjà");
        } else {
            // process update
            $data->setAttribute('pays_id', $data->pays_id);
            $data->setAttribute('modepaiement_id', $data->modepaiement_id); 
            $data->setAttribute('updated_by', auth('api')->id());
            $data->update();
            return ResponseHandler::success("Modification effectuée avec succès");
        }
    }

    public function remove(int $idModepays): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement remove() method.
        $data = Modepays::find($idModepays);
        if (!$data) {
            return ResponseHandler::error("Modepaiement par pays à supprimer introuvable",404);
        }

        $delete = $data->delete();

        if ($delete) {
            return ResponseHandler::success("Suppression effectuée.");
        } else {
            return ResponseHandler::error("Echec lors de la suppression, vous ne pouvez pas surpprimer cette données.",401);
        }
    }

    public function getModepaysObj(): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getModepaysObj() method.
        $data = Modepays::innerJoin('pays', 'pays.id', '=', 'modepays.pays_id')
        ->innerJoin('modepaiements', 'modepaiements.id', '=', 'modepays.modepaiement_id')
        ->where('is_enable', true)
        ->get(['modepays.id','modepays.pays_id','modepays.modepaiement_id', 'modepaiements.libelle','pays.nom_pays']);
        return ResponseHandler::success("ok",$data);
    }
}
