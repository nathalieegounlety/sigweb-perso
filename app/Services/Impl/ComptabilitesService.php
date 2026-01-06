<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Mail\PaiementValideDsc;
use App\Mail\Paiementvalider;
use App\Models\Auditeurs;
use App\Models\Detailpaiementouvrages;
use App\Models\Detailspaiements;
use App\Models\Modepaiements;
use App\Models\Paiements;
use App\Models\PaiementsTranche;
use App\Models\Reductions;
use App\Models\Tranches;
use App\Services\contracts\ComptabilitesInterface;
use App\Services\util\GenarateurCodeService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ComptabilitesService implements ComptabilitesInterface
{
    private GenarateurCodeService $genarateurCodeService;

    public function __construct(GenarateurCodeService $genarateurCodeService)
    {
        $this->genarateurCodeService = $genarateurCodeService;
    }

    public function getCommandes(Request $request) //: LengthAwarePaginator
    {
        $date_now = now()->subDays(90)->format('Y-m-d'); // Plus propre que date() + strtotime
        $get_date = now()->format('Y-m-d');

        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        $filterUpdatedAt = $request->input('filter_updated_at');

        // Construire la requête avec le Query Builder (pas de get() trop tôt)
        $query = DB::table('paiements')
            ->leftJoin('auditeurs', 'auditeurs.email', '=', 'paiements.email')
            ->leftJoin('modepaiements', 'modepaiements.id', '=', 'paiements.id_modepaiement')
            ->select(
                'auditeurs.nom',
                'auditeurs.prenoms',
                'paiements.telephone',
                'paiements.email',
                'paiements.reference',
                'paiements.montant_restant',
                'paiements.num_transaction',
                'paiements.updated_at',
                'paiements.type',
                'paiements.id',
                'modepaiements.libelle',
                'paiements.id_modepaiement'
            )
            ->whereNull('paiements.num_transaction')
            ->whereDate('paiements.created_at', '>=', $date_now)
            ->whereDate('paiements.created_at', '<=', $get_date)
            ->where('paiements.declasse', false)
            ->where('paiements.is_paiement_partiel', false)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('paiements_tranches')
                    ->whereRaw('paiements_tranches.reference LIKE CONCAT("%", paiements.reference, "%")');
            });

        // Recherche globale
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('auditeurs.nom', 'like', "%{$search}%")
                    ->orWhere('auditeurs.prenoms', 'like', "%{$search}%")
                    ->orWhere('paiements.telephone', 'like', "%{$search}%")
                    ->orWhere('paiements.reference', 'like', "%{$search}%")
                    ->orWhere('paiements.email', 'like', "%{$search}%")
                    ->orWhere('paiements.type', 'like', "%{$search}%");

                    if (is_numeric(str_replace([' ', ','], '', $search))) {
                        $numericValue = (float)str_replace([' ', ','], '', $search);
                        $q->orWhere('paiements.montant_restant', '=', $numericValue);
                    }
            });
        }

        // Filtre par date de mise à jour
        if ($filterUpdatedAt) {
            $query->whereDate('paiements.updated_at', $filterUpdatedAt);
        }

        // Tri dynamique (sécurise $sortField pour éviter injection SQL !)
        $allowedSortFields = ['id', 'reference', 'montant_restant', 'updated_at', 'type'];
        $sortField = in_array($sortField, $allowedSortFields) ? $sortField : 'id';
        $sortOrder = $sortOrder === 'asc' ? 'asc' : 'desc';

        $query->orderBy("paiements.{$sortField}", $sortOrder);

        // Pagination correcte
        $paginated = $query->paginate($perPage);

        // Calcul de la somme totale sur les éléments paginés (ou sur toute la requête filtrée)
        $somme_totale = $query->sum('paiements.montant_restant');

        // Ajouter la somme dans les meta de la pagination
        //$paginated->additional(['montant_total' => $somme_totale]);
        return [
            'pagination' => $paginated,
            'montant_total' => $somme_totale
        ];
        // return $paginated;
    }

    public function getPaiementsattente(Request $request) //: LengthAwarePaginator
    {
        // TODO: Implement getPaiementsattente() method.
        $perPage   = $request->input('per_page', 50);
        $search    = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');
        $filterDate = $request->input('filter_date');

        /** PAIEMENTS CLASSIQUES */
        $classiques = DB::table('paiements')
            ->leftJoin('auditeurs', 'auditeurs.email', '=', 'paiements.email')
            ->where('statut_traitement', 'ATTENTE')
            ->where('paiements.montant_restant', '!=', 0)
            ->where('paiements.type', '!=', 'dsc')
            ->where('is_paiement_partiel', false)
            ->whereNotNull('num_transaction')
            ->selectRaw("
            paiements.id,
            auditeurs.nom,
            auditeurs.prenoms,
            paiements.reference,
            paiements.montant_restant as montant,
            paiements.num_transaction,
            paiements.type,
            paiements.updated_at as date,
            paiements.reference as action_ref,
            'classique' as route_type
        ");

        /** PAIEMENTS PARTIELS */
        $partiels = DB::table('detailspaiements')
            ->leftJoin('auditeurs', 'auditeurs.email', '=', 'detailspaiements.email')
            ->where('detailspaiements.statut_paiement', 'PAYE')
            ->where('detailspaiements.statut_traitement', 'ATTENTE')
            ->where('detailspaiements.is_paiement_partiel', true)
            ->selectRaw("
            detailspaiements.id,
            auditeurs.nom,
            auditeurs.prenoms,
            detailspaiements.reference_paiement as reference,
            0 as montant,
            detailspaiements.reference_paiement_partiel as num_transaction,
            'FORMATION PARTIEL' as type,
            detailspaiements.updated_at as date,
            detailspaiements.id as action_ref,
            'partiel' as route_type
        ");

        /** PAIEMENTS DSC (TRANCHES) */
        $dsc = DB::table('paiements_tranches')
            ->leftJoin('detailspaiements', 'detailspaiements.id', '=', 'paiements_tranches.details_paiements_id')
            ->leftJoin('auditeurs', 'auditeurs.email', '=', 'detailspaiements.email')
            ->where('paiements_tranches.statut_traitement', 'ATTENTE')
            ->whereNotNull('paiements_tranches.num_transaction')
            ->groupBy('paiements_tranches.reference')
            ->selectRaw("
        MIN(paiements_tranches.id) as id,
        MAX(auditeurs.nom) as nom,
        MAX(auditeurs.prenoms) as prenoms,
        paiements_tranches.reference,
        SUM(paiements_tranches.montant_paye) as montant,
        MAX(paiements_tranches.num_transaction) as num_transaction,
        'DSC' as type,
        MAX(paiements_tranches.updated_at) as date,
        paiements_tranches.reference as action_ref,
        'dsc' as route_type
    ");

        /** UNION GLOBAL */
        $unionQuery = $classiques
            ->unionAll($partiels)
            ->unionAll($dsc);

        /** PAGINATION */
        $query = DB::query()->fromSub($unionQuery, 't');
            //->orderBy('date', 'desc')
            //->paginate($perPage);

        /* ==============================
     |  SEARCH GLOBAL
     ============================== */
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('t.nom', 'like', "%{$search}%")
                    ->orWhere('t.prenoms', 'like', "%{$search}%")
                    ->orWhere('t.reference', 'like', "%{$search}%")
                    ->orWhere('t.num_transaction', 'like', "%{$search}%")
                    ->orWhere('t.type', 'like', "%{$search}%");

                $clean = str_replace([' ', ','], '', $search);
                if (is_numeric($clean)) {
                    $q->orWhere('t.montant', (float) $clean);
                }
            });
        }

        if ($filterDate) {
            $query->whereDate('t.date', $filterDate);
        }


        $allowedSortFields = [
            'id',
            'nom',
            'prenoms',
            'reference',
            'montant',
            'num_transaction',
            'type',
            'date'
        ];

        $sortField = in_array($sortField, $allowedSortFields) ? $sortField : 'date';
        $sortOrder = $sortOrder === 'asc' ? 'asc' : 'desc';

        $query->orderBy("t.$sortField", $sortOrder);


        /* ==============================
         |  PAGINATION
         ============================== */
        $paiements = $query->paginate($perPage);

        /** TOTAL GLOBAL */
        $total = (clone $query)->sum('montant'); // collect($paiements->items())->sum('montant');

        return [
            'pagination' => $paiements,
            'montant_total' => $total
        ];


    }

    public function getPaiementsvalides(Request $request): LengthAwarePaginator
    {
        // TODO: Implement getPaiementsvalides() method.
    }

    public function getDetailsCommandeFormation(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getDetailsCommandeFormation() method.
        $details_commande = DB::table('detailspaiements')
            ->join('paiements', 'paiements.reference', '=', 'detailspaiements.reference_paiement')
            ->leftjoin('reductions', 'reductions.id', '=', 'detailspaiements.id_reduction')
            ->join('tarificationsformations', 'tarificationsformations.id', '=', 'detailspaiements.id_tarification_formation')
            ->join('grilletarifaires', 'tarificationsformations.id_grilletarifaire', '=', 'grilletarifaires.id')
            ->join('formations', 'formations.code_formation', '=', 'tarificationsformations.code_formation')
            ->select('detailspaiements.have_reduction_dsc','detailspaiements.montant_restant','formations.code_formation', 'formations.theme', 'formations.date_debut','grilletarifaires.montant','reductions.pourcentage','detailspaiements.preuve_path')
            ->where('detailspaiements.reference_paiement',$request->get('reference_paiement'))
            ->orderBy('detailspaiements.id', 'DESC')
            ->get();

        return  ResponseHandler::success("ok",$details_commande);
    }

    public function getDetailsCommandeOuvrage(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getDetailsCommandeOuvrage() method.
        $paiement = Paiements::whereReference($request->get('reference_paiement'))->first();
        $details_commande = Detailpaiementouvrages::join('ouvrages','ouvrages.id','=','detailpaiementouvrages.ouvrage_id')
        ->where("paiementouvrage_id",$paiement->id)
            ->get(['detailpaiementouvrages.id','detailpaiementouvrages.reference','detailpaiementouvrages.montant','ouvrages.libelle']);

        return  ResponseHandler::success("ok",$details_commande);
    }

    public function getDetailsCommandeDsc(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getDetailsCommandeDsc() method.
        $details_commande = DB::table('detailspaiements')
            ->join('paiements', 'paiements.reference', '=', 'detailspaiements.reference_paiement')
            ->leftjoin('reductions', 'reductions.id', '=', 'detailspaiements.id_reduction')
            ->join('tarificationsformations', 'tarificationsformations.id', '=', 'detailspaiements.id_tarification_formation')
            ->join('grilletarifaires', 'tarificationsformations.id_grilletarifaire', '=', 'grilletarifaires.id')
            ->join('formations', 'formations.code_formation', '=', 'tarificationsformations.code_formation')
            ->select('formations.code_formation', 'formations.theme', 'formations.date_debut','grilletarifaires.type_tarif','grilletarifaires.montant','detailspaiements.montant_restant','detailspaiements.have_reduction_dsc','reductions.pourcentage','detailspaiements.preuve_path')
            ->where('detailspaiements.reference_paiement',$request->get('reference_paiement'))
            ->orderBy('detailspaiements.id', 'DESC')
            ->get();

        return  ResponseHandler::success("ok",$details_commande);
    }

    public function storeDeplacerCommandeFormation(Request $request): \Illuminate\Http\JsonResponse
    {
        // update statut paiement
        $getPaiement = Paiements::where("reference",$request->reference)->get()->first();
        $paiement = Paiements::find($getPaiement->id);
        $user = auth('api')->user();

        if ($paiement->montant_total == 0) {
            # code...
            if ($request->statut_traitement == "VALIDER") {
                # code...
                $montant_paye = $paiement->montant_paye + $paiement->montant_restant;
                $montant_restant = $paiement->montant_total - $montant_paye;

                $paiement->setAttribute("statut_paiement","PAYE");
                $paiement->setAttribute("commentaire",$request->commentaire);
                $paiement->setAttribute("statut_traitement",trim($request->statut_traitement));
                $paiement->setAttribute('agent_traiteur', $user->id);
                $paiement->setAttribute('date_traitement', new \DateTime());
                $paiement->setAttribute("montant_paye",$montant_paye);
                $paiement->setAttribute("montant_restant",$montant_restant);
                $paiement->update();

                // send mail paiement validé
                // update table details paiement
                $details_paiement = Detailspaiements::where('reference_paiement',$request->reference)->get();

                foreach($details_paiement as $detail){
                    $upDetail = Detailspaiements::find($detail->id);
                    $upDetail->setAttribute('statut_traitement',trim($request->statut_traitement));
                    $upDetail->setAttribute('statut_paiement', "PAYE");
                    $upDetail->setAttribute("montant_paye",$upDetail->montant_restant);
                    $upDetail->setAttribute("montant_restant",0);
                    $upDetail->update();
                }

            } else {
                # code...
                $paiement->setAttribute("statut_paiement","EN ATTENTE");
                $paiement->setAttribute("statut_traitement","ATTENTE");
                $paiement->setAttribute("commentaire",$request->commentaire);
                $paiement->update();
            }

            return  ResponseHandler::success("Commande déplacée avec succès");

        }
        //######################################## montant supérieur à 0 ##############################################################################

        if ($request->statut_traitement == "VALIDER") {

            // vérifier si les champs mode_paiement sont disponible
            if(!empty($request->modepaiement_id)){

                $montant_paye = $paiement->montant_paye + $paiement->montant_restant;
                $montant_restant = $paiement->montant_total - $montant_paye;

                # code...
                $paiement->setAttribute("num_transaction",trim($request->num_transaction));
                $paiement->setAttribute("statut_transaction",trim($request->num_transaction));
                $paiement->setAttribute("id_modepaiement",$request->modepaiement_id);
                $paiement->setAttribute("statut_paiement","PAYE");
                $paiement->setAttribute("statut_traitement",trim($request->statut_traitement));
                $paiement->setAttribute('agent_traiteur', $user->id);
                $paiement->setAttribute('date_traitement', new \DateTime());
                $paiement->setAttribute("montant_paye",$montant_paye);
                $paiement->setAttribute("montant_restant",$montant_restant);
                $paiement->update();

                // send mail paiement validé
                // update table details paiement
                $details_paiement = Detailspaiements::where('reference_paiement',$request->reference)->get();

                foreach($details_paiement as $detail){
                    $upDetail = Detailspaiements::find($detail->id);
                    $upDetail->setAttribute('statut_traitement',trim($request->statut_traitement));
                    $upDetail->setAttribute('statut_paiement', "PAYE");
                    $upDetail->setAttribute("montant_paye",$upDetail->montant_restant);
                    $upDetail->setAttribute("montant_restant",0);
                    $upDetail->update();
                }
                $auditeur = Auditeurs::where("email",$paiement->email)->first();
                // envoie de mail pour la reférence de la facture
                $modepaiement = Modepaiements::find($request->modepaiement_id);
                $contenu = [
                    'nom' => $auditeur->nom ,
                    'prenoms' => $auditeur->prenoms,
                    'reference_paiement' => $request->reference,
                    'subject' => 'TRAITEMENT DE VOTRE PAIEMENT',
                    'commentaire' => "Paiement validé",
                    'email'=>$paiement->email,
                    'mode_paiement'=>$modepaiement->libelle,
                    'montant'=>$paiement->montant_total,
                    'type'=>$paiement->type,
                    'url_recu' => $this->genarateurCodeService->generateUrlSigned('paiements.recu',['reference' => $request->reference])
                ];

                try {
                    # code...
                    Mail::to(trim($paiement->email))->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new Paiementvalider($contenu));

                } catch (\Exception $e) {
                    //throw $th;
                }
            }else{
                // le champ mode de paiement est obligatoire
                return  ResponseHandler::error("Erreur : le champ mode de paiement est obligatoire.");
            }



        } else {

            if(!empty($request->reduction_id)){

            }else{

                $paiement->setAttribute("num_transaction",$request->num_transaction);
                $paiement->setAttribute("statut_transaction",$request->num_transaction);
            }
            $paiement->setAttribute("id_modepaiement",$request->modepaiement_id);
            $paiement->setAttribute("statut_paiement","EN ATTENTE");
            $paiement->setAttribute("statut_traitement","ATTENTE");
            $paiement->update();

            // application réduction

            //################# mise à jour reduction #################
            // mise à jour tarif formation
            if(!empty($request->reduction_id)){

                if($paiement->montant_total > 0){
                    // check details paiements

                    $chec_details_paiements = Detailspaiements::where("reference_paiement",$paiement->reference)
                        ->exists();

                    if($chec_details_paiements){
                        $detail_paiement = Detailspaiements::where("reference_paiement",$paiement->reference)
                            ->get();

                        # code...
                        $update_montant_paiement = $paiement->montant_total ;
                        $reduction = Reductions::find($request->reduction_id);
                        // application reduction au montant existant
                        $montant_paiement_reduction = $update_montant_paiement - ($update_montant_paiement*($reduction->pourcentage/100));

                        // update table details paiement
                        foreach ($detail_paiement as $details) {
                            # code...
                            $get_detail = Detailspaiements::find($details->id);
                            $get_detail->setAttribute("id_reduction",$request->reduction_id);
                            $get_detail->update();
                        }
                        // update table paiement
                        $paiement->setAttribute("montant_total",$montant_paiement_reduction);
                        $paiement->update();

                    }
                }
            }
            // ################# end mise à jour ####################
        }

        return  ResponseHandler::success("Commande déplacée avec succès");
    }

    public function storeDeplacerCommandeDsc(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement storeDeplacerCommandeDsc() method.
        // initialisé la vraiable permettant de savoir si le montant payé couvre une partie des frais de scolarité ou bien entierement
        $user = auth('api')->user();
        $have_frais_scolarite = false;
        $solde_frais_scolarite = false;
        $frais_scolarite_paye = $request->montant_total - $request->get_frais_inscription;
        $frais_inscription_paye = $request->get_frais_inscription;
        $montant_attendu = $request->get_frais_inscription + $request->get_frais_scolarite;
        $reference_paiement_partiel = "";
        // vérifier si le montant payé est supérieur ou égale au frais d'inscription
        if ($request->montant_total < $request->get_frais_inscription) {
            # code... alert
            return  ResponseHandler::error("Le montant payé ".$request->montant_total." ne couvre pas les frais d'inscription qui s'élève à ".$request->get_frais_inscription);

        }
        //################# vérification des conditions côté serveur ###########
        if ($request->montant_total != "" && $request->montant_total > 0) {
            $montant_paye = $request->montant_total;
            if ($montant_paye > $request->get_frais_inscription) {
                $diff_montant = $montant_paye - $request->get_frais_inscription;
                if ($request->get_reduction == 1) { // tranche unique
                    if ($diff_montant > $request->get_frais_scolarite) {
                        // alert attention le montant payé est supérieur au montant attendu
                        return  ResponseHandler::error("Attention le montant payé $montant_paye est supérieur au montant attendu $montant_attendu");

                    } else if($diff_montant == $request->get_frais_scolarite) {
                        // ok pour paiement
                        $have_frais_scolarite = true;
                        $solde_frais_scolarite = true;
                    }else{
                        // alert vous devez solder les frais de scolarité
                        return  ResponseHandler::error("Vous devez solder les frais de scolarité");

                    }
                } else { // double tranche
                    if ($diff_montant > $request->get_frais_scolarite) {
                        // alert attention le montant payé est supérieur au montant attendu
                        // disable btn enregistrer
                        return  ResponseHandler::error("Attention le montant payé $montant_paye est supérieur au montant attendu $montant_attendu");

                    } else if($diff_montant == $request->get_frais_scolarite) {
                        // ok pour paiement

                        $have_frais_scolarite = true;
                        $solde_frais_scolarite = true;

                    }else if($diff_montant == ($request->get_frais_scolarite/2)){ // 50%
                        // ok pour paiement

                        $have_frais_scolarite = true;
                        $solde_frais_scolarite = false;
                    }else { // < 50%
                        // alert vous devez solder les frais de scolarité
                        return  ResponseHandler::error("Vous devez solder 50% des frais de scolarité.");

                    }
                }
            } else if($montant_paye == $request->get_frais_inscription) {
                // ok pour paiement des frais d'inscription
            }else{
                return  ResponseHandler::error("Vous devez solder les frais d\'inscription.");

            }
        } else {

            return  ResponseHandler::error("Vous devez solder les frais d\'inscription.");
        }

        //################# fin vérification des conditions côté serveur #########



        // update statut paiement
        $getPaiement = Paiements::where("reference",$request->reference)->get()->first();
        $paiement = Paiements::find($getPaiement->id);
        $reference_paiement_partiel = $paiement->reference;
        //######################################## montant supérieur à 0 ################

        if ($request->statut_traitement == "VALIDER") {
            # code...
            // 1- vérifier s'il s'agit de tranche unique ou double tranche
            if ($request->get_reduction == 1) { // tranche unique
                # code...

            } else { // double tranche
                # code...
            }


            if ($have_frais_scolarite == true && $solde_frais_scolarite == true) {
                # code...
                $paiement->setAttribute("num_transaction",trim($request->num_transaction));
                $paiement->setAttribute("statut_paiement","PAYE");
                $paiement->setAttribute("statut_transaction",trim($request->num_transaction));
                $paiement->setAttribute("statut_traitement",trim($request->statut_traitement));
            }
            // update montant payé dans la table paiement, detailspaiement et paiementstranchez
            $update_montant_restant = $paiement->montant_restant - $request->montant_total;
            $update_montant_paye = $paiement->montant_paye + $request->montant_total;
            //
            $paiement->setAttribute("montant_paye",$update_montant_paye);
            $paiement->setAttribute("montant_restant",$update_montant_restant);
            $paiement->setAttribute("id_modepaiement",$request->modepaiement_id);
            $paiement->setAttribute('agent_traiteur', $user->id);
            $paiement->setAttribute('date_traitement', new \DateTime());
            $paiement->update();

            // update table details paiements
            if($request->montant_paye == $montant_attendu){ // solder
                $detailsPaiements = Detailspaiements::where('code_inscription',$paiement->code_inscription)->get();
                foreach($detailsPaiements as $item){
                    $detailPaiement = Detailspaiements::find($item->id);
                    $detailPaiement->setAttribute('montant_paye',$item->montant_restant);
                    $detailPaiement->setAttribute('montant_restant',$item->montant_restant-$detailPaiement->montant_restant);
                    $detailPaiement->setAttribute('statut_traitement',trim($request->statut_traitement));
                    $detailPaiement->setAttribute('statut_paiement', "PAYE");
                    $detailPaiement->update();

                    // enregistrement de la tranche
                    if($item->getTarification->getGrilletarifaire->type_tarif != 'INSCRIPTION'){
                        //get tranche
                        $tranche = Tranches::where('code_inscription',$paiement->code_inscription)->first();
                        $tranche_paiement = new PaiementsTranche();
                        $tranche_paiement->setAttribute('details_paiements_id',$item->id);
                        $tranche_paiement->setAttribute('montant_paye',$item->montant_restant);
                        $tranche_paiement->setAttribute('montant_restant',$item->montant_restant-$detailPaiement->montant_restant);
                        $tranche_paiement->setAttribute('reference',$paiement->reference);
                        //
                        $tranche_paiement->setAttribute("num_transaction",trim($request->num_transaction));
                        $tranche_paiement->setAttribute("statut_transaction",trim($request->num_transaction));
                        $tranche_paiement->setAttribute("statut_traitement",trim($request->statut_traitement));
                        $tranche_paiement->setAttribute("modepaiement_id",trim($paiement->id_modepaiement));
                        //
                        $tranche_paiement->setAttribute('tranche_id',$tranche->id);
                        $tranche_paiement->setAttribute('created_by',$user->id);
                        $tranche_paiement->setAttribute('updated_by',$user->id);
                        $tranche_paiement->save();
                    }else{
                        //enregistrement tranche pour inscription
                        $tranche_paiement = new PaiementsTranche();
                        $tranche_paiement->setAttribute('details_paiements_id',$item->id);
                        $tranche_paiement->setAttribute('montant_paye',$item->montant_restant);
                        $tranche_paiement->setAttribute('montant_restant',$item->montant_restant-$detailPaiement->montant_restant);
                        $tranche_paiement->setAttribute('reference',$paiement->reference);
                        //
                        $tranche_paiement->setAttribute("num_transaction",trim($request->num_transaction));
                        $tranche_paiement->setAttribute("statut_transaction",trim($request->num_transaction));
                        $tranche_paiement->setAttribute("statut_traitement",trim($request->statut_traitement));
                        $tranche_paiement->setAttribute("modepaiement_id",trim($paiement->id_modepaiement));
                        //
                        $tranche_paiement->setAttribute('is_inscription',true);
                        $tranche_paiement->setAttribute('created_by',$user->id);
                        $tranche_paiement->setAttribute('updated_by',$user->id);
                        $tranche_paiement->save();
                    }
                }
            }

            // cas de montant payé < montant attendu
            if($request->montant_total < $montant_attendu){ // non solder & paiement partiel
                $detailsPaiements = Detailspaiements::where('code_inscription',$paiement->code_inscription)->get();

                // update frais inscription
                foreach($detailsPaiements as $item){
                    if($item->getTarification->getGrilletarifaire->type_tarif == 'INSCRIPTION' && $frais_inscription_paye >= $item->montant_restant){
                        $detailPaiement = Detailspaiements::find($item->id);
                        $detailPaiement->setAttribute('montant_paye',$item->montant_restant);
                        $detailPaiement->setAttribute('montant_restant',$item->montant_restant-$detailPaiement->montant_restant);
                        $detailPaiement->setAttribute('statut_traitement',trim($request->statut_traitement));
                        $detailPaiement->setAttribute('statut_paiement', "PAYE");

                        // enregistrement de la preuve si ça existe
                        $get_preuve = $request->preuve_paiement;
                        if (!empty($get_preuve)) {
                            $extension = $get_preuve->getClientOriginalExtension();
                            $preuve_name = date('Ymd-His') . '.' . $extension;
                            $get_preuve->move(public_path('preuve'), $preuve_name);
                            $detailPaiement->setAttribute('preuve_path', $preuve_name);
                        }

                        $detailPaiement->update();
                        $frais_inscription = $item->montant_restant;

                        // enregistrement de la tranche pour inscription
                        $tranche_paiement = new PaiementsTranche();
                        $tranche_paiement->setAttribute('details_paiements_id',$detailPaiement->id);
                        $tranche_paiement->setAttribute('montant_paye',$item->montant_restant);
                        $tranche_paiement->setAttribute('montant_restant',$item->montant_restant-$detailPaiement->montant_restant);
                        $tranche_paiement->setAttribute('reference',$paiement->reference.'_1');
                        //
                        $tranche_paiement->setAttribute("num_transaction",trim($request->num_transaction));
                        $tranche_paiement->setAttribute("statut_transaction",trim($request->num_transaction));
                        $tranche_paiement->setAttribute("statut_traitement",trim($request->statut_traitement));
                        $tranche_paiement->setAttribute("modepaiement_id",trim($paiement->id_modepaiement));
                        $tranche_paiement->setAttribute('is_inscription',true);
                        //
                        $tranche_paiement->setAttribute('created_by',$user->id);
                        $tranche_paiement->setAttribute('updated_by',$user->id);
                        $tranche_paiement->save();
                        $reference_paiement_partiel = $paiement->reference.'_1';

                    }
                }

                // update frais scolarite
                foreach($detailsPaiements as $data){
                    if($data->getTarification->getGrilletarifaire->type_tarif != 'INSCRIPTION' && $frais_scolarite_paye > 0){
                        // verifier si le montant restant apres avoir soustrait les frais d'inscription, couvre le montant de la scolarite
                        $montant_scolarite_restant = $data->montant_restant - $frais_scolarite_paye;
                        $detailPaiement = Detailspaiements::find($data->id);
                        $detailPaiement->setAttribute('montant_paye',$data->montant_paye + $frais_scolarite_paye);
                        $detailPaiement->setAttribute('montant_restant',$montant_scolarite_restant);
                        $detailPaiement->setAttribute('statut_traitement',trim($request->statut_traitement));
                        $detailPaiement->setAttribute('statut_paiement', "PAYE");

                        // enregistrement de la preuve si ça existe
                        $get_preuve = $request->preuve_paiement;
                        if (!empty($get_preuve)) {
                            $extension = $get_preuve->getClientOriginalExtension();
                            $preuve_name = date('Ymd-His') . '.' . $extension;
                            $get_preuve->move(public_path('preuve'), $preuve_name);
                            $detailPaiement->setAttribute('preuve_path', $preuve_name);
                        }
                        $detailPaiement->update();
                        //get tranche
                        $tranche = Tranches::where('code_inscription',$paiement->code_inscription)->first();

                        $tranche_paiement = new PaiementsTranche();
                        $tranche_paiement->setAttribute('details_paiements_id',$data->id);
                        $tranche_paiement->setAttribute('montant_paye',$frais_scolarite_paye);
                        $tranche_paiement->setAttribute('montant_restant',$montant_scolarite_restant);
                        $tranche_paiement->setAttribute('reference',$paiement->reference.'_1');
                        //
                        $tranche_paiement->setAttribute("num_transaction",trim($request->num_transaction));
                        $tranche_paiement->setAttribute("statut_transaction",trim($request->num_transaction));
                        $tranche_paiement->setAttribute("statut_traitement",trim($request->statut_traitement));
                        $tranche_paiement->setAttribute("modepaiement_id",trim($paiement->id_modepaiement));
                        //
                        $tranche_paiement->setAttribute('tranche_id',$tranche->id);
                        $tranche_paiement->setAttribute('created_by',$user->id);
                        $tranche_paiement->setAttribute('updated_by',$user->id);
                        $tranche_paiement->save();
                        $reference_paiement_partiel = $paiement->reference.'_1';

                    }
                }
            }


            //####################### fin traitement pour statut valider #######################


            $auditeur = Auditeurs::where("email",$paiement->email)->first();
            // envoie de mail pour la reférence de la facture
            $modepaiement = Modepaiements::find($request->modepaiement_id);
            $contenu = [
                'nom' => $auditeur->nom ,
                'prenoms' => $auditeur->prenoms,
                'reference_paiement' => $reference_paiement_partiel,
                'subject' => 'TRAITEMENT DE VOTRE PAIEMENT',
                'commentaire' => "Paiement validé",
                'email'=>$paiement->email,
                'mode_paiement'=>$modepaiement->libelle,
                'montant'=>$request->montant_total,
                'type'=>$paiement->type,
                'url_recu'=> $this->genarateurCodeService->generateUrlSigned('paiements.recu',['reference'=>$reference_paiement_partiel])
            ];

            try {
                # code...
                Mail::to(trim($paiement->email))->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new PaiementValideDsc($contenu));
            } catch (\Exception $e) {
                //throw $th;
            }

        } else {
            # code...
            // update montant payé dans la table paiement, detailspaiement et paiementstranchez
            $update_montant_restant = $paiement->montant_restant - $request->montant_total;
            $update_montant_paye = $paiement->montant_paye + $request->montant_total;
            //
            $paiement->setAttribute("montant_paye",$update_montant_paye);
            $paiement->setAttribute("montant_restant",$update_montant_restant);
            $paiement->setAttribute("id_modepaiement",$request->modepaiement_id);
            $paiement->setAttribute("statut_paiement","EN ATTENTE");
            $paiement->setAttribute("statut_traitement","ATTENTE");
            $paiement->update();

            // update table details paiements
            if($request->montant_paye == $montant_attendu){ // solder
                $detailsPaiements = Detailspaiements::where('code_inscription',$paiement->code_inscription)->get();
                foreach($detailsPaiements as $item){
                    $detailPaiement = Detailspaiements::find($item->id);
                    $detailPaiement->setAttribute('montant_paye',$item->montant_restant);
                    $detailPaiement->setAttribute('montant_restant',$item->montant_restant-$detailPaiement->montant_restant);
                    $detailPaiement->setAttribute('statut_traitement',trim($request->statut_traitement));
                    $detailPaiement->setAttribute('statut_paiement', "PAYE");
                    // si preuve de paiement, on sauvegarde

                    $detailPaiement->update();

                    // enregistrement de la tranche
                    if($item->getTarification->getGrilletarifaire->type_tarif != 'INSCRIPTION'){
                        //get tranche
                        $tranche = Tranches::where('code_inscription',$paiement->code_inscription)->first();
                        $tranche_paiement = new PaiementsTranche();
                        $tranche_paiement->setAttribute('details_paiements_id',$item->id);
                        $tranche_paiement->setAttribute('montant_paye',$item->montant_restant);
                        $tranche_paiement->setAttribute('montant_restant',$item->montant_restant-$detailPaiement->montant_restant);
                        $tranche_paiement->setAttribute('reference',$paiement->reference);
                        //
                        $tranche_paiement->setAttribute("num_transaction",trim($request->num_transaction));
                        $tranche_paiement->setAttribute("statut_transaction",trim($request->num_transaction));
                        $tranche_paiement->setAttribute("statut_traitement",trim($request->statut_traitement));
                        //
                        $tranche_paiement->setAttribute('tranche_id',$tranche->id);
                        $tranche_paiement->setAttribute('created_by',$user->id);
                        $tranche_paiement->setAttribute('updated_by',$user->id);
                        $tranche_paiement->save();
                    }
                }
            }
            // cas de montant payé < montant attendu
            if($request->montant_paye < $montant_attendu){ // non solder & paiement partiel
                $detailsPaiements = Detailspaiements::where('code_inscription',$paiement->code_inscription)->get();

                // update frais inscription
                foreach($detailsPaiements as $item){
                    if($item->getTarification->getGrilletarifaire->type_tarif == 'INSCRIPTION' && $frais_inscription_paye >= $item->montant_restant){
                        $detailPaiement = Detailspaiements::find($item->id);
                        $detailPaiement->setAttribute('montant_paye',$item->montant_restant);
                        $detailPaiement->setAttribute('montant_restant',$item->montant_restant-$detailPaiement->montant_restant);
                        $detailPaiement->update();
                        $frais_inscription = $item->montant_restant;
                    }
                }

                // update frais scolarite
                foreach($detailsPaiements as $data){
                    if($data->getTarification->getGrilletarifaire->type_tarif != 'INSCRIPTION' && $frais_scolarite_paye > 0){
                        // verifier si le montant restant apres avoir soustrait les frais d'inscription, couvre le montant de la scolarite
                        $montant_scolarite_restant = $data->montant_restant - $frais_scolarite_paye;
                        $detailPaiement = Detailspaiements::find($data->id);
                        $detailPaiement->setAttribute('montant_paye',$data->montant_paye + $frais_scolarite_paye);
                        $detailPaiement->setAttribute('montant_restant',$montant_scolarite_restant);
                        $detailPaiement->update();
                        //get tranche
                        $tranche = Tranches::where('code_inscription',$paiement->code_inscription)->first();

                        $tranche_paiement = new PaiementsTranche();
                        $tranche_paiement->setAttribute('details_paiements_id',$data->id);
                        $tranche_paiement->setAttribute('montant_paye',$frais_scolarite_paye);
                        $tranche_paiement->setAttribute('montant_restant',$montant_scolarite_restant);
                        $tranche_paiement->setAttribute('reference',$paiement->reference.'_1');
                        //
                        $tranche_paiement->setAttribute("num_transaction",trim($request->num_transaction));
                        $tranche_paiement->setAttribute("statut_transaction",trim($request->num_transaction));
                        $tranche_paiement->setAttribute("statut_traitement",trim($request->statut_traitement));
                        //
                        $tranche_paiement->setAttribute('tranche_id',$tranche->id);
                        $tranche_paiement->setAttribute('created_by',$user->id);
                        $tranche_paiement->setAttribute('updated_by',$user->id);
                        $tranche_paiement->save();
                        $reference_paiement_partiel = $paiement->reference.'_1';

                    }
                }
            }
        }

        return  ResponseHandler::success("Commande déplacée avec succès");
    }

    public function saveDeclassercommande(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement saveDeclassercommande() method.
        $paiement = Paiements::whereReference($request->reference)->first();
        $updPaiement = Paiements::find($paiement->id);
        $updPaiement->setAttribute('declasse',1);
        $updPaiement->update();

        return  ResponseHandler::success("ok");
    }
}
