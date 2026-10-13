<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Mail\NotificationComptable;
use App\Mail\Paiementattente;
use App\Models\Auditeurs;
use App\Models\Detailspaiements;
use App\Models\Modepaiements;
use App\Models\Paiements;
use App\Models\User;
use App\Services\contracts\AfribapayInterface;  
use Illuminate\Support\Facades\Cache; 
use \GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AfribapayService implements AfribapayInterface
{  
    public function getToken()
{
    // Récupérer le tableau {access_token, expires_at} depuis le cache
    $cached = Cache::get('afribapay_token'); 

    // Pas de cache → générer
    if (!$cached) {
        return $this->generateToken();
    }

    // Token expiré → regénérer
    if (now()->greaterThan($cached['expires_at'])) {
        return $this->generateToken();
    }

    // Retourner uniquement la string du token
    return $cached['access_token'];
}

public function generateToken()
{
    $authorize = config('services.afribapay.authorisation_key');
    $client = new Client([
        'verify'  => false, // désactiver en production
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode($authorize),
            'Content-Type'  => 'application/json'
        ]
    ]);

    $response = $client->request('POST', config('services.afribapay.base_url') . '/v1/token', []);
    $body     = json_decode($response->getBody(), true);

    if ($response->getStatusCode() === 200 && isset($body['data']['access_token'])) {

        $ttl        = $body['data']['expires_in'] ?? 80000;
        $expires_at = now()->addSeconds($ttl - 60);

        // Sauvegarder un TABLEAU avec le token ET la date d'expiration
        $payload = [
            'access_token' => $body['data']['access_token'],
            'expires_at'   => $expires_at->toDateTimeString(),
        ];
        Cache::forget('afribapay_token');
        Cache::put('afribapay_token', $payload, $expires_at);

        // Retourner uniquement la string du token
        return $payload['access_token'];
    }

    logger()->warning('Afribapay token invalide', [
        'response' => $body ?? null,
    ]);

    return null;
}

    public function listeVariables(): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement listeVariables() method. 
        $token = $this->getToken();
        $url = config('services.afribapay.base_url'); 
        $merchant_key = config('services.afribapay.merchant_id'); 

        $dataResponse = [
            'token' => $token, 
            'url' => $url,  
            'merchant_key' => $merchant_key,
            'success' => true
        ];

        return ResponseHandler::success("ok",$dataResponse);
    }

    public function payIn(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement payIn() method. 
        $token = $this->getToken();
        //$url = config('services.afribapay.base_url'); 
        $merchant_key = config('services.afribapay.merchant_id'); 
        $operation = [
            "operator" => $request->operator,
            "country" => $request->country,
            "phone_number" => $request->phone_number,
            "amount" => $request->amount,
            "currency" => $request->currency,
            "merchant_key" => $merchant_key,
            "reference_id" => $request->reference_id,
            "lang" => "fr",
            "return_url" => env("AFRIBAPAY_CALLBACK"),
            "cancel_url" => env("AFRIBAPAY_CALLBACK"),
            "notify_url" => env("AFRIBAPAY_CALLBACK"),
        ]; 

        // initialiser la requete

         $client = new Client([
            'verify'  => false, // désactiver en production
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json'
            ]
        ]);

        // Log::info(json_encode($operation));
        $response = $client->request('POST', config('services.afribapay.base_url') . '/v1/pay/payin', ['json' => $operation]);
        $body = json_decode($response->getBody(), true);
        
        // Log::info($response->getBody());

        if ($response->getStatusCode() === 200) {
            return ResponseHandler::success("ok",$body);
        }else{
            return ResponseHandler::error("echec de la création du paiement");
        }  
    }

    

    public function checkPayIn(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement payIn() method. 
        $token = $this->getToken();
        //$url = config('services.afribapay.base_url'); 
        $merchant_key = config('services.afribapay.merchant_id');  

        // initialiser la requete

         $client = new Client([
                'verify'  => false, // désactiver en production
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json'
                ]
            ]);
 
            $response = $client->request('GET', config('services.afribapay.base_url') . '/v1/status?transaction_id='.$id, []);
            $body = json_decode($response->getBody(), true);
            
            //Log::info($response->getBody());

            if ($response->getStatusCode() === 200) {
                // procéder à l'enregistrement des données 
                $statut = $body['data']['status'];
                $reference = $body['data']['reference_id'];
                    $montant_paye = $body['data']['amount_total']; 

                    // mise à jour de la table paiement
                    $getPaiement = Paiements::whereReference($reference)->first();
                    $montant_restant = $getPaiement->montant_restant;
                    // mise à jour du montant paye
                    $paiement = Paiements::find($getPaiement->id);
                    $paiement->setAttribute('num_transaction', $id);
                    $paiement->setAttribute('statut_transaction', $statut);
                    $paiement->setAttribute('updated_at', date('Y-m-d H:i:s'));
                    $mp = Modepaiements::where('libelle','AfribaPay')->first();
                    $paiement->setAttribute('id_modepaiement', $mp->id);
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
    }else{
        return ResponseHandler::error("echec de la création du paiement");
    } 

        
    }
     
    public function storePaiementPartielAFB(Request $request, String $id): \Illuminate\Http\JsonResponse
{
    $reference = null; // FIX : défini tôt, utilisable dans les retours d'erreur

    try {
        $token = $this->getToken();
        $merchant_key = config('services.afribapay.merchant_id');

        $client = new Client([
            // FIX : vérification TLS désactivée seulement hors production
            'verify' => app()->environment('production'),
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ],
        ]);

        // FIX : l'appel HTTP est maintenant dans le try — Guzzle lève
        // une exception sur tout code 4xx/5xx, on la catch proprement
        $response = $client->request('GET', config('services.afribapay.base_url') . '/v1/status?transaction_id=' . $id, []);
        $body = json_decode($response->getBody(), true);

        if ($response->getStatusCode() !== 200) {
            Log::warning('AfribaPay : statut HTTP inattendu', ['transaction_id' => $id, 'status_code' => $response->getStatusCode()]);
            return ResponseHandler::success("ok", [
                'reference' => $reference,
                'success' => false,
            ]);
        }

        $statut = $body['data']['status'];
        $reference = $body['data']['reference_id'];
        $actuel_montant_paye = $body['data']['amount_total'];
 
        if (!in_array(strtolower($statut), ['success', 'completed', 'approved'])) {
            Log::warning('AfribaPay : transaction non confirmée', [
                'transaction_id' => $id,
                'reference' => $reference,
                'status' => $statut,
            ]);
            return ResponseHandler::success("paiement invalide", [
                'reference' => $reference,
                'success' => false,
            ]);
        }

        // FIX : null-check mode de paiement
        $mode_paiement = Modepaiements::whereLibelle('AfribaPay')->first();
        if (!$mode_paiement) {
            Log::warning('AfribaPay : mode de paiement introuvable', ['reference' => $reference]);
            return ResponseHandler::success("mode de paiement introuvable", [
                'reference' => $reference,
                'success' => false,
            ]);
        }

        // FIX : null-check paiement, first() au lieu de get()->first()
        $paiement = Paiements::where('reference', $reference)->first();
        if (!$paiement) {
            Log::warning('AfribaPay : paiement introuvable', ['reference' => $reference]);
            return ResponseHandler::success("paiement introuvable", [
                'reference' => $reference,
                'success' => false,
            ]);
        }
 
        $dejaTraite = ($paiement->num_transaction === $id);

        $update_paiement = Paiements::find($paiement->id);

        if (!$dejaTraite) {
            $nouveau_montant_restant = $paiement->montant_restant - $actuel_montant_paye;

            Log::info('AfribaPay paiement', [
                'reference' => $reference,
                'montant_paye_cumule' => $paiement->montant_paye + $actuel_montant_paye,
                'montant_restant' => $nouveau_montant_restant,
                'actuel_montant_paye' => $actuel_montant_paye,
            ]);

            $update_paiement->setAttribute('num_transaction', $id);
            $update_paiement->setAttribute('statut_transaction', $statut);
            $update_paiement->setAttribute('id_modepaiement', $mode_paiement->id);
            $update_paiement->setAttribute('montant_paye', $paiement->montant_paye + $actuel_montant_paye);
            $update_paiement->setAttribute('montant_restant', max($nouveau_montant_restant, 0));

            // FIX : pas de paiement partiel ici — chaque ligne est
            // soldée intégralement, donc dès que ce paiement arrive
            // approuvé, on considère le montant dû comme réglé.
            $update_paiement->setAttribute('statut_paiement', 'PAYE');
            $update_paiement->setAttribute('statut_traitement', 'VALIDER');
            $update_paiement->update();

            // update détails paiements
            foreach ($request->formations as $key => $value) {
                // FIX : null-check
                $update_detailspaiement = Detailspaiements::find($value['detail_id']);
                if (!$update_detailspaiement) {
                    Log::warning('AfribaPay : détail paiement introuvable', [
                        'detail_id' => $value['detail_id'],
                        'reference' => $reference,
                    ]);
                    continue;
                }

                // FIX : plus de branche "partiel" — toujours soldé
                $update_detailspaiement->setAttribute('modepaiement_id', $mode_paiement->id);
                $update_detailspaiement->setAttribute('reference_paiement_partiel', $id);
                $update_detailspaiement->setAttribute('statut_paiement', "PAYE");
                $update_detailspaiement->update();
            }
        }

        // ============================================================
        // Emails — protégés par $dejaTraite
        // ============================================================
        if (!$dejaTraite) {
            $auditeur = Auditeurs::where("email", $paiement->email)->first();

            if ($auditeur) {
                $contenu = [
                    'nom' => $auditeur->nom,
                    'prenoms' => $auditeur->prenoms,
                    'reference_paiement' => $reference,
                    'subject' => 'INFORMATION SUR VOTRE PAIEMENT ' . $reference,
                    'email' => $paiement->email,
                    'montant' => $actuel_montant_paye,
                    'mode_paiement' => $mode_paiement->libelle,
                ];
            } else {
                $contenu = [
                    'nom' => "",
                    'prenoms' => "",
                    'reference_paiement' => $reference,
                    'subject' => 'INFORMATION SUR VOTRE PAIEMENT ' . $reference,
                    'email' => $paiement->email,
                    'montant' => $actuel_montant_paye,
                    'mode_paiement' => $mode_paiement->libelle,
                ];
            }

            try {
                Mail::to($paiement->email)->cc([env('MAIL_CC')])->bcc(env('MAIL_BCC'))->queue(new Paiementattente($contenu));
            } catch (\Exception $e) {
                Log::info('echec de l\'envoi du mail à l\'auditeur');
            }

            if (env('PAIEMENT_NOTIFIER_COMPTABLE')) {
                $liste_comptable = User::whereHas('roles', function ($q) {
                    $q->where('libelle', 'COMPTABLE');
                })->pluck('email');

                $contenu = [
                    'name' => 'COMPTABLE',
                    'reference_paiement' => $reference,
                    'email_auditeur' => $paiement->email,
                    // FIX : null-check
                    'nom_auditeur' => $auditeur ? trim($auditeur->prenoms . ' ' . $auditeur->nom) : $paiement->email,
                    'subject' => 'NOUVEAU PAIEMENT ' . date('d-m-Y H:i:s'),
                    'email' => '',
                    'montant' => $paiement->montant_restant,
                    'mode_paiement' => $mode_paiement->libelle,
                ];
                try {
                    Mail::to(env('MAIL_CC'))->cc($liste_comptable)->queue(new NotificationComptable($contenu));
                } catch (\Exception $e) {
                    Log::error('Erreur envoi mail NotificationComptable', ['error' => $e->getMessage()]);
                }
            }
        }

        // FIX : reference, pas reference_id (qui n'existait pas dans $request)
        return ResponseHandler::success("ok", [
            'reference' => $reference,
            'success' => true,
        ]);

    } catch (\Throwable $e) { // FIX : attrape aussi les erreurs Guzzle (RequestException) et les \Error
        Log::error('Erreur vérification AfribaPay', ['error' => $e->getMessage()]);

        return ResponseHandler::success("ok", [
            'reference' => $reference,
            'success' => false,
        ]);
    }
}
    
    public function storePaiementPartielAFBOld(Request $request, String $id): \Illuminate\Http\JsonResponse
    {

    // TODO: Implement payIn() method. 
        $token = $this->getToken();
        //$url = config('services.afribapay.base_url'); 
        $merchant_key = config('services.afribapay.merchant_id');  

        // initialiser la requete

         $client = new Client([
                'verify'  => false, // désactiver en production
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json'
                ]
            ]);
 
            $response = $client->request('GET', config('services.afribapay.base_url') . '/v1/status?transaction_id='.$id, []);
            $body = json_decode($response->getBody(), true);
            
            //Log::info($response->getBody());

            if ($response->getStatusCode() === 200) {
                // procéder à l'enregistrement des données 
                $statut = $body['data']['status'];
                $reference = $body['data']['reference_id'];
                $actuel_montant_paye = $body['data']['amount_total']; 

                 try { 
   
                $mode_paiement = Modepaiements::whereLibelle('AfribaPay')->first();
                // mise à jour de la table paiement
                $paiement = Paiements::where('reference', $reference)->get()->first();
                $update_montant_restant = $paiement->montant_restant - $actuel_montant_paye;
                $update_montant_paye = $paiement->montant_paye + $actuel_montant_paye;
            Log::info($update_montant_restant);
            Log::info($update_montant_paye);
            Log::info($actuel_montant_paye);

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
                    $update_paiement->setAttribute('num_transaction', $id);
                    $update_paiement->setAttribute('statut_transaction', $statut);

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
                        $update_detailspaiement->setAttribute('reference_paiement_partiel', $id);
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
                    'reference' => $request->reference_id,
                    'success' => true,
                ];

                return  ResponseHandler::success("ok",$dataResponse);


        } catch (\Exception $e) {
            //\Log::error('Erreur vérification AfribaPay', ['error' => $e->getMessage()]);
            Log::info('Erreur vérification AfribaPay');

            $dataResponse = [
                'reference' => $request->reference_id,
                'success' => false,
            ];

            return  ResponseHandler::success("ok",$dataResponse);

        }

            }else{
                $dataResponse = [
                    'reference' => $request->reference_id,
                    'success' => false,
                ];

                return  ResponseHandler::success("ok",$dataResponse);
            }
       
    }
}