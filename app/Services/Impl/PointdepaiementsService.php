<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\PointDePaiements;
use App\Services\contracts\PointdepaiementsInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class PointdepaiementsService implements PointdepaiementsInterface
{
    public function savePointdepaiement(Request $data): \Illuminate\Http\JsonResponse
    {
        // Vérifier si le point de paiement existe déjà
        $checkPointsdepaiement = PointDePaiements::where('nom_organisme', $data->nom_organisme)->exists();
        if ($checkPointsdepaiement) {
            return ResponseHandler::error("Ce point de paiement existe déjà", 409);
        }

        $pointsdepaiement = new PointDePaiements();
        $pointsdepaiement->setAttribute('nom_organisme', $data->nom_organisme);
        $pointsdepaiement->setAttribute('adresse', $data->adresse);
        $pointsdepaiement->setAttribute('ville', $data->ville);
        $pointsdepaiement->setAttribute('pays', $data->pays);
        $pointsdepaiement->setAttribute('telephone', $data->telephone);
        $pointsdepaiement->setAttribute('courriel', $data->courriel);
        $pointsdepaiement->setAttribute('contact1', $data->contact1);
        $pointsdepaiement->setAttribute('telephone1', $data->telephone1);
        $pointsdepaiement->setAttribute('courriel1', $data->courriel1);
        $pointsdepaiement->setAttribute('contact2', $data->contact2);
        $pointsdepaiement->setAttribute('telephone2', $data->telephone2);
        $pointsdepaiement->setAttribute('courriel2', $data->courriel2);
        $pointsdepaiement->setAttribute('description', $data->description);

        if (auth('api')->user()) {
            $user = auth('api')->user();
            $pointsdepaiement->setAttribute('created_by', $user->id);
            $pointsdepaiement->setAttribute('updated_by', $user->id);
        }

        $pointsdepaiement->save();
        return ResponseHandler::success("Création effectuée", $pointsdepaiement);
    }

    public function getPointdepaiement(int $id)
    {
        $pointsdepaiement = PointDePaiements::find($id);
        if (!$pointsdepaiement) {
            return ResponseHandler::error("Le point de paiement n'existe pas", 404);
        }
        return ResponseHandler::success("ok", $pointsdepaiement);
    }

    public function getPointdepaiements(Request $request): LengthAwarePaginator
    {
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        $query = PointDePaiements::with(['getUserUpdated' => function($query) {
            $query->select('id', 'name', 'email', 'code_user');
        }])
        ->select('point_de_paiements.*');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nom_organisme', 'like', "%{$search}%")
                  ->orWhere('ville', 'like', "%{$search}%")
                  ->orWhere('pays', 'like', "%{$search}%");
            });
        }

        $query->orderBy($sortField, $sortOrder);
        return $query->paginate($perPage);
    }

    public function update(Request $data): \Illuminate\Http\JsonResponse
    {
        $pointsdepaiement = PointDePaiements::find($data->id);
        if (!$pointsdepaiement) {
            return ResponseHandler::error("Point de paiement à modifier introuvable", 404);
        }

        // Vérifier si le nom_organisme existe déjà (hors mise à jour)
        $checkData = PointDePaiements::where('nom_organisme', $data->nom_organisme)
            ->where("id", "!=", $pointsdepaiement->id)
            ->exists();

        if ($checkData) {
            return ResponseHandler::error("Ce nom d'organisme existe déjà", 409);
        }

        // Mise à jour des champs
        $pointsdepaiement->setAttribute('nom_organisme', $data->nom_organisme);
        $pointsdepaiement->setAttribute('adresse', $data->adresse);
        $pointsdepaiement->setAttribute('ville', $data->ville);
        $pointsdepaiement->setAttribute('pays', $data->pays);
        $pointsdepaiement->setAttribute('telephone', $data->telephone);
        $pointsdepaiement->setAttribute('courriel', $data->courriel);
        $pointsdepaiement->setAttribute('contact1', $data->contact1);
        $pointsdepaiement->setAttribute('telephone1', $data->telephone1);
        $pointsdepaiement->setAttribute('courriel1', $data->courriel1);
        $pointsdepaiement->setAttribute('contact2', $data->contact2);
        $pointsdepaiement->setAttribute('telephone2', $data->telephone2);
        $pointsdepaiement->setAttribute('courriel2', $data->courriel2);
        $pointsdepaiement->setAttribute('description', $data->description);

        if (auth('api')->user()) {
            $pointsdepaiement->setAttribute('updated_by', auth('api')->id());
        }

        $pointsdepaiement->update();
        return ResponseHandler::success("Modification effectuée avec succès");
    }

    public function remove(int $id): \Illuminate\Http\JsonResponse
    {
        $pointsdepaiement = PointDePaiements::find($id);
        if (!$pointsdepaiement) {
            return ResponseHandler::error("Point de paiement à supprimer introuvable", 404);
        }

        $deletePointsdepaiement = $pointsdepaiement->delete();
        if ($deletePointsdepaiement) {
            return ResponseHandler::success("Suppression effectuée");
        } else {
            return ResponseHandler::error("Échec lors de la suppression du point de paiement : {$pointsdepaiement->nom_organisme}", 401);
        }
    }
}
