<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Signataires;
use App\Services\contracts\SignatairesInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class SignatairesService implements SignatairesInterface
{

    public function saveSignataire(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement saveSignataire() method.
        $checkData = Signataires::whereNom($data->nom)
            ->wherePrenoms($data->prenoms)
            ->whereTitre($data->titre)
            ->whereOrdre($data->ordre)
            ->exists();
        if ($checkData) {
            return ResponseHandler::error("La données existe déjà", 409);
        }

        // initialize signature_path value
        $signature_path = null;
        $picture = $data->signature;
        if (!empty($picture)) {
            # code...
            $extension_picture = $picture->extension(); //getClientOriginalExtension();
            $signature_path = "signature_".date('Ymd-His').'.'.$extension_picture;
            $picture->storeAs('signature', $signature_path,'public');
        }

        $dataCreate = new Signataires();
        $dataCreate->setAttribute('nom', $data->nom);
        $dataCreate->setAttribute('prenoms', $data->prenoms);
        $dataCreate->setAttribute('titre', $data->titre);
        $dataCreate->setAttribute('fonction', $data->fonction);
        $dataCreate->setAttribute('date_debut', $data->date_debut);
        $dataCreate->setAttribute('date_fin', $data->date_fin);
        $dataCreate->setAttribute('ordre', $data->ordre);
        $dataCreate->setAttribute('signature', $signature_path);

        if (auth('api')->user()) {
            $user = auth('api')->user();
            $dataCreate->setAttribute('created_by', $user->id);
            $dataCreate->setAttribute('updated_by', $user->id);
        }

        $dataCreate->save();
        return ResponseHandler::success("Création efféctuée");
    }

    public function getSignataire(int $id): Signataires
    {
        // TODO: Implement getSignataire() method.
        $data = Signataires::find($id);
        if (!$data) {
            return ResponseHandler::error("La données n'existe pas", 409);
        }
        return ResponseHandler::success("ok", $data);
    }

    public function getSignataires(Request $request): LengthAwarePaginator
    {
        // TODO: Implement getSignataires() method.
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Filtres spécifiques
        $filterIsEnable = $request->input('filter_is_enable');
        $filterDateDebut = $request->input('filter_date_debut');
        $filterDateFin = $request->input('filter_date_fin');
        $filterUpdatedAt = $request->input('filter_updated_at');
        $filterAuthor = $request->input('filter_author');

        $query = Signataires::with('getUserUpdated:id,name,email,code_user')
            ->select('signatures.id','signatures.nom', 'signatures.prenoms','signatures.enable','signatures.titre','signatures.fonction','signatures.ordre','signatures.date_debut','signatures.date_fin','signatures.signature', 'signatures.updated_at');

        // On fait la jointure
        $needsUserJoin = $search || $filterAuthor || $sortField === 'get_user_updated.name';

        if ($needsUserJoin) {
            $query->leftJoin('users', 'signatures.updated_by', '=', 'users.id');
        }

        // Recherche globale sur plusieurs champs
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('signatures.nom', 'like', "%{$search}%")
                    ->orWhere('signatures.prenoms', 'like', "%{$search}%")
                    ->orWhere('signatures.titre', 'like', "%{$search}%")
                    ->orWhere('signatures.fonction', 'like', "%{$search}%")
                    ->orWhere('signatures.ordre', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%"); // Recherche sur auteur
            });
        }

        // Filtres par colonne
        if ($filterIsEnable !== null && $filterIsEnable !== '') {
            $query->where('signatures.enable', $filterIsEnable); // 0 ou 1
        }
        if ($filterDateDebut) {
            $query->whereDate('signatures.date_debut', $filterDateDebut);
        }
        if ($filterDateFin) {
            $query->whereDate('signatures.updated_at', $filterDateFin);
        }
        if ($filterUpdatedAt) {
            $query->whereDate('signatures.updated_at', $filterUpdatedAt);
        }
        if ($filterAuthor) {
            $query->where('users.name', 'like', "%{$filterAuthor}%");
        }

        // Tri
        if ($sortField === 'get_user_updated.name') {
            $query->orderBy('users.name', $sortOrder);
        } else {
            $query->orderBy("signatures.{$sortField}", $sortOrder);
        }

        $query->orderBy($sortField, $sortOrder);

        return $query->paginate($perPage);
    }

    public function update(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement update() method.
        $dataUpdate = Signataires::find($data->id);

        if (!$dataUpdate) {
            return ResponseHandler::error("Données à modifier introuvable",404);
        }
        // check existing data
        $checkData = Signataires::whereNom($data->nom)
            ->wherePrenoms($data->prenoms)
            ->whereTitre($data->titre)
            ->whereOrdre($data->ordre)
            ->where("id", "!=", $dataUpdate->id)->exists();

        if ($checkData) {
            return ResponseHandler::error("Le données existe déjà");
        } else {

            // initialize signature_path value
            $signature_path = null;
            $picture = $data->signature;
            if (!empty($picture)) {
                # code...
                $extension_picture = $picture->extension(); //getClientOriginalExtension();
                $signature_path = "signature_".date('Ymd-His').'.'.$extension_picture;
                $picture->storeAs('signature', $signature_path,'public');
            }


            // process update
            $dataUpdate->setAttribute('nom', $data->nom);
            $dataUpdate->setAttribute('prenoms', $data->prenoms);
            $dataUpdate->setAttribute('titre', $data->titre);
            $dataUpdate->setAttribute('fonction', $data->fonction);
            $dataUpdate->setAttribute('ordre', $data->ordre);
            $dataUpdate->setAttribute('date_debut', $data->date_debut);
            $dataUpdate->setAttribute('date_fin', $data->date_fin);
            if (!empty($signature_path)) {
                # code...
                $dataUpdate->setAttribute('signature', $signature_path);
            }
            $dataUpdate->setAttribute('updated_by', auth('api')->id());
            $dataUpdate->update();
            return ResponseHandler::success("Modification effectuée avec succès");
        }
    }

    public function remove(int $id): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement remove() method.
        $dataDelete = Signataires::find($id);
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
