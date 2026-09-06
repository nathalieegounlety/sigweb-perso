<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Mail\PaiementValideDsc;
use App\Mail\Paiementvalider;
use App\Models\Auditeurs;
use App\Models\Detailspaiements;
use App\Models\Modepaiements;
use App\Models\Paiements;
use App\Models\PaiementsTranche;
use App\Models\User;
use App\Services\contracts\KkiapayInterfarce;
use App\Services\util\GenarateurCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class KkiapayController extends Controller
{
    private KkiapayInterfarce $kkiapayservice;
    private GenarateurCodeService $genarateurCodeService;

    public function __construct(KkiapayInterfarce $kkiapayservice, GenarateurCodeService $genarateurCodeService)
    {
        $this->kkiapayservice = $kkiapayservice;
        $this->genarateurCodeService = $genarateurCodeService;
    }

    public function savePaiement(Request $request){
        try {
            $validated = $request->validate([
                'transaction_id' => 'required|string'
            ]);

            return $data = $this->kkiapayservice->storePaiementKkiapay($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function savePaiementPartiel(Request $request){
        try {
            $validated = $request->validate([
                'transaction_id' => 'required|string',
                'formations' => 'required',
                'montant_total' => 'required',
                'mode_paiement' => 'required',
                'reference_paiement' => 'required'
            ]);

            return $data = $this->kkiapayservice->storePaiementPartielKkiapay($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function savePaiementOuvrage(Request $request){
        try {
            $validated = $request->validate([
                'transaction_id' => 'required|string'
            ]);

            return $data = $this->kkiapayservice->storePaiementOuvrageKkiapay($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function savePaiementDsc(Request $request){
        try {
            $validated = $request->validate([
                'transaction_id' => 'required|string'
            ]);

            return $data = $this->kkiapayservice->storePaiementDscKkiapay($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function savePaiementPartielDsc(Request $request){
        try {   
            $validated = $request->validate([
                'transaction_id' => 'required|string'
            ]);

            return $data = $this->kkiapayservice->storePaiementPartielDscKkiapay($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function webhook(Request $request){ 
        try {      

            return $data = $this->kkiapayservice->startWebhook($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            //return ResponseHandler::error("Erreur de validation", 422, $e->errors());
            return response()->json(['status' => 'error webook'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error webook'], 200);
            //return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function webhookOld(Request $request)
    {  
        // Répondez à Kkiapay avec un statut HTTP 200 OK 
		// Capture et traiter les données de la requête du webhook
            $data = $request->all();
            Log::info('webhook kkiapay received', ['request' => $data]);  
                
            if($data['isPaymentSucces'] == true){
                // vérifier si le statut de paiement est true
                $referenceTransaction = $data['stateData'];

                $transactionId =$data['transactionId'];
                $montant_paye = $data['amount'];
                $get_reference = $referenceTransaction;

                // check if reference exists
                $reference = explode("_",$get_reference)[0];

                //$paiement = Paiement::where('reference', $transactionId)->first();

                //########################## start process validation ############################################

                
                // récupérer le compte formation en lieu et place de validateur
                $user = User::where("email","formation.ersuma@ohada.org")->first(); 

                $check_paiement = Paiements::where("reference", $reference)->exists();

                if ($check_paiement) {

                    $paiement = Paiements::where("reference", $reference)->first();

                    if ($paiement->type == "DSC") {
                        # code...
                        // Process validation des paiements DSC
                         // récupérer la référence de la table paiement
                        $getOneTranchePaiement = PaiementsTranche::where('reference',$get_reference)->first();                            
                        $getOneDtPaiement = Detailspaiements::find($getOneTranchePaiement->details_paiements_id);
                        $getPaiement = Paiements::where('reference',$getOneDtPaiement->reference_paiement)->first();
                        $updatePaiement = Paiements::find($getPaiement->id);

                         // update statut paiement si montant restant = 0
                        if ($updatePaiement->montant_restant == 0) {
                            // si la validation est déjà faite on fait simplement une redirection de sortie                            
                            if ($updatePaiement->statut_traitement == "VALIDER") {
                                # code...
                                // Répondez à Kkiapay avec un statut HTTP 200 OK
                                return response()->json(['status' => 'success'], 200); 
                            }
                            # code...
                            $updatePaiement->setAttribute("num_transaction",$transactionId);
                            $updatePaiement->setAttribute("statut_transaction",$transactionId);
                            $updatePaiement->setAttribute("id_modepaiement",1); // Kkiapay
                            $updatePaiement->setAttribute("statut_paiement","PAYE");
                            $updatePaiement->setAttribute("statut_traitement","VALIDER");
                            $updatePaiement->setAttribute("commentaire","Validation automatique webhook KkiaPay");
                            $updatePaiement->setAttribute('agent_traiteur', $user->id);
                            $updatePaiement->setAttribute('date_traitement', new \DateTime());
                            $updatePaiement->update();
                        }
                        # code...
                        // variable pour vérifier si la validation est faite déjà
                        $checkValidation = true;
                        // update table paiement tranche
                        $getAllPaiementTranche = PaiementsTranche::where('reference',$get_reference)->get();

                        foreach ($getAllPaiementTranche as $key => $data) {
                            # code...
                            $paiementTranche = PaiementsTranche::find($data->id);
                            $detailPaiementTranche = Detailspaiements::find($paiementTranche->details_paiements_id);
                            Log::info('paiement tranche statut before', ['request' => $paiementTranche->statut_traitement]);  
                            if ($paiementTranche->statut_traitement != "VALIDER") {
                                # code...
                                // update each table $item->getTarification->getGrilletarifaire->type_tarif == 'INSCRIPTION'
                                $paiementTranche->setAttribute("num_transaction",$transactionId);
                                $paiementTranche->setAttribute("statut_transaction",$transactionId);
                                $paiementTranche->setAttribute("modepaiement_id",1); // Kkiapay
                                $paiementTranche->setAttribute("statut_traitement","VALIDER");
                                $paiementTranche->setAttribute('traiter_par', $user->id);
                                $paiementTranche->update();
                                // update details paiements
                                // if rest amount equal to 0, do update
                                if ($detailPaiementTranche->montant_restant == 0) {
                                    $detailPaiementTranche->setAttribute('statut_traitement',"VALIDER");
                                    $detailPaiementTranche->setAttribute('statut_paiement', "PAYE");
                                    $detailPaiementTranche->setAttribute("commentaire","Validation automatique webhook KkiaPay");
                                    $detailPaiementTranche->update();
                                }
                                // update etat 
                                $checkValidation = false;

                            }
                            

                        }

                        if ($checkValidation) {
                            # code...
                            // Répondez à Kkiapay avec un statut HTTP 200 OK
                            return response()->json(['status' => 'success'], 200);

                        }

                         // send mail paiement validé 
                        $auditeur = Auditeurs::where("email",$paiement->email)->first();
                        // envoie de mail pour la reférence de la facture
                        $modepaiement = Modepaiements::find(1); // FedaPay
                        $contenu = [
                            'nom' => $auditeur->nom ,
                            'prenoms' => $auditeur->prenoms,
                            'reference_paiement' => $get_reference,
                            'subject' => 'TRAITEMENT DE VOTRE PAIEMENT',
                            'commentaire' => "Paiement validé",
                            'email'=>$paiement->email,
                            'mode_paiement'=>$modepaiement->libelle,
                            'montant'=>$montant_paye, 
                            'montant_restant'=>$paiement->montant_restant, 
                            'type'=>$paiement->type,
                            'url_recu'=> $this->genarateurCodeService->generateUrlSigned('paiements.recu',['reference'=>$get_reference])
                        ];

                        try {
                            # code... 
                            Mail::to(trim($paiement->email))->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new PaiementValideDsc($contenu));

                        } catch (\Exception $e) {
                            //throw $th;
                        }
 

                    } else { // IL S'AGIT DE FORMATION CLASSIQUE ET NON FORMATION DSC

                        // vérifier s'il s'agit d'un paiement partiel
                        $checkPaiementPartiel = Detailspaiements::where("reference_paiement_partiel",$transactionId)->where("reference_paiement",$paiement->reference)->exists();
                        if ($checkPaiementPartiel) {
                            # code... // il s'agit d'un paiement partiel
                             $dtPaiementPariel = Detailspaiements::where("reference_paiement_partiel",$transactionId)->where("reference_paiement",$paiement->reference)->first();
                             $getDetailPaiement = Detailspaiements::find($dtPaiementPariel->id);
                            $montant_paye = getMontantFormation($getDetailPaiement->id);
                            // ! (vérifier si tous les autres occurences disponibles dans la table détailspaiements sont validées)
                            $checkPaiementNonSolde = Detailspaiements::where("id","!=",$dtPaiementPariel->id)
                            ->where("reference_paiement",$paiement->reference)
                            ->where("statut_traitement","ATTENTE")
                            ->exists();
                            $checkValidation = true;
                            if ($getDetailPaiement->statut_traitement != "VALIDER") {
                                # code...
                                 // update detailspaiements
                                $getDetailPaiement->setAttribute('statut_traitement',"VALIDER");
                                $getDetailPaiement->setAttribute('statut_paiement', "PAYE");
                                $getDetailPaiement->setAttribute("montant_paye",$getDetailPaiement->montant_restant);
                                $getDetailPaiement->setAttribute("montant_restant",0);
                                $getDetailPaiement->setAttribute("commentaire","Validation automatique webhook KkiaPay");
                                $getDetailPaiement->update();

                                // si facture soldée update paiements
                                if (!$checkPaiementNonSolde) {
                                    # code...
                                    if ($paiement->montant_restant == 0) {
                                        $paiement->setAttribute("num_transaction",$transactionId);
                                        $paiement->setAttribute("statut_transaction",$transactionId);
                                        $paiement->setAttribute("id_modepaiement",1); // Kkiapay
                                        $paiement->setAttribute("statut_paiement","PAYE");
                                        $paiement->setAttribute("statut_traitement","VALIDER"); 
                                        $paiement->setAttribute("commentaire","Validation automatique webhook KkiaPay");
                                        $paiement->setAttribute('agent_traiteur', $user->id);
                                        $paiement->setAttribute('date_traitement', new \DateTime());
                                        $paiement->update();
                                    }
                                } 
                                $checkValidation = false;
                            }

                           if ($checkValidation) { // paiement déjà validé
                            # code...
                                // Répondez à Kkiapay avec un statut HTTP 200 OK
                                return response()->json(['status' => 'success'], 200);
                            }

                             // send mail paiement validé 
                            $auditeur = Auditeurs::where("email",$paiement->email)->first();
                            // envoie de mail pour la reférence de la facture
                            $modepaiement = Modepaiements::find(1);
                            $contenu = [
                                'nom' => $auditeur->nom ,
                                'prenoms' => $auditeur->prenoms,
                                'reference_paiement' => $get_reference,
                                'subject' => 'TRAITEMENT DE VOTRE PAIEMENT '.$get_reference,
                                'commentaire' => "Paiement validé",
                                'email'=>$paiement->email,
                                'mode_paiement'=>$modepaiement->libelle,
                                'montant'=>$montant_paye, 
                                'type'=>$paiement->type,
                                'url_recu'=> $this->genarateurCodeService->generateUrlSigned('paiements.recu',['reference'=>$get_reference])
                            ];

                            try {
                                # code... 
                                Mail::to(trim($paiement->email))->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new Paiementvalider($contenu));

                            } catch (\Exception $e) {
                                //throw $th;
                            }

                        } else {
                            # code...  // ce n'est pas un paiement partiel

                            if ($paiement->statut_traitement == "VALIDER") {
                                # code...
                               // Répondez à Kkiapay avec un statut HTTP 200 OK
                                return response()->json(['status' => 'success'], 200);
                            }
 
                                $paiement->setAttribute('num_transaction', $transactionId);
                                $paiement->setAttribute('statut_transaction', $transactionId);   
                                $paiement->setAttribute("statut_paiement","PAYE");
                                $paiement->setAttribute("montant_paye",$paiement->montant_restant);
                                $paiement->setAttribute("montant_restant",0);
                                $paiement->setAttribute("statut_traitement","VALIDER");
                                $paiement->setAttribute("commentaire","Validation automatique webhook KkiaPay");
                                $paiement->setAttribute("id_modepaiement",1); // Kkiapay 
                                $paiement->setAttribute('agent_traiteur', $user->id);
                                $paiement->setAttribute('date_traitement', new \DateTime());
                                $paiement->update();

                                // send mail paiement validé
                            
                                if ($paiement->type != "OUVRAGE") {
                                    # code...
                                    // update table details paiement
                                $details_paiement = Detailspaiements::where('reference_paiement',$paiement->reference)->get();

                                foreach($details_paiement as $detail){
                                    $detail->setAttribute('statut_traitement',"VALIDER");
                                    $detail->setAttribute('statut_paiement', "PAYE");
                                    $detail->setAttribute("montant_paye",$detail->montant_restant);
                                    $detail->setAttribute("montant_restant",0);
                                    $detail->setAttribute("commentaire","Validation automatique webhook KkiaPay");
                                    $detail->update();
                                }

                                }
                                $auditeur = Auditeurs::where("email",$paiement->email)->first();
                                // envoie de mail pour la reférence de la facture
                                $modepaiement = Modepaiements::find($paiement->id_modepaiement);
                                $nom = $auditeur->nom ?? '';
                                $prenoms = $auditeur->prenoms ?? '';
                                if($paiement->type !== "OUVRAGE"){
                                        $url = $this->genarateurCodeService->generateUrlSigned('paiements.recu',['reference'=>$paiement->reference]);
                                    }else{
                                        $url = $this->genarateurCodeService->generateUrlSigned('paiements.recu-ouvrage',['reference'=>$paiement->reference]);
                                    }
                                $contenu = [
                                    'nom' => $nom,
                                    'prenoms' => $prenoms,
                                    'reference_paiement' => $paiement->reference,
                                    'subject' => 'TRAITEMENT DE VOTRE PAIEMENT '.$paiement->reference,
                                    'commentaire' => "Paiement validé",
                                    'email'=>$paiement->email,
                                    'mode_paiement'=>$modepaiement->libelle,
                                    'montant'=>$paiement->montant_total, 
                                    'type'=>$paiement->type,
                                    'url_recu'=> $url
                                ];

                                try {
                                    # code... 
                                    Mail::to(trim($paiement->email))->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new Paiementvalider($contenu));

                                } catch (\Exception $e) {
                                    //throw $th;
                                }
                        }
                        
                        # code...
                        
                        // validation automatique du paiement
                        // vérifier 
                    }
                    
                    
                } 

                //########################## end process validation ##############################################

            } 

            // Log::info('Webhook received TEST', ['request' => $data]);  

            // Répondez à Kkiapay avec un statut HTTP 200 OK
            return response()->json(['status' => 'success'], 200);
    }
}
