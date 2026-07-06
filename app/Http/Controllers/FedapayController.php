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
use App\Services\contracts\FedapayInterfarce;
use App\Services\util\GenarateurCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use FedaPay\FedaPay;

class FedapayController extends Controller
{
    private FedapayInterfarce $fedapayInterferce;
    private GenarateurCodeService $genarateurCodeService;

    public function __construct(FedapayInterfarce $fedapayInterferce, GenarateurCodeService $genarateurCodeService)
    {
        // $this->middleware('auth:api',['except' => ['webhook']]);
        $this->fedapayInterferce = $fedapayInterferce;
        $this->genarateurCodeService = $genarateurCodeService;
    }

    public function savePaiement(Request $request){
        try {
            $validated = $request->validate([
                'reference' => 'required|string',
                'transaction_id' => 'required|string'
            ]);

            return $data = $this->fedapayInterferce->storePaiementFedapay($request);

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

            return $data = $this->fedapayInterferce->storePaiementPartielFedapay($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function savePaiementOuvrage(Request $request){
        try {
            $validated = $request->validate([
                'reference' => 'required|string',
                'transaction_id' => 'required|string'
            ]);

            return $data = $this->fedapayInterferce->storePaiementOuvrageFedapay($request);

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

            return $data = $this->fedapayInterferce->storePaiementDscFedaPay($request);

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

            return $data = $this->fedapayInterferce->storePaiementPartielDscFedaPay($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

     // webhook
    public function webhook(Request $request)
    { 
        // You can find your endpoint's secret key in your webhook settings
        $endpoint_secret = env("FEDAPAY_WEBHOOK");
        //$data = $request->name;
        //	return response()->json(["data"=>$data],200) ;

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_X_FEDAPAY_SIGNATURE'];
        $event = null;

        try {
            $event = \FedaPay\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload 
            return response()->json(["data" => null, "statut" => "Invalid payload"], 200);
            exit();
        } catch (\FedaPay\Error\SignatureVerification $e) {
            // Invalid signature

            return response()->json(["data" => null, "statut" => "Invalid payload"], 200);
            exit();
        }

        // Handle the event
        switch ($event->name) {
            case 'transaction.created':
                // Transaction créée
                return response()->json(["data" => $event->entity->id, "statut" => "created"], 200);
                break;
            case 'transaction.approved':
                // Transaction approuvée
                Log::info('Webhook fedapay received ', ['request' => $event]); 

                $get_reference = $event->entity->description;
                
                // check if reference exists
                $reference = explode("_",$get_reference)[0];
                
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
                                return response()->json(["data" => $event, "statut" => "approved"], 200);
                                break;
                            }
                            # code...
                            $updatePaiement->setAttribute("num_transaction",$event->entity->id);
                            $updatePaiement->setAttribute("statut_transaction",trim($event->entity->status));
                            $updatePaiement->setAttribute("id_modepaiement",2); // FedaPay
                            $updatePaiement->setAttribute("statut_paiement","PAYE");
                            $updatePaiement->setAttribute("statut_traitement","VALIDER");
                            $updatePaiement->setAttribute("commentaire","Validation automatique webhook FedaPay");
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

                            if ($paiementTranche->statut_traitement != "VALIDER") {
                                # code...
                                // update each table $item->getTarification->getGrilletarifaire->type_tarif == 'INSCRIPTION'
                                $paiementTranche->setAttribute("num_transaction",$event->entity->id);
                                $paiementTranche->setAttribute("statut_transaction",trim($event->entity->status));
                                $paiementTranche->setAttribute("modepaiement_id",2); // FedaPay
                                $paiementTranche->setAttribute("statut_traitement","VALIDER");
                                $paiementTranche->setAttribute('traiter_par', $user->id);
                                $paiementTranche->update();
                                // update details paiements
                                // if rest amount equal to 0, do update
                                if ($detailPaiementTranche->montant_restant == 0) {
                                    # code...
                                    $detailPaiementTranche->setAttribute('statut_traitement',"VALIDER");
                                    $detailPaiementTranche->setAttribute('statut_paiement', "PAYE"); 
                                    $detailPaiementTranche->setAttribute("commentaire","Validation automatique webhook FedaPay");
                                    $detailPaiementTranche->update();
                                }
                                // update etat 
                                $checkValidation = false;

                            }
                            

                        }

                        if ($checkValidation) {
                            # code...
                            return response()->json(["data" => $event, "statut" => "approved"], 200);
                            break;
                        }

                         // send mail paiement validé 
                        $auditeur = Auditeurs::where("email",$paiement->email)->first();
                        // envoie de mail pour la reférence de la facture
                        $modepaiement = Modepaiements::find(2); // FedaPay
                        $contenu = [
                            'nom' => $auditeur->nom ,
                            'prenoms' => $auditeur->prenoms,
                            'reference_paiement' => $get_reference,
                            'subject' => 'TRAITEMENT DE VOTRE PAIEMENT '.$get_reference,
                            'commentaire' => "Paiement validé",
                            'email'=>$paiement->email,
                            'mode_paiement'=>$modepaiement->libelle,
                            'montant'=>$event->entity->amount, 
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
                        $checkPaiementPartiel = Detailspaiements::where("reference_paiement_partiel",$event->entity->id)->where("reference_paiement",$paiement->reference)->exists();
                        if ($checkPaiementPartiel) {
                            # code... // il s'agit d'un paiement partiel
                            $dtPaiementPartiel = Detailspaiements::where("reference_paiement_partiel",$event->entity->id)->where("reference_paiement",$paiement->reference)->first();
                            $getDetailPaiement = Detailspaiements::find($dtPaiementPartiel->id);
                            $montant_paye = getMontantFormation($getDetailPaiement->id);
                            // ! (vérifier si tous les autres occurences disponibles dans la table détailspaiements sont validées)
                            $checkPaiementNonSolde = Detailspaiements::where("id","!=",$dtPaiementPartiel->id)
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
                                $getDetailPaiement->setAttribute("commentaire","Validation automatique webhook FedaPay");
                                $getDetailPaiement->update();

                                // si facture soldée update paiements
                                if (!$checkPaiementNonSolde) {
                                    # code...
                                    if ($paiement->montant_restant == 0) {
                                        $paiement->setAttribute("num_transaction",$event->entity->id);
                                        $paiement->setAttribute("statut_transaction",trim($event->entity->status));
                                        $paiement->setAttribute("id_modepaiement",2); // FedaPay
                                        $paiement->setAttribute("statut_paiement","PAYE");
                                        $paiement->setAttribute("statut_traitement","VALIDER"); 
                                        $paiement->setAttribute("commentaire","Validation automatique webhook FedaPay");
                                        $paiement->setAttribute('agent_traiteur', $user->id);
                                        $paiement->setAttribute('date_traitement', new \DateTime());
                                        $paiement->update();
                                    }
                                } 
                                $checkValidation = false;
                            }

                           if ($checkValidation) { // paiement déjà validé
                            # code...
                                return response()->json(["data" => $event, "statut" => "approved"], 200);
                                break;
                            }

                             // send mail paiement validé 
                            $auditeur = Auditeurs::where("email",$paiement->email)->first();
                            // envoie de mail pour la reférence de la facture
                            $modepaiement = Modepaiements::find(2);
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
                                return response()->json(["data" => $event, "statut" => "approved"], 200);
                                break;
                            }
 
                                $paiement->setAttribute('num_transaction', $event->entity->id);
                                $paiement->setAttribute('statut_transaction', $event->entity->status);   
                                $paiement->setAttribute("statut_paiement","PAYE");
                                $paiement->setAttribute("montant_paye",$paiement->montant_restant);
                                $paiement->setAttribute("montant_restant",0);
                                $paiement->setAttribute("statut_traitement","VALIDER");
                                $paiement->setAttribute("commentaire","Validation automatique webhook FedaPay");
                                $paiement->setAttribute("id_modepaiement",2); // FedaPay 
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
                                    $detail->setAttribute("commentaire","Validation automatique webhook Fedapay");
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

                return response()->json(["data" => $event, "statut" => "approved"], 200);
                break; 
            
            case 'transaction.transferred':
                // Transaction approuvée
                Log::info('Webhook fedapay received ', ['request' => $event]); 

                $get_reference = $event->entity->description;
                
                // check if reference exists
                $reference = explode("_",$get_reference)[0];
                
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
                                return response()->json(["data" => $event, "statut" => "approved"], 200);
                                break;
                            }
                            # code...
                            $updatePaiement->setAttribute("num_transaction",$event->entity->id);
                            $updatePaiement->setAttribute("statut_transaction",trim($event->entity->status));
                            $updatePaiement->setAttribute("id_modepaiement",2); // FedaPay
                            $updatePaiement->setAttribute("statut_paiement","PAYE");
                            $updatePaiement->setAttribute("statut_traitement","VALIDER");
                            $updatePaiement->setAttribute("commentaire","Validation automatique webhook FedaPay");
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

                            if ($paiementTranche->statut_traitement != "VALIDER") {
                                # code...
                                // update each table $item->getTarification->getGrilletarifaire->type_tarif == 'INSCRIPTION'
                                $paiementTranche->setAttribute("num_transaction",$event->entity->id);
                                $paiementTranche->setAttribute("statut_transaction",trim($event->entity->status));
                                $paiementTranche->setAttribute("modepaiement_id",2); // FedaPay
                                $paiementTranche->setAttribute("statut_traitement","VALIDER");
                                $paiementTranche->setAttribute('traiter_par', $user->id);
                                $paiementTranche->update();
                                // update details paiements
                                // if rest amount equal to 0, do update
                                if ($detailPaiementTranche->montant_restant == 0) {
                                    # code...
                                    $detailPaiementTranche->setAttribute('statut_traitement',"VALIDER");
                                    $detailPaiementTranche->setAttribute('statut_paiement', "PAYE"); 
                                    $detailPaiementTranche->setAttribute("commentaire","Validation automatique webhook FedaPay");
                                    $detailPaiementTranche->update();
                                }
                                // update etat 
                                $checkValidation = false;

                            }
                            

                        }

                        if ($checkValidation) {
                            # code...
                            return response()->json(["data" => $event, "statut" => "approved"], 200);
                            break;
                        }

                         // send mail paiement validé 
                        $auditeur = Auditeurs::where("email",$paiement->email)->first();
                        // envoie de mail pour la reférence de la facture
                        $modepaiement = Modepaiements::find(2); // FedaPay
                        $contenu = [
                            'nom' => $auditeur->nom ,
                            'prenoms' => $auditeur->prenoms,
                            'reference_paiement' => $get_reference,
                            'subject' => 'TRAITEMENT DE VOTRE PAIEMENT '.$get_reference,
                            'commentaire' => "Paiement validé",
                            'email'=>$paiement->email,
                            'mode_paiement'=>$modepaiement->libelle,
                            'montant'=>$event->entity->amount, 
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
                        $checkPaiementPartiel = Detailspaiements::where("reference_paiement_partiel",$event->entity->id)->where("reference_paiement",$paiement->reference)->exists();
                        if ($checkPaiementPartiel) {
                            # code... // il s'agit d'un paiement partiel
                            $dtPaiementPartiel = Detailspaiements::where("reference_paiement_partiel",$event->entity->id)->where("reference_paiement",$paiement->reference)->first();
                            $getDetailPaiement = Detailspaiements::find($dtPaiementPartiel->id);
                            $montant_paye = getMontantFormation($getDetailPaiement->id);
                            // ! (vérifier si tous les autres occurences disponibles dans la table détailspaiements sont validées)
                            $checkPaiementNonSolde = Detailspaiements::where("id","!=",$dtPaiementPartiel->id)
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
                                $getDetailPaiement->setAttribute("commentaire","Validation automatique webhook FedaPay");
                                $getDetailPaiement->update();

                                // si facture soldée update paiements
                                if (!$checkPaiementNonSolde) {
                                    # code...
                                    if ($paiement->montant_restant == 0) {
                                        $paiement->setAttribute("num_transaction",$event->entity->id);
                                        $paiement->setAttribute("statut_transaction",trim($event->entity->status));
                                        $paiement->setAttribute("id_modepaiement",2); // FedaPay
                                        $paiement->setAttribute("statut_paiement","PAYE");
                                        $paiement->setAttribute("statut_traitement","VALIDER"); 
                                        $paiement->setAttribute("commentaire","Validation automatique webhook FedaPay");
                                        $paiement->setAttribute('agent_traiteur', $user->id);
                                        $paiement->setAttribute('date_traitement', new \DateTime());
                                        $paiement->update();
                                    }
                                } 
                                $checkValidation = false;
                            }

                           if ($checkValidation) { // paiement déjà validé
                            # code...
                                return response()->json(["data" => $event, "statut" => "approved"], 200);
                                break;
                            }

                             // send mail paiement validé 
                            $auditeur = Auditeurs::where("email",$paiement->email)->first();
                            // envoie de mail pour la reférence de la facture
                            $modepaiement = Modepaiements::find(2);
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
                                return response()->json(["data" => $event, "statut" => "approved"], 200);
                                break;
                            }
 
                                $paiement->setAttribute('num_transaction', $event->entity->id);
                                $paiement->setAttribute('statut_transaction', $event->entity->status);   
                                $paiement->setAttribute("statut_paiement","PAYE");
                                $paiement->setAttribute("montant_paye",$paiement->montant_restant);
                                $paiement->setAttribute("montant_restant",0);
                                $paiement->setAttribute("statut_traitement","VALIDER");
                                $paiement->setAttribute("commentaire","Validation automatique webhook FedaPay");
                                $paiement->setAttribute("id_modepaiement",2); // FedaPay 
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
                                    $detail->setAttribute("commentaire","Validation automatique webhook Fedapay");
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

                return response()->json(["data" => $event, "statut" => "approved"], 200);
                break;

            case 'transaction.canceled':
                // Transaction annulée
                return response()->json(["data" => $event, "statut" => "annulée"], 200);
                break;
            default:
                return response()->json(["data" => $event, "statut" => "approved"], 200);
                exit();
        }

        return response()->json(["data" => null, "statut" => "invalide statut"], 200);

    }

}
