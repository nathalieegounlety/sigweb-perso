<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Typeformations;
use App\Services\contracts\TypeformationsInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class TypeformationsService implements TypeformationsInterface
{

    public function saveTypeformation(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement saveTypeformation() method.
        $checkData = Typeformations::whereLibelle($data->libelle)->exists();
        if ($checkData) {
            return ResponseHandler::error("La données existe déjà", 409);
        }
        $dataCreate = new Typeformations();
        $dataCreate->setAttribute('libelle', $data->libelle);
        $dataCreate->setAttribute('description', $data->description);

        if (auth('api')->user()) {
            $user = auth('api')->user();
            $dataCreate->setAttribute('created_by', $user->id);
            $dataCreate->setAttribute('updated_by', $user->id);
        }

        $dataCreate->save();
        return ResponseHandler::success("Création efféctuée");
    }

    public function getTypeformation(int $id): Typeformations
    {
        // TODO: Implement getTypeformation() method.
        $data = Typeformations::find($id);
        if (!$data) {
            return ResponseHandler::error("La données n'existe pas", 409);
        }
        return ResponseHandler::success("ok", $data);
    }

    public function getTypeformations(Request $request): LengthAwarePaginator
    {
        // TODO: Implement getTypeformations() method.
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Filtres spécifiques
        $filterLibelle = $request->input('filter_libelle');
        $filterIsEnable = $request->input('filter_is_enable');
        $filterUpdatedAt = $request->input('filter_updated_at');
        $filterAuthor = $request->input('filter_author');

        $query = Typeformations::with('getUserUpdated:id,name,email,code_user')
            ->select('typeformations.id', 'typeformations.libelle', 'typeformations.updated_at','typeformations.enable','typeformations.description','typeformations.updated_by');


        // On fait la jointure
        $needsUserJoin = $search || $filterAuthor || $sortField === 'get_user_updated.name';

        if ($needsUserJoin) {
            $query->leftJoin('users', 'typeformations.updated_by', '=', 'users.id');
        }

        // Recherche globale sur plusieurs champs
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('typeformations.libelle', 'like', "%{$search}%")
                    ->where('typeformations.description', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%"); // Recherche sur auteur
            });
        }

        // Filtres par colonne
        if ($filterLibelle) {
            $query->where('typeformations.libelle', 'like', "%{$filterLibelle}%");
        }
        if ($filterIsEnable !== null && $filterIsEnable !== '') {
            $query->where('typeformations.enable', $filterIsEnable); // 0 ou 1
        }
        if ($filterUpdatedAt) {
            $query->whereDate('typeformations.updated_at', $filterUpdatedAt);
        }
        if ($filterAuthor) {
            $query->where('users.name', 'like', "%{$filterAuthor}%");
        }

        // Tri
        if ($sortField === 'get_user_updated.name') {
            $query->orderBy('users.name', $sortOrder);
        } else {
            $query->orderBy("typeformations{$sortField}", $sortOrder);
        }

        $query->orderBy($sortField, $sortOrder);

        return $query->paginate($perPage);
    }

    public function update(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement update() method.
        $dataCreate = Typeformations::find($data->id);

        if (!$dataCreate) {
            return ResponseHandler::error("Données à modifier introuvable",404);
        }
        // check existing data
        $checkData = Typeformations::whereLibelle($data->libelle)->where("id", "!=", $dataCreate->id)->exists();

        if ($checkData) {
            return ResponseHandler::error("Le libellé existe déjà");
        } else {
            // process update
            $dataCreate->setAttribute('libelle', $data->libelle);
            if(!is_null($data->description)){
                $dataCreate->setAttribute('description', $data->description);
            }
            $dataCreate->setAttribute('updated_by', auth('api')->id());
            $dataCreate->update();
            return ResponseHandler::success("Modification effectuée avec succès");
        }
    }

    public function remove(int $id): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement remove() method.
        $data = Typeformations::find($id);
        if (!$data) {
            return ResponseHandler::error("Modepaiement à supprimer introuvable",404);
        }

        $delete = $data->delete();

        if ($delete) {
            return ResponseHandler::success("Suppression effectuée.");
        } else {
            return ResponseHandler::error("Echec lors de la suppression, vous ne pouvez pas surpprimer cette données : $data->libelle.",401);
        }
    }
}
