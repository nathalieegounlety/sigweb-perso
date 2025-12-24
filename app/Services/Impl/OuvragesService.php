<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Services\contracts\OuvragesInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use \App\Models\Ouvrages;

class OuvragesService implements OuvragesInterface
{

    public function saveOuvrage(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement saveOuvrage() method.
        $checkData = Ouvrages::whereLibelle($data->libelle)
            ->whereMontant($data->montant)
            ->exists();
        if ($checkData) {
            return ResponseHandler::error("La données existe déjà", 409);
        }

        $dataCreate = new Ouvrages();
        $dataCreate->setAttribute('libelle', $data->libelle);
        $dataCreate->setAttribute('montant', $data->montant);

        if (auth('api')->user()) {
            $user = auth('api')->user();
            $dataCreate->setAttribute('created_by', $user->id);
            $dataCreate->setAttribute('updated_by', $user->id);
        }

        $dataCreate->save();
        return ResponseHandler::success("Création efféctuée");
    }

    public function getOuvrage(int $id): \App\Models\Ouvrages
    {
        // TODO: Implement getOuvrage() method.
        $data = Ouvrages::find($id);
        if (!$data) {
            return ResponseHandler::error("La données n'existe pas", 409);
        }
        return ResponseHandler::success("ok", $data);
    }

    public function getOuvrages(Request $request): LengthAwarePaginator
    {
        // TODO: Implement getOuvrages() method.
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

        $query = Ouvrages::with('getUserUpdated:id,name,email,code_user')
            ->select('ouvrages.id', 'ouvrages.libelle','ouvrages.enable','ouvrages.montant', 'ouvrages.updated_at','ouvrages.updated_by');

        // On fait la jointure
        $needsUserJoin = $search || $filterAuthor || $sortField === 'get_user_updated.name';

        if ($needsUserJoin) {
            $query->leftJoin('users', 'ouvrages.updated_by', '=', 'users.id');
        }

        // Recherche globale sur plusieurs champs
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->Where('ouvrages.libelle', 'like', "%{$search}%")
                    ->orWhere('ouvrages.montant', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%"); // Recherche sur auteur
            });
        }

        // Filtres par colonne
        if ($filterLibelle) {
            $query->where('ouvrages.libelle', 'like', "%{$filterLibelle}%");
        }
        if ($filterMontant) {
            $query->where('ouvrages.montant', 'like', "%{$filterMontant}%");
        }
        if ($filterIsEnable !== null && $filterIsEnable !== '') {
            $query->where('ouvrages.enable', $filterIsEnable); // 0 ou 1
        }
        if ($filterUpdatedAt) {
            $query->whereDate('ouvrages.updated_at', $filterUpdatedAt);
        }
        if ($filterAuthor) {
            $query->where('users.name', 'like', "%{$filterAuthor}%");
        }

        // Tri
        if ($sortField === 'get_user_updated.name') {
            $query->orderBy('users.name', $sortOrder);
        } else {
            $query->orderBy("ouvrages.{$sortField}", $sortOrder);
        }

        $query->orderBy($sortField, $sortOrder);

        return $query->paginate($perPage);
    }

    public function update(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement update() method.
        $dataUpdate = Ouvrages::find($data->id);

        if (!$dataUpdate) {
            return ResponseHandler::error("Données à modifier introuvable",404);
        }
        // check existing data
        $checkData = Ouvrages::whereLibelle($data->libelle)
            ->whereMontant($data->montant)
            ->where("id", "!=", $dataUpdate->id)->exists();

        if ($checkData) {
            return ResponseHandler::error("Le données existe déjà");
        } else {
            // process update
            $dataUpdate->setAttribute('libelle', $data->libelle);
            $dataUpdate->setAttribute('montant', $data->montant);
            $dataUpdate->setAttribute('updated_by', auth('api')->id());
            $dataUpdate->update();
            return ResponseHandler::success("Modification effectuée avec succès");
        }
    }

    public function remove(int $id): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement remove() method.
        $dataDelete = Ouvrages::find($id);
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
