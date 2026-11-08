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
use App\Models\Moodlemoyennegenerales;
use App\Models\Moodlemoyennes;
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
        $checkInscription = Moodlemoyennes::whereReferenceCandidat($email)->distinct('reference_candidat')->pluck('code_formation');
        Log::info($checkInscription);
        $formations = Formations::whereIn('code_formation',$checkInscription)->get(['code_formation','theme','date_debut','date_fin','id']); 
        $candidat = Tmpmoodlecandidats::whereEmail($email)->first();
        $response["formations"] = $formations;
        $response["id_candidat"] = $candidat->id; 

        return  ResponseHandler::success("ok",$response);
    }

        public function getListeNoteFinaleCandidats(Request $request, string $code_formation)
    {
        // TODO: Implement getListeNoteFinaleCandidats() method.

        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc'); 

        
      $moyennes = DB::table('moodlemoyennes')
        ->join('moodlemodules', 'moodlemodules.id', '=', 'moodlemoyennes.moodlemodule_id')
        ->join('tmpmoodlecandidats', 'tmpmoodlecandidats.email', '=', 'moodlemoyennes.reference_candidat') 
        ->select('moodlemoyennes.note_totale', 'moodlemoyennes.note_coef', 'moodlemodules.code as code_module', 'tmpmoodlecandidats.prenoms', 'tmpmoodlecandidats.nom','tmpmoodlecandidats.email')
        ->where('moodlemodules.code_formation', $code_formation)
        ->where('moodlemoyennes.code_formation', $code_formation) ;
        //->orderBy('moodlemodules.code', 'ASC')
        //->get();    

        $check_moyenne_generale = Moodlemoyennegenerales::whereCodeFormation($code_formation)->exists(); 

        // Recherche globale sur plusieurs champs
        if ($search) {
            $moyennes->where(function ($q) use ($search) {
                $q->where('tmpmoodlecandidats.nom', 'like', "%{$search}%")
                    ->orWhere('tmpmoodlecandidats.prenoms', 'like', "%{$search}%")
                    ->orWhere('tmpmoodlecandidats.email', 'like', "%{$search}%")
                    ->orWhere('moodlemodules.code', 'like', "%{$search}%");
            });
        } 

        if ($sortField == 'nom' || $sortField == 'prenoms' || $sortField == 'email') {
            # code...
        $moyennes->orderBy("tmpmoodlecandidats.{$sortField}", $sortOrder);

        }elseif ($sortField == 'code_module') {
            # code... 
        $moyennes->orderBy("moodlemodules.code", $sortOrder);

        }elseif ($sortField == 'note_coef') {
            # code... 
        $moyennes->orderBy("moodlemoyennes.{$sortField}", $sortOrder);

        }elseif ($sortField == 'note_totale') {
            # code... 
        $moyennes->orderBy("moodlemoyennes.{$sortField}", $sortOrder);

        } 
        
        else{
            
        $moyennes->orderBy("moodlemodules.code", $sortOrder);

        } 

        $data = $moyennes->paginate($perPage); 

        return [
            'pagination' => $data,
            'check_moyenne_generale' => $check_moyenne_generale,
            'code_formation' => $code_formation
        ];
    }
 
    public function getListeNotesBrutes(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getListeNotesBrutes() method.
        if ($data->code_module == 'N/A') {
            # code...
             $notes = Moodlenotes::join("moodlemodules","moodlemodules.id","=","moodlenotes.moodlemodule_id")
            ->join("moodletypeexamens","moodletypeexamens.id","=","moodlenotes.moodletypeexamen_id")
            ->where('moodlemodules.code_formation',$data->code_formation)
            ->where('moodlenotes.reference_candidat',$data->email_candidat)
            //->where("moodlemodules.code",$data->code_module) 
            ->select('moodlemodules.code as code_module','moodletypeexamens.code as code_typeexamen','moodlenotes.note','moodlenotes.reference_candidat','moodlenotes.id')->get();
            
            $candidat = Tmpmoodlecandidats::whereEmail($data->email_candidat)->first();
            //Log::info('liste note  ', ['line' => $notes]); 
            $response["notes"] = $notes;
            $response["id_candidat"] = $candidat->id; 
            $response["code_formation"] = $data->code_formation; 

        return  ResponseHandler::success("ok",$response);
        } else {
            # code...
             $notes = Moodlenotes::join("moodlemodules","moodlemodules.id","=","moodlenotes.moodlemodule_id")
            ->join("moodletypeexamens","moodletypeexamens.id","=","moodlenotes.moodletypeexamen_id")
            ->where('moodlenotes.reference_candidat',$data->email_candidat) 
            ->where('moodlemodules.code_formation',$data->code_formation) 
            ->where("moodlemodules.code",$data->code_module) 
            ->select('moodlemodules.code as code_module','moodletypeexamens.code as code_typeexamen','moodlenotes.note','moodlenotes.reference_candidat','moodlenotes.id')->get();
            $candidat = Tmpmoodlecandidats::whereEmail($data->email_candidat)->first();
            //Log::info('liste note  ', ['line' => $notes]); 
            $response["notes"] = $notes;
            $response["id_candidat"] = $candidat->id; 
            $response["code_formation"] = $data->code_formation; 

        return  ResponseHandler::success("ok",$response);
        }
        
       
    }

     public function doCalculMg(String $code_formation): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getListeNotesBrutes() method.
       
        # code... 
        Log::info('calcul moyenne begin ', ['request' => $code_formation]); 
        // call service calcul moyenne generale
        $calculMg = $this->genarateurCodeService->startCalculMg($code_formation);
        Log::info('calcul moyenne result ', ['request' => $calculMg]); 

        if (isset($calculMg['statut']) && $calculMg['statut'] == true) {
            # code...
            $response["statut"] = $calculMg['statut']; 
            $response["code_formation"] = $code_formation; 
            $response["message"] = $calculMg['message']; 
        } else {
            # code...
            $response["statut"] = false; 
            $response["code_formation"] = $code_formation; 
            $response["message"] = "Echec lors de calcul de la moyenne générale"; 
        }

        return  ResponseHandler::success("ok",$response);
    }

    public function getRegistreNotesCandidats(Request $request, string $code_formation)
    {
        // TODO: Implement getRegistreNotesCandidats() method.

        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'moyenne');
        $sortOrder = $request->input('sort_order', 'desc');

        $subQuery = DB::table('moodlemoyennegenerales')
            ->join('tmpmoodlecandidats', 'tmpmoodlecandidats.email', '=', 'moodlemoyennegenerales.reference_candidat')
            ->select(
                'tmpmoodlecandidats.id as id_candidat',
                'moodlemoyennegenerales.annee_academique',
                'moodlemoyennegenerales.code_formation',
                'moodlemoyennegenerales.moyenne',
                'tmpmoodlecandidats.prenoms',
                'tmpmoodlecandidats.nom',
                'tmpmoodlecandidats.email',

                DB::raw("EXISTS (
                    SELECT 1 
                    FROM moodlemoyennes 
                    WHERE moodlemoyennes.code_formation = moodlemoyennegenerales.code_formation 
                    AND moodlemoyennes.reference_candidat = moodlemoyennegenerales.reference_candidat 
                    AND moodlemoyennes.note_totale <= 7
                ) AS check_ajourne"),

                DB::raw("CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM moodlemoyennes
                        WHERE moodlemoyennes.code_formation = moodlemoyennegenerales.code_formation
                        AND moodlemoyennes.reference_candidat = moodlemoyennegenerales.reference_candidat
                        AND moodlemoyennes.note_totale <= 7
                    ) THEN 'Refusé'
                    WHEN moodlemoyennegenerales.moyenne < 12 THEN 'Refusé'
                    ELSE 'Admis'
                END AS decision"),

                DB::raw("CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM moodlemoyennes
                        WHERE moodlemoyennes.code_formation = moodlemoyennegenerales.code_formation
                        AND moodlemoyennes.reference_candidat = moodlemoyennegenerales.reference_candidat
                        AND moodlemoyennes.note_totale <= 7
                    ) THEN 'N/A'
                    WHEN moodlemoyennegenerales.moyenne < 12 THEN 'N/A'
                    WHEN moodlemoyennegenerales.moyenne >= 12 AND moodlemoyennegenerales.moyenne < 14 THEN 'ASSEZ BIEN'
                    WHEN moodlemoyennegenerales.moyenne >= 14 AND moodlemoyennegenerales.moyenne < 16 THEN 'BIEN'
                    ELSE 'TRÈS BIEN'
                END AS mention"),

                DB::raw("CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM moodlemoyennes
                        WHERE moodlemoyennes.code_formation = moodlemoyennegenerales.code_formation
                        AND moodlemoyennes.reference_candidat = moodlemoyennegenerales.reference_candidat
                        AND moodlemoyennes.note_totale <= 7
                    ) THEN 0
                    WHEN moodlemoyennegenerales.moyenne < 12 THEN 0
                    ELSE 1
                END AS printdp")
            )
            ->where('moodlemoyennegenerales.code_formation', $code_formation);

        $moyennes = DB::query()->fromSub($subQuery, 'resultats');

        if ($search) {
            $search = strtolower($search);

            $moyennes->where(function ($q) use ($search) {
                $q->whereRaw("LOWER(resultats.nom) LIKE ?", ["%$search%"])
                ->orWhereRaw("LOWER(resultats.prenoms) LIKE ?", ["%$search%"])
                ->orWhereRaw("LOWER(resultats.email) LIKE ?", ["%$search%"])
                ->orWhereRaw("CAST(resultats.moyenne AS CHAR) LIKE ?", ["%$search%"])
                ->orWhereRaw("LOWER(resultats.decision) LIKE ?", ["%$search%"])
                ->orWhereRaw("LOWER(resultats.mention) LIKE ?", ["%$search%"]);
            });
        }

        if (in_array($sortField, ['nom', 'prenoms', 'email', 'decision', 'mention'])) {
            $moyennes->orderBy("resultats.$sortField", $sortOrder);
        } else {
            $moyennes->orderBy("resultats.moyenne", $sortOrder);
        }

        $data = $moyennes->paginate($perPage);

        return [
            'pagination' => $data,
            'code_formation' => $code_formation
        ];
    }


    public function infoConsultationDiplome($code_user, $code_formation) : \Illuminate\Http\JsonResponse
    {
        $user = User::whereCodeUser($code_user)->first();
        if ($user) {
            # code...
            // Récupérer le participant avec ses moyennes 
            /*
            'moodlemoyennegenerales.annee_academique', 
            'moodlemoyennegenerales.moyenne', 
            ,'tmpmoodlecandidats.email','tmpmoodlecandidats.ddn',
            'tmpmoodlecandidats.lieu_naissance',
            */
        $participant = DB::table('moodlemoyennegenerales') 
            ->join('formations', 'formations.code_formation', '=', 'moodlemoyennegenerales.code_formation')
            ->join('formations_dscs', 'formations_dscs.formation_id', '=', 'formations.id')
            ->join('tmpmoodlecandidats', 'tmpmoodlecandidats.email', '=', 'moodlemoyennegenerales.reference_candidat')
            ->leftjoin('users', 'users.email', '=', 'tmpmoodlecandidats.email') 
            ->select('moodlemoyennegenerales.code_formation', 'tmpmoodlecandidats.prenoms', 'tmpmoodlecandidats.nom','users.code_user','formations.theme','formations.code_formation','formations_dscs.date_remise_diplomes','formations.lieu')
            ->where('moodlemoyennegenerales.code_formation', $code_formation) 
            ->where('moodlemoyennegenerales.reference_candidat', $user->email)
            ->orderBy('moodlemoyennegenerales.moyenne', 'DESC')
            ->first();  

            if ($participant) {
                # code...
                $data = ['info' => $participant];
                return  ResponseHandler::success("ok",$data);
            } else {
                # code...
                return  ResponseHandler::success("non ok",null, 302);
            }
            

        } else {
            # code...
        return  ResponseHandler::success("non ok",null, 302);
        }  

    }
}
