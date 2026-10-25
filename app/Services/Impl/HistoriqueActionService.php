<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Detailpaiementouvrages;
use App\Models\Detailspaiements;
use App\Models\Historiqueactions;
use App\Models\PaiementsTranche;
use App\Services\contracts\HistoriqueActionServiceInterface;
use App\Services\util\GenarateurCodeService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HistoriqueActionService implements HistoriqueActionServiceInterface
{

    private GenarateurCodeService $genarateurCodeService; 

    public function __construct(GenarateurCodeService $genarateurCodeService)
    {
        $this->genarateurCodeService = $genarateurCodeService;
    }

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
        $historique = Historiqueactions::find($id);
        if (!$historique) {
            return ResponseHandler::error("Log d'action à supprimer introuvable", 404);
        }

        if ($historique->delete()) {
            return ResponseHandler::success("Suppression effectuée.");
        } 
        
        return ResponseHandler::error("Échec lors de la suppression du log d'action.", 401);
    } 

    public function updateDateComptable(Request $request)
    {
        // récupérer la liste des détails paiements à mettre à jour
        switch ($request->code) {
            case 'FM':
                # code...
                return $this->updateDateComptableFM();
                break;
            case 'DSC':
                # code...
                return $this->updateDateComptableDSC();
                break;
            case 'OUVRAGE':
                # code...
                return $this->updateDateComptableOuvrage();
                break; 
            default:
                # code...
                return ResponseHandler::error("code non valide");
                break;
        } 
    }

    private function updateDateComptableFM()
    {
        // récupérer la liste des détails paiements à mettre à jour
        $nbLignes = Detailspaiements::query()
        ->where('statut_paiement', 'PAYE')
        ->where('statut_traitement', 'VALIDER')
        ->whereNull('date_comptable')
        ->toBase()
        ->update(['date_comptable' => DB::raw('DATE(updated_at)')]); 
        if ($nbLignes > 0) {
            # code... 
            $action = "MAJ DATE COMPTABLE";
            $description = "Mise à jour en masse de la date comptable sur les paiements relatifs aux formations classiques. Nbre ligne mise à jour : ".$nbLignes;
            $this->genarateurCodeService->saveHistoriqueAction($action, $description, auth('api')->user()->id);
 
        } 

           
        return ResponseHandler::success("ok",['nbreLigne' => $nbLignes]);
    }

    
    private function updateDateComptableOuvrage()
    {  
        $nbLignesOuvrage = Detailpaiementouvrages::query()
        ->join('paiements', 'paiements.id', '=', 'detailpaiementouvrages.paiementouvrage_id')
        ->where('paiements.statut_traitement', 'VALIDER')
        ->where('paiements.type', 'OUVRAGE')
        ->whereNull('detailpaiementouvrages.date_comptable')
        ->toBase()                                                     
        ->update([
            'detailpaiementouvrages.date_comptable' => DB::raw('DATE(paiements.updated_at)'),
        ]);  
        if ($nbLignesOuvrage > 0) {
            # code...
         $action = "MAJ DATE COMPTABLE";
        $description = "Mise à jour en masse de la date comptable sur les paiements relatifs aux ouvrages. Nbre ligne mise à jour : ".$nbLignesOuvrage;
            $this->genarateurCodeService->saveHistoriqueAction($action, $description, auth('api')->user()->id);


        }

        return ResponseHandler::success("ok",['nbreLigne' => $nbLignesOuvrage]);
    }

    
    private function updateDateComptableDSC()
    { 
         $nbLignesDsc = PaiementsTranche::query() 
        ->where('statut_traitement', 'VALIDER')
        ->whereNull('date_comptable')
        ->toBase()
        ->update(['date_comptable' => DB::raw('DATE(updated_at)')]);  

        if ($nbLignesDsc > 0) {
            # code...
         $action = "MAJ DATE COMPTABLE";
        $description = "Mise à jour en masse de la date comptable sur les paiements relatifs aux ouvrages. Nbre ligne mise à jour : ".$nbLignesDsc;
            $this->genarateurCodeService->saveHistoriqueAction($action, $description, auth('api')->user()->id);

        }

        return ResponseHandler::success("ok",['nbreLigne' => $nbLignesDsc]);
    }
}
