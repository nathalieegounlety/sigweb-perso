<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Mail\InscriptionFormationDiplomante;
use App\Mail\InscriptionGratuit;
use App\Mail\Inscriptionpresentiel;
use App\Mail\NotificationCompte;
use App\Mail\Paiement;
use App\Models\Auditeurs;
use App\Models\AuditeursPieces;
use App\Models\Detailspaiements;
use App\Models\Formations;
use App\Models\Grilletarifaires;
use App\Models\Inscriptions;
use App\Models\InscritsTypePieces;
use App\Models\Modepaiements;
use App\Models\Moodlenotes;
use App\Models\Paiements;
use App\Models\PaiementsTranche;
use App\Models\Participants;
use App\Models\Pays;
use App\Models\Rapports;
use App\Models\Reductions;
use App\Models\Tarificationsformation;
use App\Models\Tmpmoodlecandidats;
use App\Models\Tranches;
use App\Models\User;
use App\Services\contracts\CandidaturesInterface;
use App\Services\util\GenarateurCodeService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CandidaturesService implements CandidaturesInterface
{
    private GenarateurCodeService $genarateurCodeService;
    private UsersService $usersService;

    public function __construct(GenarateurCodeService $genarateurCodeService, UsersService $usersService)
    {
        $this->genarateurCodeService = $genarateurCodeService;
        $this->usersService = $usersService;
    }  

    public function getListeCandidats(Request $request): LengthAwarePaginator
    {
        // TODO: Implement getListeCandidats() method.
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Filtres spécifiques
        $filterActif = $request->input('filter_actif');
        $filterUpdatedAt = $request->input('filter_updated_at');
        $filterDdn = $request->input('filter_ddn');

        $queryData = Tmpmoodlecandidats::select(["tmpmoodlecandidats.id", "tmpmoodlecandidats.nom", "tmpmoodlecandidats.prenoms","tmpmoodlecandidats.telephone", "tmpmoodlecandidats.email", "tmpmoodlecandidats.ddn", "tmpmoodlecandidats.lieu_naissance", "tmpmoodlecandidats.updated_at"]);


        // Recherche globale sur plusieurs champs
        if ($search) {
            $queryData->where(function ($q) use ($search) {
                $q->where('tmpmoodlecandidats.nom', 'like', "%{$search}%")
                    ->orWhere('tmpmoodlecandidats.prenoms', 'like', "%{$search}%")
                    ->orWhere('tmpmoodlecandidats.telephone', 'like', "%{$search}%") 
                    ->orWhere('tmpmoodlecandidats.lieu_naissance', 'like', "%{$search}%")
                    ->orWhere('tmpmoodlecandidats.email', 'like', "%{$search}%"); 
            });
        }

        // Filtres par colonne
        if ($filterDdn) {
            $queryData->whereDate('tmpmoodlecandidats.ddn', $filterDdn);
        }
        if ($filterUpdatedAt) {
            $queryData->whereDate('tmpmoodlecandidats.updated_at', $filterUpdatedAt);
        }
        // Tri
        $queryData->orderBy("tmpmoodlecandidats.{$sortField}", $sortOrder);

        return $queryData->paginate($perPage);
    } 

    public function saveUpdateInfoCandidat(Request $request): \Illuminate\Http\JsonResponse
    {   

        // échapement des données en supprimant les espaces au début et à la fin
        $nom = trim($request->nom);
        $prenoms = trim($request->prenoms);
        $email = trim($request->email);

        // check if email exists
        $checkData = Tmpmoodlecandidats::where('email',$email)->where('id','!=',$request->identifiant)->exists();
        if ($checkData) {
            # code...
            return  ResponseHandler::error("Attention!!! l'e-mail existe déjà."); 
        }

        $candidat = Tmpmoodlecandidats::find($request->identifiant);
        $candidat->setAttribute('nom', $nom);
        $candidat->setAttribute('prenoms', $prenoms);
        $candidat->setAttribute('email', $email);
        if ($request->ddn) {
            # code...
            $candidat->setAttribute('ddn', trim($request->ddn));
        }
        if ($request->lieu_naissance) {
            # code...
            $candidat->setAttribute('lieu_naissance', trim($request->lieu_naissance));
        } 
        $candidat->setAttribute('updated_by', auth('api')->user()->id); 
        $candidat->save(); 
         

        $action = 'MISE A JOUR INFO CANDIDAT';
        $description = "Mise à jour des information du candidat : Nom ".$request->nom." Prénoms ".$request->prenoms;
        $this->genarateurCodeService->saveHistoriqueAction($action, $description, auth('api')->user()->id);
        return  ResponseHandler::success("Mise à jour effectuée avec succès"); 
    }
    
    public function getListeFormationsCandidat(string $email): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getListeFormationsCandidat() method.
        $checkInscription = Moodlenotes::whereReferenceCandidat($email)->distinct('reference_candidat')->pluck('code_formation');
        $formations = Formations::whereIn('code_formation',$checkInscription)->get(['code_formation','theme','date_debut','date_fin','id']); 
        $candidat = Tmpmoodlecandidats::whereEmail($email)->first();
        $response["formations"] = $formations;
        $response["id_candidat"] = $candidat->id; 

        return  ResponseHandler::success("ok",$response);
    }
}
