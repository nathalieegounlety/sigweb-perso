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
            return ResponseHandler::error("La modepaiement existe déjà", 409);
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
            return ResponseHandler::error("La modepaiement n'existe pas", 409);
        }
        return ResponseHandler::success("ok", $modepaiement);
    }

    public function getModepaiements(int $perPage): LengthAwarePaginator
    {
        // TODO: Implement getModepaiements() method.
        $queryModepaiements = Modepaiements::with('getUserUpdated:id,name,email,code_user')
            ->select('Modepaiements.id', 'Modepaiements.libelle', 'Modepaiements.updated_at')
            ->paginate($perPage);

        return $queryModepaiements;
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
}
