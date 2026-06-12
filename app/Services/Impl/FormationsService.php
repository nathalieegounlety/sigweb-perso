<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Detailspaiements;
use App\Models\Dureeformations;
use App\Models\Formations;
use App\Models\FormationsDsc;
use App\Models\FormationsTranches;
use App\Models\Grilletarifaires;
use App\Models\Inscriptions;
use App\Models\Logopartenaires;
use App\Models\Paiements;
use App\Models\Reductions;
use App\Models\ReductionsFormations;
use App\Models\Tarificationsformation;
use App\Models\Tranches;
use App\Services\contracts\FormationsInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FormationsService implements FormationsInterface
{

    public function saveFormation(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement saveFormation() method.
        // sauvegarde des logos si disponible
        $have_logo = false;
        $logo_partenaires = $request->logos;

        // sauvegarde du visuel
        $name_visuel = null;
        if ($request->file) {
            $visuel = $request->file;
            $extension = $visuel->getClientOriginalExtension();
            $name_visuel = 'visuel_'.$request->code_formation. '_' .date('Ymd-His'). '.'.$visuel->extension();
            //$visuel->move(public_path('img/visuel/'), $name_visuel);
            $visuel->storeAs('visuel_formation', $name_visuel, 'public');

        }
        // procéder à l'enregistrement des données
        // traitement des données liées à la tarification
        //1- extration des données liés au cout de la formation
        $cout_formation = $request->cout_formation;
        // vérifier le nom d'élément dans la table cout_formation (maximum 4)
        $nbr_cout = count($cout_formation);
        $check_attestation = false;

        if($nbr_cout > 10){
            // erreur les coût associés à une formation ne peut pas dépassé trois rubriques
            return ResponseHandler::error("les coûts associés à la formation ne peut pas dépassé trois rubriques.", 401);
        }else{

            // enregistrement des données de la formation
            $formation = new Formations();
            $formation->setAttribute('code_formation',$request->get('code_formation')) ;
            $formation->setAttribute('theme',$request->get('theme')) ;
            $formation->setAttribute('id_webinaire',$request->get('id_webinaire')) ;
            $formation->setAttribute('description',$request->get('description')) ;
            $formation->setAttribute('date_debut',$request->get('date_debut')) ;
            $formation->setAttribute('date_fin',$request->get('date_fin')) ;
            $formation->setAttribute('date_limite',$request->get('date_limite')) ;
            $formation->setAttribute('statut','ACTIF');
            $formation->setAttribute('visuel',$name_visuel) ;
            if($request->get('fm_visio') === true || $request->get('fm_visio') === 'true'){
                $formation->setAttribute('fm_visio',1) ;
            }else{
                $formation->setAttribute('fm_visio',0) ;
            }

            if($request->get('fm_presentielle') === true || $request->get('fm_presentielle') === 'true'){
                $formation->setAttribute('fm_presentielle',1) ;
            }else{
                $formation->setAttribute('fm_presentielle',0) ;
            }
            if ($request->get('is_formation_dsc') === true || $request->get('is_formation_dsc') === 'true') {
                # code...
                $formation->setAttribute('is_formation_dsc',1) ;
            }else{
                $formation->setAttribute('is_formation_dsc',0) ;
            }
            $formation->setAttribute('lieu',$request->get('lieu')) ;
            //$formation->setAttribute('is_formation',$request->is_formation) ;
            $formation->setAttribute('created_by', auth('api')->id());
            $formation->setAttribute('updated_by', auth('api')->id());
            $formation->setAttribute('typeformation_id',$request->get('typeformation_id')) ;
            //$formation->setAttribute('created_at', new \DateTime());
            $formation->setAttribute('is_event', $request->is_event);
            $formation->setAttribute('is_coorganisation', $request->is_coorganisation);
            if ($request->titre_evenement) {
                # code...
                $formation->setAttribute('titre_evenement', $request->titre_evenement);
            }
            $formation->save();
            
            if ($request->has('idReductions') && !empty($request->input('idReductions'))) {
                    
                    $idReductionsJson = $request->input('idReductions');
                    $idReductions = json_decode($idReductionsJson, true); 
                    // Vérifier si c'est bien un tableau et qu'il n'est pas vide
                    if (is_array($idReductions) && count($idReductions) > 0) {
                        // delete all existing reduction
                        $deleteReduction = ReductionsFormations::where('formation_id',$formation->id)->delete();
                        // Parcourir chaque réduction
                        foreach ($idReductions as $reduction) {
                            $newReduction = new ReductionsFormations();
                            $newReduction->setAttribute('formation_id',$formation->id);
                            $newReduction->setAttribute('reduction_id',$reduction);
                            $newReduction->save(); 
                        } 
                    } 
                } 

            // update duree enregistrement
            if ($request->filled('id_webinaire') && $request->filled('duree_enregistrement') && $request->duree_enregistrement >= 0) {
                # code...
                $update_duree_formation = Dureeformations::where("code_formation",$formation->code_formation)->first();
                if ($update_duree_formation) {
                    $update_duree_formation->setAttribute("duree_enregistrement",$request->duree_enregistrement);
                    $update_duree_formation->update();
                } else {
                    # code... create durée
                    $update_duree_formation = new Dureeformations();
                    $update_duree_formation->setAttribute("code_formation",$formation->code_formation);
                    $update_duree_formation->setAttribute("duree_enregistrement",$request->duree_enregistrement);
                    $update_duree_formation->setAttribute("id_webinaire",$request->id_webinaire);
                    $update_duree_formation->setAttribute("duree",0);
                    $update_duree_formation->save();
                }


            }

            // vérifier si la formation est gratuite pour insérer le montant attestation dynamiquement
            for($i = 0; $i<$nbr_cout;$i++){
                $get_cout = $cout_formation[$i];
                if (!empty($get_cout)) {
                    # code...
                    $id_grille = $get_cout ;

                    $grilleTarifaire = Grilletarifaires::find($id_grille) ;

                    // enregistrement dans la table tarification
                    // vérifier si la données existe dans le cas contraire enregistrer
                    $check_existe = Tarificationsformation::where('code_formation',$request->code_formation)
                        ->where('id_grilletarifaire',$id_grille)->get();

                    if (count($check_existe) == 0) { // je fais l'enregistrement
                        # code...
                        $tarification_formation = new Tarificationsformation();
                        $tarification_formation->setAttribute('code_formation',$request->get('code_formation')) ;
                        $tarification_formation->setAttribute('id_grilletarifaire',$id_grille) ;
                        $tarification_formation->setAttribute('created_by', auth('api')->id());
                        $tarification_formation->setAttribute('created_at', new \DateTime());
                        $tarification_formation->save();


                        if ($formation->is_formation_dsc) {

                            $type_tarif = Grilletarifaires::where('type_tarif', 'INSCRIPTION')
                                ->orWhere('type_tarif', 'ETUDIANTS')
                                ->orWhere('type_tarif', 'PROFESSIONNELS')
                                ->distinct()->get();

                            //dd( $type_tarif);

                            if ($type_tarif != "INSCRIPTION")  {

                                $formation_tranche = new FormationsTranches();
                                $formation_tranche->setAttribute('formation_id',$formation->id) ;
                                $formation_tranche->setAttribute('grilletarifaire_id', $id_grille) ;
                                $formation_tranche->setAttribute('tranche', 'tranche 1') ;
                                $formation_tranche->setAttribute('taux_1', 100) ;
                                $formation_tranche->setAttribute('taux_2', 0) ;
                                $formation_tranche->setAttribute('echeance_1', '2024-06-30') ;
                                $formation_tranche->setAttribute('echeance_2', null) ;
                                $formation_tranche->setAttribute('categorie_auditeur',$grilleTarifaire->libelle) ;
                                $formation_tranche->setAttribute('reduction',5);
                                $formation_tranche->setAttribute('created_at', new \DateTime());
                                $formation_tranche->save();


                                $formation_tranche = new FormationsTranches();
                                $formation_tranche->setAttribute('formation_id',$formation->id) ;
                                $formation_tranche->setAttribute('grilletarifaire_id', $id_grille) ;
                                $formation_tranche->setAttribute('tranche', 'tranche 2') ;
                                $formation_tranche->setAttribute('taux_1', 50) ;
                                $formation_tranche->setAttribute('taux_2', 50) ;
                                $formation_tranche->setAttribute('echeance_1', '2024-06-30') ;
                                $formation_tranche->setAttribute('echeance_2', '2024-10-30') ;
                                $formation_tranche->setAttribute('categorie_auditeur', $grilleTarifaire->libelle) ;
                                $formation_tranche->setAttribute('reduction',0) ;
                                $formation_tranche->setAttribute('created_at', new \DateTime());
                                $formation_tranche->save();

                                # code...

                            }

                        }

                        # code...
                    } else {
                        # code...
                    }
                }

            }

            // save logo partenaire si disponible
             if($request->logos){
                if(count($logo_partenaires) > 0){
                    $i=0;
                    foreach ($logo_partenaires as $key => $logo) {
                        $i= $i+1;
                        # code...
                        if ($logo) {
                            # code...
                            $extension = $logo->getClientOriginalExtension();
                            $name_logo = 'logo_'.$request->code_formation. '_' .date('YmdHis').$i. '.'.$logo->extension();
                            //$logo->move(public_path('img/logo/'), $name_logo);
                            $logo->storeAs('logo_partenaire', $name_logo, 'public');

                            // enregistrement dans la table logo
                            $logo_partenaire = new Logopartenaires();
                            $logo_partenaire->setAttribute('logo_path',$name_logo);
                            $logo_partenaire->setAttribute('code_formation',$formation->code_formation);
                            $logo_partenaire->setAttribute('created_by', auth('api')->id());
                            $logo_partenaire->setAttribute('updated_by', auth('api')->id());
                            $logo_partenaire->save();
                        }
                    }
                }
            }


        }
        // return
        return ResponseHandler::success("Enregistrement effectuée.");
    }

    public function getFormation(int $id): Formations
    {
        // TODO: Implement getFormation() method.
    }

    public function getFormations(Request $request): LengthAwarePaginator
    {
        // TODO: Implement getFormations() method.
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Filtres spécifiques
        $filterTypeFormation = $request->input('filter_type_formation');
        $filterIsEnable = $request->input('filter_enable');
        $filterUpdatedAt = $request->input('filter_updated_at');
        $filterAuthor = $request->input('filter_author');
        $filterDateDebut = $request->input('filter_date_debut');
        $filterDateFin = $request->input('filter_date_fin');

        $queryFormations = Formations::select('formations.*')
            ->with('getUserUpdated:id,name,email,code_user')
            ->with('getTypeformation:id,libelle')
            ->with('tarifications');

        // On fait la jointure dès qu'on a besoin de users.name (recherche globale OU filtre auteur OU tri par auteur)
        $needsUserJoin = $search || $filterAuthor || $sortField === 'get_user_updated.name';

        if ($needsUserJoin) {
            $queryFormations->leftJoin('users', 'formations.updated_by', '=', 'users.id');
        }

        // Recherche globale sur plusieurs champs
        if ($search) {
            $queryFormations->where(function ($q) use ($search) {
                $q->where('formations.code_formation', 'like', "%{$search}%")
                ->orWhere('formations.theme', 'like', "%{$search}%")
                ->orWhere('formations.id_webinaire', 'like', "%{$search}%"); // Recherche sur auteur
            });
        }

        // Filtres par colonne
        if ($filterTypeFormation) {
            $queryFormations->leftJoin('typeformations', 'formations.typeformation_id', '=', 'typeformations.id');
            $queryFormations->where('typeformations.libelle', 'like', "%{$filterTypeFormation}%");
        }

        if ($filterIsEnable !== null && $filterIsEnable !== '') {
            $queryFormations->where('formations.enable', $filterIsEnable); // 0 ou 1
        }
        if ($filterDateDebut) {
            $queryFormations->whereDate('formations.date_debut', $filterDateDebut);
        }
        if ($filterDateFin) {
            $queryFormations->whereDate('formations.date_fin', $filterDateFin);
        }
        if ($filterUpdatedAt) {
            $queryFormations->whereDate('formations.updated_at', $filterUpdatedAt);
        }
        if ($filterAuthor) {
            $queryFormations->where('users.name', 'like', "%{$filterAuthor}%");
        } 

        // Tri
        if ($sortField === 'get_user_updated.name') {
            $queryFormations->orderBy('users.name', $sortOrder);
        } else {
            $queryFormations->orderBy("formations.{$sortField}", $sortOrder);
        }

       // $queryFormations->orderBy($sortField, $sortOrder);

        return $queryFormations->paginate($perPage);
    }

    public function update(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement update() method.
        $have_logo = false;
        $logo_partenaires = $request->logos;
        Log::info('begin update formation');

        // sauvegarde du visuel
        $name_visuel = null;
        $haveVisuel = false;
        if ($request->file) {
            $visuel = $request->file;
            $extension = $visuel->getClientOriginalExtension();
            $name_visuel = 'visuel_'.$request->code_formation. '_' .date('Ymd-His'). '.'.$visuel->extension();
            //$visuel->move(public_path('img/visuel/'), $name_visuel);
            $visuel->storeAs('visuel_formation', $name_visuel, 'public');
            $haveVisuel = true;

        }
        // procéder à l'enregistrement des données
        // traitement des données liées à la tarification
        //1- extration des données liés au cout de la formation
        $cout_formation = json_decode($request->cout_formation, true);
        // vérifier le nom d'élément dans la table cout_formation (maximum 4)
        $nbr_cout = count($cout_formation);
        $check_attestation = false;

        if($nbr_cout > 10){
            // erreur les coût associés à une formation ne peut pas dépassé trois rubriques
            return ResponseHandler::error("les coûts associés à la formation ne peut pas dépassé trois rubriques.", 401);
        }else{
            // enregistrement des données de la formation
            $formation = Formations::find($request->id);
            // $formation->setAttribute('code_formation',$request->get('code_formation')) ;
            $formation->setAttribute('theme',$request->get('theme')) ;
            $formation->setAttribute('id_webinaire',$request->get('id_webinaire')) ;
            $formation->setAttribute('description',$request->get('description')) ;
            $formation->setAttribute('date_debut',$request->get('date_debut')) ;
            $formation->setAttribute('date_fin',$request->get('date_fin')) ;
            $formation->setAttribute('date_limite',$request->get('date_limite')) ;
            //$formation->setAttribute('statut','ACTIF');
            if($haveVisuel){
                $formation->setAttribute('visuel',$name_visuel) ;
            }
            if($request->get('fm_visio') === true || $request->get('fm_visio') === 'true'){
                $formation->setAttribute('fm_visio',1) ;
            }else{
                $formation->setAttribute('fm_visio',0) ;
            }

            if($request->get('fm_presentielle') === true || $request->get('fm_presentielle') === 'true'){
                $formation->setAttribute('fm_presentielle',1) ;
            }else{
                $formation->setAttribute('fm_presentielle',0) ;
            }
            if ($request->get('is_formation_dsc') === true || $request->get('is_formation_dsc') === 'true') {
                # code...
                $formation->setAttribute('is_formation_dsc',1) ;
            }else{
                $formation->setAttribute('is_formation_dsc',0) ;
            }
            $formation->setAttribute('lieu',$request->get('lieu')) ;
            //$formation->setAttribute('is_formation',$request->is_formation) ;
            $formation->setAttribute('updated_by', auth('api')->id());
            $formation->setAttribute('typeformation_id',$request->get('typeformation_id')) ;
            //$formation->setAttribute('created_at', new \DateTime());
            $formation->setAttribute('is_event', $request->is_event);
            $formation->setAttribute('is_coorganisation', $request->is_coorganisation);
            if ($request->titre_evenement) {
                # code...
                $formation->setAttribute('titre_evenement', $request->titre_evenement);
            }
            $formation->save();

            
         // save logo partenaire si disponible 
             if($request->logos){
        Log::info('start save logo');
                if(count($logo_partenaires) > 0){
                    $i=0;
        Log::info('nbre de logo '.count($logo_partenaires));
                     
                    foreach ($logo_partenaires as $key => $logo) {
                        $i= $i+1;
                        # code...
                        if ($logo) {
                            # code...
                            $extension = $logo->getClientOriginalExtension();
                            $name_logo = 'logo_'.$formation->code_formation. '_' .date('YmdHis').$i. '.'.$logo->extension();
                            //$logo->move(public_path('img/logo/'), $name_logo);
                            $logo->storeAs('logo_partenaire', $name_logo, 'public');
        Log::info('save logo '.$name_logo);

                            // enregistrement dans la table logo
                            $logo_partenaire = new Logopartenaires();
                            $logo_partenaire->setAttribute('logo_path',$name_logo);
                            $logo_partenaire->setAttribute('code_formation',$formation->code_formation);
                            $logo_partenaire->setAttribute('created_by', auth('api')->id());
                            $logo_partenaire->setAttribute('updated_by', auth('api')->id());
                            $logo_partenaire->save();
                        }
                    }
                }
            }

            // update reduction if exists
            Log::info($request->input('idReductions'));
             if ($request->has('idReductions') && !empty($request->input('idReductions'))) {
                    $idReductionsJson = $request->input('idReductions');
                    $idReductions = json_decode($idReductionsJson, true); 
                    // Vérifier si c'est bien un tableau et qu'il n'est pas vide
                    if (is_array($idReductions) && count($idReductions) > 0) {
                        // delete all existing reduction
                        $deleteReduction = ReductionsFormations::where('formation_id',$formation->id)->delete();
                        // Parcourir chaque réduction
                        foreach ($idReductions as $reduction) {
                            $newReduction = new ReductionsFormations();
                            $newReduction->setAttribute('formation_id',$formation->id);
                            $newReduction->setAttribute('reduction_id',$reduction);
                            $newReduction->save(); 
                        } 
                    } 
                } 

            // update duree enregistrement
            if ($request->filled('id_webinaire') && $request->filled('duree_enregistrement') && $request->duree_enregistrement >= 0) {
                # code...
                $update_duree_formation = Dureeformations::where("code_formation",$formation->code_formation)->first();
                if ($update_duree_formation) {
                    $update_duree_formation->setAttribute("duree_enregistrement",$request->duree_enregistrement);
                    $update_duree_formation->update();
                } else {
                    # code... create durée
                    $update_duree_formation = new Dureeformations();
                    $update_duree_formation->setAttribute("code_formation",$formation->code_formation);
                    $update_duree_formation->setAttribute("duree_enregistrement",$request->duree_enregistrement);
                    $update_duree_formation->setAttribute("id_webinaire",$request->id_webinaire);
                    $update_duree_formation->setAttribute("duree",0);
                    $update_duree_formation->save();
                }
            }

            // vérifier si la formation est gratuite pour insérer le montant attestation dynamiquement
            $check_details_paiement = DB::table('detailspaiements')
                ->join('paiements', 'paiements.reference', '=', 'detailspaiements.reference_paiement')
                ->leftjoin('reductions', 'reductions.id', '=', 'detailspaiements.id_reduction')
                ->join('tarificationsformations', 'tarificationsformations.id', '=', 'detailspaiements.id_tarification_formation')
                ->join('grilletarifaires', 'tarificationsformations.id_grilletarifaire', '=', 'grilletarifaires.id')
                ->join('formations', 'formations.code_formation', '=', 'tarificationsformations.code_formation')
                ->select('formations.code_formation', 'formations.theme', 'formations.date_debut','grilletarifaires.montant','reductions.pourcentage','detailspaiements.preuve_path')
                ->where('tarificationsformations.code_formation',$request->get('code_formation'))
                ->whereNotNull('paiements.num_transaction')
                ->exists();

            if($check_details_paiement){
                // vérifier si un paiement existe
                return ResponseHandler::success("Modification effectuée mais le coût de cette formation ne pourra pas être mise à jour car un paiement est déjà enregistré");
            }else{

            // filtrer les tarifs reçus pour retirer les nouveaux
            $sentTarifsIds = collect($cout_formation)
            ->pluck('id_tarif')
            ->filter() // enlève null
            ->toArray();

            // récupérer les tarifs existants
            $existingTarifsIds = Tarificationsformation::where('code_formation', $request->code_formation)
            ->pluck('id')
            ->toArray();

            
            // vérifier les tarifs supprimés
            $toDeleteTarif = array_diff($existingTarifsIds, $sentTarifsIds);

            // vérifier si la suppression de tarif est possible

            foreach ($toDeleteTarif as $tarifId) {
                $hasPaiement = Detailspaiements::where('id_tarification_formation', $tarifId)->exists();
                $hasInscription = Inscriptions::where('tarif_formation_id', $tarifId)->exists();
                if ($hasInscription && $hasPaiement) {
                    //throw new Exception("Impossible de supprimer un tarif déjà utilisé");
                }else{
                    Tarificationsformation::where('id', $tarifId)->delete();
                }
            }

            // mise à jour des tarifs
            foreach ($cout_formation as $item) {

                if ($item['id_tarif']) {

                    // UPDATE
                    Tarificationsformation::where('id', $item['id_tarif'])
                        ->update([
                            'id_grilletarifaire' => $item['id_grille']
                        ]);

                    // vérifier s'il y a des inscriptions pour faire la mise à jour
                    // 1- récupérer la liste des inscriptions
                    $idTarif = $item['id_tarif'];
                    $getTarif = Tarificationsformation::find($idTarif);

                    if ($formation->is_formation_dsc) {
                        # code...

                        // vérifier s'il ne s'agit du montant d'inscription
                        if ($getTarif->getGrille->type_tarif != "INSCRIPTION") {
                            # code...
                             $listeInscriptions = Inscriptions::where('code_formation',$request->code_formation)->where("tarif_formation_id",$idTarif)->get();
                    foreach ($listeInscriptions as $key => $data) {
                    
                        // CAS DES FORMATIONS NON DSC
                        
                        # code...
                        // 1- mise à jour details paiement
                        $getDetailsPaiements = Detailspaiements::where('code_inscription',$data->code_inscription)->get();
                        foreach ($getDetailsPaiements as $key => $value) {
                            # code...
                            $updDetailPaiement = Detailspaiements::find($value->id);
                            $red = 0; // mis pour réduction %

                            // vérifier si réduction existe
                            if ($updDetailPaiement->have_reduction_dsc) {
                                # code...
                                $reduction = Reductions::find($updDetailPaiement->have_reduction_dsc);
                                $red = 5; //$reduction->pourcentage;
                            } 

                            // mise à jour montant restant
                            $tarif = Tarificationsformation::find($updDetailPaiement->id_tarification_formation);
                            $montant = $tarif->getGrilletarifaire->montant;
                            $montantOk = $montant - ($montant * ($red/100));
                            $updDetailPaiement->setAttribute('montant_restant',$montantOk);
                            $updDetailPaiement->update();
                            
                        }

                        // update billing
                        $getOneDetailPaiement = Detailspaiements::where('code_inscription',$data->code_inscription)->first();
                        $getMontantTotalDetailPaiement = Detailspaiements::where('code_inscription',$data->code_inscription)->sum('montant_restant');
                        $paiement = Paiements::whereReference($getOneDetailPaiement->reference_paiement)->first();
                        $updPaiement = Paiements::find($paiement->id);
                        $updPaiement->setAttribute('montant_total',$getMontantTotalDetailPaiement);
                        $updPaiement->setAttribute('montant_restant',$getMontantTotalDetailPaiement);
                        $updPaiement->update();

                        // mise à jour de ma tranche
                        $checkTranche = Tranches::where("code_inscription",$data->code_inscription)->exists();
                        if($checkTranche){
                            $getTranche = Tranches::where("code_inscription",$data->code_inscription)->first();
                            if($getTranche->tranche_2 > 0){ // double tranche
                            $updTranche = Tranches::find($getTranche->id);
                            $updTranche->setAttribute("tranche_1", $updPaiement->montant_total/2);
                            $updTranche->setAttribute("tranche_2", $updPaiement->montant_total/2);
                            $updTranche->update();

                            }else{ // tranche unique
                            $updTranche = Tranches::find($getTranche->id);
                            $updTranche->setAttribute("tranche_1", $updPaiement->montant_total);
                            $updTranche->update();
                            }
                        }
                        // send mail for auditeur to notify for price change
                    }

                        } else {
                            # code... montant inscription à mettre à jour
                        } 

                    } else {
                        # code... IL S'AGIT DES FORMATIONS CLASSIQUES

                    $listeInscriptions = Inscriptions::where('code_formation',$request->code_formation)->where("tarif_formation_id",$idTarif)->get();
                    foreach ($listeInscriptions as $key => $data) {
                    
                        // CAS DES FORMATIONS NON DSC
                        
                        # code...
                        // 1- mise à jour details paiement
                        $getDetailsPaiements = Detailspaiements::where('code_inscription',$data->code_inscription)->get();
                        foreach ($getDetailsPaiements as $key => $value) {
                            # code...
                            $updDetailPaiement = Detailspaiements::find($value->id);
                            $red = 0; // mis pour réduction %

                            // vérifier si réduction existe
                            if ($updDetailPaiement->id_reduction) {
                                # code...
                                $reduction = Reductions::find($updDetailPaiement->id_reduction);
                                $red = $reduction->pourcentage;
                            } 

                            // mise à jour montant restant
                            $tarif = Tarificationsformation::find($updDetailPaiement->id_tarification_formation);
                            $montant = $tarif->getGrilletarifaire->montant;
                            $montantOk = $montant - ($montant * ($red/100));
                            $updDetailPaiement->setAttribute('montant_restant',$montantOk);
                            $updDetailPaiement->update();
                            
                        }

                        // update billing
                        $getOneDetailPaiement = Detailspaiements::where('code_inscription',$data->code_inscription)->first();
                        if($getOneDetailPaiement){
                            $getMontantTotalDetailPaiement = Detailspaiements::where('code_inscription',$data->code_inscription)->sum('montant_restant');
                            $paiement = Paiements::whereReference($getOneDetailPaiement->reference_paiement)->first();
                            $updPaiement = Paiements::find($paiement->id);
                            $updPaiement->setAttribute('montant_total',$getMontantTotalDetailPaiement);
                            $updPaiement->setAttribute('montant_restant',$getMontantTotalDetailPaiement);
                            $updPaiement->update();

                        }

                        // send mail for auditeur to notify for price change
                    }

                    }
                    
                  

                } else {

                    // CREATE  
                    $tarification_formation = new Tarificationsformation();
                    $tarification_formation->setAttribute('code_formation',$request->get('code_formation')) ;
                    $tarification_formation->setAttribute('id_grilletarifaire',$item['id_grille']) ;
                    $tarification_formation->setAttribute('created_by', auth('api')->id());
                    $tarification_formation->setAttribute('created_at', new \DateTime());
                    $tarification_formation->save();

                    if ($formation->is_formation_dsc) {

                                $grille = Grilletarifaires::find($item['id_grille']);

                                //dd( $type_tarif);

                                if ($grille->type_tarif != "INSCRIPTION")  {

                                    $formation_tranche = new FormationsTranches();
                                    $formation_tranche->setAttribute('formation_id',$formation->id) ;
                                    $formation_tranche->setAttribute('grilletarifaire_id', $item['id_grille']) ;
                                    $formation_tranche->setAttribute('tranche', 'tranche 1') ;
                                    $formation_tranche->setAttribute('taux_1', 100) ;
                                    $formation_tranche->setAttribute('taux_2', 0) ;
                                    $formation_tranche->setAttribute('echeance_1', '2024-06-30') ;
                                    $formation_tranche->setAttribute('echeance_2', null) ;
                                    $formation_tranche->setAttribute('categorie_auditeur',$grille->libelle) ;
                                    $formation_tranche->setAttribute('reduction',5);
                                    $formation_tranche->setAttribute('created_at', new \DateTime());
                                    $formation_tranche->save();


                                    $formation_tranche = new FormationsTranches();
                                    $formation_tranche->setAttribute('formation_id',$formation->id) ;
                                    $formation_tranche->setAttribute('grilletarifaire_id', $item['id_grille']) ;
                                    $formation_tranche->setAttribute('tranche', 'tranche 2') ;
                                    $formation_tranche->setAttribute('taux_1', 50) ;
                                    $formation_tranche->setAttribute('taux_2', 50) ;
                                    $formation_tranche->setAttribute('echeance_1', '2024-06-30') ;
                                    $formation_tranche->setAttribute('echeance_2', '2024-10-30') ;
                                    $formation_tranche->setAttribute('categorie_auditeur', $grille->libelle) ;
                                    $formation_tranche->setAttribute('reduction',0) ;
                                    $formation_tranche->setAttribute('created_at', new \DateTime());
                                    $formation_tranche->save();

                                    # code...

                                }

                            }

                }
            } 
               
            }
        }

        // return
        return ResponseHandler::success("Modification effectuée.");
    }

    public function remove(int $idRole): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement remove() method.
    }

    public function getRecentFormations(Request $request)
    {
        // TODO: Implement getRecentFormations() method.
        $perPage = min($request->input('per_page', 6),6);

        $date_now = date('Y-m-d');
        // $date_now = date('Y-m-d',strtotime($date_now . "-45 days"));
        $date_actuelle = date('m/d/Y');
        $get_date = date('Y-m-d',strtotime($date_actuelle . "+60 days"));

        $queryFormations = Formations::select('formations.*')
            ->with('getTypeformation:id,libelle')
            ->with('tarifications')
            ->whereDate('formations.date_fin','>=',$date_now)
            ->where('formations.is_formation_dsc',false)
            ->where('formations.statut','ACTIF')
            ->where('formations.enable',true)
            ->orderBy("formations.date_debut","ASC")
            ->limit(6) ;
        return $queryFormations->get();
    }

    public function getDetailsFormation(Request $request): \Illuminate\Http\JsonResponse
    {
        $queryFormations = Formations::select('formations.*')
            ->with('getTypeformation:id,libelle')
            ->with('tarifications')
            ->with('reductions')
            ->where('formations.code_formation', $request->code_formation)
            ->get()->first();
        return ResponseHandler::success('ok', $queryFormations);
    }

    public function getFormationsAttestation(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getFormationsAttestation() method.
        $liste_formations = Formations::query()
            //->where('statut', 'INACTIF')
            ->where(function ($q) {
                $q->where('enable', true)
                    ->orWhere('presentiel', 1);
            })
            ->select([
                'code_formation as code',
                DB::raw("CONCAT(formations.code_formation, ' - ', formations.theme) AS name")
            ])
            ->orderBy('id', 'DESC')
            ->get();
        return ResponseHandler::success('ok', $liste_formations);
    }

    public function getFormationsDscObj(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getFormationsDscObj() method.
        $liste_formations = Formations::query()
            ->where('is_formation_dsc', true) 
            ->select([
                'code_formation as code',
                DB::raw("CONCAT(formations.code_formation, ' - ', formations.theme) AS name")
            ])
            ->orderBy('id', 'DESC')
            ->get();
        return ResponseHandler::success('ok', $liste_formations);
    }

    public function getFormationsDsc(Request $request)
    {
        // TODO: Implement getFormationsDsc() method.
        $date_now = date('Y-m-d');
        $queryFormations = Formations::select('formations.*')
            ->with('getTypeformation:id,libelle')
            ->with('tarifications')
            ->whereDate('formations.date_debut', '>=', $date_now)
            ->where('formations.is_formation_dsc',true)
            ->where('formations.statut','ACTIF')
            ->where('formations.enable',true)
            ->orderBy("formations.date_debut","ASC") ;
        return $queryFormations->get();
    }

    public function getDetailFormationDsc(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getDetailFormationDsc() method.
        $queryFormations = Formations::select('formations.*')
            ->with('getTypeformation:id,libelle')
            ->with('tarifications')
            ->whereCodeFormation($request->code_formation)
            ->first() ;
        $delais = FormationsDsc::join('formations','formations.id','=','formations_dscs.formation_id')
            ->where('formations.code_formation',$request->code_formation)
            ->get(['formations.code_formation','formations_dscs.modalites_path','formations_dscs.duree','date_limite_paiement_inscription','date_limite_inscription','date_remise_diplomes'])->first();

        $dataResponse = [
            'delais' => $delais,
            'formation' => $queryFormations
        ];

        return ResponseHandler::success('ok', $dataResponse);
    }

    public function getFormationsObj(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getFormationsObj() method.
        $liste_formations = Formations::query()
            //->where('statut', 'ACTIF')
            ->select([
                'code_formation as code',
                DB::raw("CONCAT(formations.code_formation, ' - ', formations.theme) AS name")
            ])
            //->where('is_formation_dsc',false)
            ->orderBy('date_debut', 'DESC')
            ->get();
        return ResponseHandler::success('ok', $liste_formations);
    }

    public function getListeInscrits(Request $request, string $code_formation)
    {
        // TODO: Implement getListeInscrits() method.

        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');
        
        $filterNom = $request->input('filter_nom');
        $filterPrenoms = $request->input('filter_prenoms');
        $filterCreatedAt = $request->input('filter_created_at');
        

        /*$liste_inscrits = Inscriptions::leftjoin('auditeurs', 'inscriptions.email', '=', 'auditeurs.email')
            ->leftjoin('tarificationsformations', 'inscriptions.tarif_formation_id', '=', 'tarificationsformations.id')
            ->leftjoin('formations', 'inscriptions.code_formation', '=', 'formations.code_formation')
            ->leftjoin('grilletarifaires', 'tarificationsformations.id_grilletarifaire', '=', 'grilletarifaires.id')
            ->leftjoin("detailspaiements", function ($leftjoin) {
                $leftjoin->on('detailspaiements.email', '=', 'inscriptions.email');
                $leftjoin->on('detailspaiements.id_tarification_formation', '=', 'inscriptions.tarif_formation_id');
            })
            ->where('inscriptions.code_formation', $code_formation)
            ->where('inscriptions.is_delete', false)
            ->select(['inscriptions.id','inscriptions.sync_zoom','inscriptions.code_inscription', 'inscriptions.created_at',
                'auditeurs.nom', 'auditeurs.prenoms', 'formations.fm_presentielle', 'formations.fm_visio', 'auditeurs.email',
                'auditeurs.telephone', 'grilletarifaires.type_tarif as mode', 'inscriptions.type_formation as mode','grilletarifaires.montant',
                'inscriptions.type_formation', 'detailspaiements.statut_paiement','detailspaiements.reference_paiement', 'auditeurs.id as id_auditeur',
                'inscriptions.is_formation_dsc', 'inscriptions.is_delete','inscriptions.code_formation','formations.is_formation_dsc'])
            ->selectRaw(
                '(EXISTS (
                SELECT 1 FROM auditeurs_pieces
                WHERE auditeurs_pieces.auditeur_id = auditeurs.id
            )) as check_dossier'
            );*/

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

    //  dossier check corrigé
    ->selectRaw("
        EXISTS (
            SELECT 1
            FROM auditeurs_pieces
            WHERE auditeurs_pieces.auditeur_id = auditeurs.id_auditeur
        ) as check_dossier
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
    

    public function saveReduction(Request $request): \Illuminate\Http\JsonResponse
    {
         if (is_array($request->idReduction) && count($request->idReduction) > 0) {
            $liste = $request->idReduction;
            $formation = Formations::where('code_formation',$request->code_formation)->first();

            foreach ($liste as $key => $id) {
                # code...
                $reduction = Reductions::find($id);
                $checkReduction = ReductionsFormations::where('reduction_id',$reduction->id)
                ->where('formation_id',$formation->id)
                ->exists();
                if(!$checkReduction){
                    $newFmReduction = new ReductionsFormations();
                    $newFmReduction->setAttribute('reduction_id',$reduction->id);
                    $newFmReduction->setAttribute('formation_id',$formation->id);
                    $newFmReduction->save();
                }
            } 

            return  ResponseHandler::success("Création efféctuée",['success'=>true]);

        }else{
            return  ResponseHandler::error("Echec lors de la création");
        } 
    }

    

    public function getListeReductions(Request $request): \Illuminate\Http\JsonResponse
    {
        $formation = Formations::where('code_formation',$request->code_formation)->first();
        $liste = ReductionsFormations::join('reductions','reductions.id','=','reductions_formations.reduction_id')
        ->where('formation_id',$formation->id)
        ->get(['reductions_formations.id','reductions.id as id_reduction','reductions.libelle','reductions.pourcentage']);

        return ResponseHandler::success('ok', ['success'=>true,'listeReductions' => $liste]);
    }

    

    public function saveDeleteReduction(Request $request): \Illuminate\Http\JsonResponse
    { 
        try {
            Log::info($request->idReduction);
            //code...
            $id = (int) $request->idReduction;
            $data = ReductionsFormations::find($id);
            $data->delete();
            return ResponseHandler::success("ok",['success'=>true]);
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseHandler::error("ok",400,$th->getMessage());
        }  
    }

    public function getReductionFormation(Request $request): \Illuminate\Http\JsonResponse
    {
        $formation = Formations::where('code_formation',$request->code_formation)->firstOrFail();
        $data = ReductionsFormations::query()
        ->join('reductions','reductions.id','=','reductions_formations.reduction_id')
        ->where('formation_id',$formation->id) 
        ->select([ 
                DB::raw("CONCAT(reductions.id, '-', reductions.pourcentage) AS code"),
                DB::raw("CONCAT(reductions.libelle, ' - ', reductions.pourcentage,'%') AS name")
        ]) 
        ->get(); 

        return ResponseHandler::success('ok', ['success'=>true,'listeReductions' => $data]);
    }

    
    

    public function saveLogos(Request $request): \Illuminate\Http\JsonResponse
    {
        // save logo partenaire si disponible
        $logo_partenaires = $request->logos;
        $formation = Formations::whereCodeFormation($request->code_formation)->first();
             if($request->logos){
                if(count($logo_partenaires) > 0){
                    $i=0;
                    foreach ($logo_partenaires as $key => $logo) {
                        $i= $i+1;
                        # code...
                        if ($logo) {
                            # code...
                            $extension = $logo->getClientOriginalExtension();
                            $name_logo = 'logo_'.$request->code_formation. '_' .date('YmdHis').$i. '.'.$logo->extension();
                            //$logo->move(public_path('img/logo/'), $name_logo);
                            $logo->storeAs('logo_partenaire', $name_logo, 'public');

                            // enregistrement dans la table logo
                            $logo_partenaire = new Logopartenaires();
                            $logo_partenaire->setAttribute('logo_path',$name_logo);
                            $logo_partenaire->setAttribute('code_formation',$formation->code_formation);
                            $logo_partenaire->setAttribute('created_by', auth('api')->id());
                            $logo_partenaire->setAttribute('updated_by', auth('api')->id());
                            $logo_partenaire->save();
                        }
                    }
                    
            return  ResponseHandler::success("Enregistrement efféctué",['success'=>true]);

                }else{
            return  ResponseHandler::error("Echec lors de la l'enregistrement");

                }
            }else{
            return  ResponseHandler::error("Echec lors de la l'enregistrement");

            } 
    }

    

    public function getListeLogos(Request $request): \Illuminate\Http\JsonResponse
    {
        $logopartenaires = Logopartenaires::where("code_formation",$request->code_formation)
        ->get(['logopartenaires.id','logopartenaires.code_formation','logopartenaires.logo_path','logopartenaires.largeur','logopartenaires.hauteur']);

        return ResponseHandler::success('ok', ['success'=>true,'listeLogos' => $logopartenaires]);
    }

    

    public function saveDeleteLogo(Request $request): \Illuminate\Http\JsonResponse
    { 
        try {
            Log::info($request->idLogo);
            //code...
            $id = (int) $request->idLogo;
            $data = Logopartenaires::find($id);
            $data->delete();
            return ResponseHandler::success("ok",['success'=>true]);
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseHandler::error("ok",400,$th->getMessage());
        }  
    }
    
    public function saveEditLogos(Request $request): \Illuminate\Http\JsonResponse
    { 
        try { 
            //code...
            $id = (int) $request->idLogo;
            $logo_partenaire = Logopartenaires::find($id);
            $logo = $request->fichier;
             if ($logo) {
                            # code...
                            $extension = $logo->getClientOriginalExtension();
                            $name_logo = 'logo_'.$request->code_formation. '_' .date('YmdHis'). '.'.$logo->extension();
                            //$logo->move(public_path('img/logo/'), $name_logo);
                            $logo->storeAs('logo_partenaire', $name_logo, 'public');

                            // enregistrement dans la table logo 
                            $logo_partenaire->setAttribute('logo_path',$name_logo);  
                            $logo_partenaire->setAttribute('updated_by', auth('api')->id());
                            $logo_partenaire->update();
                            return ResponseHandler::success("ok",['success'=>true]);
                        }else{
                            return ResponseHandler::success("Echec lors de l'enregistrement",['success'=>false]);

                        } 
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseHandler::error("ok",400,$th->getMessage());
        }  
    }
}
