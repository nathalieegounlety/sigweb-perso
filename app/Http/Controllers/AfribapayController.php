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
use App\Services\contracts\AfribapayInterface;
use App\Services\util\GenarateurCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AfribapayController extends Controller
{
     private AfribapayInterface $afribapayService;
    private GenarateurCodeService $genarateurCodeService;

    public function __construct(AfribapayInterface $afribapayService, GenarateurCodeService $genarateurCodeService)
    {
        // restriction des accès
        $this->middleware('auth:api',['except' => ['generateToken','getListeVar','sendPayIn','checkPayIn','webhook','savePaiementPartiel']]);
        $this->afribapayService = $afribapayService;
        $this->genarateurCodeService = $genarateurCodeService;

    }

    
    /**
     * Store a newly created resource in storage.
     */
    public function geToken(Request $request)
    {
        try { 
            // procéder à l'enregistrement
             $token = $this->afribapayService->getToken();
             return ResponseHandler::success("ok",$token);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function generateToken()
    {
        try { 
            // procéder à l'enregistrement
             $token = $this->afribapayService->generateToken();
             return ResponseHandler::success("ok",$token);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function getHeaders()
    {
        return [
            'Authorization' => 'Bearer '.$this->getToken(),
            'Accept' => 'application/json'
        ];
    }

    public function requestAfribapay(string $method,string $url,array $data = [])
    {
        $response = Http::withHeaders(
            $this->getHeaders()
        )->$method($url, $data);

        if ($response->status() == 401) {

            Cache::forget('afribapay_token');

            $response = Http::withHeaders([
                'Authorization' =>
                    'Bearer '.$this->generateToken()
            ])->$method($url, $data);
        }

        return $response;
    }

    public function getListeVar(Request $request){
        try { 

            return $data = $this->afribapayService->listeVariables();

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function sendPayIn(Request $request){
        try { 

            return $data = $this->afribapayService->payIn($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function checkPayIn(Request $request, $id){
        try { 

            return $data = $this->afribapayService->checkPayIn($request, $id);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function afribapay_sign($json, $api_key) {
        $hash = hash_hmac('sha256', $json, $api_key);
        return $hash;
    }

    public function webhook(Request $request){
       
        // You can find your endpoint's secret key in your webhook settings
        $api_key = env("AFRIBAPAY_SECRET_API_KEY"); 

        $payload = @file_get_contents('php://input'); 

        // Decode the JSON payload into a PHP associative array
        $dataAP = json_decode($payload, true);
        Log::info($dataAP);
        // Check if the payload was successfully decoded
        if ($dataAP === null) {
            // Handle the error if the JSON could not be decoded
            //http_response_code(400); // Respond with a 400 Bad Request status
            //echo "Invalid JSON received."; // Output error message
            Log::info("Invalid JSON received.");
            return response()->json(["data" => null, "statut" => "Invalid JSON received."], 200);
            exit; // Stop further execution
        }
        // START Signature Verification
        // Retrieve AfribaPAY signature from headers
        $afribaPaySignature = $_SERVER['HTTP_AFRIBAPAY_SIGN'] ?? null;  
        // Sign the payload using your api_key
        $computedSignature = $this->afribapay_sign($payload, $api_key);  

        //Log::info($computedSignature);

        // Compare the signature received with the computed one
        if (hash_equals($computedSignature, $afribaPaySignature)) {
            // Signature is valid, meaning the webhook is from AfribaPAY
            //echo "Valid webhook";
            Log::info("Valid webhook");
        } else {
            // Signature mismatch, possibly indicating that the request did not originate from AfribaPAY
            // http_response_code(403); // Respond with a 403 Forbidden status
            //echo "Invalid webhook";
            Log::info("Invalid webhook"); 
            return response()->json(["data" => null, "statut" => "Invalid webhook"], 200);
            exit; // Stop further execution
        }
        // END Signature Verification 
        // Extract important data from the decoded payload
        $orderId = $dataAP['order_id'] ?? null;
        $status = $dataAP['status'] ?? null;
        $idTransaction = $dataAP['transaction_id'] ?? null;
        // Optional: Log the data for troubleshooting or record-keeping purposes
        // file_put_contents("webhook_log.txt", date('Y-m-d H:i:s') . " - Received webhook: " . json_encode($data) . PHP_EOL, FILE_APPEND);
        // Process the transaction status
        if ($status === 'SUCCESS') {
            // Handle successful transaction (e.g., update order status in your database)
            // http_response_code(200); // Respond with a 200 OK status
            //echo "Transaction successful!";
            Log::info("Transaction successful!");
            $dt_reference = $dataAP['reference_id'];
            $mp = Modepaiements::whereLibelle('AfribaPay')->first(); 
//************************************* start process  ***************************************/
                $get_reference = $dt_reference;
                
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
                                // Répondez à AfribaPay avec un statut HTTP 200 OK
                               return response()->json(["data" => $dataAP, "statut" => "approved"], 200); 
                                
                            }
                            # code...
                            $updatePaiement->setAttribute("num_transaction",$idTransaction);
                            $updatePaiement->setAttribute("statut_transaction",$status);
                            $updatePaiement->setAttribute("id_modepaiement",$mp->id); 
                            $updatePaiement->setAttribute("statut_paiement","PAYE");
                            $updatePaiement->setAttribute("statut_traitement","VALIDER");
                            $updatePaiement->setAttribute("commentaire","Validation automatique webhook AfribaPay");
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
                                $paiementTranche->setAttribute("num_transaction",$idTransaction);
                                $paiementTranche->setAttribute("statut_transaction",$status);
                                $paiementTranche->setAttribute("modepaiement_id",$mp->id); 
                                $paiementTranche->setAttribute("statut_traitement","VALIDER");
                                $paiementTranche->setAttribute('traiter_par', $user->id);
                                $paiementTranche->update();
                                // update details paiements
                                // if rest amount equal to 0, do update
                                if ($detailPaiementTranche->montant_restant == 0) {
                                    $detailPaiementTranche->setAttribute('statut_traitement',"VALIDER");
                                    $detailPaiementTranche->setAttribute('statut_paiement', "PAYE");
                                    $detailPaiementTranche->setAttribute("commentaire","Validation automatique webhook AfribaPay");
                                    $detailPaiementTranche->update();
                                }
                                // update etat 
                                $checkValidation = false;

                            }
                            

                        }

                        if ($checkValidation) {
                            # code...
                            // Répondez à AfribaPay avec un statut HTTP 200 OK
                            return response()->json(["data" => $dataAP, "statut" => "approved"], 200); 

                        }

                         // send mail paiement validé 
                        $auditeur = Auditeurs::where("email",$paiement->email)->first();
                        // envoie de mail pour la reférence de la facture 
                        $contenu = [
                            'nom' => $auditeur->nom ,
                            'prenoms' => $auditeur->prenoms,
                            'reference_paiement' => $get_reference,
                            'subject' => 'TRAITEMENT DE VOTRE PAIEMENT',
                            'commentaire' => "Paiement validé",
                            'email'=>$paiement->email,
                            'mode_paiement'=>$mp->libelle,
                            'montant'=>$dataAP['amount_total'], 
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
                        $checkPaiementPartiel = Detailspaiements::where("reference_paiement_partiel",$idTransaction)->where("reference_paiement",$paiement->reference)->exists();
                        if ($checkPaiementPartiel) {
                            # code... // il s'agit d'un paiement partiel
                            $dtPaiementPariel = Detailspaiements::where("reference_paiement_partiel",$idTransaction)->where("reference_paiement",$paiement->reference)->first();
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
                                $getDetailPaiement->setAttribute("commentaire","Validation automatique webhook AfribaPay");
                                $getDetailPaiement->update();

                                // si facture soldée update paiements
                                if (!$checkPaiementNonSolde) {
                                    # code...
                                    if ($paiement->montant_restant == 0) {
                                        $paiement->setAttribute("num_transaction",$idTransaction);
                                        $paiement->setAttribute("statut_transaction",$status);
                                        $paiement->setAttribute("id_modepaiement",$mp->id); 
                                        $paiement->setAttribute("statut_paiement","PAYE");
                                        $paiement->setAttribute("statut_traitement","VALIDER"); 
                                        $paiement->setAttribute("commentaire","Validation automatique webhook AfribaPay");
                                        $paiement->setAttribute('agent_traiteur', $user->id);
                                        $paiement->setAttribute('date_traitement', new \DateTime());
                                        $paiement->update();
                                    }
                                } 
                                $checkValidation = false;
                            }

                           if ($checkValidation) { // paiement déjà validé
                            # code...
                                // Répondez à AfribaPay avec un statut HTTP 200 OK
                               return response()->json(["data" => $dataAP, "statut" => "approved"], 200); 
                            }

                             // send mail paiement validé 
                            $auditeur = Auditeurs::where("email",$paiement->email)->first();
                            // envoie de mail pour la reférence de la facture
                            $contenu = [
                                'nom' => $auditeur->nom ,
                                'prenoms' => $auditeur->prenoms,
                                'reference_paiement' => $get_reference,
                                'subject' => 'TRAITEMENT DE VOTRE PAIEMENT '.$get_reference,
                                'commentaire' => "Paiement validé",
                                'email'=>$paiement->email,
                                'mode_paiement'=>$mp->libelle,
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
                               // Répondez à AfribaPay avec un statut HTTP 200 OK
                                return response()->json(["data" => $dataAP, "statut" => "approved"], 200); 
                            }
 
                                $paiement->setAttribute('num_transaction', $idTransaction);
                                $paiement->setAttribute('statut_transaction', $status);   
                                $paiement->setAttribute("statut_paiement","PAYE");
                                $paiement->setAttribute("montant_paye",$paiement->montant_restant);
                                $paiement->setAttribute("montant_restant",0);
                                $paiement->setAttribute("statut_traitement","VALIDER");
                                $paiement->setAttribute("commentaire","Validation automatique webhook AfribaPay");
                                $paiement->setAttribute("id_modepaiement",$mp->id);  
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
                                    $detail->setAttribute("commentaire","Validation automatique webhook AfribaPay");
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
                                    'mode_paiement'=>$mp->libelle,
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
                    }
                    
                }
//************************************* end process  *****************************************/
            // procéder à la validation et notification de l'utilisateur

            //
            return response()->json(["data" => null, "statut" => "Transaction successful!"], 200);
        } elseif ($status === 'FAILED') {
            // Handle failed transaction
            //http_response_code(200); // Respond with a 200 OK status
            //echo "Transaction failed.";
            Log::info("Transaction failed.");
            return response()->json(["data" => null, "statut" => "Transaction failed"], 200);
        } else {
            // Handle any unexpected statuses
            // http_response_code(400); // Respond with a 400 Bad Request status
            //echo "Unknown status.";
            Log::info("Unknown status.");
            return response()->json(["data" => null, "statut" => "Unknown status"], 200);
        } 
    }

    public function savePaiementPartiel(Request $request, $id){
        try {
            $validated = $request->validate([ 
                'formations' => 'required',
                'amount' => 'required', 
                'reference_id' => 'required'
            ]);

            return $data = $this->afribapayService->storePaiementPartielAFB($request, $id);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
}
