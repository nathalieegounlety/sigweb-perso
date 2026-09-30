<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Historiqueactions; 
use App\Services\contracts\HistoriqueActionServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class HistoriqueActionService implements HistoriqueActionServiceInterface
{
    public function getHistoriques(int $perPage): LengthAwarePaginator
    {
        return HistoriqueActions::with('getUserCreated:id,name,email')
            ->select('id', 'action', 'description', 'ip_adresse', 'mac_adresse', 'created_by', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getHistorique(int $id)
    {
        $historique = HistoriqueActions::with('getUserCreated:id,name,email')->find($id);
        if (!$historique) {
            return ResponseHandler::error("Le log d'action demandé n'existe pas", 404);
        }
        return ResponseHandler::success("ok", $historique);
    }

    public function saveHistorique(Request $data): \Illuminate\Http\JsonResponse
    {
        $historique = new HistoriqueActions();
        $historique->setAttribute('action', $data->action);
        $historique->setAttribute('description', $data->description);
        
        // Injection de l'IP passée ou récupération auto via le serveur de secours
        $historique->setAttribute('ip_adresse', $data->ip_adresse ?? $data->ip());
        $historique->setAttribute('mac_adresse', $data->mac_adresse);

        if (auth('api')->user()) {
            $user = auth('api')->user();
            $historique->setAttribute('created_by', $user->id);
        }

        $historique->save();
        return ResponseHandler::success("Log d'action enregistré avec succès");
    }

    public function update(Request $data): \Illuminate\Http\JsonResponse
    {
        $historique = HistoriqueActions::find($data->id);

        if (!$historique) {
            return ResponseHandler::error("Log d'action introuvable", 404);
        }

        $historique->setAttribute('action', $data->action);
        $historique->setAttribute('description', $data->description);
        $historique->setAttribute('ip_adresse', $data->ip_adresse ?? $data->ip());
        $historique->setAttribute('mac_adresse', $data->mac_adresse);
        
        $historique->update();
        return ResponseHandler::success("Log d'action mis à jour avec succès");
    }

    public function remove(int $id): \Illuminate\Http\JsonResponse
    {
        $historique = HistoriqueActions::find($id);
        if (!$historique) {
            return ResponseHandler::error("Log d'action à supprimer introuvable", 404);
        }

        if ($historique->delete()) {
            return ResponseHandler::success("Suppression effectuée.");
        } 
        
        return ResponseHandler::error("Échec lors de la suppression du log d'action.", 401);
    }
}
