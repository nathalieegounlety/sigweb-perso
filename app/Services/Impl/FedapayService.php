<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Mail\DscNotificationComptable;
use App\Mail\DscPaiementAttente;
use App\Mail\NotificationComptable;
use App\Mail\NotificationPaiementOuvrageStaff;
use App\Mail\Paiementattente;
use App\Mail\Paiementattenteouvrage;
use App\Mail\PaiementValideDsc;
use App\Models\Auditeurs;
use App\Models\Detailspaiements;
use App\Models\FormationsTranches;
use App\Models\Inscriptions;
use App\Models\Modepaiements;
use App\Models\Paiements;
use App\Models\PaiementsTranche;
use App\Models\Tranches;
use App\Models\User;
use App\Services\contracts\FedapayInterfarce;
use App\Services\util\GenarateurCodeService;
use FedaPay\FedaPay;
use FedaPay\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FedapayService implements  FedapayInterfarce
{

    private GenarateurCodeService $genarateurCodeService;

    public function __construct(GenarateurCodeService $genarateurCodeService)
    {
        FedaPay::setApiKey(config('fedapay.secret_key'));
        FedaPay::setEnvironment(config('fedapay.environment'));
        $this->genarateurCodeService = $genarateurCodeService;
    }

    public function storePaiementFedapay(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement storePaiementFedapay() method.

            # code...
            $reference = $request->reference;
            $montant_paye = $request->montant_payer;
            // récupérer le statut de la transaction côté serveur
            $data_transaction = Transaction::retrieve($request->transaction_id);

            // mise à jour de la table paiement
            $getPaiement = Paiements::whereReference($reference)->first();
            $montant_restant = $getPaiement->montant_restant;
            // mise à jour du montant paye
            $paiement = Paiements::find($getPaiement->id);
            $paiement->setAttribute('num_transaction', $data_transaction->id);
            $paiement->setAttribute('statut_transaction', $data_transaction->status);
            $paiement->setAttribute('updated_at', date('Y-m-d H:i:s'));
            $paiement->setAttribute('id_modepaiement', 2); // FedaPay
            if($montant_paye < $montant_restant){ // il s'agit d'un paiement partiel
                $paiement->setAttribute('is_paiementpartiel', 1);
            }
            $paiement->update();
            // récupérer le mode de paiement
            $libelle_mode_paiement = Modepaiements::find($paiement->id_modepaiement);

            // envoie de mail pour notification paiement en attente
            // envoie de mail pour notifier l'enregistrement du paiement
            $auditeur = Auditeurs::where("email",$paiement->email)->first();
            if ($auditeur) {
                # code...
                $contenu = [
                    'nom' => $auditeur->nom,
                    'prenoms' => $auditeur->prenoms,
                    'reference_paiement' => $reference,
                    'subject' => 'INFORMATION SUR VOTRE PAIEMENT '.$reference,
                    'email' => $paiement->email,
                    'montant' => $montant_paye,
                    'mode_paiement' => $libelle_mode_paiement->libelle
                ];
            } else {
                # code...
                $contenu = [
                    'nom' => "",
                    'prenoms' => "",
                    'reference_paiement' => $reference,
                    'subject' => 'INFORMATION SUR VOTRE PAIEMENT '.$reference,
                    'email' => $paiement->email,
                    'montant' => $montant_paye,
                    'mode_paiement' => $libelle_mode_paiement->libelle
                ];
            }
            try {
                # code...
                 Mail::to($paiement->email)->cc([env('MAIL_CC')])->bcc(env('MAIL_BCC'))->queue(new Paiementattente($contenu));
            } catch (\Exception $e) {
                // Get error here
                Log::info('echec de l\'envoi du mail');
            }

            if(env('PAIEMENT_NOTIFIER_COMPTABLE')){

                $liste_comptable = User::whereHas('roles', function ($q) {
                    $q->where('libelle', 'COMPTABLE');
                })->pluck('email');

                //foreach ($liste_comptable as $comptable) {
                $contenu = [
                    'name' => 'COMPTABLE',
                    'reference_paiement' => $reference,
                    'email_auditeur' => $paiement->email,
                    'nom_auditeur' => $auditeur->prenoms.' '.$auditeur->nom,
                    'subject' => 'NOUVEAU PAIEMENT ' . date('d-m-Y H:i:s'),
                    'email' => '',
                    'montant' => $montant_paye,
                    'mode_paiement' => $libelle_mode_paiement->libelle
                ];
                try {
                    # code...
                    Mail::to(env('MAIL_CC'))->cc($liste_comptable)->queue(new NotificationComptable($contenu));
                } catch (\Exception $e) {
                    // Get error here
                }
            }

            //}

            $dataResponse = [
                'reference' => $paiement->reference,
                'success' => true,
            ];

            return  ResponseHandler::success("ok",$dataResponse);

    }


    public function storePaiementPartielFedapay(Request $request): \Illuminate\Http\JsonResponse
    {

        try {
            // récupérer le statut de la transaction côté serveur
            $data_transaction = Transaction::retrieve($request->transaction_id);
            
            Log::info('info fedapay');
            Log::info($data_transaction);
            Log::info('end info fedapay');
 
                $reference = $request->reference_paiement;
                $actuel_montant_paye = $request->montant_total;
                $mode_paiement = Modepaiements::whereLibelle($request->mode_paiement)->first();
                // mise à jour de la table paiement
                $paiement = Paiements::where('reference', $reference)->get()->first();
                $update_montant_restant = $paiement->montant_restant - $actuel_montant_paye;
                $update_montant_paye = $paiement->montant_paye + $actuel_montant_paye;

                $update_paiement = Paiements::find($paiement->id);

                if ($update_paiement->is_paiement_partiel) {
                    # code...
                    $paiement_partiel = true;
                } else {
                    # code...
                    $paiement_partiel = false;
                }

                $statut_details_paiement = "IMPAYE";
                $statut_paiement = "EN ATTENTE";

                if ($update_montant_restant == 0 && $actuel_montant_paye == $paiement->montant_restant) {
                    # code...
                    $update_paiement->setAttribute('statut_paiement',$statut_paiement);
                    $update_paiement->setAttribute('num_transaction', $data_transaction->id);
                    $update_paiement->setAttribute('statut_transaction', $data_transaction->status);

                    if ($paiement_partiel) {
                        # code...
                        $update_paiement->setAttribute('montant_paye', $update_montant_paye);
                        $update_paiement->setAttribute('montant_restant', $update_montant_restant);
                    }
                    $update_paiement->setAttribute('id_modepaiement', $mode_paiement->id);
                    $update_paiement->update();

                }else {
                    # code...
                    $update_paiement->setAttribute('is_paiement_partiel', true);
                    $paiement_partiel = true;
                    if ($update_montant_restant == 0) {
                        # code...
                        $update_paiement->setAttribute('statut_paiement',$statut_paiement);
                    }
                    $update_paiement->setAttribute('montant_paye', $update_montant_paye);
                    $update_paiement->setAttribute('montant_restant', $update_montant_restant);
                    $update_paiement->setAttribute('id_modepaiement', $mode_paiement->id);
                    $update_paiement->update();
                }


                // update détails paiements
                foreach ($request->formations as $key => $value) {
                    # code...
                    $update_detailspaiement = Detailspaiements::find($value['detail_id']);
                    if ($paiement_partiel) {
                        # code...
                        $update_detailspaiement->setAttribute('is_paiement_partiel', true);
                        $update_detailspaiement->setAttribute('modepaiement_id', $mode_paiement->id);
                        $update_detailspaiement->setAttribute('reference_paiement_partiel', $data_transaction->id);
                        $update_detailspaiement->setAttribute('statut_paiement', "PAYE");
                    }else {
                        # code...
                        $update_detailspaiement->setAttribute('statut_paiement', $statut_details_paiement);
                    }
                    $update_detailspaiement->update();
                }

                // envoie de mail pour notification paiement en attente
                // envoie de mail pour notifier l'enregistrement du paiement
                $auditeur = Auditeurs::where("email",$paiement->email)->first();

                if ($auditeur) {
                    # code...
                    $contenu = [
                        'nom' => $auditeur->nom,
                        'prenoms' => $auditeur->prenoms,
                        'reference_paiement' => $reference,
                        'subject' => 'INFORMATION SUR VOTRE PAIEMENT '.$reference,
                        'email' => $paiement->email,
                        'montant' => $actuel_montant_paye,
                        'mode_paiement' => $mode_paiement->libelle
                    ];
                } else {
                    # code...
                    $contenu = [
                        'nom' => "",
                        'prenoms' => "",
                        'reference_paiement' => $reference,
                        'subject' => 'INFORMATION SUR VOTRE PAIEMENT '.$reference,
                        'email' => $paiement->email,
                        'montant' => $actuel_montant_paye,
                        'mode_paiement' => $mode_paiement->libelle
                    ];
                }

                try {
                    # code...
                    Mail::to($paiement->email)->cc([env('MAIL_CC')])->bcc(env('MAIL_BCC'))->queue(new Paiementattente($contenu));
                } catch (\Exception $e) {
                    // Get error here
                    Log::info('echec de l\'envoi du mail à l\'auditeur');
                }

                if(env('PAIEMENT_NOTIFIER_COMPTABLE')){

                    $liste_comptable = User::whereHas('roles', function ($q) {
                        $q->where('libelle', 'COMPTABLE');
                    })->pluck('email');

                    $contenu = [
                        'name' => 'COMPTABLE',
                        'reference_paiement' => $reference,
                        'email_auditeur' => $paiement->email,
                        'nom_auditeur' => $auditeur->prenoms.' '.$auditeur->nom,
                        'subject' => 'NOUVEAU PAIEMENT ' . date('d-m-Y H:i:s'),
                        'email' => '',
                        'montant' => $paiement->montant_restant,
                        'mode_paiement' => $mode_paiement->libelle
                    ];
                    try {
                        # code...
                        Mail::to(env('MAIL_CC'))->cc($liste_comptable)->queue(new NotificationComptable($contenu));
                    } catch (\Exception $e) {
                        // Get error here
                    }
                }


                $dataResponse = [
                    'reference' => $paiement->reference_paiement,
                    'success' => true,
                ];

                return  ResponseHandler::success("ok",$dataResponse);


        } catch (\Exception $e) {
            \Log::error('Erreur vérification FedaPay', ['error' => $e->getMessage()]);

            $dataResponse = [
                'reference' => $request->reference_paiement,
                'success' => false,
            ];

            return  ResponseHandler::success("ok",$dataResponse);

        }
    }

    public function storePaiementOuvrageFedapay(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement storePaiementOuvrageFedapay() method.

        # code...
        $reference = $request->reference;
        $montant_paye = $request->montant_payer;
        // récupérer le statut de la transaction côté serveur
        $data_transaction = Transaction::retrieve($request->transaction_id);

        // mise à jour de la table paiement
        $getPaiement = Paiements::whereReference($reference)->first();
        $montant_restant = $getPaiement->montant_restant;
        // mise à jour du montant paye
        $paiement = Paiements::find($getPaiement->id);
        $paiement->setAttribute('num_transaction', $data_transaction->id);
        $paiement->setAttribute('statut_transaction', $data_transaction->status);
        $paiement->setAttribute('updated_at', date('Y-m-d H:i:s'));
        $paiement->setAttribute('id_modepaiement', 2); // FedaPay
        $paiement->update();
        // récupérer le mode de paiement
        $libelle_mode_paiement = Modepaiements::find($paiement->id_modepaiement);

        // envoie de mail pour notification paiement en attente
        // envoie de mail pour notifier l'enregistrement du paiement
        $auditeur = Auditeurs::where("email",$paiement->email)->first();
        if ($auditeur) {
            # code...
            $contenu = [
                'nom' => $auditeur->nom,
                'prenoms' => $auditeur->prenoms,
                'reference_paiement' => $reference,
                'subject' => 'INFORMATION SUR VOTRE PAIEMENT '.$reference,
                'email' => $paiement->email,
                'montant' => $montant_paye,
                'mode_paiement' => $libelle_mode_paiement->libelle
            ];
        } else {
            # code...
            $contenu = [
                'nom' => "",
                'prenoms' => "",
                'reference_paiement' => $reference,
                'subject' => 'INFORMATION SUR VOTRE PAIEMENT '.$reference,
                'email' => $paiement->email,
                'montant' => $montant_paye,
                'mode_paiement' => $libelle_mode_paiement->libelle
            ];
        }
        try {
            # code...
            Mail::to($paiement->email)->cc([env('MAIL_CC')])->bcc(env('MAIL_BCC'))->queue(new Paiementattenteouvrage($contenu));
        } catch (\Exception $e) {
            // Get error here
            Log::info('echec de l\'envoi du mail');
        }

        if(env('PAIEMENT_NOTIFIER_COMPTABLE')){

            $liste_comptable = User::whereHas('roles', function ($q) {
                $q->where('libelle', 'COMPTABLE');
            })->pluck('email');

            $contenu = [
                'name' => 'COMPTABLE',
                'reference_paiement' => $reference,
                'email_auditeur' => $paiement->email,
                'nom_auditeur' => $auditeur->prenoms.' '.$auditeur->nom,
                'subject' => 'NOUVEAU PAIEMENT ' . date('d-m-Y H:i:s'),
                'email' => '',
                'montant' => $montant_paye,
                'mode_paiement' => $libelle_mode_paiement->libelle
            ];
            try {
                # code...
                Mail::to(env('MAIL_CC'))->cc($liste_comptable)->queue(new NotificationPaiementOuvrageStaff($contenu));
            } catch (\Exception $e) {
                // Get error here
            }
        }

        if(env('PAIEMENT_NOTIFIER_DRD')){

            $liste_DRD = User::whereHas('roles', function ($q) {
                $q->where('libelle', 'DOCUMENTATION');
            })->pluck('email');

            $contenu = [
                'name' => 'DOCUMENTATION',
                'reference_paiement' => $reference,
                'email_auditeur' => $paiement->email,
                'nom_auditeur' => $auditeur->prenoms.' '.$auditeur->nom,
                'subject' => 'NOUVEAU PAIEMENT ' . date('d-m-Y H:i:s'),
                'email' => '',
                'montant' => $montant_paye,
                'mode_paiement' => $libelle_mode_paiement->libelle
            ];
            try {
                # code...
                Mail::to(env('MAIL_CC'))->cc($liste_DRD)->queue(new NotificationPaiementOuvrageStaff($contenu));
            } catch (\Exception $e) {
                // Get error here
            }
        }

        $dataResponse = [
            'reference' => $paiement->reference,
            'success' => true,
        ];

        return  ResponseHandler::success("ok",$dataResponse);
    }



    public function storePaiementDscFedaPay(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement storePaiementDscFedaPay() method.
        $transactionId = Transaction::retrieve($request->transaction_id);

        try {

            if($transactionId->status == 'approved' || $transactionId->status == 'APPROVED'){
                $reference = $request->reference;
                $getPaiement = Paiements::where('reference',$reference)->first();
                $paiement = Paiements::find($getPaiement->id);
                $inscrit = Inscriptions::where("code_inscription", $paiement->code_inscription)->orderBy("id", "DESC")->get()->first();
                $getUser = User::where('email',$paiement->email)->first();
                $user = User::find($getUser->id);

                // récupérer le compte formation en lieu et place de validateur
                $userValidateur = User::where("email","formation.ersuma@ohada.org")->first();

                $liste_details = collect($request->input('formations'))->map(fn ($fm) => (object) $fm);

                // update montant table paiement
                $montant_paye = $request->montant_paye;
                $montant_restant = $request->montant_total_du - $montant_paye;
                // 1 update table paiements
                $paiement->setAttribute('montant_total',$request->montant_total_du);
                $paiement->setAttribute('montant_restant',$montant_restant);
                $paiement->setAttribute('montant_paye',$montant_paye);
                if($montant_restant == 0){
                    $paiement->setAttribute('num_transaction', $transactionId->id);
                    $paiement->setAttribute('statut_transaction', $transactionId->status);
                    $paiement->setAttribute('statut_paiement', 'PAYE');
                    $paiement->setAttribute('statut_traitement', 'VALIDER');
                    $paiement->setAttribute('agent_traiteur', $userValidateur->id);
                    $paiement->setAttribute('commentaire', 'Validation automatique webhook Fedapay');
                }
                $paiement->setAttribute('id_modepaiement', 2);
                $paiement->update();
                // 2- update details paiement
                $getPTranche = PaiementsTranche::where("reference","like","%$paiement->reference%")->latest()->first();
                //dd($getPTranche);
                if ($getPTranche) {
                    # code...
                    $ref = explode("_",$getPTranche->reference)[1]+1;
                    $update_reference = $paiement->reference."_".$ref;
                } else {
                    # code...
                    $update_reference = $paiement->reference."_1";
                }

                foreach ($liste_details as $detail){
                    if($detail->type_tarif == 'INSCRIPTION' && $detail->choix && $detail->montant_paye > 0){
                        // paiement effectué pour inscription
                        $detailPaiement = Detailspaiements::find($detail->detail_id);
                        $detailPaiement->setAttribute('montant_paye',$detail->montant_paye);
                        $detailPaiement->setAttribute('montant_restant',$detail->montant_restant-$detail->montant_paye);
                        $detailPaiement->setAttribute('reference_paiement_partiel',$transactionId->id);
                        $detailPaiement->setAttribute('have_reduction_dsc',false);
                        $detailPaiement->setAttribute('statut_paiement',"PAYE");
                        $detailPaiement->setAttribute('statut_traitement',"VALIDER");
                        $detailPaiement->setAttribute("commentaire","Validation automatique webhook FedaPay");
                        $detailPaiement->update();

                        $tranche_paiement = new PaiementsTranche();
                        $tranche_paiement->setAttribute('details_paiements_id',$detailPaiement->id);
                        $tranche_paiement->setAttribute('montant_paye',$detail->montant_paye);
                        $tranche_paiement->setAttribute('montant_restant',$detailPaiement->montant_restant);
                        $tranche_paiement->setAttribute('reference',$update_reference);
                        $tranche_paiement->setAttribute('num_transaction', $transactionId->id);
                        $tranche_paiement->setAttribute('statut_transaction', $transactionId->status);
                        # code...
                        $tranche_paiement->setAttribute('statut_traitement', 'VALIDER');
                        $tranche_paiement->setAttribute('traiter_par',$userValidateur->id);
                        $tranche_paiement->setAttribute('is_inscription',true);
                        $tranche_paiement->setAttribute('modepaiement_id',$paiement->id_modepaiement);
                        $tranche_paiement->setAttribute('created_by',$user->id);
                        $tranche_paiement->setAttribute('updated_by',$user->id);
                        $tranche_paiement->save();

                    }else{
                        if($request->tranche == 'tranche_unique'){ // tranche unique
                            $detailPaiement = Detailspaiements::find($detail->detail_id);
                            // sauvegarde de la tranche
                            $formationTranche = FormationsTranches::where('tranche','tranche 1')
                                ->where('grilletarifaire_id',$detailPaiement->getTarification->getGrilletarifaire->id)
                                ->first();
                            // vérifier si la tranche existe pour update
                            $checkTranche = Tranches::whereCodeInscription($paiement->code_inscription)->exists();

                            if($checkTranche){
                                $getTranche = Tranches::whereCodeInscription($paiement->code_inscription)->first();
                                $tranche = Tranches::find($getTranche->id);
                                $tranche->setAttribute('code_inscription',$paiement->code_inscription);
                                $tranche->setAttribute('email',$paiement->email);
                                //$tranche->setAttribute('tranche_1',$item->getTarification->getGrilletarifaire->montant*0.95);
                                $tranche->setAttribute('tranche_1',$detailPaiement->getTarification->getGrilletarifaire->montant);
                                $tranche->setAttribute('tranche_2',0);
                                $tranche->setAttribute('echeance_1',$formationTranche->echeance_1);
                                $tranche->setAttribute('echeance_2',$formationTranche->echeance_1);
                                $tranche->setAttribute('statut','ATTENTE');
                                $tranche->setAttribute('created_by',$user->id);
                                $tranche->setAttribute('updated_by',$user->id);
                                $tranche->update();
                            }else{
                                $tranche = new Tranches();
                                $tranche->setAttribute('code_inscription',$paiement->code_inscription);
                                $tranche->setAttribute('email',$paiement->email);
                                //$tranche->setAttribute('tranche_1',$item->getTarification->getGrilletarifaire->montant*0.95);
                                $tranche->setAttribute('tranche_1',$detailPaiement->getTarification->getGrilletarifaire->montant);
                                $tranche->setAttribute('tranche_2',0);
                                $tranche->setAttribute('echeance_1',$formationTranche->echeance_1);
                                $tranche->setAttribute('echeance_2',$formationTranche->echeance_1);
                                $tranche->setAttribute('statut','ATTENTE');
                                $tranche->setAttribute('created_by',$user->id);
                                $tranche->setAttribute('updated_by',$user->id);
                                $tranche->save();
                            }
                            // vérifier si paiement enregistré
                            if($detail->choix && $detail->montant_paye > 0){
                                $baseMontantRestant = $detail->montant_restant*0.95;
                                $mtRestant =  $baseMontantRestant - $detail->montant_paye;
                                $detailPaiement->setAttribute('montant_paye',$detail->montant_paye);
                                $detailPaiement->setAttribute('montant_restant',$mtRestant);
                                if($mtRestant == 0){
                                    $detailPaiement->setAttribute('reference_paiement_partiel',$transactionId->id);
                                    $detailPaiement->setAttribute('statut_paiement',"PAYE");
                                    $detailPaiement->setAttribute('statut_traitement',"VALIDER");
                                    $detailPaiement->setAttribute("commentaire","Validation automatique webhook FedaPay");
                                }
                                $detailPaiement->setAttribute('have_reduction_dsc',true);
                                $detailPaiement->update();

                                # code...
                                $tranche_paiement = new PaiementsTranche();
                                $tranche_paiement->setAttribute('details_paiements_id',$detailPaiement->id);
                                $tranche_paiement->setAttribute('montant_paye',$detail->montant_paye);
                                $tranche_paiement->setAttribute('montant_restant',$detailPaiement->montant_restant);
                                $tranche_paiement->setAttribute('reference',$update_reference);
                                $tranche_paiement->setAttribute('num_transaction', $transactionId->id);
                                $tranche_paiement->setAttribute('statut_transaction', $transactionId->status);
                                $tranche_paiement->setAttribute('statut_traitement', 'VALIDER');
                                $tranche_paiement->setAttribute('traiter_par',$userValidateur->id);
                                $tranche_paiement->setAttribute('tranche_id',$tranche->id);
                                $tranche_paiement->setAttribute('modepaiement_id',$paiement->id_modepaiement);
                                $tranche_paiement->setAttribute('created_by',$user->id);
                                $tranche_paiement->setAttribute('updated_by',$user->id);
                                $tranche_paiement->save();

                            }else{
                                $detailPaiement->setAttribute('montant_restant',$detail->montant_restant*0.95);
                                $detailPaiement->setAttribute('have_reduction_dsc',true);
                                $detailPaiement->update();
                            }
                        }else{ // double tranche
                            $detailPaiement = Detailspaiements::find($detail->detail_id);
                            // sauvegarde de la tranche
                            $formationTranche = FormationsTranches::where('tranche','tranche 2')->where('grilletarifaire_id',$detailPaiement->getTarification->getGrilletarifaire->id)->first();

                            // vérifier si la tranche existe pour update
                            $checkTranche = Tranches::whereCodeInscription($paiement->code_inscription)->exists();
                            if($checkTranche){
                                $getTranche = Tranches::whereCodeInscription($paiement->code_inscription)->first();
                                $tranche = Tranches::find($getTranche->id);
                                $tranche->setAttribute('code_inscription',$paiement->code_inscription);
                                $tranche->setAttribute('email',$paiement->email);
                                $tranche->setAttribute('tranche_1',$detailPaiement->getTarification->getGrilletarifaire->montant*0.5);
                                $tranche->setAttribute('tranche_2',$detailPaiement->getTarification->getGrilletarifaire->montant*0.5);
                                $tranche->setAttribute('echeance_1',$formationTranche->echeance_1);
                                $tranche->setAttribute('echeance_2',$formationTranche->echeance_2);
                                $tranche->setAttribute('statut','ATTENTE');
                                $tranche->setAttribute('created_by',$user->id);
                                $tranche->setAttribute('updated_by',$user->id);
                                $tranche->update();
                            }else{
                                $tranche = new Tranches();
                                $tranche->setAttribute('code_inscription',$paiement->code_inscription);
                                $tranche->setAttribute('email',$paiement->email);
                                $tranche->setAttribute('tranche_1',$detailPaiement->getTarification->getGrilletarifaire->montant*0.5);
                                $tranche->setAttribute('tranche_2',$detailPaiement->getTarification->getGrilletarifaire->montant*0.5);
                                $tranche->setAttribute('echeance_1',$formationTranche->echeance_1);
                                $tranche->setAttribute('echeance_2',$formationTranche->echeance_2);
                                $tranche->setAttribute('statut','ATTENTE');
                                $tranche->setAttribute('created_by',$user->id);
                                $tranche->setAttribute('updated_by',$user->id);
                                $tranche->save();
                            }

                            // vérifier si paiement enregistré
                            if($detail->choix && $detail->montant_paye > 0){
                                $mtRestant = $detail->montant_restant - $detail->montant_paye;
                                $detailPaiement->setAttribute('montant_paye',$detail->montant_paye);
                                $detailPaiement->setAttribute('montant_restant',$mtRestant);
                                if($mtRestant == 0){
                                    $detailPaiement->setAttribute('reference_paiement_partiel',$transactionId->id);
                                    $detailPaiement->setAttribute('statut_paiement',"PAYE");
                                    $detailPaiement->setAttribute('statut_traitement',"VALIDER");
                                    $detailPaiement->setAttribute("commentaire","Validation automatique webhook FedaPay");
                                }
                                $detailPaiement->setAttribute('have_reduction_dsc',false);
                                $detailPaiement->update();

                                # code...
                                $tranche_paiement = new PaiementsTranche();
                                $tranche_paiement->setAttribute('details_paiements_id',$detailPaiement->id);
                                $tranche_paiement->setAttribute('montant_paye',$detail->montant_paye);
                                $tranche_paiement->setAttribute('montant_restant',$detailPaiement->montant_restant);
                                $tranche_paiement->setAttribute('reference',$update_reference);
                                $tranche_paiement->setAttribute('num_transaction', $transactionId->id);
                                $tranche_paiement->setAttribute('statut_transaction', $transactionId->status);
                                $tranche_paiement->setAttribute('statut_traitement', 'VALIDER');
                                $tranche_paiement->setAttribute('traiter_par',$userValidateur->id);
                                $tranche_paiement->setAttribute('tranche_id',$tranche->id);
                                $tranche_paiement->setAttribute('modepaiement_id',$paiement->id_modepaiement);
                                $tranche_paiement->setAttribute('created_by',$user->id);
                                $tranche_paiement->setAttribute('updated_by',$user->id);
                                $tranche_paiement->save();

                            }else{
                                $detailPaiement->setAttribute('montant_restant',$detail->montant_restant);
                                $detailPaiement->setAttribute('have_reduction_dsc',false);
                                $detailPaiement->update();
                            }
                        }
                    }
                }


                // envoie de mail pour notification paiement en attente
                // envoie de mail pour notifier l'enregistrement du paiement
                $auditeur = Auditeurs::where("email",$paiement->email)->first();

                $libelle_mode_paiement = Modepaiements::find($paiement->id_modepaiement);

                if ($auditeur) {
                    # code...
                    $contenu = [
                        'nom' => $auditeur->nom,
                        'prenoms' => $auditeur->prenoms,
                        'reference_paiement' => $update_reference,
                        'subject' => 'INFORMATION SUR VOTRE PAIEMENT '.$update_reference,
                        'email' => $paiement->email,
                        'montant' => $request->montant_paye,
                        'montant_restant' => $paiement->montant_restant,
                        'mode_paiement' => $libelle_mode_paiement->libelle
                    ];
                } else {
                    # code...
                    $contenu = [
                        'nom' => "",
                        'prenoms' => "",
                        'reference_paiement' => $update_reference,
                        'montant' => $request->montant_paye,
                        'montant_restant' => $paiement->montant_restant,
                        'subject' => 'INFORMATION SUR VOTRE PAIEMENT '.$update_reference,
                        'email' => $paiement->email,
                        'mode_paiement' => $libelle_mode_paiement->libelle
                    ];
                }

                try {
                    # code...
                    Mail::to($paiement->email)->cc([env('MAIL_CC')])->bcc(env('MAIL_BCC'))->queue(new DscPaiementAttente($contenu));
                } catch (\Exception $e) {
                    // Get error here

                }

                // Notification au comptable
                // send mail comptable
                $liste_comptable = User::whereHas('roles', function ($q) {
                    $q->where('libelle', 'COMPTABLE');
                })->pluck('email');

                if (env("PAIEMENT_NOTIFIER_COMPTABLE")) {
                    # code...
                    $contenu = [
                        'name' => 'COMPTABLE',
                        'reference_paiement' => $update_reference,
                        'email_auditeur' => $paiement->email,
                        'nom_auditeur' => $auditeur->prenoms.' '.$auditeur->nom,
                        'subject' => 'NOUVEAU PAIEMENT ' . date('d-m-Y H:i:s'),
                        'email' => '',
                        'montant' => $request->montant_paye,
                        'mode_paiement' => $libelle_mode_paiement->libelle
                    ];

                    try {
                        # code...
                        Mail::to(env('MAIL_CC'))->cc($liste_comptable)->queue(new DscNotificationComptable($contenu));
                    } catch (\Exception $e) {
                        // Get error here

                    }
                }

                // vérifier si le statut est succès pour envoyer le mail de notification traitement paiement
                # code...
                $contenuTraitement = [
                    'nom' => $auditeur->nom ,
                    'prenoms' => $auditeur->prenoms,
                    'reference_paiement' => $update_reference,
                    'subject' => 'TRAITEMENT DE VOTRE PAIEMENT '.$update_reference,
                    'commentaire' => "Paiement validé",
                    'email'=>$paiement->email,
                    'mode_paiement'=>$libelle_mode_paiement->libelle,
                    'montant'=>$request->montant_paye,
                    'montant_restant'=>$paiement->montant_restant,
                    'type'=>$paiement->type,
                    'url_recu'=>$this->genarateurCodeService->generateUrlSigned('paiements.recu',['reference'=>$update_reference])
                ];

                try {
                    # code...
                    Mail::to(trim($paiement->email))->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new PaiementValideDsc($contenuTraitement));

                } catch (\Exception $e) {
                    //throw $th;
                }


                $dataResponse = [
                    'reference' => $request->reference,
                    'success' => true,
                ];
                return ResponseHandler::success("ok", $dataResponse);

            }else{
                $dataResponse = [
                    'reference' => $request->reference,
                    'success' => false,
                ];
                return ResponseHandler::success("paiement invalide", $dataResponse);
            }

        } catch (\Exception $e) {
            \Log::error('Erreur vérification FedaPay', ['error' => $e->getMessage()]);

            $dataResponse = [
                'reference' => $request->reference,
                'success' => false,
            ];

            return ResponseHandler::success("ok", $dataResponse);

        }
    }
}

