<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Inscritstypepieces;
use App\Services\contracts\InscritstypepiecesInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class InscritstypepiecesService implements InscritstypepiecesInterface
{

    public function saveInscritstypepiece(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement saveInscritstypepiece() method.
        // vérifier si la inscritstypepiece existe déjà
        $checkInscritstypepiece = Inscritstypepieces::whereLibelle($data->libelle)->exists();
        if ($checkInscritstypepiece) {
            return ResponseHandler::error("La inscritstypepiece existe déjà", 409);
        }
        $inscritstypepiece = new Inscritstypepieces();
        $inscritstypepiece->setAttribute('libelle', $data->libelle);
        $inscritstypepiece->setAttribute('description', $data->description);

        if (auth('api')->user()) {
            $user = auth('api')->user();
            $inscritstypepiece->setAttribute('created_by', $user->id);
            $inscritstypepiece->setAttribute('updated_by', $user->id);
        }

        $inscritstypepiece->save();
        return ResponseHandler::success("Création efféctuée");

    }

    public function getInscritstypepiece(int $id): Inscritstypepieces
    {
        // TODO: Implement getInscritstypepiece() method.
        $inscritstypepiece = Inscritstypepieces::find($id);
        if (!$inscritstypepiece) {
            return ResponseHandler::error("La inscritstypepiece n'existe pas", 409);
        }
        return ResponseHandler::success("ok", $inscritstypepiece);
    }

    public function getInscritstypepieces(Request $request): LengthAwarePaginator
    {
        // TODO: Implement getInscritstypepieces() method.

        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Filtres spécifiques
        $filterLibelle = $request->input('filter_libelle');
        $filterIsEnable = $request->input('filter_is_enable');
        $filterUpdatedAt = $request->input('filter_updated_at');
        $filterAuthor = $request->input('filter_author');

            $query = InscritsTypePieces::with(['getUserUpdated' => function($query) {
                $query->select('id', 'name', 'email', 'code_user');
            }])
            ->select('Inscrits_type_pieces.*'); // Sélectionnez toutes les colonnes de Inscrits_type_pieces

        // On fait la jointure
        $needsUserJoin = $search || $filterAuthor || $sortField === 'get_user_updated.name';

        if ($needsUserJoin) {
            $query->leftJoin('users', 'Inscrits_type_pieces.updated_by', '=', 'users.id');
        }

        // Recherche globale sur plusieurs champs
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('inscrits_type_pieces.libelle', 'like', "%{$search}%")
                    ->orWhere('inscrits_type_pieces.description', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%"); // Recherche sur auteur
            });
        }

        // Filtres par colonne
        if ($filterLibelle) {
            $query->where('inscrits_type_pieces.libelle', 'like', "%{$filterLibelle}%");
        }
        if ($filterIsEnable !== null && $filterIsEnable !== '') {
            $query->where('inscrits_type_pieces.is_enable', $filterIsEnable); // 0 ou 1
        }
        if ($filterUpdatedAt) {
            $query->whereDate('inscrits_type_pieces.updated_at', $filterUpdatedAt);
        }
        if ($filterAuthor) {
            $query->where('users.name', 'like', "%{$filterAuthor}%");
        }

        // Tri
        if ($sortField === 'get_user_updated.name') {
            $query->orderBy('users.name', $sortOrder);
        } else {
            $query->orderBy("Inscrits_type_pieces.{$sortField}", $sortOrder);
        }

        $query->orderBy($sortField, $sortOrder);

        return $query->paginate($perPage);
    }

    public function update(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement update() method.
        $inscritstypepiece = Inscritstypepieces::find($data->id);

        if (!$inscritstypepiece) {
            return ResponseHandler::error("Inscritstypepiece à modifier introuvable",404);
        }
        // check existing data
        $checkData = Inscritstypepieces::whereLibelle($data->libelle)->where("id", "!=", $inscritstypepiece->id)->exists();

        if ($checkData) {
            return ResponseHandler::error("Le libellé existe déjà");
        } else {
            // process update
            $inscritstypepiece->setAttribute('libelle', $data->libelle);
            
            $inscritstypepiece->setAttribute('updated_by', auth('api')->id());
            $inscritstypepiece->update();
            return ResponseHandler::success("Modification effectuée avec succès");
        }
    }

    public function remove(int $idInscritstypepiece): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement remove() method.
        // TODO: Implement remove() method.
        $inscritstypepiece = Inscritstypepieces::find($idInscritstypepiece);
        if (!$inscritstypepiece) {
            return ResponseHandler::error("Inscritstypepiece à supprimer introuvable",404);
        }

        $deleteInscritstypepiece = $inscritstypepiece->delete();

        if ($deleteInscritstypepiece) {
            return ResponseHandler::success("Suppression effectuée.");
        } else {
            return ResponseHandler::error("Echec lors de la suppression, vous ne pouvez pas surpprimer cette inscritstypepiece : $inscritstypepiece->libelle.",401);
        }
    }
}
