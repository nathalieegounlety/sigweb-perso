<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Mail\InscriptionGratuit;
use App\Mail\Inscriptionpresentiel;
use App\Mail\NotificationCompte;
use App\Models\Auditeurs;
use App\Models\Detailspaiements;
use App\Models\Formations;
use App\Models\Inscriptions;
use App\Models\Paiements;
use App\Models\Pays;
use App\Models\Tarificationsformation;
use App\Models\User;
use App\Services\contracts\AuditeursInterface;
use App\Services\util\GenarateurCodeService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AuditeursService implements AuditeursInterface
{
    private GenarateurCodeService $genarateurCodeService;
    private UsersService $usersService;

    public function __construct(GenarateurCodeService $genarateurCodeService, UsersService $usersService)
    {
        $this->genarateurCodeService = $genarateurCodeService;
        $this->usersService = $usersService;
    }
    public function saveAuditeur(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement saveAuditeur() method.
        $user = auth()->user();
        // generator password
        $generate_password = $this->genarateurCodeService->genererCodeAlphaNumeric(8);
        $check_auditeur = Auditeurs::where("email", $request->email)->exists();
        $check_user = User::where("email", $request->email)->exists();

        if ($check_user == true && $check_auditeur == true) {
            return redirect()->back()->with("Cet auditeur existe déjà");
        }
        // create user acount
        $dataUser = ['email' => $request->email, 'password' => $request->password,'name' => $request->nom.' '.$request->prenoms];
        $userRequest = new Request($dataUser);
        $this->usersService->saveUser($userRequest);

        $user = User::whereEmail($request->email)->first();

        // register data into auditeur table
        $auditeur = new Auditeurs();
        $auditeur->setAttribute("nom", trim($request->nom));
        $auditeur->setAttribute("prenoms", trim($request->prenoms));
        $auditeur->setAttribute("email", trim($request->email));
        $auditeur->setAttribute("telephone", trim($request->telephone));
        $auditeur->setAttribute("ville", trim($request->ville));
        $auditeur->setAttribute("civilite", trim($request->civilite));
        $auditeur->setAttribute("genre", trim($request->genre));
        $auditeur->setAttribute("secteur_activite", trim($request->secteur_activite));
        $auditeur->setAttribute("organisme", trim($request->organisation));
        $auditeur->setAttribute("intitule_poste", trim($request->intitule_poste));
        // récupéré l'objet pays
        $pays = Pays::where("nom_pays", $request->get('pays'))->get()->first();
        $count_pays = Pays::where("nom_pays", trim($request->get('pays')))->count();
        if ($count_pays > 0) {
            # code...
            $auditeur->setAttribute('pays', $pays->nom_pays);
            $auditeur->setAttribute('code_pays', $pays->code_iso);
            $auditeur->setAttribute('nationalite', $pays->nationalite);
        } else {
            # code...
            $auditeur->setAttribute('pays', $request->get('pays'));
        }
        $auditeur->save();

        // send mail for user to inform that about acount

        // send identifiant to mail for user
        $contenu = [
            'nom_prenoms' => $user->name,
            'email' => trim($user->email),
            'mot_de_passe' => $generate_password,
            'subject' => 'SIGWEB - CREATION DE COMPTE'
        ];

        # code...
        Mail::to(trim($user->email))->queue(new NotificationCompte($contenu));

        try {

        } catch (\Exception $e) {
            // Get error here


        }

        return  ResponseHandler::success("Enregistrement effectué avec succès");

    }

    public function getAuditeur(int $id): Auditeurs
    {
        // TODO: Implement getAuditeur() method.
    }

    public function getAuditeurs(Request $request): LengthAwarePaginator
    {
        // TODO: Implement getAuditeurs() method.
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Filtres spécifiques
        $filterActif = $request->input('filter_actif');
        $filterUpdatedAt = $request->input('filter_updated_at');

        $queryData = Auditeurs::select(["auditeurs.id", "auditeurs.nom", "auditeurs.prenoms", "auditeurs.email", "auditeurs.telephone", "auditeurs.pays", "auditeurs.created_at"])
            ->selectRaw(
                '(EXISTS (
                    SELECT 1 FROM auditeurs_pieces
                    WHERE auditeurs_pieces.auditeur_id = auditeurs.id
                )) as check_dossier'
            );


        // Recherche globale sur plusieurs champs
        if ($search) {
            $queryData->where(function ($q) use ($search) {
                $q->where('auditeurs.nom', 'like', "%{$search}%")
                    ->orWhere('auditeurs.prenoms', 'like', "%{$search}%")
                    ->orWhere('auditeurs.telephone', 'like', "%{$search}%")
                    ->orWhere('auditeurs.pays', 'like', "%{$search}%")
                    ->orWhere('auditeurs.email', 'like', "%{$search}%"); // Recherche sur auteur
            });
        }

        // Filtres par colonne
        if ($filterUpdatedAt) {
            $queryData->whereDate('auditeurs.updated_at', $filterUpdatedAt);
        }
        // Tri
        $queryData->orderBy("auditeurs.{$sortField}", $sortOrder);

        return $queryData->paginate($perPage);
    }

    public function update(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement update() method.
    }

    public function remove(int $idRole): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement remove() method.
    }

    public function storeInscriptionFormation(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement storeInscriptionFormation() method.
        $user = auth('api')->user();
        $auditeur = Auditeurs::where("email", $user->email)->first();

        if ($auditeur) {
            # code...
        } else {
            # code...
            $dataResponse = [
                'success' => false,
                'message' => "Le compte de l'auditeur n'existe pas"
            ];
            return ResponseHandler::success("Vous avez déjà une inscription en cours pour cette formation",$dataResponse,500);
        }

        // ### generer code inscription
        $generateCode = $this->genarateurCodeService->genererCodeAlphaNumeric(8);

        $montant_total = 0;
        // vérifier si la variable session existe
        if ($request->formations_classique) {
            // récupérer la liste des formations
            $liste_formation_tarif = $request->formations_classique;
            $total_formation = 0;
            $check_exist = 0;
            $formationSave = false;

            // enregistrement dans la table inscription suivant le nbre de la formation
            foreach ($liste_formation_tarif as $data) {
                $explode_data = explode("_", $data['tarif']);
                $tarif_id = $explode_data[0];

                // get formation objet
                $tarif_formation = Tarificationsformation::find($tarif_id);

                if (isset($explode_data[1])) {
                    $formation_type = $explode_data[1];
                } else {
                    // récupérer le type de la formation dans la grille
                    $formation_type = $tarif_formation->getGrilletarifaire->type_tarif;
                }
                //return $formation_type;
                $total_formation  = $total_formation + 1;
                # code...
                // récupérer le montant total pour vérifier si la formation est gratuite ou pas
                $montant_total =  $montant_total + $tarif_formation->getGrilletarifaire->montant;
                $formation = Formations::where("code_formation", $tarif_formation->code_formation)->get()->first();

                // insert data in table inscrits
                $created_at = date("Y-m-d H:i:s");

                // enregistrement des données dans la table inscrit
                // vérifier si l'e-mail existe dans la table des inscrits
                $check_auditeur = Inscriptions::where('email', trim($auditeur->email))->where('code_formation', $formation->code_formation)->exists();

                if ($check_auditeur) {
                    $check_exist  = $check_exist + 1;
                    // if participant exist
                    $get_auditeur = Inscriptions::where('email', $auditeur->email)->where('code_formation', $formation->code_formation)->get()->first();
                    $generateCode = $get_auditeur->code_inscription;
                } else {
                    // vérifier si la formation est présentiel ou pas
                    $presentiel = 0; // default visio
                    if ($formation_type == "PRESENTIEL") {
                        $presentiel = 1;
                    }

                    $checkExitingFormation = Inscriptions::whereCodeFormation($formation->code_formation)
                        ->whereEmail(trim($auditeur->email))
                        ->whereTarifFormationId($tarif_formation->id)
                        ->exists();

                    if (!$checkExitingFormation) {

                        $inscrit = new Inscriptions();
                        $inscrit->setAttribute("email", trim($auditeur->email));
                        $inscrit->setAttribute("tarif_formation_id", $tarif_formation->id);
                        $inscrit->setAttribute("code_inscription", $generateCode);
                        $inscrit->setAttribute("heure_inscription", date("d-m-Y H:i:s"));
                        $inscrit->setAttribute("id_webinaire", $formation->code_formation);
                        $inscrit->setAttribute("code_formation", $formation->code_formation);
                        $inscrit->setAttribute("presentiel", $presentiel);
                        $inscrit->setAttribute("type_formation", $formation_type);
                        $inscrit->setAttribute("created_at", $created_at);
                        $inscrit->setAttribute("updated_at", $created_at);
                        $inscrit->save();

                        // vérifier si la formation est visio pour ajouter le participant sur zoom
                        if ($formation_type == "VISIO") {
                            # code...
                            // vérifier si id_webinaire existe dans la table formation
                            if ($formation->id_webinaire != "") {
                                # code...

                                try {

                                    // inscription sur zoom
                                    $data = array(
                                        'first_name' => $auditeur->prenoms,
                                        'last_name' => $auditeur->nom,
                                        'email' => $auditeur->email,
                                        'comments' => "Inscription depuis SIGWEB ERSUMA",
                                    );

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

                                    $zoom_inscription = new \GuzzleHttp\Client(['headers' => ['Authorization' => 'Bearer ' . $access_token, 'Host' => env("HOST"), 'Content-Type' => 'application/json', 'Accept' => 'application/json']]);
                                    $response_zoom = $zoom_inscription->request('POST', 'https://api.zoom.us/v2/webinars/' . $formation->id_webinaire . '/registrants', [
                                        'body'    => json_encode($data)
                                    ]);
                                    // mise à jour inscrit
                                    $inscrit->setAttribute("sync_zoom", true);
                                    $inscrit->update();
                                } catch (\Throwable $th) {
                                    //throw $th;
                                }
                                // end inscription sur zoom
                            }
                        }

                        $formationSave = true;

                    }
                }
            }

            $contenu = [
                'nom' => $auditeur->nom,
                'prenoms' => $auditeur->prenoms,
                'formations' => $request->formations_classique,
                'subject' => 'Inscription sur SIGWEB de ' . $auditeur->nom . ' ' . $auditeur->prenoms,
                'email' => $auditeur->email,
                'code_inscription' => $generateCode,
            ];


            if ($check_exist == $total_formation) {
                # code...
                $ok = true;
            } else {

                try {
                    # code...
                    if ($montant_total == 0) {
                        # code...
                        Mail::to($auditeur->email)->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new InscriptionGratuit($contenu));
                    } else {
                        # code...
                        Mail::to($auditeur->email)->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new Inscriptionpresentiel($contenu));
                    }
                } catch (\Exception $e) {
                    // Get error here

                }
            }

            if($formationSave){
                // enregistrement des informations dans la table de paiement

                # code...
                /************************ start *********************** */
                $inscrit = Inscriptions::where("code_inscription", $generateCode)->orderBy("id", "DESC")->get()->first();
                $liste_formation = Inscriptions::where("code_inscription", $generateCode)->orderBy("id", "DESC")->get();

                // vérifier si le paiement existe déjà
                $check_paiement = Paiements::where("email", $inscrit->email)->where("code_inscription", $inscrit->code_inscription)->exists();

                if ($check_paiement) {
                    # code...
                    # code... la formation a été déjà enregistrée
                } else {
                    # code...

                    // récupération des variables
                    $nom = $auditeur->nom;
                    $prenoms = $auditeur->prenoms;
                    $email = $auditeur->email;
                    $telephone = $auditeur->telephone;

                    // générer le numero de reference facture : ERS-2025-FAC-000001
                    $reference_paiement =$this->genarateurCodeService->genererNumeroPaiement();

                    // enregistrement du paiement
                    $statut_details_paiement = "IMPAYE";
                    $statut_paiement = "EN ATTENTE";

                    // vérifier si le paiement n'est pas déjà enregistré
                    $checkExistingPaiement = Paiements::whereReference($reference_paiement)->exists();
                    if (!$checkExistingPaiement) { // si le paiement n'existe pas encore

                        $paiement = new Paiements();
                        $paiement->setAttribute('reference', $reference_paiement);
                        $paiement->setAttribute('email', $email);
                        $paiement->setAttribute('telephone', $telephone);
                        $paiement->setAttribute('code_inscription', $inscrit->code_inscription);
                        $paiement->setAttribute('montant_total', $montant_total);
                        $paiement->setAttribute('montant_restant', $montant_total);
                        $paiement->setAttribute('statut_paiement', $statut_paiement);
                        $paiement->setAttribute('type', "FORMATION");

                        // enregistrement des détails de la facture

                        try {
                            //code...
                            $paiement->save();

                            foreach ($liste_formation as $formations) {
                                $id_tarification_formation = $formations->tarif_formation_id;
                                $tarif_formation = Tarificationsformation::find($id_tarification_formation);
                                $montant = $tarif_formation->getGrilletarifaire->montant;
                                // enregistrement détails paiement
                                $details_paiement = new Detailspaiements();
                                $details_paiement->setAttribute('reference_paiement', $paiement->reference);
                                $details_paiement->setAttribute('code_formation', $inscrit->code_formation);
                                $details_paiement->setAttribute('code_inscription', $inscrit->code_inscription);
                                $details_paiement->setAttribute('montant_restant', $montant);
                                $details_paiement->setAttribute('email', $email);
                                $details_paiement->setAttribute('id_tarification_formation', $id_tarification_formation);
                                $details_paiement->setAttribute('statut_paiement', 'IMPAYE');
                                $details_paiement->save();
                            }
                        } catch (\Exception $e) {
                            //throw $th;
                            echo $e->getMessage();
                        }

                    }

                    //##################### end start #################
                }
                // formation enregistré :
                $dataResponse = [
                    'code_inscription' => $generateCode,
                    'message' => 'Inscription effectuée',
                    'success' => true,
                ];

                return ResponseHandler::success("Modification effectuée avec succès",$dataResponse);

            }else{
                $dataResponse = [
                    'success' => false,
                    'message' => "Vous avez déjà une inscription en cours pour cette formation"
                ];
                return ResponseHandler::success("Vous avez déjà une inscription en cours pour cette formation",$dataResponse,500);
            }

            /************************** end ******************* */
        } else {
            $dataResponse = [
                'success' => false,
                'message' => "Veuillez choisir la formation auquelle vous voulez vous inscrire."
            ];
            return ResponseHandler::success("Vous avez déjà une inscription en cours pour cette formation",$dataResponse,500);
        }
    }

    public function getAuditeurByEmail(string $email): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getAuditeurByEmail() method.
        $auditeur = Auditeurs::whereEmail($email)->first();

        return  ResponseHandler::success("ok",$auditeur);
    }

    public function getListeFormationsByCodeInscription(string $codeInscription): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getListeFormationsByCodeInscription() method.
        $liste_formation = Inscriptions::join('tarificationsformations','tarificationsformations.id','=','inscriptions.tarif_formation_id')
            ->join('grilletarifaires','grilletarifaires.id','=','tarificationsformations.id_grilletarifaire')
            ->join('formations','formations.code_formation','=','inscriptions.code_formation')
            ->where("code_inscription", $codeInscription)
            ->orderBy("id", "DESC")
            ->select(
                [
                    'inscriptions.id','inscriptions.email','inscriptions.code_inscription','inscriptions.code_formation','inscriptions.type_formation',
                    'inscriptions.presentiel','inscriptions.is_formation_dsc','formations.theme','formations.date_debut','formations.date_fin',
                    'grilletarifaires.libelle',
                    'grilletarifaires.type_tarif',
                    'grilletarifaires.montant',
                    'grilletarifaires.id as id_grille',
                    'inscriptions.tarif_formation_id'

                ]
            )->get();
        $oneInscription = Inscriptions::where("code_inscription", $codeInscription)->first();
        $auditeur = Auditeurs::whereEmail($oneInscription->email)->first();
        $dataResponse = [
            'liste_formation' => $liste_formation,
            'auditeur' => $auditeur
        ];
        return  ResponseHandler::success("ok",$dataResponse);
    }
}
