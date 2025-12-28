<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Modepaiements;
use App\Services\contracts\ModepaiementsInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ModepaiementsService implements ModepaiementsInterface
{

    public function saveModepaiement(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement saveModepaiement() method.
        // vérifier si la modepaiement existe déjà
        $checkModepaiement = Modepaiements::whereLibelle($data->libelle)->exists();
        if ($checkModepaiement) {
            return ResponseHandler::error("La données existe déjà", 409);
        }
        $modepaiement = new Modepaiements();
        $modepaiement->setAttribute('libelle', $data->libelle);
        $modepaiement->setAttribute('description', $data->description);

        if (auth('api')->user()) {
            $user = auth('api')->user();
            $modepaiement->setAttribute('created_by', $user->id);
            $modepaiement->setAttribute('updated_by', $user->id);
        }

        $modepaiement->save();
        return ResponseHandler::success("Création efféctuée");

    }

    public function getModepaiement(int $id): Modepaiements
    {
        // TODO: Implement getModepaiement() method.
        $modepaiement = Modepaiements::find($id);
        if (!$modepaiement) {
            return ResponseHandler::error("La données n'existe pas", 409);
        }
        return ResponseHandler::success("ok", $modepaiement);
    }

    public function getModepaiements(Request $request): LengthAwarePaginator
    {
        // TODO: Implement getModepaiements() method.

        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Filtres spécifiques
        $filterLibelle = $request->input('filter_libelle');
        $filterIsEnable = $request->input('filter_is_enable');
        $filterUpdatedAt = $request->input('filter_updated_at');
        $filterAuthor = $request->input('filter_author');

        $query = Modepaiements::with('getUserUpdated:id,name,email,code_user')
            ->select('modepaiements.id', 'modepaiements.libelle','modepaiements.is_enable', 'modepaiements.updated_at');


        // On fait la jointure
        $needsUserJoin = $search || $filterAuthor || $sortField === 'get_user_updated.name';

        if ($needsUserJoin) {
            $query->leftJoin('users', 'modepaiements.updated_by', '=', 'users.id');
        }

        // Recherche globale sur plusieurs champs
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('modepaiements.libelle', 'like', "%{$search}%")
                    ->orWhere('modepaiements.description', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%"); // Recherche sur auteur
            });
        }

        // Filtres par colonne
        if ($filterLibelle) {
            $query->where('modepaiements.libelle', 'like', "%{$filterLibelle}%");
        }
        if ($filterIsEnable !== null && $filterIsEnable !== '') {
            $query->where('modepaiements.is_enable', $filterIsEnable); // 0 ou 1
        }
        if ($filterUpdatedAt) {
            $query->whereDate('modepaiements.updated_at', $filterUpdatedAt);
        }
        if ($filterAuthor) {
            $query->where('users.name', 'like', "%{$filterAuthor}%");
        }

        // Tri
        if ($sortField === 'get_user_updated.name') {
            $query->orderBy('users.name', $sortOrder);
        } else {
            $query->orderBy("modepaiements{$sortField}", $sortOrder);
        }

        $query->orderBy($sortField, $sortOrder);

        return $query->paginate($perPage);
    }

    public function update(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement update() method.
        $modepaiement = Modepaiements::find($data->id);

        if (!$modepaiement) {
            return ResponseHandler::error("Modepaiement à modifier introuvable",404);
        }
        // check existing data
        $checkData = Modepaiements::whereLibelle($data->libelle)->where("id", "!=", $modepaiement->id)->exists();

        if ($checkData) {
            return ResponseHandler::error("Le libellé existe déjà");
        } else {
            // process update
            $modepaiement->setAttribute('libelle', $data->libelle);
            if(!is_null($data->description)){
                $modepaiement->setAttribute('description', $data->description);
            }
            $modepaiement->setAttribute('updated_by', auth('api')->id());
            $modepaiement->update();
            return ResponseHandler::success("Modification effectuée avec succès");
        }
    }

    public function remove(int $idModepaiement): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement remove() method.
        $modepaiement = Modepaiements::find($idModepaiement);
        if (!$modepaiement) {
            return ResponseHandler::error("Modepaiement à supprimer introuvable",404);
        }

        $deleteModepaiement = $modepaiement->delete();

        if ($deleteModepaiement) {
            return ResponseHandler::success("Suppression effectuée.");
        } else {
            return ResponseHandler::error("Echec lors de la suppression, vous ne pouvez pas surpprimer cette modepaiement : $modepaiement->libelle.",401);
        }
    }

    public function getModepaiementsObj(): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getModepaiementsObj() method.
        $data = Modepaiements::where('is_enable', true)->get(['id as code', 'libelle as name']);
        return ResponseHandler::success("ok",$data);
    }
}
