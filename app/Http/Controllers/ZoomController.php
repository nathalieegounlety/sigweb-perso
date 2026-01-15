<?php

namespace App\Http\Controllers;

use App\Models\Auditeurs;
use App\Models\Formations;
use App\Models\Inscriptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ZoomController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function indexReunion(Request $request)
    {
        return dd($request->code);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return dd($request->code);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function refreshToken(Request $request)
    {
        /*** DONT DELETE THIS SCRIPT */

        $client = new \GuzzleHttp\Client(['headers' => ['Authorization' => env("AUTHORIZATION"), 'Host' => env("HOST"), 'Content-Type' => 'application/x-www-form-urlencoded']]);
        $response = $client->request('POST', 'https://zoom.us/oauth/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => env("REFRESH_TOKEN") // "eyJzdiI6IjAwMDAwMSIsImFsZyI6IkhTNTEyIiwidiI6IjIuMCIsImtpZCI6IjliY2MwMWQyLWM5ZDQtNDA2MS1hMTViLWQ1ZDc0MDBjYmRjMiJ9.eyJ2ZXIiOjksImF1aWQiOiJjZGMwMTUyZWMzMTY3N2ZlMjE1OTgyNzVlNWE5YjZkNiIsImNvZGUiOiJpR1NXZzQyeWlZZlJmWDE5R0RJVENDRWxfelFmX2J5eXciLCJpc3MiOiJ6bTpjaWQ6dllkYmVOamZRRHFXNXlsN1MwcG5vdyIsImdubyI6MCwidHlwZSI6MSwidGlkIjowLCJhdWQiOiJodHRwczovL29hdXRoLnpvb20udXMiLCJ1aWQiOiJYZG5uRmIyZlE0aVZqd0VMckZEMWFnIiwibmJmIjoxNjgyNDIwOTQ3LCJleHAiOjE2OTAxOTY5NDcsImlhdCI6MTY4MjQyMDk0NywiYWlkIjoidmFnX1pScTdRMGkwTDdTcDdnbjIzQSJ9.5x-r4RYcFIhVd3nKnwDa_HIaHol4j6ayMQwwjKSak3JIoFZnM0wzQEQw7UsIWYRZQ9iKnJTu4Q71QsF0ok0dHA"

            ]
        ]);
        $response = json_decode($response->getBody(), true);

        return dd($response);
    }

    public function authorisation(Request $request)
    {
        $client = new \GuzzleHttp\Client(['headers' => ['Authorization' => env("AUTHORIZATION"), 'Host' => env("HOST"), 'Content-Type' => 'application/x-www-form-urlencoded']]);
        $response = $client->request('POST', 'https://zoom.us/oauth/token', [
            'form_params' => [
                'code' => env("CODE_ZOOM"), // "QDbrSYlqJMupy0hvHulRjuoFaUNEH1S5Q",
                'grant_type' => trim(env("GRANT_TYPE")),
                'redirect_uri' => trim(env("CALLBACK_ZOOM")),
                'code_verifier' => trim(env("CODE_VERIFICATION"))
            ]
        ]);
        //return "ok";
        return $response = json_decode($response->getBody(), true);
        // dd($response);
    }


    public function inscription(Request $request)
    {
        $formations = Formations::where("statut", "ACTIF")->whereNotNull("id_webinaire")->orderBy('id', 'DESC')->get();
        return view("importations.zoom_inscription", compact("formations"));
    }


    public function inscriptionStore(Request $request)
    {
        $code_formation = $request->code_formation;
        $formation = Formations::where("code_formation", $code_formation)->first();
        $inscrits = Inscriptions::where("code_formation", $code_formation)->where("type_formation", "VISIO")->where("sync_zoom", 0)->get();
        $compteur = 0;

        // inscription sur zoom
        try {

            // refresh token
            $client = new \GuzzleHttp\Client(['headers' => ['Authorization' => env("AUTHORIZATION"), 'Host' => env("HOST"), 'Content-Type' => 'application/x-www-form-urlencoded']]);
            $response = $client->request('POST', 'https://zoom.us/oauth/token', [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => env("REFRESH_TOKEN")
                ]
            ]);
            $response = json_decode($response->getBody(), true);
            $access_token = "";
            foreach ($response as $key => $value) {
                if ($key == "access_token") {
                    $access_token = $value;
                }
            }
            // synchronisation sur zoom

            foreach ($inscrits as $inscrit) {
                # code...
                $auditeur = Auditeurs::where("email", $inscrit->email)->first();
                if ($auditeur) {

                    $data = [
                        'email' => $auditeur->email,
                    ];

                    $validator = Validator::make($data, [
                        'email' => ['required', 'email:rfc,dns'],
                    ]);

                    if ($validator->passes()) {
                        // echo 'Mail valide';

                        if (!empty($auditeur->nom)) {
                            # code...
                            # code...
                            // inscription sur zoom
                            $data = array(
                                'first_name' => $auditeur->prenoms,
                                'last_name' => $auditeur->nom,
                                'email' => $auditeur->email,
                                // 'city' => $auditeur->ville,
                                // 'state' => $auditeur->code_pays,
                                // 'country' => $auditeur->code_pays,
                                // 'phone' => $auditeur->telephone,
                                'comments' => "Inscription depuis SIGWEB ERSUMA",
                                // 'industry' => $auditeur->secteur,
                                // 'job_title' => $auditeur->poste,
                                // 'org' => $auditeur->organisation
                            );

                            $zoom_inscription = new \GuzzleHttp\Client(['headers' => ['Authorization' => 'Bearer ' . $access_token, 'Host' => env("HOST"), 'Content-Type' => 'application/json', 'Accept' => 'application/json']]);
                            if($formation->code_formation == env("CODE_FORMATION")){
                                $response_zoom = $zoom_inscription->request('POST', 'https://api.zoom.us/v2/meetings/' . $formation->id_webinaire . '/registrants', [
                                    'body'    => json_encode($data)
                                ]);
                            }else{
                                $response_zoom = $zoom_inscription->request('POST', 'https://api.zoom.us/v2/webinars/' . $formation->id_webinaire . '/registrants', [
                                    'body'    => json_encode($data)
                                ]);
                            }
                            /*$response_zoom = $zoom_inscription->request('POST', 'https://api.zoom.us/v2/webinars/' . $formation->id_webinaire . '/registrants', [
                                'body'    => json_encode($data)
                            ]);*/
                            // mise à jour statut dans la table inscrit
                            $inscrit->setAttribute("sync_zoom",true);
                            $inscrit->update();
                            $compteur = $compteur + 1;
                            //

                        }
                    }


                }
            }

            $response = [
                'data'    => $compteur,
            ];

            return response()->json($response, 200);

        } catch (\Throwable $th) {
            //throw $th;

            $response = [
                'data'    => $th->getMessage(),
            ];

            return response()->json($response, 200);
        }

        // end inscription sur zoom

    }


    public function inscriptionReunion(Request $request)
    {
        $code_formation = $request->code_formation;
        $formation = Formations::where("code_formation", $code_formation)->first();
        $inscrits = Inscription::where("code_formation", $code_formation)->where("type_formation", "VISIO")->where("sync_zoom", 0)->get();
        $compteur = 0;

        // inscription sur zoom
        try {

            // refresh token
            $client = new \GuzzleHttp\Client(['headers' => ['Authorization' => env("AUTHORIZATION"), 'Host' => env("HOST"), 'Content-Type' => 'application/x-www-form-urlencoded']]);
            $response = $client->request('POST', 'https://zoom.us/oauth/token', [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => env("REFRESH_TOKEN")
                ]
            ]);
            $response = json_decode($response->getBody(), true);
            $access_token = "";
            foreach ($response as $key => $value) {
                if ($key == "access_token") {
                    $access_token = $value;
                }
            }
            // synchronisation sur zoom

            foreach ($inscrits as $inscrit) {
                # code...
                $auditeur = Auditeurs::where("email", $inscrit->email)->first();
                if ($auditeur) {
                    # code...
                    // inscription sur zoom
                    $data = array(
                        'first_name' => $auditeur->prenoms,
                        'last_name' => $auditeur->nom,
                        'email' => $auditeur->email,
                        // 'city' => $auditeur->ville,
                        // 'state' => $auditeur->code_pays,
                        // 'country' => $auditeur->code_pays,
                        // 'phone' => $auditeur->telephone,
                        'comments' => "Inscription depuis SIGWEB ERSUMA",
                        // 'industry' => $auditeur->secteur,
                        // 'job_title' => $auditeur->poste,
                        // 'org' => $auditeur->organisation
                    );
                    $meetingId = 87222974893;
                    $zoom_inscription = new \GuzzleHttp\Client(['headers' => ['Authorization' => 'Bearer ' . $access_token, 'Host' => env("HOST"), 'Content-Type' => 'application/json', 'Accept' => 'application/json']]);
                    $response_zoom = $zoom_inscription->request('POST', 'https://api.zoom.us/v2/meetings/' . $meetingId . '/registrants', [
                        'body'    => json_encode($data)
                    ]);
                    // mise à jour statut dans la table inscrit
                    $inscrit->setAttribute("sync_zoom",true);
                    $inscrit->update();
                    $compteur = $compteur + 1;
                    //
                }
            }

            $response = [
                'data'    => $compteur,
            ];

            return response()->json($response, 200);
        } catch (\Throwable $th) {
            //throw $th;

            $response = [
                'data'    => $th->getMessage(),
            ];

            return response()->json($response, 200);
        }

        // end inscription sur zoom

    }


}
