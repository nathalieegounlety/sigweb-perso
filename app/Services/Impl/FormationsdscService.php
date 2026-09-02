<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Mail\ValidationDSC;
use App\Models\Auditeurs;
use App\Models\Formations;
use App\Models\FormationsDsc;
use App\Models\Historiquetraitementdossiers;
use App\Models\Inscriptions;
use App\Models\InscriptionsDsc;
use App\Services\contracts\FormationsdscInterface;
use App\Services\util\GenarateurCodeService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FormationsdscService implements FormationsdscInterface
{
    private GenarateurCodeService $genarateurCodeService;

    public function __construct(GenarateurCodeService $genarateurCodeService)
    {
        $this->genarateurCodeService = $genarateurCodeService;
    }
    public function saveFormationdsc(Request $data): \Illuminate\Http\JsonResponse
    {
        $formation = Formations::whereCodeFormation($data->code_formation)->first();
        $checkData = FormationsDsc::whereFormationId($formation->id)
            ->exists();
        if ($checkData) {
            return ResponseHandler::error("La données existe déjà", 409);
        }

        $file = $data->file('visuel_modalite');
        $path = null;
        if (!empty($file)) {
            $allowedfileExtension = ['pdf','PDF'];
            $extension_document = $file->getClientOriginalExtension();
            $check = in_array($extension_document, $allowedfileExtension);
            if($check) {
                $path =  "modalite_document_".date('Ymd-His').'.'.$extension_document;
                $file->storeAs('modalites', $path,'public');
            }else {
                return ResponseHandler::error("Echec de l'enregistrement. Format du document interdit", 409);
            }
        }

        $user = auth('api')->user();
        $user_id = $user->id;
        $formationdsc = new FormationsDSC();
        $formationdsc->setAttribute('formation_id', $formation->id);
        $formationdsc->setAttribute('modalites_path', $path);
        $formationdsc->setAttribute('duree', $data->get('duree'));
        $formationdsc->setAttribute('date_limite_paiement_inscription', $data->get('date_paiement'));
        $formationdsc->setAttribute('date_limite_inscription', $data->get('date_inscription'));
        $formationdsc->setAttribute('date_remise_diplomes', $data->get('date_diplome'));
        $formationdsc->setAttribute('created_by', $user_id);
        $formationdsc->setAttribute('updated_by', $user_id);
        $formationdsc->save();
        return ResponseHandler::success("Création efféctuée");
    }

    public function getFormationDsc(int $id)
    {
        $data = new FormationsDsc();
        return ResponseHandler::success("ok", $data);
    }

    public function getFormationsdsc(Request $request): LengthAwarePaginator
    {
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Filtres spécifiques
        $filterCodeFormation = $request->input('filter_code_formation');
        $filterDuree = $request->input('filter_duree');
        $filterDateLimitePaiement = $request->input('filter_date_limite_paiement_inscription');
        $filterDateLimiteInscription = $request->input('filter_date_limite_inscription');
        $filterDateRemiseDiplome = $request->input('filter_date_remise_diplomes');
        $filterUpdatedAt = $request->input('filter_updated_at');
        $filterAuthor = $request->input('filter_author');

        $query = FormationsDsc::with('getUserUpdated:id,name,email,code_user')
            ->join('formations','formations.id','=','formations_dscs.formation_id')
            ->select('formations_dscs.id', 'formations_dscs.duree','formations_dscs.date_limite_paiement_inscription','formations_dscs.date_limite_inscription',
                'formations_dscs.updated_at','formations_dscs.date_remise_diplomes','formations.code_formation','formations_dscs.formation_id',
                'formations_dscs.updated_by','formations_dscs.modalites_path','formations.theme');

        // On fait la jointure
        $needsUserJoin = $search || $filterAuthor || $sortField === 'get_user_updated.name';

        if ($needsUserJoin) {
            $query->leftJoin('users', 'formations_dscs.updated_by', '=', 'users.id');
        }

        // Recherche globale sur plusieurs champs
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->Where('formations.code_formation', 'like', "%{$search}%")
                    ->orWhere('formations_dscs.duree', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%"); // Recherche sur auteur
            });
        }

        // Filtres par colonne
        if ($filterDateLimiteInscription) {
            $query->where('formations_dscs.date_limite_inscription', 'like', "%{$filterDateLimiteInscription}%");
        }
        if ($filterDateLimitePaiement) {
            $query->where('formations_dscs.date_limite_paiement_inscription', 'like', "%{$filterDateLimitePaiement}%");
        }
        if ($filterDateRemiseDiplome) {
            $query->where('formations_dscs.date_remise_diplomes', 'like', "%{$filterDateRemiseDiplome}%");
        }
        if ($filterUpdatedAt) {
            $query->whereDate('formations_dscs.updated_at', $filterUpdatedAt);
        }
        if ($filterAuthor) {
            $query->where('users.name', 'like', "%{$filterAuthor}%");
        }

        // Tri
        if ($sortField === 'get_user_updated.name') {
            $query->orderBy('users.name', $sortOrder);
        } else {
            $query->orderBy("formations_dscs.{$sortField}", $sortOrder);
        }

        $query->orderBy($sortField, $sortOrder);

        return $query->paginate($perPage);
    }

    public function update(Request $data): \Illuminate\Http\JsonResponse
    {
        $user = auth('api')->user();
        $user_id = $user->id;
        $dataUpdate = FormationsDsc::find($data->id);

        if (!$dataUpdate) {
            return ResponseHandler::error("Données à modifier introuvable",404);
        }
        // check existing data
        $formation = Formations::whereCodeFormation($data->code_formation)->first();
        $checkData = FormationsDsc::whereFormationId($formation->id)
            ->where("id", "!=", $dataUpdate->id)->exists();

        if ($checkData) {
            return ResponseHandler::error("La données existe déjà");
        } else {
            // process update
            if ($data->hasFile('visuel_modalite')) {
                $file = $data->file('visuel_modalite');
                if (!empty($file)) {
                    $allowedfileExtension = ['pdf', 'PDF'];
                    $extension_document = $file->getClientOriginalExtension();
                    $check = in_array($extension_document, $allowedfileExtension);
                    if ($check) {
                        $path = "modalite_document_" . date('Ymd-His') . '.' . $extension_document;
                        $file->storeAs('modalites', $path, 'public');
                        $dataUpdate->setAttribute('modalites_path', $path);
                    } else {
                        return ResponseHandler::error("Echec de l'enregistrement. Format du document interdit", 409);
                    }
                }
            }

            $dataUpdate->setAttribute('duree', $data->get('duree'));
            $dataUpdate->setAttribute('date_limite_paiement_inscription', $data->get('date_paiement'));
            $dataUpdate->setAttribute('date_limite_inscription', $data->get('date_inscription'));
            $dataUpdate->setAttribute('date_remise_diplomes', $data->get('date_diplome'));
            $dataUpdate->setAttribute('updated_by', $user_id);
            $dataUpdate->update();
            return ResponseHandler::success("Modification effectuée avec succès");
        }

    }

    public function remove(int $id): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement remove() method.
        $dataDelete = FormationsDsc::find($id);
        if (!$dataDelete) {
            return ResponseHandler::error("Données à supprimer introuvable",404);
        }

        $delete = $dataDelete->delete();

        if ($delete) {
            return ResponseHandler::success("Suppression effectuée.");
        } else {
            return ResponseHandler::error("Echec lors de la suppression, vous ne pouvez pas surpprimer cette données : $dataDelete->duree.",401);
        }
    }

    public function objFormationDsc(): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement objFormationDsc() method.
        $data = Formations::where('is_formation_dsc', true)
            ->select([
                'code_formation as code',
                DB::raw("CONCAT(formations.code_formation, ' - ', formations.theme) AS name")
            ])
            ->get();
        return ResponseHandler::success("ok",$data);
    }

    

    public function getListeDossiersAttente(Request $request, string $code_formation)
    {
        /*
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');
        
        $filterNom = $request->input('filter_nom');
        $filterPrenoms = $request->input('filter_prenoms');
        $filterCreatedAt = $request->input('filter_created_at'); 
        

        $liste_inscrits = Inscriptions::leftjoin('auditeurs', 'inscriptions.email', '=', 'auditeurs.email')
            ->leftjoin('tarificationsformations', 'inscriptions.tarif_formation_id', '=', 'tarificationsformations.id')
            ->leftjoin('formations', 'inscriptions.code_formation', '=', 'formations.code_formation')
            ->leftjoin('grilletarifaires', 'tarificationsformations.id_grilletarifaire', '=', 'grilletarifaires.id')
            ->leftjoin("detailspaiements", function ($leftjoin) {
                $leftjoin->on('detailspaiements.email', '=', 'inscriptions.email');
                $leftjoin->on('detailspaiements.id_tarification_formation', '=', 'inscriptions.tarif_formation_id');
            })
            ->where('inscriptions.code_formation', $code_formation)
            ->where('inscriptions.is_delete', false) 
          ->where('inscriptions.traiter', false)
            ->select(['inscriptions.id','inscriptions.sync_zoom','inscriptions.code_inscription', 'inscriptions.created_at',
                'auditeurs.nom', 'auditeurs.prenoms', 'formations.fm_presentielle', 'formations.fm_visio', 'auditeurs.email',
                'auditeurs.telephone', 'grilletarifaires.type_tarif as mode', 'inscriptions.type_formation as mode','grilletarifaires.montant',
                'inscriptions.type_formation', 'detailspaiements.statut_paiement','detailspaiements.reference_paiement', 'auditeurs.id as id_auditeur',
                'inscriptions.is_formation_dsc', 'inscriptions.is_delete','inscriptions.code_formation','formations.is_formation_dsc'])
            ->selectRaw(
                '(EXISTS (
                SELECT 1 FROM auditeurs_pieces
                WHERE auditeurs_pieces.auditeur_id = auditeurs.id AND auditeurs_pieces.code_formation = ?
            )) as check_dossier', [$code_formation]
            )
            ->selectRaw(
          '(
            SELECT count(auditeurs_pieces.id) FROM auditeurs_pieces 
            WHERE auditeurs_pieces.auditeur_id = auditeurs.id AND auditeurs_pieces.code_formation = ?
          ) as nbre_dossier' , [$code_formation]
      );*/
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        $filterNom = $request->input('filter_nom');
        $filterPrenoms = $request->input('filter_prenoms');
        $filterCreatedAt = $request->input('filter_created_at');


        // AUDITEUR UNIQUE PAR EMAIL (évite doublons)
        $subAuditeur = DB::table('auditeurs as a')
            ->select([
                'a.email',
                'a.nom',
                'a.prenoms',
                'a.telephone',
                'a.id as id_auditeur'
            ])
            ->join(DB::raw('(
                SELECT email, MAX(id) as max_id
                FROM auditeurs
                GROUP BY email
            ) last'), function ($join) {
                $join->on('a.id', '=', 'last.max_id');
            });


        // QUERY PRINCIPALE
        $liste_inscrits = Inscriptions::query()

            ->leftJoin('formations', 'formations.code_formation', '=', 'inscriptions.code_formation')
            ->leftJoin('tarificationsformations', 'inscriptions.tarif_formation_id', '=', 'tarificationsformations.id')
            ->leftJoin('grilletarifaires', 'tarificationsformations.id_grilletarifaire', '=', 'grilletarifaires.id')

            // AUDITEUR UNIQUE
            ->leftJoinSub($subAuditeur, 'auditeurs', function ($join) {
                $join->on('inscriptions.email', '=', 'auditeurs.email');
            })

            ->where('inscriptions.code_formation', $code_formation)
            ->where('inscriptions.is_delete', false)
            ->where('inscriptions.traiter', false)

            ->select([
                'inscriptions.id',
                'inscriptions.sync_zoom',
                'inscriptions.code_inscription',
                'inscriptions.created_at',

                'auditeurs.nom',
                'auditeurs.prenoms',
                'auditeurs.email',
                'auditeurs.telephone',
                'auditeurs.id_auditeur',

                'formations.fm_presentielle',
                'formations.fm_visio',
                'formations.is_formation_dsc',

                'grilletarifaires.type_tarif as mode_tarif',
                'inscriptions.type_formation as mode',
                'grilletarifaires.montant',

                'inscriptions.is_formation_dsc',
                'inscriptions.is_delete',
                'inscriptions.code_formation', 
            ])

            //  CHECK dossier existence
            ->selectRaw("
                EXISTS (
                    SELECT 1
                    FROM auditeurs_pieces
                    WHERE auditeurs_pieces.auditeur_id = auditeurs.id_auditeur
                    AND auditeurs_pieces.code_formation = ?
                ) as check_dossier
            ", [$code_formation])

            //  nombre de pièces
            ->selectRaw("
                (
                    SELECT COUNT(*)
                    FROM auditeurs_pieces
                    WHERE auditeurs_pieces.auditeur_id = auditeurs.id_auditeur
                    AND auditeurs_pieces.code_formation = ?
                ) as nbre_dossier
            ", [$code_formation])

            //  paiement existe
            ->selectRaw("
                EXISTS (
                    SELECT 1
                    FROM detailspaiements dp
                    WHERE dp.email = inscriptions.email
                    AND dp.id_tarification_formation = inscriptions.tarif_formation_id
                ) as has_paiement
            ")

            //  dernier statut paiement
            ->selectRaw("
                (
                    SELECT dp.statut_paiement
                    FROM detailspaiements dp
                    WHERE dp.email = inscriptions.email
                    AND dp.id_tarification_formation = inscriptions.tarif_formation_id
                    ORDER BY dp.id DESC
                    LIMIT 1
                ) as statut_paiement
            ")

            //  dernière référence paiement
            ->selectRaw("
                (
                    SELECT dp.reference_paiement
                    FROM detailspaiements dp
                    WHERE dp.email = inscriptions.email
                    AND dp.id_tarification_formation = inscriptions.tarif_formation_id
                    ORDER BY dp.id DESC
                    LIMIT 1
                ) as reference_paiement
            ");


        // Recherche globale sur plusieurs champs
        if ($search) {
            $liste_inscrits->where(function ($q) use ($search) {
                $q->where('auditeurs.nom', 'like', "%{$search}%")
                    ->orWhere('auditeurs.prenoms', 'like', "%{$search}%")
                    ->orWhere('auditeurs.email', 'like', "%{$search}%");
            });
        }

        if ($filterNom) {
            $liste_inscrits->whereDate('auditeurs.nom', $filterNom);
        }

        if ($filterPrenoms) {
            $liste_inscrits->whereDate('auditeurs.prenoms', $filterPrenoms);
        }

        if ($filterCreatedAt) {
            $liste_inscrits->whereDate('inscriptions.created_at', $filterCreatedAt);
        }

        if ($sortField == 'nom' || $sortField == 'prenoms' || $sortField == 'email') {
            # code...
        $liste_inscrits->orderBy("auditeurs.{$sortField}", $sortOrder);

        }elseif ($sortField == 'mode') {
            # code... 
        $liste_inscrits->orderBy("grilletarifaires.type_tarif", $sortOrder);

        }elseif ($sortField == 'montant') {
            # code... 
        $liste_inscrits->orderBy("grilletarifaires.{$sortField}", $sortOrder);

        }elseif ($sortField == 'statut_paiement') {
            # code... 
        $liste_inscrits->orderBy("detailspaiements.{$sortField}", $sortOrder);

        } 
        elseif ($sortField == 'check_dossier') {

            $liste_inscrits->orderBy('check_dossier', $sortOrder);

        }
        else{
            
        $liste_inscrits->orderBy("inscriptions.{$sortField}", $sortOrder);

        }
 
        //$liste_inscrits->orderBy("inscriptions.{$sortField}", $sortOrder);

        $data = $liste_inscrits->paginate($perPage);
        $formation = Formations::where('code_formation', $code_formation)->first();

        return [
            'pagination' => $data,
            'is_dsc' => $formation->is_formation_dsc
        ];
    }

    

    public function validerDossiersCandidature(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement validerDossiersCandidature() method.

        $listeInscAuditeurs = json_decode($request->listeAuditeurs, true);
        $decision = $request->decision;
        $motif = $request->motif;
        $user = auth('api')->user();
        //Log::info($listeInscAuditeurs);
        if (!empty($listeInscAuditeurs)) {
            foreach ($listeInscAuditeurs as $key => $id) {
                $inscrit = Inscriptions::find($id);
                //Log::info($inscrit);
                if ($inscrit) { 
                     $auditeur = Auditeurs::where("email", trim($inscrit->email))->first();
                    if ($auditeur) {

                    
                        // Enregistrement des informations dans la table inscriptions_dscs si dossier validé
                        if ($decision == "VALIDEE") {
                            # code...
                            // Mise à jour de l'inscription existante
                            $updateInscrit = Inscriptions::find($inscrit->id);
                            $updateInscrit->setAttribute("traiter",true);
                            $updateInscrit->setAttribute("statut_dossier",trim($decision));
                            //$updateInscrit->setAttribute("valider", $request->statut === 'valider' ? 1 : 0);
                            $updateInscrit->setAttribute("traiter_par",$user->id);
                            $updateInscrit->setAttribute("observations", $motif);
                            $updateInscrit->update(); 
                            
                            // vérifier si la données n'existe pas
                            $checkData = InscriptionsDsc::where('code_inscription', $updateInscrit->code_inscription)
                            ->where('inscrits_id',$updateInscrit->id)
                            ->exists();
                            if(!$checkData){
                            $inscription_dsc = new InscriptionsDsc();
                            $inscription_dsc->inscrits_id = $updateInscrit->id;
                            $inscription_dsc->code_inscription = $updateInscrit->code_inscription;
                            $inscription_dsc->traiter_par = $user->id;
                            $inscription_dsc->updated_by = $user->id;
                            $inscription_dsc->save();

                              // enregistrement de l'historique
                        $historiqueTraitementDossier = new Historiquetraitementdossiers();
                        $historiqueTraitementDossier->setAttribute("inscrits_id",$updateInscrit->id);
                        $historiqueTraitementDossier->setAttribute("code_inscription",$inscrit->code_inscription);
                        $historiqueTraitementDossier->setAttribute("traiter_par",$user->id);
                        $historiqueTraitementDossier->setAttribute("message",trim($motif)); 
                        $historiqueTraitementDossier->setAttribute("statut_dossier",trim($decision));
                        $historiqueTraitementDossier->save();

                        // envoi de mail 
                            $contenu = [
                                'nom' => $auditeur->nom,
                                'prenoms' => $auditeur->prenoms,
                                'nom_prenoms' =>  $auditeur->nom . ' ' . $auditeur->prenoms,
                                'subject' => 'INFORMATION SUR VOTRE CANDIDATURE ' . $request->code_formation,
                                'email' => $auditeur->email,
                                'statut' => $decision,
                                'observations' => $motif,
                            ];
                            
                           
                            try {
                               Mail::to($inscrit->email)->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new ValidationDSC($contenu)); 

                            } catch (\Exception $e) { 
                            }

                            } 
                            
                        } elseif ($decision == "RELANCE") {
                            // Mise à jour de l'inscription existante
                            $updateInscrit = Inscriptions::find($inscrit->id);
                            $updateInscrit->setAttribute("nbre_relance",$updateInscrit->nbre_relance +1); 
                            $updateInscrit->update(); 

                              // enregistrement de l'historique
                        $historiqueTraitementDossier = new Historiquetraitementdossiers();
                        $historiqueTraitementDossier->setAttribute("inscrits_id",$updateInscrit->id);
                        $historiqueTraitementDossier->setAttribute("code_inscription",$inscrit->code_inscription);
                        $historiqueTraitementDossier->setAttribute("traiter_par",$user->id);
                        $historiqueTraitementDossier->setAttribute("message",trim($motif)); 
                        $historiqueTraitementDossier->setAttribute("statut_dossier",trim($decision));
                        $historiqueTraitementDossier->save();

                        // envoi de mail 
                            $contenu = [
                                'nom' => $auditeur->nom,
                                'prenoms' => $auditeur->prenoms,
                                'nom_prenoms' =>  $auditeur->nom . ' ' . $auditeur->prenoms,
                                'subject' => 'INFORMATION SUR VOTRE CANDIDATURE ' . $request->code_formation,
                                'email' => $auditeur->email,
                                'statut' => $decision,
                                'observations' => $motif,
                            ];
                            
                           
                            try {
                               Mail::to($inscrit->email)->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new ValidationDSC($contenu)); 

                            } catch (\Exception $e) { 
                            }

                        }else{
                            // Mise à jour de l'inscription existante
                            $updateInscrit = Inscriptions::find($inscrit->id);
                            $updateInscrit->setAttribute("traiter",true);
                            $updateInscrit->setAttribute("statut_dossier",trim($decision));
                            //$updateInscrit->setAttribute("valider", $request->statut === 'valider' ? 1 : 0);
                            $updateInscrit->setAttribute("traiter_par",$user->id);
                            $updateInscrit->setAttribute("observations", $motif);
                            $updateInscrit->update(); 

                              // enregistrement de l'historique
                        $historiqueTraitementDossier = new Historiquetraitementdossiers();
                        $historiqueTraitementDossier->setAttribute("inscrits_id",$updateInscrit->id);
                        $historiqueTraitementDossier->setAttribute("code_inscription",$inscrit->code_inscription);
                        $historiqueTraitementDossier->setAttribute("traiter_par",$user->id);
                        $historiqueTraitementDossier->setAttribute("message",trim($motif)); 
                        $historiqueTraitementDossier->setAttribute("statut_dossier",trim($decision));
                        $historiqueTraitementDossier->save();

                        // envoi de mail 
                            $contenu = [
                                'nom' => $auditeur->nom,
                                'prenoms' => $auditeur->prenoms,
                                'nom_prenoms' =>  $auditeur->nom . ' ' . $auditeur->prenoms,
                                'subject' => 'INFORMATION SUR VOTRE CANDIDATURE ' . $request->code_formation,
                                'email' => $auditeur->email,
                                'statut' => $decision,
                                'observations' => $motif,
                            ];
                            
                           
                            try {
                               Mail::to($inscrit->email)->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new ValidationDSC($contenu)); 

                            } catch (\Exception $e) { 
                            }

                        } 
                             
                    }
                }
            }
        }
        return  ResponseHandler::success('E-mail envoyé avec succès',['success'=> true, 'code_formation'=>$request->code_formation]);
    }
 

    public function getListeDossiersValides(Request $request, string $code_formation)
    {
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');
        
        $filterNom = $request->input('filter_nom');
        $filterPrenoms = $request->input('filter_prenoms');
        $filterCreatedAt = $request->input('filter_created_at');

        $subAuditeur = DB::table('auditeurs as a')
    ->select([
        'a.email',
        'a.nom',
        'a.prenoms',
        'a.telephone',
        'a.id as id_auditeur'
    ])
    ->join(DB::raw('(
        SELECT email, MAX(id) as max_id
        FROM auditeurs
        GROUP BY email
    ) last'), function ($join) {
        $join->on('a.id', '=', 'last.max_id');
    });
        

         $liste_inscrits = Inscriptions::query()

    ->leftjoin('tarificationsformations', 'inscriptions.tarif_formation_id', '=', 'tarificationsformations.id')
    ->leftjoin('formations', 'inscriptions.code_formation', '=', 'formations.code_formation')
    ->leftjoin('grilletarifaires', 'tarificationsformations.id_grilletarifaire', '=', 'grilletarifaires.id')

    //  auditeur unique (anti doublon email)
    ->leftJoinSub($subAuditeur, 'auditeurs', function ($join) {
        $join->on('inscriptions.email', '=', 'auditeurs.email');
    })
 

    ->where('inscriptions.code_formation', $code_formation)
    ->where('inscriptions.is_delete', false)
    ->where('inscriptions.traiter', true)
    ->where('inscriptions.statut_dossier', 'VALIDEE')

    ->select([
        'inscriptions.id',
        'inscriptions.sync_zoom',
        'inscriptions.code_inscription',
        'inscriptions.created_at',

        'auditeurs.nom',
        'auditeurs.prenoms',
        'auditeurs.email',
        'auditeurs.telephone',
        'auditeurs.id_auditeur',

        'formations.fm_presentielle',
        'formations.fm_visio',
        'formations.is_formation_dsc',

        // 
        'grilletarifaires.type_tarif as mode_tarif',
        'inscriptions.type_formation as mode',

        'grilletarifaires.montant',

        'inscriptions.type_formation',
        'inscriptions.observations',

        'inscriptions.is_formation_dsc',
        'inscriptions.is_delete',
        'inscriptions.code_formation',
    ])

    //  check dossier
    ->selectRaw("
        EXISTS (
            SELECT 1
            FROM auditeurs_pieces
            WHERE auditeurs_pieces.auditeur_id = auditeurs.id_auditeur
            AND auditeurs_pieces.code_formation = ?
        ) as check_dossier
    ", [$code_formation])

    //  nombre de pièces
    ->selectRaw("
        (
            SELECT COUNT(*)
            FROM auditeurs_pieces
            WHERE auditeurs_pieces.auditeur_id = auditeurs.id_auditeur
            AND auditeurs_pieces.code_formation = ?
        ) as nbre_dossier
    ", [$code_formation])

    //  paiement existe
    ->selectRaw("
        EXISTS (
            SELECT 1
            FROM detailspaiements dp
            WHERE dp.email = inscriptions.email
            AND dp.id_tarification_formation = inscriptions.tarif_formation_id
        ) as has_paiement
    ")

    //  dernier statut paiement
    ->selectRaw("
        (
            SELECT dp.statut_paiement
            FROM detailspaiements dp
            WHERE dp.email = inscriptions.email
            AND dp.id_tarification_formation = inscriptions.tarif_formation_id
            ORDER BY dp.id DESC
            LIMIT 1
        ) as statut_paiement
    ")

    //  dernière référence paiement
    ->selectRaw("
        (
            SELECT dp.reference_paiement
            FROM detailspaiements dp
            WHERE dp.email = inscriptions.email
            AND dp.id_tarification_formation = inscriptions.tarif_formation_id
            ORDER BY dp.id DESC
            LIMIT 1
        ) as reference_paiement
    ");

        // Recherche globale sur plusieurs champs
        if ($search) {
            $liste_inscrits->where(function ($q) use ($search) {
                $q->where('auditeurs.nom', 'like', "%{$search}%")
                    ->orWhere('auditeurs.prenoms', 'like', "%{$search}%")
                    ->orWhere('auditeurs.email', 'like', "%{$search}%");
            });
        }

        if ($filterNom) {
            $liste_inscrits->whereDate('auditeurs.nom', $filterNom);
        }

        if ($filterPrenoms) {
            $liste_inscrits->whereDate('auditeurs.prenoms', $filterPrenoms);
        }

        if ($filterCreatedAt) {
            $liste_inscrits->whereDate('inscriptions.created_at', $filterCreatedAt);
        }

        if ($sortField == 'nom' || $sortField == 'prenoms' || $sortField == 'email') {
            # code...
        $liste_inscrits->orderBy("auditeurs.{$sortField}", $sortOrder);

        }elseif ($sortField == 'mode') {
            # code... 
        $liste_inscrits->orderBy("grilletarifaires.type_tarif", $sortOrder);

        }elseif ($sortField == 'montant') {
            # code... 
        $liste_inscrits->orderBy("grilletarifaires.{$sortField}", $sortOrder);

        }elseif ($sortField == 'statut_paiement') {
            # code... 
        $liste_inscrits->orderBy("detailspaiements.{$sortField}", $sortOrder);

        } 
        
        elseif ($sortField == 'check_dossier') {

            $liste_inscrits->orderBy('check_dossier', $sortOrder);

        }
        else{
            
        $liste_inscrits->orderBy("inscriptions.{$sortField}", $sortOrder);

        }
 
        //$liste_inscrits->orderBy("inscriptions.{$sortField}", $sortOrder);

        $data = $liste_inscrits->paginate($perPage);
        $formation = Formations::where('code_formation', $code_formation)->first();

        return [
            'pagination' => $data,
            'is_dsc' => $formation->is_formation_dsc
        ];
    }

    public function getListeDossierSousreserve(Request $request, string $code_formation)
    {$perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');
        
        $filterNom = $request->input('filter_nom');
        $filterPrenoms = $request->input('filter_prenoms');
        $filterCreatedAt = $request->input('filter_created_at');
 
            // AUDITEUR UNIQUE PAR EMAIL (évite doublons)
            $subAuditeur = DB::table('auditeurs as a')
                ->select([
                    'a.email',
                    'a.nom',
                    'a.prenoms',
                    'a.telephone',
                    'a.id as id_auditeur'
                ])
                ->join(DB::raw('(
                    SELECT email, MAX(id) as max_id
                    FROM auditeurs
                    GROUP BY email
                ) last'), function ($join) {
                    $join->on('a.id', '=', 'last.max_id');
                });

        
            $liste_inscrits = Inscriptions::query()

    ->leftJoin('tarificationsformations', 'inscriptions.tarif_formation_id', '=', 'tarificationsformations.id')
    ->leftJoin('formations', 'inscriptions.code_formation', '=', 'formations.code_formation')
    ->leftJoin('grilletarifaires', 'tarificationsformations.id_grilletarifaire', '=', 'grilletarifaires.id')

    //  auditeur unique (anti doublon email)
    ->leftJoinSub($subAuditeur, 'auditeurs', function ($join) {
        $join->on('inscriptions.email', '=', 'auditeurs.email');
    })
 

    ->where('inscriptions.code_formation', $code_formation)
    ->where('inscriptions.is_delete', false)
    ->where('inscriptions.traiter', true)
    ->where('inscriptions.statut_dossier', 'SOUS RESERVE')

    ->select([
        'inscriptions.id',
        'inscriptions.sync_zoom',
        'inscriptions.code_inscription',
        'inscriptions.created_at',

        'auditeurs.nom',
        'auditeurs.prenoms',
        'auditeurs.email',
        'auditeurs.telephone',
        'auditeurs.id_auditeur',

        'formations.fm_presentielle',
        'formations.fm_visio',
        'formations.is_formation_dsc',
 
        'grilletarifaires.type_tarif as mode_tarif',
        'inscriptions.type_formation as mode',

        'grilletarifaires.montant',

        'inscriptions.type_formation',

        'inscriptions.is_formation_dsc',
        'inscriptions.is_delete',
        'inscriptions.code_formation',
    ])

    //  dossier existe
    ->selectRaw("
        EXISTS (
            SELECT 1
            FROM auditeurs_pieces
            WHERE auditeurs_pieces.auditeur_id = auditeurs.id_auditeur
            AND auditeurs_pieces.code_formation = ?
        ) as check_dossier
    ", [$code_formation])

    //  nombre de pièces
    ->selectRaw("
        (
            SELECT COUNT(*)
            FROM auditeurs_pieces
            WHERE auditeurs_pieces.auditeur_id = auditeurs.id_auditeur
            AND auditeurs_pieces.code_formation = ?
        ) as nbre_dossier
    ", [$code_formation])

    //  paiement existe (optionnel mais safe)
    ->selectRaw("
        EXISTS (
            SELECT 1
            FROM detailspaiements dp
            WHERE dp.email = inscriptions.email
            AND dp.id_tarification_formation = inscriptions.tarif_formation_id
        ) as has_paiement
    ")

    //  dernier statut paiement
    ->selectRaw("
        (
            SELECT dp.statut_paiement
            FROM detailspaiements dp
            WHERE dp.email = inscriptions.email
            AND dp.id_tarification_formation = inscriptions.tarif_formation_id
            ORDER BY dp.id DESC
            LIMIT 1
        ) as statut_paiement
    ")

    //  dernière référence paiement
    ->selectRaw("
        (
            SELECT dp.reference_paiement
            FROM detailspaiements dp
            WHERE dp.email = inscriptions.email
            AND dp.id_tarification_formation = inscriptions.tarif_formation_id
            ORDER BY dp.id DESC
            LIMIT 1
        ) as reference_paiement
    ");

        // Recherche globale sur plusieurs champs
        if ($search) {
            $liste_inscrits->where(function ($q) use ($search) {
                $q->where('auditeurs.nom', 'like', "%{$search}%")
                    ->orWhere('auditeurs.prenoms', 'like', "%{$search}%")
                    ->orWhere('auditeurs.email', 'like', "%{$search}%");
            });
        }

        if ($filterNom) {
            $liste_inscrits->whereDate('auditeurs.nom', $filterNom);
        }

        if ($filterPrenoms) {
            $liste_inscrits->whereDate('auditeurs.prenoms', $filterPrenoms);
        }

        if ($filterCreatedAt) {
            $liste_inscrits->whereDate('inscriptions.created_at', $filterCreatedAt);
        }

        if ($sortField == 'nom' || $sortField == 'prenoms' || $sortField == 'email') {
            # code...
        $liste_inscrits->orderBy("auditeurs.{$sortField}", $sortOrder);

        }elseif ($sortField == 'mode') {
            # code... 
        $liste_inscrits->orderBy("grilletarifaires.type_tarif", $sortOrder);

        }elseif ($sortField == 'montant') {
            # code... 
        $liste_inscrits->orderBy("grilletarifaires.{$sortField}", $sortOrder);

        }elseif ($sortField == 'statut_paiement') {
            # code... 
        $liste_inscrits->orderBy("detailspaiements.{$sortField}", $sortOrder);

        } 
        
        elseif ($sortField == 'check_dossier') {

            $liste_inscrits->orderBy('check_dossier', $sortOrder);

        }
        else{
            
        $liste_inscrits->orderBy("inscriptions.{$sortField}", $sortOrder);

        }
 
        //$liste_inscrits->orderBy("inscriptions.{$sortField}", $sortOrder);

        $data = $liste_inscrits->paginate($perPage);
        $formation = Formations::where('code_formation', $code_formation)->first();

        return [
            'pagination' => $data,
            'is_dsc' => $formation->is_formation_dsc
        ];
    }

    public function getListeDossiersRejetes(Request $request, string $code_formation)
    {$perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');
        
        $filterNom = $request->input('filter_nom');
        $filterPrenoms = $request->input('filter_prenoms');
        $filterCreatedAt = $request->input('filter_created_at');
        
        
            // AUDITEUR UNIQUE PAR EMAIL (évite doublons)
            $subAuditeur = DB::table('auditeurs as a')
                ->select([
                    'a.email',
                    'a.nom',
                    'a.prenoms',
                    'a.telephone',
                    'a.id as id_auditeur'
                ])
                ->join(DB::raw('(
                    SELECT email, MAX(id) as max_id
                    FROM auditeurs
                    GROUP BY email
                ) last'), function ($join) {
                    $join->on('a.id', '=', 'last.max_id');
                });
                    $liste_inscrits = Inscriptions::query()

                        ->leftJoin('tarificationsformations', 'inscriptions.tarif_formation_id', '=', 'tarificationsformations.id')
                        ->leftJoin('formations', 'inscriptions.code_formation', '=', 'formations.code_formation')
                        ->leftJoin('grilletarifaires', 'tarificationsformations.id_grilletarifaire', '=', 'grilletarifaires.id')

                        //  auditeur unique (anti doublon email)
                        ->leftJoinSub($subAuditeur, 'auditeurs', function ($join) {
                            $join->on('inscriptions.email', '=', 'auditeurs.email');
                        }) 

                        ->where('inscriptions.code_formation', $code_formation)
                        ->where('inscriptions.is_delete', false)
                        ->where('inscriptions.traiter', true)
                        ->where('inscriptions.statut_dossier', 'REJETEE')

                        ->select([
                            'inscriptions.id',
                            'inscriptions.sync_zoom',
                            'inscriptions.code_inscription',
                            'inscriptions.created_at',

                            'auditeurs.nom',
                            'auditeurs.prenoms',
                            'auditeurs.email',
                            'auditeurs.telephone',
                            'auditeurs.id_auditeur',

                            'formations.fm_presentielle',
                            'formations.fm_visio',
                            'formations.is_formation_dsc',

                            'grilletarifaires.type_tarif as mode_tarif',
                            'inscriptions.type_formation as mode',

                            'grilletarifaires.montant',

                            'inscriptions.type_formation',
                            'inscriptions.observations',

                            'inscriptions.is_formation_dsc',
                            'inscriptions.is_delete',
                            'inscriptions.code_formation',
                        ])

                        //  dossier check
                        ->selectRaw("
                            EXISTS (
                                SELECT 1
                                FROM auditeurs_pieces
                                WHERE auditeurs_pieces.auditeur_id = auditeurs.id_auditeur
                                AND auditeurs_pieces.code_formation = ?
                            ) as check_dossier
                        ", [$code_formation])

                        //  nombre de pièces
                        ->selectRaw("
                            (
                                SELECT COUNT(*)
                                FROM auditeurs_pieces
                                WHERE auditeurs_pieces.auditeur_id = auditeurs.id_auditeur
                                AND auditeurs_pieces.code_formation = ?
                            ) as nbre_dossier
                        ", [$code_formation])

                        //  paiement existe (optionnel, sans JOIN)
                        ->selectRaw("
                            EXISTS (
                                SELECT 1
                                FROM detailspaiements dp
                                WHERE dp.email = inscriptions.email
                                AND dp.id_tarification_formation = inscriptions.tarif_formation_id
                            ) as has_paiement
                        ")

                        //  dernier statut paiement
                        ->selectRaw("
                            (
                                SELECT dp.statut_paiement
                                FROM detailspaiements dp
                                WHERE dp.email = inscriptions.email
                                AND dp.id_tarification_formation = inscriptions.tarif_formation_id
                                ORDER BY dp.id DESC
                                LIMIT 1
                            ) as statut_paiement
                        ")

                        //  dernière référence paiement
                        ->selectRaw("
                            (
                                SELECT dp.reference_paiement
                                FROM detailspaiements dp
                                WHERE dp.email = inscriptions.email
                                AND dp.id_tarification_formation = inscriptions.tarif_formation_id
                                ORDER BY dp.id DESC
                                LIMIT 1
                            ) as reference_paiement
                        ");

        // Recherche globale sur plusieurs champs
        if ($search) {
            $liste_inscrits->where(function ($q) use ($search) {
                $q->where('auditeurs.nom', 'like', "%{$search}%")
                    ->orWhere('auditeurs.prenoms', 'like', "%{$search}%")
                    ->orWhere('auditeurs.email', 'like', "%{$search}%");
            });
        }

        if ($filterNom) {
            $liste_inscrits->whereDate('auditeurs.nom', $filterNom);
        }

        if ($filterPrenoms) {
            $liste_inscrits->whereDate('auditeurs.prenoms', $filterPrenoms);
        }

        if ($filterCreatedAt) {
            $liste_inscrits->whereDate('inscriptions.created_at', $filterCreatedAt);
        }

        if ($sortField == 'nom' || $sortField == 'prenoms' || $sortField == 'email') {
            # code...
        $liste_inscrits->orderBy("auditeurs.{$sortField}", $sortOrder);

        }elseif ($sortField == 'mode') {
            # code... 
        $liste_inscrits->orderBy("grilletarifaires.type_tarif", $sortOrder);

        }elseif ($sortField == 'montant') {
            # code... 
        $liste_inscrits->orderBy("grilletarifaires.{$sortField}", $sortOrder);

        }elseif ($sortField == 'statut_paiement') {
            # code... 
        $liste_inscrits->orderBy("detailspaiements.{$sortField}", $sortOrder);

        } 
        
        elseif ($sortField == 'check_dossier') {

            $liste_inscrits->orderBy('check_dossier', $sortOrder);

        }
        else{
            
        $liste_inscrits->orderBy("inscriptions.{$sortField}", $sortOrder);

        }
 
        //$liste_inscrits->orderBy("inscriptions.{$sortField}", $sortOrder);

        $data = $liste_inscrits->paginate($perPage);
        $formation = Formations::where('code_formation', $code_formation)->first();

        return [
            'pagination' => $data,
            'is_dsc' => $formation->is_formation_dsc
        ];
    }
}
