<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Operateurs;
use App\Services\contracts\OperateursInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class OperateursService implements OperateursInterface
{

    public function saveOperateurs(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement saveOperateurs() method.
        // vérifier si la données existe déjà
        $checkData = Operateurs::whereModepaysId($data->modepays_id)
        ->whereOperateur($data->operateur)->exists();
        if ($checkData) {
            return ResponseHandler::error("La données existe déjà", 409);
        }
        $saveData = new Operateurs();
        $saveData->setAttribute('modepays_id', $data->modepays_id);
        $saveData->setAttribute('operateur', $data->operateur);
        $saveData->setAttribute('devise', $data->devise);
        $saveData->setAttribute('logo_path', 'N/A');

        if (auth('api')->user()) {
            $user = auth('api')->user();
            $saveData->setAttribute('created_by', $user->id);
            $saveData->setAttribute('updated_by', $user->id);
        }

        $saveData->save();
        return ResponseHandler::success("Création efféctuée");

    }

    public function getOneOperateurs(int $id)
    {
        // TODO: Implement getOneOperateurs() method.
        $data = Operateurs::join("modepays","modepays.id","=","operateurs.modepays_id")
        ->join("pays","pays.id","=","modepays.pays_id")
        ->join("modepaiements","modepaiements.id","=","modepays.modepaiement_id")
        ->select(["operateurs.id","operateurs.operateur", "modepays.pays_id","modepays.modepaiement_id","pays.nom_pays","pays.code_iso",
        "modepaiements.libelle"])
        ->whereId($id)->first();
        if (!$data) {
            return ResponseHandler::error("La données n'existe pas", 409);
        }
        return ResponseHandler::success("ok", $data);
    }

    public function getOperateurs(Request $request): LengthAwarePaginator
    {
        // TODO: Implement getOperateurs() method.

        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Filtres spécifiques
        $filterOperateur = $request->input('filter_operateur');
        $filterIsEnable = $request->input('filter_is_enable');
        $filterUpdatedAt = $request->input('filter_updated_at');
        $filterAuthor = $request->input('filter_author');
 
            $query = Operateurs::with(['getUserUpdated' => function($query) {
                $query->select('id', 'name', 'email', 'code_user');
            }])
            ->join('modepays', 'modepays.id', '=', 'operateurs.modepays_id')
            ->join('pays', 'pays.id', '=', 'modepays.pays_id')
            ->join('modepaiements', 'modepaiements.id', '=', 'modepays.modepaiement_id')
            ->select('operateurs.id','operateurs.is_enable','operateurs.devise','operateurs.modepays_id','operateurs.operateur','modepays.modepaiement_id','modepays.pays_id','modepaiements.libelle','pays.nom_pays'); // Sélectionnez toutes les colonnes de modepays

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
        if ($filterOperateur !== null && $filterOperateur !== '') {
            $query->where('operateurs.operateur', $filterOperateur); // 0 ou 1
        }
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
        $data = Operateurs::find($data->id);

        if (!$data) {
            return ResponseHandler::error("Operateur à modifier introuvable",404);
        }
        // check existing data
        $checkData = Operateurs::whereModepaysId($data->modepays_id)
        ->whereOperateur($data->operateur)
        ->where("id", "!=", $data->id)->exists();

        if ($checkData) {
            return ResponseHandler::error("La données existe déjà");
        } else {
            // process update
            $data->setAttribute('modepays_id', $data->modepays_id);
            $data->setAttribute('operateur', $data->operateur); 
            $data->setAttribute('updated_by', auth('api')->id());
            $data->update();
            return ResponseHandler::success("Modification effectuée avec succès");
        }
    }

    public function remove(int $idOperateur): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement remove() method.
        $data = Operateurs::find($idOperateur);
        if (!$data) {
            return ResponseHandler::error("Opérateur à supprimer introuvable",404);
        }

        $delete = $data->delete();

        if ($delete) {
            return ResponseHandler::success("Suppression effectuée.");
        } else {
            return ResponseHandler::error("Echec lors de la suppression, vous ne pouvez pas surpprimer cette données.",401);
        }
    }

    public function getOperateursObj(): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getOperateurObj() method.
        $data = Operateurs::join('modepays', 'modepays.id', '=', 'operateurs.modepays_id')
        ->join('pays', 'pays.id', '=', 'modepays.pays_id')
        ->join('modepaiements', 'modepaiements.id', '=', 'modepays.modepaiement_id')
        ->where('is_enable', true)
        ->get(['operateurs.id','operateurs.operateur', 'operateurs.modepays_id','modepays.pays_id','modepays.modepaiement_id', 'modepaiements.libelle','pays.nom_pays']);
        return ResponseHandler::success("ok",$data);
    }
}
