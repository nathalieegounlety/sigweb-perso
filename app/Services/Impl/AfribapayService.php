<?php

namespace App\Services\Impl;
 
use App\Services\contracts\AfribapayInterface;  
use Illuminate\Support\Facades\Cache; 
use \GuzzleHttp\Client;

class AfribapayService implements AfribapayInterface
{  
    public function getToken()
    {
         // 1. Vérifier le cache DB
         $token = Cache::get('afribapay_token');

        if (!$token) {
                return $this->generateToken();
            }


        // token expiré
        if (now()->greaterThan($token['expires_at'])) {
            return $this->generateToken();
        }

         // 2. Sinon générer un nouveau token
        return $token;
    }
 
    public function generateToken()
    {
        $authorize = config('services.afribapay.authorisation_key');
        $client = new Client(
        [
            'verify' => true, // <=== à désactiver en production
            'headers' => ['Authorization' => 'Basic ' . base64_encode($authorize), 'Content-Type' => 'application/json']
        ]); 

        $response = $client->request('POST', config('services.afribapay.base_url').'/v1/token', [ 
        ]); 
        $body = json_decode($response->getBody(), true);

         if ( $response->getStatusCode() === 200 && isset($body['data']['access_token']) ) {

                $token = $body['data']['access_token'];

                // 3. Gestion expiration dynamique (IMPORTANT)
                $ttl = $body['data']['expires_in'] ?? 80000;

                // 4. Sauvegarde en cache DATABASE
                Cache::put(
                    'afribapay_token',
                    $token,
                    now()->addSeconds($ttl - 60) // marge sécurité
                );

                return $token;
            }

            logger()->warning('Afribapay token invalide', [
                'response' => $body ?? null,
            ]);

            return null;

    }

    
     
}