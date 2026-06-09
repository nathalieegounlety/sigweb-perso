<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AfribapayController extends Controller
{
    /**
     * Génère un mot de passe aléatoire.
     */
    private function genererToken(Request $request)
    {
       $client = new \GuzzleHttp\Client(
        [
            'headers' => ['Authorization' => env("AUTHORIZATION"), 'Content-Type' => 'application/json']
        ]);
        /*
        $body = '';
        $request = new Request('POST', 'https://api-sandbox.afribapay.com/v1/token', $headers, $body);
        $res = $client->sendAsync($request)->wait();
        echo $res->getBody();
        */

        $response = $client->request('POST', 'https://api-sandbox.afribapay.com/v1/token', [ 
        ]);
        $response = json_decode($response->getBody(), true);

        return dd($response);
    } 
}
