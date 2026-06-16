<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Services\contracts\AfribapayInterface;  
use Illuminate\Support\Facades\Cache; 
use \GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class AfribapayService implements AfribapayInterface
{  
    public function getToken()
{
    // Récupérer le tableau {access_token, expires_at} depuis le cache
    $cached = Cache::get('afribapay_token');

    Log::info($cached);

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
        'verify'  => true, // désactiver en production
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

        $dataResponse = [
            'token' => $token, 
            'url' => $url,
            'success' => true
        ];

        return ResponseHandler::success("ok",$dataResponse);
    }

    
     
}