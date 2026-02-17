<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Formateurs;
use App\Services\contracts\FormateursInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class FormateursService implements FormateursInterface
{

    public function saveFormateur(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement saveGrilletarifaire() method.
        $checkData = Formateurs::whereTitre($data->titre)
            ->whereNom($data->nom)
            ->wherePrenoms($data->prenoms)
            ->whereEmail($data->email)
            ->exists();

        if ($checkData) {
            return ResponseHandler::error("La données existe déjà", 409);
        }

        $dataCreate = new Formateurs();
        $dataCreate->setAttribute('titre', $data->titre);
        $dataCreate->setAttribute('nom', $data->nom);
        $dataCreate->setAttribute('prenoms', $data->prenoms);
        $dataCreate->setAttribute('email', $data->email);
        $dataCreate->setAttribute('telephone', $data->telephone);
        $dataCreate->setAttribute('genre', $data->genre);
        $dataCreate->setAttribute('nationalite', $data->pays);
        $dataCreate->setAttribute('fonction', $data->fonction);
        $dataCreate->setAttribute('organisme', $data->organisme);
        $dataCreate->setAttribute('specialite', $data->specialite);

        if (auth('api')->user()) {
            $user = auth('api')->user();
            $dataCreate->setAttribute('created_by', $user->id);
            $dataCreate->setAttribute('updated_by', $user->id);
        }

        $dataCreate->save();
        return ResponseHandler::success("Création efféctuée");
    } 

    public function getFormateurs(Request $request): LengthAwarePaginator
    {
        // TODO: Implement getFormateurs() method.
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Filtres spécifiques 
        $filterIsEnable = $request->input('filter_is_enable');
        $filterUpdatedAt = $request->input('filter_updated_at');
        $filterAuthor = $request->input('filter_author'); 

        $query = Formateurs::with('getUserUpdated:id,name,email,code_user')
            ->leftJoin('pays','pays.id','=','formateurs.nationalite')
            ->select('formateurs.id','formateurs.titre', 'formateurs.nom','formateurs.enable','formateurs.prenoms', 'formateurs.email','formateurs.telephone',
            'formateurs.organisme','formateurs.specialite','formateurs.fonction','formateurs.genre','formateurs.updated_by', 'formateurs.updated_at',
            'pays.nom_pays','pays.id as pays_id');

        // On fait la jointure
        $needsUserJoin = $search || $filterAuthor || $sortField === 'get_user_updated.name';

        if ($needsUserJoin) {
            $query->leftJoin('users', 'formateurs.updated_by', '=', 'users.id');
        }

        // Recherche globale sur plusieurs champs
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('formateurs.titre', 'like', "%{$search}%")
                    ->orWhere('formateurs.nom', 'like', "%{$search}%")
                    ->orWhere('formateurs.prenoms', 'like', "%{$search}%")
                    ->orWhere('formateurs.email', 'like', "%{$search}%")
                    ->orWhere('formateurs.telephone', 'like', "%{$search}%")
                    ->orWhere('formateurs.genre', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%"); // Recherche sur auteur
            });
        } 

        // Tri
        if ($sortField === 'get_user_updated.name') {
            $query->orderBy('users.name', $sortOrder);
        } else {
            $query->orderBy("formateurs.{$sortField}", $sortOrder);
        }

        $query->orderBy($sortField, $sortOrder);

        return $query->paginate($perPage);
    } 

    public function update(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement update() method.
        $dataUpdate = Formateurs::find($data->id);

        if (!$dataUpdate) {
            return ResponseHandler::error("Données à modifier introuvable",404);
        }
        // check existing data
        $checkData = Formateurs::whereTitre($data->titre)
            ->whereNom($data->nom)
            ->wherePrenoms($data->prenoms)
            ->whereEmail($data->email) 
            ->where("id", "!=", $dataUpdate->id)
            ->exists();

        if ($checkData) {
            return ResponseHandler::error("Le données existe déjà");
        } else {
            // process update
        $dataUpdate->setAttribute('titre', $data->titre);
        $dataUpdate->setAttribute('nom', $data->nom);
        $dataUpdate->setAttribute('prenoms', $data->prenoms);
        $dataUpdate->setAttribute('email', $data->email);
        $dataUpdate->setAttribute('telephone', $data->telephone);
        $dataUpdate->setAttribute('genre', $data->genre);
        $dataUpdate->setAttribute('nationalite', $data->pays);
        $dataUpdate->setAttribute('fonction', $data->fonction);
        $dataUpdate->setAttribute('organisme', $data->organisme);
        $dataUpdate->setAttribute('specialite', $data->specialite); 
            $dataUpdate->setAttribute('updated_by', auth('api')->id());
            $dataUpdate->update();
            return ResponseHandler::success("Modification effectuée avec succès");
        }
    }

    public function remove(int $id): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement remove() method.
        $dataDelete = Formateurs::find($id);
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

    public function getFormateur(int $id) 
    {
        // TODO: Implement getGrilletarifaire() method.
        $data = Formateurs::find($id);
        if (!$data) {
            return ResponseHandler::error("La données n'existe pas", 409);
        }
        return ResponseHandler::success("ok", $data);
    }
}
