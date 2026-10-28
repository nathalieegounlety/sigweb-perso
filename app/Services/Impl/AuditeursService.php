<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Mail\InscriptionFormationDiplomante;
use App\Mail\InscriptionGratuit;
use App\Mail\Inscriptionpresentiel;
use App\Mail\NotificationCompte;
use App\Mail\Paiement;
use App\Models\Auditeurs;
use App\Models\AuditeursPieces;
use App\Models\Detailspaiements;
use App\Models\Formations;
use App\Models\Grilletarifaires;
use App\Models\Inscriptions;
use App\Models\InscritsTypePieces;
use App\Models\Modepaiements;
use App\Models\Paiements;
use App\Models\PaiementsTranche;
use App\Models\Participants;
use App\Models\Pays;
use App\Models\Rapports;
use App\Models\Reductions;
use App\Models\Tarificationsformation;
use App\Models\Tranches;
use App\Models\User;
use App\Services\contracts\AuditeursInterface;
use App\Services\util\GenarateurCodeService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client; 
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\QueryException;

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
    // Celui qui crée le compte (admin ou auditeur lui-même) : ne sera plus écrasé
    $createur = auth('api')->user();
 
    $email = strtolower(trim((string) $request->input('email')));
 
    /* =========================================================
     |  1. Validation
     ========================================================= */
    $validator = Validator::make(
        [
            'email'   => $email,
            'nom'     => trim((string) $request->input('nom')),
            'prenoms' => trim((string) $request->input('prenoms')),
        ],
        [
            'email'   => ['required', 'email'],
            'nom'     => ['required', 'string', 'max:255'],
            'prenoms' => ['required', 'string', 'max:255'],
        ]
    );
 
    if ($validator->fails()) {
        $message = $validator->errors()->first();
        return ResponseHandler::success($message, [
            'success' => false,
            'message' => $message,
        ]);
    }
 
    /* =========================================================
     |  2. L'auditeur existe déjà ? (contrôle rapide ; l'index unique
     |     reste le vrai garde-fou en cas de requêtes simultanées)
     ========================================================= */
    if (Auditeurs::where('email', $email)->exists()) {
        return ResponseHandler::success("Cet auditeur existe déjà", [
            'success' => false,
            'message' => "Un auditeur avec l'adresse {$email} existe déjà.",
        ]);
    }
 
    /* =========================================================
     |  3. Préparation
     ========================================================= */
    // Mot de passe saisi s'il est fourni, sinon généré.
    // C'est CE mot de passe qui est enregistré ET envoyé par mail.
    $motDePasse = $request->filled('password')
        ? $request->input('password')
        : $this->genarateurCodeService->genererCodeAlphaNumeric(8);
 
    $pays = $request->filled('pays') ? Pays::find($request->input('pays')) : null;
 
    /* =========================================================
     |  4. Création du compte + de l'auditeur : tout ou rien
     ========================================================= */
    try {
        [$user, $auditeur, $compteCree] = DB::transaction(function () use ($request, $email, $motDePasse, $pays) {
 
            // Compte utilisateur : réutilisé s'il existe déjà, créé sinon
            $user       = User::where('email', $email)->first();
            $compteCree = false;
 
            if (!$user) {
                // Renvoie l'utilisateur créé (rôle auditeur par défaut),
                // ou lève une exception qui annule toute la transaction
                $user = $this->usersService->createUser([
                    'email'    => $email,
                    'password' => $motDePasse,
                    'nom'      => $request->input('nom'),
                    'prenoms'  => $request->input('prenoms'),
                ]);
                $compteCree = true;
            }
 
            // Auditeur
            $auditeur = new Auditeurs();
            $auditeur->forceFill([
                'nom'              => trim((string) $request->input('nom')),
                'prenoms'          => trim((string) $request->input('prenoms')),
                'email'            => $email,
                'telephone'        => trim((string) $request->input('telephone')),
                'ville'            => trim((string) $request->input('ville')),
                'civilite'         => trim((string) $request->input('civilite')),
                'genre'            => trim((string) $request->input('genre')),
                'secteur_activite' => trim((string) $request->input('secteur_activite')),
                'organisme'        => trim((string) $request->input('organisme')),
                'intitule_poste'   => trim((string) $request->input('intitule_poste')),
                'pays'             => $pays?->nom_pays,
                'code_pays'        => $pays?->code_iso,
                'nationalite'      => $pays?->nationalite,
            ])->save();
 
            return [$user, $auditeur, $compteCree];
        });
 
    } catch (\DomainException $e) {
        // Erreur métier renvoyée par createUser (rôle par défaut absent, etc.)
        return ResponseHandler::success($e->getMessage(), [
            'success' => false,
            'message' => $e->getMessage(),
        ]);
 
    } catch (QueryException $e) {
        // 1062 = email déjà présent (index unique) : double soumission simultanée
        if (($e->errorInfo[1] ?? null) == 1062) {
            return ResponseHandler::success("Cet auditeur existe déjà", [
                'success' => false,
                'message' => "Un auditeur avec l'adresse {$email} existe déjà.",
            ]);
        }
        Log::error('Création auditeur - erreur SQL : ' . $e->getMessage());
        return ResponseHandler::error("Erreur lors de l'enregistrement de l'auditeur", 500, $e->getMessage());
 
    } catch (\Throwable $e) {
        Log::error('Création auditeur - erreur : ' . $e->getMessage());
        return ResponseHandler::error("Erreur lors de l'enregistrement de l'auditeur", 500, $e->getMessage());
    }
 
    /* =========================================================
     |  5. Après le commit : historique + mail
     ========================================================= */
    $this->genarateurCodeService->saveHistoriqueAction(
        'CREATION COMPTE AUDITEUR',
        "Création du compte de l'auditeur {$user->code_user} {$auditeur->nom} {$auditeur->prenoms}",
        $createur?->id ?? $user->id   // l'auteur de l'action, pas le nouvel auditeur
    );
 
    // Identifiants envoyés uniquement si un compte vient d'être créé : 
    if ($compteCree) {
        try {
            Mail::to($email)->queue(new NotificationCompte([
                'nom_prenoms'  => $user->name,
                'email'        => $email,
                'mot_de_passe' => $motDePasse,
                'subject'      => 'SIGWEB - CREATION DE COMPTE',
            ]));
        } catch (\Throwable $e) {
            // Le compte est créé : un échec d'envoi ne doit pas annuler l'enregistrement
            Log::error('Création auditeur - envoi mail : ' . $e->getMessage());
        }
    }
 
    return ResponseHandler::success("Enregistrement effectué avec succès", [
        'success'     => true,
        'message'     => $compteCree
            ? "Auditeur et compte créés. Les identifiants ont été envoyés à {$email}."
            : "Auditeur créé et rattaché au compte existant {$email}.",
        'auditeur_id' => $auditeur->id,
    ]);
}


    public function saveAuditeurOld(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement saveAuditeur() method.
        $user = auth('api')->user();
        // generator password
        $generate_password = $this->genarateurCodeService->genererCodeAlphaNumeric(8);
        $check_auditeur = Auditeurs::where("email", trim($request->email))->exists();
        $check_user = User::where("email", trim($request->email))->exists();

        if ($check_user == true && $check_auditeur == true) {
            return ResponseHandler::success("Cet auditeur existe déjà");
        }
        // create user acount
        $dataUser = ['email' => trim($request->email), 'password' => $request->password,'name' => $request->nom.' '.$request->prenoms];
        $userRequest = new Request($dataUser);
        $this->usersService->saveUser($userRequest);

        $user = User::whereEmail(trim($request->email))->first();

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
        $auditeur->setAttribute("organisme", trim($request->organisme));
        $auditeur->setAttribute("intitule_poste", trim($request->intitule_poste));
        // récupéré l'objet pays
        $pays = Pays::where("id", $request->get('pays'))->get()->first();
        $count_pays = Pays::where("id", trim($request->get('pays')))->count();
        if ($count_pays > 0) {
            # code...
            $auditeur->setAttribute('pays', $pays->nom_pays);
            $auditeur->setAttribute('code_pays', $pays->code_iso);
            $auditeur->setAttribute('nationalite', $pays->nationalite);
        } else {
            # code...
            // $auditeur->setAttribute('pays', $request->get('pays'));
        }

        try {
            $auditeur->save();

            // save historique action
            $action = 'CREATION COMPTE AUDITEUR';
            $description = "Création du compte de l'auditeur ".$user->code_user." ".$auditeur->nom." ".$auditeur->prenoms;
            $this->genarateurCodeService->saveHistoriqueAction($action, $description, $user->id);

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


        } catch (\Exception $e) {
            // Get error here
            return  ResponseHandler::success("Enregistrement effectué avec succès", $e->getMessage());

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

        $queryData = Auditeurs::select(["auditeurs.id", "auditeurs.nom", "auditeurs.prenoms", "auditeurs.email", "auditeurs.telephone", "auditeurs.pays", "auditeurs.updated_at"])
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
    /* =========================================================
     |  1. Résolution de l'auditeur et de l'utilisateur
     ========================================================= */
    if ($request->filled('idAuditeur')) {
        // Inscription faite par un admin pour un auditeur
        $auditeur = Auditeurs::find($request->idAuditeur);
    } else {
        // Inscription faite par l'auditeur lui-même
        $authUser = auth('api')->user();
        $auditeur = $authUser ? Auditeurs::where('email', $authUser->email)->first() : null;
    }
 
    if (!$auditeur) {
        return ResponseHandler::success("Le compte de l'auditeur n'existe pas", [
            'success' => false,
            'message' => "Le compte de l'auditeur n'existe pas",
        ], 404);
    }
 
    // Utilisateur pour l'historique : celui de l'auditeur, sinon la personne connectée
    $user   = User::where('email', $auditeur->email)->first();
    $userId = $user?->id ?? auth('api')->id();
 
    if (empty($request->formations_classique)) {
        return ResponseHandler::success("Aucune formation choisie", [
            'success' => false,
            'message' => "Veuillez choisir la formation à laquelle vous voulez vous inscrire.",
        ], 422);
    }
 
    /* =========================================================
     |  2. Préparation
     ========================================================= */
    $email         = trim($auditeur->email);
    $generateCode  = $this->genarateurCodeService->genererCodeAlphaNumeric(8);
 
    $listeFmExists = [];   // formations auxquelles l'auditeur est déjà inscrit
    $zoomAInscrire = [];   // [Inscriptions, Formations] à synchroniser après le commit
    $montant_total = 0;
    $formationSave = false;
 
    /* =========================================================
     |  3. Écritures en base : transaction + verrou
     ========================================================= */
    try {
        DB::transaction(function () use (
            $request, $auditeur, $email, $generateCode,
            &$listeFmExists, &$zoomAInscrire, &$montant_total, &$formationSave
        ) {
            // Verrou sur la ligne de l'auditeur : une 2e requête simultanée
            // du même auditeur attend ici jusqu'à la fin de la première.
            Auditeurs::whereKey($auditeur->id)->lockForUpdate()->first();
 
            foreach ($request->formations_classique as $data) {
 
                // "12_VISIO" -> [12, 'VISIO'] ; "12" -> [12, null]
                [$tarif_id, $type] = array_pad(explode('_', $data['tarif']), 2, null);
 
                $tarif_formation = Tarificationsformation::findOrFail($tarif_id);
                $formation_type  = $type ?: $tarif_formation->getGrilletarifaire->type_tarif;
 
                $formation = Formations::where('code_formation', $tarif_formation->code_formation)->firstOrFail();
 
                // Contrôle d'unicité (fait APRÈS le verrou, donc fiable)
                $dejaInscrit = Inscriptions::where('email', $email)
                    ->where('code_formation', $formation->code_formation)
                    ->exists();
 
                if ($dejaInscrit) {
                    $listeFmExists[] = $formation->code_formation;
                    continue;
                }
 
                $montant_total += $tarif_formation->getGrilletarifaire->montant;
 
                $inscrit = new Inscriptions();
                $inscrit->forceFill([
                    'email'              => $email,
                    'tarif_formation_id' => $tarif_formation->id,
                    'code_inscription'   => $generateCode,
                    'heure_inscription'  => date('d-m-Y H:i:s'),
                    'id_webinaire'       => $formation->code_formation,
                    'code_formation'     => $formation->code_formation,
                    'presentiel'         => $formation_type === 'PRESENTIEL' ? 1 : 0,
                    'type_formation'     => $formation_type,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ])->save();
 
                // Zoom : on mémorise, on traitera APRÈS le commit
                if ($formation_type === 'VISIO' && !empty($formation->id_webinaire)) {
                    $zoomAInscrire[] = [$inscrit, $formation];
                }
 
                $formationSave = true;
            }
 
            // Rien de nouveau : pas de paiement à créer
            if (!$formationSave) {
                return;
            }
 
            /* ---------- Paiement + détails ---------- */
            $dejaPaye = Paiements::where('email', $email)
                ->where('code_inscription', $generateCode)
                ->exists();
 
            if ($dejaPaye) {
                return;
            }
 
            $paiement = new Paiements();
            $paiement->forceFill([
                'reference'        => $this->genarateurCodeService->genererNumeroPaiement(),
                'email'            => $email,
                'telephone'        => $auditeur->telephone,
                'code_inscription' => $generateCode,
                'montant_total'    => $montant_total,
                'montant_restant'  => $montant_total,
                'statut_paiement'  => 'EN ATTENTE',
                'type'             => 'FORMATION',
            ])->save();
 
            $inscriptions = Inscriptions::where('code_inscription', $generateCode)->get();
 
            foreach ($inscriptions as $insc) {
                $tarif = Tarificationsformation::findOrFail($insc->tarif_formation_id);
 
                (new Detailspaiements())->forceFill([
                    'reference_paiement'        => $paiement->reference,
                    'code_formation'            => $insc->code_formation,
                    'code_inscription'          => $insc->code_inscription,
                    'montant_restant'           => $tarif->getGrilletarifaire->montant,
                    'email'                     => $email,
                    'id_tarification_formation' => $insc->tarif_formation_id,
                    'statut_paiement'           => 'IMPAYE',
                ])->save();
            }
        }, 3); // 3 tentatives en cas de deadlock
 
    } catch (QueryException $e) {
        // 1062 = violation de la contrainte unique (inscriptions_email_formation_unique)
        if (($e->errorInfo[1] ?? null) == 1062) {
            return ResponseHandler::success("Inscription déjà enregistrée", [
                'success' => false,
                'message' => "Une inscription identique est déjà enregistrée.",
            ], 409);
        }
        Log::error('Inscription formation - erreur SQL : ' . $e->getMessage());
        return ResponseHandler::error("Erreur lors de l'inscription", 500, $e->getMessage());
 
    } catch (\Throwable $e) {
        Log::error('Inscription formation - erreur : ' . $e->getMessage());
        return ResponseHandler::error("Erreur lors de l'inscription", 500, $e->getMessage());
    }
 
    /* =========================================================
     |  4. Aucune nouvelle inscription : toutes existaient déjà
     ========================================================= */
    if (!$formationSave) {
        return ResponseHandler::success("Vous avez déjà une inscription en cours pour cette formation", [
            'success' => false,
            'message' => "Vous avez déjà une inscription en cours pour cette formation " . implode(', ', $listeFmExists),
        ], 409);
    }
 
    /* =========================================================
     |  5. Après le commit : actions externes (ne bloquent jamais l'inscription)
     ========================================================= */
 
    // 5.1 Zoom
    foreach ($zoomAInscrire as [$inscrit, $formation]) {
        $this->inscrireSurZoom($inscrit, $formation, $auditeur, $userId);
    }
 
    // 5.2 Historique
    $this->genarateurCodeService->saveHistoriqueAction(
        'INSCRIPTION FORMATION NON DSC',
        "Inscription de l'auditeur {$auditeur->nom} {$auditeur->prenoms} à la formation référence {$generateCode}",
        $userId
    );
 
    // 5.3 Mail
    $contenu = [
        'nom'              => $auditeur->nom,
        'prenoms'          => $auditeur->prenoms,
        'formations'       => $request->formations_classique,
        'subject'          => 'Inscription sur SIGWEB de ' . $auditeur->nom . ' ' . $auditeur->prenoms,
        'email'            => $email,
        'code_inscription' => $generateCode,
        'url_proforma'     => $this->genarateurCodeService->generateUrlSigned('pro-forma', ['reference' => $generateCode]),
    ];
 
    try {
        $mailable = $montant_total == 0
            ? new InscriptionGratuit($contenu)
            : new Inscriptionpresentiel($contenu);
 
        Mail::to($email)->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue($mailable);
    } catch (\Throwable $e) {
        Log::error('Inscription formation - envoi mail : ' . $e->getMessage());
    }
 
    /* =========================================================
     |  6. Réponse
     ========================================================= */
    $message = 'Inscription effectuée';
    if (!empty($listeFmExists)) {
        $message .= '. Déjà inscrit à : ' . implode(', ', $listeFmExists);
    }
 
    return ResponseHandler::success("Inscription effectuée avec succès", [
        'code_inscription'   => $generateCode,
        'message'            => $message,
        'success'            => true,
        'formations_ignorees' => $listeFmExists,
    ]);
}
 
 
/**
 * Inscrit un auditeur à un webinaire Zoom et historise le résultat.
 * Ne lève jamais d'exception : un échec Zoom ne doit pas bloquer l'inscription.
 */
private function inscrireSurZoom(Inscriptions $inscrit, Formations $formation, Auditeurs $auditeur, ?int $userId): void
{
    if (empty($formation->id_webinaire) || empty($auditeur->nom)) {
        return;
    }
 
    $validator = Validator::make(['email' => $auditeur->email], [
        'email' => ['required', 'email:rfc,dns'],
    ]);
    if ($validator->fails()) {
        return;
    }
 
    $codeFm      = $formation->code_formation;
    $description = "Synchronisation zoom de l'auditeur : {$auditeur->nom} {$auditeur->prenoms} email : {$auditeur->email} CODE FM : {$codeFm}";
 
    try {
        // 1. Rafraîchir le token
        $authClient = new Client([
            'timeout' => 10,
            'headers' => [
                'Authorization' => env('AUTHORIZATION'),
                'Host'          => env('HOST'),
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
        ]);
 
        $tokenResponse = json_decode($authClient->post('https://zoom.us/oauth/token', [
            'form_params' => [
                'grant_type'    => 'refresh_token',
                'refresh_token' => env('REFRESH_TOKEN'),
            ],
        ])->getBody(), true);
 
        $accessToken = $tokenResponse['access_token'] ?? null;
        if (!$accessToken) {
            throw new \RuntimeException('Token Zoom introuvable dans la réponse');
        }
 
        // 2. Inscription au webinaire
        $zoomClient = new Client([
            'timeout' => 10,
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept'        => 'application/json',
            ],
        ]);
 
        $response = $zoomClient->post("https://api.zoom.us/v2/webinars/{$formation->id_webinaire}/registrants", [
            'json' => [
                'first_name' => $auditeur->prenoms,
                'last_name'  => $auditeur->nom,
                'email'      => $auditeur->email,
                'country'      => $auditeur->code_pays ?? 'BJ',
                'comments'   => 'Inscription depuis SIGWEB ERSUMA',
            ],
        ]);
 
        if ($response->getStatusCode() === 201) {
            $inscrit->forceFill(['sync_zoom' => true])->save();
            $this->genarateurCodeService->saveHistoriqueAction("SUCCES SYNCHRO ZOOM {$codeFm}", $description, $userId);
            return;
        }
 
        $this->genarateurCodeService->saveHistoriqueAction("ECHEC SYNCHRO ZOOM {$codeFm}", $description, $userId);
 
    } catch (ClientException $e) {
        // 4xx : déjà inscrit, webinaire introuvable...
        Log::error('Zoom API erreur client: ' . $e->getResponse()->getBody()->getContents());
        $this->genarateurCodeService->saveHistoriqueAction("ECHEC SYNCHRO ZOOM {$codeFm}", $description, $userId);
 
    } catch (ServerException $e) {
        Log::error('Zoom API erreur serveur: ' . $e->getMessage());
        $this->genarateurCodeService->saveHistoriqueAction("ECHEC SYNCHRO ZOOM {$codeFm}", $description, $userId);
 
    } catch (GuzzleException | \RuntimeException $e) {
        // timeout, connexion refusée, token absent...
        Log::error('Zoom API erreur requête: ' . $e->getMessage());
        $this->genarateurCodeService->saveHistoriqueAction("ECHEC SYNCHRO ZOOM {$codeFm}", $description, $userId);
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
        $paiement = Paiements::whereCodeInscription($codeInscription)->first();
        $havePaiement = false;
        if($paiement && !empty($paiement->num_transaction) && $paiement->is_paiementpartiel == 0){
            $havePaiement = true;
        }
        $dataResponse = [
            'liste_formation' => $liste_formation,
            'auditeur' => $auditeur,
            'havePaiement' => $havePaiement
        ];
        return  ResponseHandler::success("ok",$dataResponse);
    }

    public function storeInscriptionPaiement(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement storeInscriptionPaiement() method.
        // récupérer les objet
        $inscrit = Inscriptions::where("code_inscription", $data->code_inscription)->orderBy("id", "DESC")->get()->first();
        $auditeur = Auditeurs::whereEmail($inscrit->email)->first();
        $modePaiement = Modepaiements::whereLibelle($data->mode_paiement)->first();
        $formations = collect($data->formations)->map(fn($item) => (object) $item);

        // vérifier si le paiement existe déjà sinon on crée le paiement
        $checkPaiement = Paiements::whereEmail(trim($data->email))->whereCodeInscription($inscrit->code_inscription)->exists();
        if($checkPaiement){
            try {
                //############################  le paiement existe ############################
                // 1 - Mise à jours des informations (montant à payer) dans la table paiement
                $getPaiement = Paiements::whereEmail(trim($data->email))->whereCodeInscription($inscrit->code_inscription)->first();
                $paiement = Paiements::find($getPaiement->id);
                $paiement->setAttribute('montant_total', $data->montant_total);
                $paiement->setAttribute('montant_restant', $data->montant_total);
                $paiement->setAttribute('id_modepaiement', $modePaiement->id);
                if ($data->montant_total == 0) {
                    # code...
                    $paiement->setAttribute('num_transaction', "GRATUIT");
                    $paiement->setAttribute('statut_transaction', "GRATUIT");
                }
                $paiement->update();

                // 2- Mise à jour des informations dans la table détails paiements
                foreach ($formations as $formation) {
                    $checkDetail = Detailspaiements::where("reference_paiement",$paiement->reference)
                        ->where('code_formation', $formation->code_formation)
                        ->where('code_inscription', $formation->code_inscription)
                        ->where('id_tarification_formation', $formation->tarif_formation_id)
                        ->first();
                    if ($checkDetail) {
                        // si l'objet existe, on fait la mise à jour des informations
                        $upDetail = Detailspaiements::find($checkDetail->id);
                        $upDetail->setAttribute('montant_restant', $formation->montant_a_payer);
                        // vérifier si la réduction est appliquée
                        if ($formation->taux_reduction !== 0 && $formation->taux_reduction !== '0'){
                            $id = explode('-',$formation->taux_reduction)[0];
                            $reduction = Reductions::find($id);
                            $upDetail->setAttribute('id_reduction', $reduction->id);
                        }
                        // enregistrement de la preuve si ça existe
                        if (!empty($data->preuve)) {
                            $preuve_paiement = $data->preuve;
                            $extension = $preuve_paiement->getClientOriginalExtension();
                            $preuve_name = date('Ymd-His') . '.' . $extension;
                            $preuve_paiement->move(public_path('preuve'), $preuve_name);
                            $upDetail->setAttribute('preuve_path', $preuve_name);
                        }
                        // do update
                        $upDetail->update();
                    }
                }

                // 3- response API
                $dataResponse = [
                    'reference_paiement' => $paiement->reference,
                    'message' => 'Enregistrement effectué',
                    'option' => $modePaiement->libelle,
                    'success' => true,
                ];

                return ResponseHandler::success("Opération effectuée avec succès",$dataResponse);

            }catch (\Exception $e) {
                // Get error here
                return  ResponseHandler::success("Erreur lors de l'enregistrement", $e->getMessage(),500);
            }

        }else{
            //############################ il faut créer le paiement ############################
            // 1- générer le numero de reference facture : ERS-2025-FAC-000001
                $reference_paiement =$this->genarateurCodeService->genererNumeroPaiement();

                // enregistrement du paiement
                $statut_details_paiement = "IMPAYE";
                $statut_paiement = "EN ATTENTE";

                //2-  vérifier si le paiement n'est pas déjà enregistré pour procéder à l'enregistrement
                $checkExistingPaiement = Paiements::whereReference($reference_paiement)->exists();
                if (!$checkExistingPaiement) { // si le paiement n'existe pas encore

                    $paiement = new Paiements();
                    $paiement->setAttribute('reference', $reference_paiement);
                    $paiement->setAttribute('email', $auditeur->email);
                    $paiement->setAttribute('telephone', $auditeur->telephone);
                    $paiement->setAttribute('code_inscription', $inscrit->code_inscription);
                    $paiement->setAttribute('montant_total', $data->montant_total);
                    $paiement->setAttribute('montant_restant', $data->montant_total);
                    $paiement->setAttribute('statut_paiement', $statut_paiement);
                    $paiement->setAttribute('type', "FORMATION");

                    // enregistrement des détails de la facture

                    try {
                        //code...
                        $paiement->save();

                        foreach ($formations as $formation) {
                            // enregistrement détails paiement
                            $details_paiement = new Detailspaiements();
                            $details_paiement->setAttribute('reference_paiement', $paiement->reference);
                            $details_paiement->setAttribute('code_formation', $inscrit->code_formation);
                            $details_paiement->setAttribute('code_inscription', $inscrit->code_inscription);
                            $details_paiement->setAttribute('montant_restant', $formation->montant_a_payer);
                            $details_paiement->setAttribute('email', $inscrit->email);
                            $details_paiement->setAttribute('id_tarification_formation', $formation->tarif_formation_id);
                            $details_paiement->setAttribute('statut_paiement', 'IMPAYE');
                            // vérifier si la réduction est appliquée
                            if ($formation->taux_reduction > 0){
                                $reduction = Reductions::where('pourcentage', $formation->taux_reduction)->first();
                                $details_paiement->setAttribute('id_reduction', $reduction->id);
                            }
                            // enregistrement de la preuve si ça existe
                            if (!empty($data->preuve)) {
                                $preuve_paiement = $data->preuve;
                                $extension = $preuve_paiement->getClientOriginalExtension();
                                $preuve_name = date('Ymd-His') . '.' . $extension;
                                $preuve_paiement->move(public_path('preuve'), $preuve_name);
                                $details_paiement->setAttribute('preuve_path', $preuve_name);
                            }
                            $details_paiement->save();
                        }

                        // 3- response API
                        $dataResponse = [
                            'reference_paiement' => $paiement->reference,
                            'message' => 'Enregistrement effectué',
                            'option' => $modePaiement->libelle,
                            'success' => true,
                        ];

                        return ResponseHandler::success("Opération effectuée avec succès",$dataResponse);


                    } catch (\Exception $e) {
                        //throw $th;
                        return  ResponseHandler::success("Erreur lors de l'enregistrement", $e->getMessage(), 500);
                    }

                }
        }

    }

    public function getListePays(): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getListePays() method.
        $data = Pays::orderBy('nom_pays')->get(['nom_pays as name','id as code','code_iso']);

        return  ResponseHandler::success("ok",$data);

    }

    public function getIndicateurs(): \Illuminate\Http\JsonResponse
    { 
        $user = auth('api')->user();
        $nbreInscription = Inscriptions::whereEmail($user->email)->count();
        $nbreAttestation = Rapports::whereEmail($user->email)->whereAttestationSend(true)->count();
        $nbreParticipation = Paiements::whereEmail($user->email)->where('montant_paye','>',0)->count();
        $nbreParticipationGratuit = Paiements::whereEmail($user->email)
            ->whereIn('montant_paye',[0,10000])->count();

        $data = [
            'nbreInscription' => $nbreInscription,
            'nbreAttestation' => $nbreAttestation,
            'nbreParticipation' => $nbreParticipation,
            'nbreParticipationGratuit' => $nbreParticipationGratuit
        ];

        return  ResponseHandler::success("ok",$data);

    }

    public function getLastThreeFormation(): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getListePays() method.
        $date_now = date('Y-m-d');
        $data = Formations::select('formations.id','formations.code_formation','formations.theme','formations.date_debut')
            ->with('getTypeformation:id,libelle')
            ->with('tarifications')
            ->whereDate('formations.date_fin','>=',$date_now)
            ->where('formations.is_formation_dsc',false)
            ->where('formations.statut','ACTIF')
            ->where('formations.enable',true)
            ->orderBy("formations.date_debut","ASC")
            ->limit(3)->get() ;

        return  ResponseHandler::success("ok",$data);
    }

    public function getListeInscriptions(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getListeInscriptions() method.
        /*
        $data = Inscriptions::query()
            ->leftJoin('formations', 'formations.code_formation', '=', 'inscriptions.code_formation')
            ->leftJoin('inscriptions_dscs', 'inscriptions_dscs.code_inscription', '=', 'inscriptions.code_inscription')
            ->leftJoin('tarificationsformations', 'tarificationsformations.id', '=', 'inscriptions.tarif_formation_id')
            ->leftJoin('grilletarifaires', 'grilletarifaires.id', '=', 'tarificationsformations.id_grilletarifaire')
            ->leftJoin('paiements', 'paiements.code_inscription', '=', 'inscriptions.code_inscription')
            ->select([
                'formations.id as id_formation',
                'formations.code_formation',
                'formations.theme',
                'formations.date_debut',
                'formations.is_formation_dsc',
                'inscriptions_dscs.id as id_dsc',
                'inscriptions.id as id_inscription',
                'inscriptions.code_inscription',
                'inscriptions.email',
                'paiements.reference',
                'paiements.num_transaction',
                'paiements.statut_paiement',
                'grilletarifaires.libelle'
            ]) 
            ->addSelect([
                // verifier le paiement
                \DB::raw('CASE WHEN paiements.num_transaction IS NOT NULL THEN 1 ELSE 0 END as checkPaiement'),
                // verifier le paiement
                \DB::raw('CASE WHEN paiements.reference IS NOT NULL THEN 1 ELSE 0 END as checkReferencePaiement'),
                // check dsc valide
                DB::raw('CASE WHEN inscriptions_dscs.id IS NOT NULL THEN 1 ELSE 0 END as checkdsc'),

                // checkdate : true si l'année de date_paiement > 2024
                \DB::raw('CASE
                    WHEN formations.date_debut IS NOT NULL
                     AND YEAR(formations.date_debut) > 2024
                    THEN 1 ELSE 0
                  END as checkDsc2024')
            ])
            ->where('inscriptions.email', $request->email)
            ->orderBy("inscriptions.created_at","DESC")
            ->get(); */

    $lastPaiement = DB::table('paiements as p')
    ->select([
        'p.code_inscription',
        'p.reference',
        'p.num_transaction',
        'p.statut_paiement',
    ])
    ->join(
        DB::raw('(
            SELECT code_inscription, MAX(id) as max_id
            FROM paiements
            GROUP BY code_inscription
        ) lp'),
        function ($join) {
            $join->on('p.id', '=', 'lp.max_id');
        }
    );

$data = Inscriptions::query()
    ->leftJoin(
        'formations',
        'formations.code_formation',
        '=',
        'inscriptions.code_formation'
    )
    ->leftJoin(
        'inscriptions_dscs',
        'inscriptions_dscs.code_inscription',
        '=',
        'inscriptions.code_inscription'
    )
    ->leftJoin(
        'tarificationsformations',
        'tarificationsformations.id',
        '=',
        'inscriptions.tarif_formation_id'
    )
    ->leftJoin(
        'grilletarifaires',
        'grilletarifaires.id',
        '=',
        'tarificationsformations.id_grilletarifaire'
    )
    ->leftJoinSub($lastPaiement, 'paiements', function ($join) {
        $join->on(
            'paiements.code_inscription',
            '=',
            'inscriptions.code_inscription'
        );
    })
    ->select([
        'formations.id as id_formation',
        'formations.code_formation',
        'formations.theme',
        'formations.date_debut',
        'formations.is_formation_dsc',

        'inscriptions_dscs.id as id_dsc',

        'inscriptions.id as id_inscription',
        'inscriptions.code_inscription',
        'inscriptions.email',

        'paiements.reference',
        'paiements.num_transaction',
        'paiements.statut_paiement',

        'grilletarifaires.libelle',
    ])
    ->addSelect([
        DB::raw("
            CASE
                WHEN paiements.num_transaction IS NOT NULL
                THEN 1
                ELSE 0
            END as checkPaiement
        "),
        DB::raw("
            CASE
                WHEN paiements.reference IS NOT NULL
                THEN 1
                ELSE 0
            END as checkReferencePaiement
        "),
        DB::raw("
            CASE
                WHEN inscriptions_dscs.id IS NOT NULL
                THEN 1
                ELSE 0
            END as checkdsc
        "),
        DB::raw("
            CASE
                WHEN formations.date_debut IS NOT NULL
                AND YEAR(formations.date_debut) > 2024
                THEN 1
                ELSE 0
            END as checkDsc2024
        "),
    ])
    ->where('inscriptions.email', $request->email)
    ->orderBy('inscriptions.created_at', 'DESC')
    ->get();

        return  ResponseHandler::success("ok",$data);
    }

    public function updateAuditeur(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement updateAuditeur() method.
        $check_auditeur = Auditeurs::where("email", $request->email)->exists();
        $check_user = User::where("email", $request->email)->exists();

        if (!$check_auditeur) {
            // le compte de l'auditeur n'existe pas
            return  ResponseHandler::error("Le compte de l'auditeur n'existe pas.",500 );
        }

        if (!$check_user) {
            // le compte de l'auditeur n'existe pas
            return  ResponseHandler::error("Le compte de l'utilisateur n'existe pas.",500 );
        }

        $getAuditeur = Auditeurs::where("email", $request->email)->first();

        // register data into auditeur table
        $auditeur = Auditeurs::find($getAuditeur->id);
        $auditeur->setAttribute("nom", trim($request->nom));
        $auditeur->setAttribute("prenoms", trim($request->prenoms));
        $auditeur->setAttribute("telephone", trim($request->telephone));
        $auditeur->setAttribute("ville", trim($request->ville));
        $auditeur->setAttribute("civilite", trim($request->civilite));
        $auditeur->setAttribute("genre", trim($request->genre));
        $auditeur->setAttribute("secteur_activite", trim($request->secteur_activite));
        $auditeur->setAttribute("organisme", trim($request->organisme));
        $auditeur->setAttribute("intitule_poste", trim($request->intitule_poste));
        // récupéré l'objet pays
        $pays = Pays::where("id", $request->get('pays'))->get()->first();
        $count_pays = Pays::where("id", trim($request->get('pays')))->count();
        if ($count_pays > 0) {
            # code...
            $auditeur->setAttribute('pays', $pays->nom_pays);
            $auditeur->setAttribute('code_pays', $pays->code_iso);
            $auditeur->setAttribute('nationalite', $pays->nationalite);
        }

        try {
            $auditeur->save();

            // mise à jour des informations dans le compte auditeur
            $getUser = User::whereEmail($request->email)->first();
            $user = User::find($getUser->id);
            $user->setAttribute('name',$request->get('nom').' '.$request->get('prenoms'));
            // sauvegarde photo si disponible
            $name_photo = null;
            if ($request->photo) {
                $photo = $request->photo;
                $extension = $photo->getClientOriginalExtension();
                $name_photo = 'photo_'.date('Ymd-His'). '.'.$photo->extension();
                //$photo->move(public_path('img/visuel/'), $name_photo);
                $photo->storeAs('photo', $name_photo, 'public');
                $user->setAttribute('logo_path', $name_photo);
            }
            $user->update();

        } catch (\Exception $e) {
            // Get error here
            return  ResponseHandler::success("Echec lors de la mise à jour", $e->getMessage(),400);

        }

        // save historique action
            $action = 'MISE A JOUR INFORMATION AUDITEUR';
            $description = "Mise à jour des informations de l'auditeur ".$auditeur->nom." ".$auditeur->prenoms;
            $this->genarateurCodeService->saveHistoriqueAction($action, $description, $user->id);

        return  ResponseHandler::success("Mise à jour effectuée avec succès");
    }

    public function getMesPaiements(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getMesPaiements() method.
        /** PAIEMENTS EN ATTENTE */
        $paiements_attente = DB::table('detailspaiements')
            ->join('paiements', 'detailspaiements.reference_paiement', '=', 'paiements.reference')
            ->where("detailspaiements.email", $request->email)
            ->where('detailspaiements.statut_paiement', 'IMPAYE')
            ->where('detailspaiements.statut_traitement', 'ATTENTE')
            ->where('detailspaiements.montant_restant', '>', 0)
            ->where('paiements.type', '!=', 'dsc')
            ->orderBy('detailspaiements.updated_at', 'desc')
            ->select([
                'detailspaiements.reference_paiement as reference',
                'detailspaiements.montant_restant as montant',
                'detailspaiements.code_inscription',
                'detailspaiements.email',
                'detailspaiements.updated_at',
                'detailspaiements.code_formation',
                'paiements.type',
                'paiements.is_paiement_partiel'
            ])->get();

        $paiements_attente_dsc = DB::table('paiements')
            ->join('inscriptions','inscriptions.code_inscription','=','paiements.code_inscription')
            ->join('formations','formations.code_formation','=','inscriptions.code_formation')
            ->leftJoin('inscriptions_dscs', 'inscriptions_dscs.code_inscription', '=', 'inscriptions.code_inscription')
            ->where("paiements.email", $request->email)
            ->whereNull('num_transaction')
            ->where('montant_restant', '>=', 0)
            ->where('statut_paiement', '=', 'EN ATTENTE')
            ->where('paiements.type', '=', 'dsc')
            ->orderBy('paiements.updated_at', 'desc')
            ->select([
                'paiements.reference',
                'paiements.montant_restant as montant',
                'paiements.code_inscription',
                'paiements.email',
                'paiements.updated_at',
                'inscriptions.code_formation',
                'paiements.type',
                'paiements.is_paiement_partiel',
                'inscriptions_dscs.id as id_insc_dsc',
                'formations.date_debut'
            ])
            ->addSelect([
                // check dsc valide
                DB::raw('CASE WHEN inscriptions_dscs.id IS NOT NULL THEN 1 ELSE 0 END as checkValidationDsc'),

                // checkdate : true si l'année de date_paiement > 2024
                DB::raw('CASE
                    WHEN formations.date_debut IS NOT NULL
                     AND YEAR(formations.date_debut) > 2024
                    THEN 1 ELSE 0
                  END as checkDsc2024')
            ])->get();


        // Liste des paiements en cours de validation
        $liste_paiement_encours_dsc = DB::table('paiements_tranches')
            ->leftjoin('detailspaiements', 'detailspaiements.id', '=', 'paiements_tranches.details_paiements_id')
            ->leftjoin('auditeurs', 'auditeurs.email', '=', 'detailspaiements.email')
            ->join('modepaiements', 'modepaiements.id', '=', 'paiements_tranches.modepaiement_id')
            ->where('paiements_tranches.statut_traitement', 'ATTENTE')
            ->where("detailspaiements.email", $request->email)
            ->select('paiements_tranches.montant_paye', 'paiements_tranches.reference', 'paiements_tranches.updated_at', 'modepaiements.libelle as mode_paiement')
            ->orderBy('paiements_tranches.id', 'DESC')
            ->get();

        $liste_paiement_encours = DB::table('paiements')
            ->leftjoin('auditeurs', 'auditeurs.email', '=', 'paiements.email')
            ->join('modepaiements', 'modepaiements.id', '=', 'paiements.id_modepaiement')
            ->select('paiements.reference', 'paiements.montant_paye','paiements.montant_restant' ,'paiements.num_transaction', 'paiements.updated_at', 'paiements.created_at', 'modepaiements.libelle as mode_paiement', 'paiements.type', 'paiements.code_inscription')
            ->where('statut_traitement', 'ATTENTE')
            ->where('is_paiement_partiel', false)
            ->where('paiements.type', '!=', 'dsc')
            ->where("paiements.email", $request->email)
            ->whereNotNull('num_transaction')
            ->orderBy('paiements.id', 'DESC')
            ->get();

        $liste_paiement_partiel_encours = DB::table('detailspaiements')
            ->join('modepaiements', 'modepaiements.id', '=', 'detailspaiements.modepaiement_id')
            ->where("detailspaiements.email", $request->email)
            ->where('statut_paiement', 'PAYE')
            ->where('statut_traitement', 'ATTENTE')
            ->where('is_paiement_partiel', true)
            ->get(["detailspaiements.id","detailspaiements.montant_paye", "detailspaiements.reference_paiement as reference", "modepaiements.libelle as mode_paiement", "detailspaiements.updated_at", "detailspaiements.code_inscription"]);

        // Liste des paiements validés
        $liste_paiement_partiel_dsc = DB::table('paiements_tranches')
            ->leftjoin('detailspaiements', 'detailspaiements.id', '=', 'paiements_tranches.details_paiements_id')
            ->leftjoin('auditeurs', 'auditeurs.email', '=', 'detailspaiements.email')
            ->join('modepaiements', 'modepaiements.id', '=', 'paiements_tranches.modepaiement_id')
            ->where('paiements_tranches.statut_traitement', 'VALIDER')
            ->where("detailspaiements.email", $request->email)
            ->select('paiements_tranches.montant_paye', 'detailspaiements.code_inscription', 'auditeurs.nom', 'auditeurs.prenoms', 'auditeurs.telephone', 'auditeurs.email', 'paiements_tranches.reference', 'paiements_tranches.num_transaction', 'paiements_tranches.updated_at', 'modepaiements.libelle as mode_paiement')
            ->orderBy('paiements_tranches.id', 'DESC')
            ->get();

        $paiements = DB::table('paiements')
            ->join('modepaiements','modepaiements.id','=','paiements.id_modepaiement')
            ->where("email", $request->email)
            ->whereNotNull('num_transaction')
            ->where('statut_traitement', 'VALIDER')
            ->where('is_paiement_partiel', false)
            ->where('paiements.type', '!=', 'dsc')
            ->get(['paiements.reference','paiements.montant_paye','paiements.updated_at','modepaiements.libelle as mode_paiement','paiements.type','paiements.montant_restant']);

        $paiements_partiel = DB::table('detailspaiements')
            ->join('modepaiements', 'modepaiements.id', '=', 'detailspaiements.modepaiement_id')
            ->where("email", $request->email)
            ->where('statut_paiement', 'PAYE')
            ->where('statut_traitement', 'VALIDER')
            ->where('is_paiement_partiel', true)
            ->get(["detailspaiements.id", "detailspaiements.reference_paiement as reference", "modepaiements.libelle as mode_paiement","detailspaiements.montant_paye", "detailspaiements.updated_at"]);


        $data = [
            'paiementNonEffectue' => $paiements_attente,
            'paiementNoneffectueDsc' => $paiements_attente_dsc,
            'liste_paiement_partiel_encours' => $liste_paiement_partiel_encours,
            'liste_paiement_encours' => $liste_paiement_encours,
            'liste_paiement_encours_dsc' => $liste_paiement_encours_dsc,
            'liste_paiement_partiel_dsc' => $liste_paiement_partiel_dsc,
            'paiements' => $paiements,
            'paiements_partiel' => $paiements_partiel
        ];

        return  ResponseHandler::success("ok",$data);

    }

    public function updateInformationInscription(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement updateInformationInscription() method.
        $inscrit = Inscriptions::find($request->inscription_id);
        $pays = Pays::find($request->pays_id);
        // update auditeur
        $auditeurs = Auditeurs::where("email",$inscrit->email)->first();
        // sauvegarde de la modification
        $auditeurs->setAttribute('nom', trim($request->get('nom')));
        $auditeurs->setAttribute('prenoms', trim($request->get('prenoms')));
        $auditeurs->setAttribute('genre', trim($request->get('genre')));
        $auditeurs->setAttribute('civilite', trim($request->get('civilite')));
        // récupéré l'objet pays
        if ($pays) {
            # code...
            $auditeurs->setAttribute('pays', $pays->nom_pays);
            $auditeurs->setAttribute('code_pays', $pays->code_iso);
            $auditeurs->setAttribute('nationalite', $pays->nationalite);
        }
        $auditeurs->setAttribute('updated_at', new \DateTime());
        $auditeurs->update();
        // update info dans la table user
        $user = User::whereEmail($request->email)->first();
        if($user){
            $updUser = User::find($user->id);
            $name = trim($request->nom).' '.trim($request->prenoms);
            $updUser->setAttribute('name', $name);
            $updUser->update();
        }

         // save historique action
            $action = 'MISE A JOUR INFORMATION AUDITEUR';
            $description = "Mise à jour des informations de l'auditeur ".$auditeurs->nom." ".$auditeurs->prenoms;
            $this->genarateurCodeService->saveHistoriqueAction($action, $description, $user->id);


        return ResponseHandler::success("Modification effectuée avec succès",['success'=>true,'code_formation' => $inscrit->code_formation]);

    }


    public function storeInscriptionFormationDsc(Request $request): \Illuminate\Http\JsonResponse
    {
        /* =========================================================
        |  1. Résolution de l'auditeur et de l'utilisateur
        ========================================================= */
        if ($request->filled('idAuditeur')) {
            $auditeur = Auditeurs::find($request->idAuditeur);
        } else {
            $authUser = auth('api')->user();
            $auditeur = $authUser ? Auditeurs::where('email', $authUser->email)->first() : null;
        }

        if (!$auditeur) {
            return ResponseHandler::success("Le compte de l'auditeur n'existe pas", [
                'success' => false,
                'message' => "Le compte de l'auditeur n'existe pas",
            ]);
        }

        $user   = User::where('email', $auditeur->email)->first();
        $userId = $user?->id ?? auth('api')->id();

        /* =========================================================
        |  2. Une et une seule formation
        ========================================================= */
        $formationsDsc = $request->input('formations_dsc', []);

        if (count($formationsDsc) === 0) {
            return ResponseHandler::success("Veuillez choisir la formation à laquelle vous voulez vous inscrire.", [
                'success' => false,
                'message' => "Veuillez choisir la formation à laquelle vous voulez vous inscrire.",
            ]);
        }
        if (count($formationsDsc) > 1) {
            return ResponseHandler::success("Vous ne pouvez vous inscrire qu'à une seule formation DSC par année.", [
                'success' => false,
                'message' => "Vous ne pouvez vous inscrire qu'à une seule formation DSC par année.",
            ]);
        }

        $tarifId = $formationsDsc[0]['tarif_id'] ?? null;
        if (!$tarifId) {
            return ResponseHandler::success("La formation sélectionnée est invalide.", [
                'success' => false,
                'message' => "La formation sélectionnée est invalide.",
            ]);
        }

        /* =========================================================
        |  3. Préparation
        ========================================================= */
        $email        = trim($auditeur->email);
        $generateCode = $this->genarateurCodeService->genererCodeAlphaNumeric(8);
        $montant_total = 0;

        /* =========================================================
        |  4. Écritures en base : transaction + verrou
        ========================================================= */
        try {
            DB::transaction(function () use ($auditeur, $email, $tarifId, $generateCode, &$montant_total) {

                // Une 2e requête simultanée du même auditeur attend ici
                Auditeurs::whereKey($auditeur->id)->lockForUpdate()->first();

                // --- Formation choisie ---
                $tarifFormation = Tarificationsformation::findOrFail($tarifId);
                $formation      = Formations::where('code_formation', $tarifFormation->code_formation)->firstOrFail();

                if (empty($formation->date_debut) || empty($formation->date_fin)) {
                    throw new \DomainException("Les dates de la formation {$formation->code_formation} ne sont pas renseignées.");
                }

                // --- Règle : une seule formation DSC par période (vérifiée SOUS le verrou) ---
                // Deux périodes se chevauchent si : debut_existante <= fin_nouvelle ET fin_existante >= debut_nouvelle
                $dscEnConflit = Inscriptions::query()
                    ->join('paiements', 'paiements.code_inscription', '=', 'inscriptions.code_inscription')
                    ->join('formations', 'formations.code_formation', '=', 'inscriptions.code_formation')
                    ->where('inscriptions.email', $email)
                    ->where('paiements.type', 'DSC')
                    ->whereDate('formations.date_debut', '<=', $formation->date_fin)
                    ->whereDate('formations.date_fin', '>=', $formation->date_debut)
                    ->select('formations.code_formation', 'formations.date_debut', 'formations.date_fin')
                    ->first();

                if ($dscEnConflit) {
                    $du = \Carbon\Carbon::parse($dscEnConflit->date_debut)->format('d/m/Y');
                    $au = \Carbon\Carbon::parse($dscEnConflit->date_fin)->format('d/m/Y');
                    throw new \DomainException(
                        "Vous êtes déjà inscrit à la formation DSC {$dscEnConflit->code_formation} (du {$du} au {$au}). "
                        . "Une seule formation DSC est autorisée sur une même période."
                    );
                }

                // --- Frais d'inscription : grille unique, identique pour tous ---
                $grilleInscription = Grilletarifaires::where('type_tarif', 'INSCRIPTION')->first();
                if (!$grilleInscription) {
                    throw new \DomainException("Les frais d'inscription DSC ne sont pas paramétrés.");
                }

                // Tarification "INSCRIPTION" rattachée à cette formation (nécessaire pour le détail de paiement)
                $tarifInscription = Tarificationsformation::where('code_formation', $formation->code_formation)
                    ->where('id_grilletarifaire', $grilleInscription->id)
                    ->first();
                if (!$tarifInscription) {
                    throw new \DomainException("Les frais d'inscription ne sont pas rattachés à la formation {$formation->code_formation}.");
                }

                $montantInscription = $grilleInscription->montant;
                $montantFormation   = $tarifFormation->getGrilletarifaire->montant;
                $montant_total      = $montantInscription + $montantFormation;

                // --- Inscription ---
                (new Inscriptions())->forceFill([
                    'email'              => $email,
                    'tarif_formation_id' => $tarifFormation->id,
                    'code_inscription'   => $generateCode,
                    'heure_inscription'  => date('d-m-Y H:i:s'),
                    'id_webinaire'       => $formation->code_formation,
                    'code_formation'     => $formation->code_formation,
                    'presentiel'         => 0,
                    'type_formation'     => $tarifFormation->getGrilletarifaire->type_tarif,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ])->save();

                // --- Paiement ---
                $paiement = new Paiements();
                $paiement->forceFill([
                    'reference'        => $this->genarateurCodeService->genererNumeroPaiement(),
                    'email'            => $email,
                    'telephone'        => $auditeur->telephone,
                    'code_inscription' => $generateCode,
                    'montant_total'    => $montant_total,
                    'montant_restant'  => $montant_total,
                    'statut_paiement'  => 'EN ATTENTE',
                    'type'             => 'DSC',
                ])->save();

                // --- Détails : frais d'inscription + frais de formation ---
                $lignes = [
                    ['tarif_id' => $tarifInscription->id, 'montant' => $montantInscription],
                    ['tarif_id' => $tarifFormation->id,   'montant' => $montantFormation],
                ];

                foreach ($lignes as $ligne) {
                    (new Detailspaiements())->forceFill([
                        'reference_paiement'        => $paiement->reference,
                        'code_formation'            => $formation->code_formation,
                        'code_inscription'          => $generateCode,
                        'montant_restant'           => $ligne['montant'],
                        'email'                     => $email,
                        'id_tarification_formation' => $ligne['tarif_id'],
                        'statut_paiement'           => 'IMPAYE',
                    ])->save();
                }
            }, 3);

        } catch (\DomainException $e) {
            // Règle métier ou paramétrage : message lisible pour l'utilisateur
            return ResponseHandler::success($e->getMessage(), [
                'success' => false,
                'message' => $e->getMessage(),
            ]);

        } catch (QueryException $e) {
            if (($e->errorInfo[1] ?? null) == 1062) {
                return ResponseHandler::success("Vous êtes déjà inscrit à cette formation.", [
                'success' => false,
                'message' => "Vous êtes déjà inscrit à cette formation.",
            ]);
            }
            Log::error('Inscription DSC - erreur SQL : ' . $e->getMessage());
            return ResponseHandler::error("Erreur lors de l'inscription", 500, $e->getMessage());

        } catch (\Throwable $e) {
            Log::error('Inscription DSC - erreur : ' . $e->getMessage());
            return ResponseHandler::error("Erreur lors de l'inscription", 500, $e->getMessage());
        }

        /* =========================================================
        |  5. Après le commit : historique + mail
        ========================================================= */
        $this->genarateurCodeService->saveHistoriqueAction(
            'INSCRIPTION FORMATION DSC',
            "Inscription à la formation DSC de l'auditeur {$auditeur->nom} {$auditeur->prenoms} référence : {$generateCode}",
            $userId
        );

        $contenu = [
            'nom'              => $auditeur->nom,
            'civilite'         => $auditeur->civilite,
            'prenoms'          => $auditeur->prenoms,
            'formations'       => $formationsDsc,
            'subject'          => 'Inscription sur SIGWEB de ' . $auditeur->nom . ' ' . $auditeur->prenoms,
            'email'            => $email,
            'code_inscription' => $generateCode,
            'url_proforma'     => $this->genarateurCodeService->generateUrlSigned('pro-forma-dsc', ['reference' => $generateCode]),
        ];

        try {
            Mail::to($email)->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))
                ->queue(new InscriptionFormationDiplomante($contenu));
        } catch (\Throwable $e) {
            Log::error('Inscription DSC - envoi mail : ' . $e->getMessage());
        }

        /* =========================================================
        |  6. Réponse
        ========================================================= */
        return ResponseHandler::success("Opération effectuée avec succès", [
            'code_inscription' => $generateCode,
            'message'          => 'Inscription effectuée',
            'success'          => true,
        ]);
    }



    public function checkPiece(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement checkPiece() method.

        //$user = auth('api')->user();
        $inscrit = Inscriptions::where('code_inscription',$request->code_inscription)->first();
        $auditeur = Auditeurs::whereEmail($inscrit->email)->first();
        $countPiece = InscritsTypePieces::count();
        $countPieceAuditeur = AuditeursPieces::where('auditeur_id',$auditeur->id)->where('code_formation',$inscrit->code_formation)->count();
        $downUploadPiece = true;
        $dossier_valide = false;

        if ($countPieceAuditeur < $countPiece) {  $downUploadPiece = false; }

        if ($inscrit->statut_dossier == 'VALIDEE') {  $dossier_valide = true; }else{ $dossier_valide = false; }

        $dataResponse = [
            'dossier_valide' => $dossier_valide,
            'downUploadPiece' => $downUploadPiece
        ];

        return ResponseHandler::success("ok",$dataResponse);
    }

    public function mesPieces(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement mesPieces() method. 
        if ($request->has('auditeur_id') && !empty($request->input('auditeur_id'))) {
            # code...
            $auditeur = Auditeurs::find($request->auditeur_id);
            $getUser = User::whereEmail($auditeur->email)->first();
            $user = User::find($getUser->id);
        } else {
            # code...
            $user = auth('api')->user();
            $auditeur = Auditeurs::whereEmail($user->email)->first();
        }
        
        $code_formation = $request->code_formation;
        $listePieces = InscritsTypePieces::leftJoin(
            'auditeurs_pieces',
            function ($join) use ($user,$code_formation, $auditeur) {
                    $join->on(
                        'auditeurs_pieces.inscrits_typespieces_id',
                        '=',
                        'inscrits_type_pieces.id'
                    )
                        ->where('auditeurs_pieces.auditeur_id', $auditeur->id)
                        ->where('auditeurs_pieces.code_formation', $code_formation);
                }
            )
            ->get([
                'inscrits_type_pieces.id',
                'inscrits_type_pieces.is_professionnel',
                'inscrits_type_pieces.is_etudiant',
                'inscrits_type_pieces.libelle',
                'auditeurs_pieces.piece_path',
                'auditeurs_pieces.code_formation',
                DB::raw('CASE WHEN auditeurs_pieces.id IS NOT NULL THEN true ELSE false END as checked')
            ]);
        $totalPieces = InscritsTypePieces::count();
        $totalPiecesFournies = DB::table('auditeurs_pieces')
            //->where('created_by', $user->id)
            ->where('auditeur_id', $auditeur->id)
            ->where('code_formation', $request->code_formation)
            ->count();
        $dataResponse = [
            'totalPiecesFournies' => $totalPiecesFournies,
            'totalPieces' => $totalPieces,
            'listePieces' => $listePieces
        ];
        return ResponseHandler::success("ok",$dataResponse);
    }

    public function ajouterPiece(Request $request)
    {
        // TODO: Implement ajouterPiece() method.
        try {
            if ($request->has('auditeur_id') && !empty($request->input('auditeur_id'))) {
            # code...
            $auditeur = Auditeurs::find($request->auditeur_id);
            $getUser = User::whereEmail($auditeur->email)->first();
            $user = User::find($getUser->id);
            } else {
                # code...
                $user = auth('api')->user();
            } 
            
            $auditeur = Auditeurs::where("email", $user->email)->first();
            // $userFolder = 'pieces_auditeur/' . Str::slug($user->name); 
            $userFolder = $user->code_user.'/'.$request->code_formation;
            $piecePath = "";
            // Enregistrer les pièces jointes
            if ($request->hasFile('document')) {

                $pieceJointe = $request->file('document');

                    if ($pieceJointe) {
                        # code...
                        // Vérifier la taille du fichier
                        if ($pieceJointe->getSize() > 5000000) { // 1 Mo en octets
                            $message = "La taille du fichier dépasse la limite autorisée (5 Mo).";
                            return ResponseHandler::error("ok",$message);
                        }

                        // vérifier si la pièce existe déjà
                        $checkPiece = AuditeursPieces::where('inscrits_typespieces_id', $request->get('idPiece'))
                            ->where('auditeur_id', $auditeur->id)
                            ->where('code_formation', $request->code_formation)
                            ->exists();

                        if (!$checkPiece) {
                            # code...
                            $originalName = $pieceJointe->getClientOriginalName();
                            $pieceName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
                            $filnalePieceName = $pieceName . '.' . $pieceJointe->extension();
                            $piecePath = $userFolder . '/' . $filnalePieceName;
                            Storage::disk('pieces_auditeur')->putFileAs($userFolder, $pieceJointe, $filnalePieceName);


                            $inscritspiece = new AuditeursPieces();
                            $inscritspiece->inscrits_typespieces_id = $request->get('idPiece');
                            $inscritspiece->auditeur_id = $auditeur->id;
                            $inscritspiece->piece_path = $piecePath;
                            $inscritspiece->created_by = $user->id;
                            $inscritspiece->updated_by = $user->id;
                            $inscritspiece->code_formation = $request->code_formation;
                            $inscritspiece->save();
                        }else{
                            // update
                            $piece = AuditeursPieces::where('inscrits_typespieces_id', $request->get('idPiece'))
                            ->where('auditeur_id', $auditeur->id)
                            ->first();
                            $originalName = $pieceJointe->getClientOriginalName();
                            $pieceName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
                            $filnalePieceName = $pieceName . '.' . $pieceJointe->extension();
                            $piecePath = $userFolder . '/' . $filnalePieceName;
                            Storage::disk('pieces_auditeur')->putFileAs($userFolder, $pieceJointe, $filnalePieceName);
                            
                            $updatePiece = AuditeursPieces::find($piece->id); 
                            $updatePiece->piece_path = $piecePath;
                            $updatePiece->created_by = $user->id;
                            $updatePiece->updated_by = $user->id;
                            $updatePiece->code_formation = $request->code_formation;
                            $updatePiece->update();

                        }
                    }
                $message = "Enregistrement effectué avec succès!";

                 // save historique action
            $action = 'AJOUT DE PIECE';
            $description = "Ajout de pièce ".$piecePath;
            $this->genarateurCodeService->saveHistoriqueAction($action, $description, $user->id);


                return ResponseHandler::success("ok",$message);
            } else {
                return ResponseHandler::error("La pièce n'existe pas","Veuillez renseigner toutes les pièces.");
            }
        } catch (\Exception $e) {
            // Gérer les erreurs
            echo $e->getMessage(); // Afficher l'erreur
        }
    }

    public function doUpdateTempsPresence(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement doUpdateTempsPresence() method.

        $check_auditeur = Auditeurs::where("email",trim($request->email))->exists();
        if ($check_auditeur) {
            $formation = Formations::where("code_formation",$request->code_formation)->first();

            if (empty($formation->id_webinaire)) {
                # code...
                return ResponseHandler::success("L'identifiant du webinaire n'est pas enregistré.",['success'=>false,'code_formation' => $request->code_formation]);
            }
            // vérifier si la personne existe déjà
            $check_rapport = Rapports::where("code_formation",$formation->code_formation)->where("email",$request->email)->exists();

            if ($check_rapport) {
                # code...
                // update data
                $rapport = Rapports::where("code_formation",$formation->code_formation)->where("email",$request->email)->first();

                $rapport->setAttribute("temps_presence",$request->temps_presence);
                $rapport->setAttribute("observation",$request->observation);
                $rapport->update();

                return ResponseHandler::success("Mise à jour effectuée avec succès",['success'=>true,'code_formation' => $request->code_formation]);
            }
            # code...
            // enregistrement des informations dans la table participant
            $participant = new Participants();
            $participant->setAttribute("a_participe","OUI");
            $participant->setAttribute("username",$request->nom_inscrit.' '.$request->prenoms_inscrit);
            $participant->setAttribute("email",$request->email);
            $participant->setAttribute("heure_arrivee",date("Y-m-d H:i:s"));
            $participant->setAttribute("heure_depart",date("Y-m-d H:i:s"));
            $participant->setAttribute("temps_presence",$request->temps_presence);
            $participant->setAttribute("id_webinaire",$formation->id_webinaire);
            $participant->setAttribute("code_formation",$formation->code_formation);
            $participant->save();
            // enregistrement des informations dans la table rapport
            $rapport = new Rapports();

            $rapport->setAttribute("email",$request->email);
            $rapport->setAttribute("temps_presence",$request->temps_presence);
            $rapport->setAttribute("code_formation",$formation->code_formation);
            $rapport->setAttribute("id_webinaire",$formation->id_webinaire);
            $rapport->setAttribute("observation",$request->observation);
            $rapport->save();

            $action = 'MISE A JOUT TEMPS DE PRESENCE';
            $description = "Mise à jour temps de présence auditeur : ".$request->email." code formation : ".$formation->code_formation." temps de présence : ".$request->temps_presence." Observation : ".$request->observation;
            $this->genarateurCodeService->saveHistoriqueAction($action, $description, auth('api')->user()->id);

            // mise à jour tarif formation
            return ResponseHandler::success("Mise à jour effectuée avec succès",['success'=>true,'code_formation' => $request->code_formation]);
        } else {
            # code...
            return ResponseHandler::success("Le compte de l'auditeur n'existe pas",['success'=>false,'code_formation' => $request->code_formation]);
        }
    }

    public function getAuditeurById($idAuditeur): \Illuminate\Http\JsonResponse
    { 
        $auditeur = Auditeurs::find($idAuditeur);

        return  ResponseHandler::success("ok",$auditeur);
    }
    

    public function getIndicateursAuditeur($email): \Illuminate\Http\JsonResponse
    { 
        $user = Auditeurs::whereEmail($email)->first();
        $nbreInscription = Inscriptions::whereEmail($user->email)->count();
        $nbreAttestation = Rapports::whereEmail($user->email)->whereAttestationSend(true)->count();
        $nbreParticipation = Paiements::whereEmail($user->email)->where('montant_paye','>',0)->count();
        $nbreParticipationGratuit = Paiements::whereEmail($user->email)
            ->whereIn('montant_paye',[0,10000])->count();
        $totalPaye = Detailspaiements::leftJoin('paiements','paiements.reference','=','detailspaiements.reference_paiement')
        ->where('paiements.email',$user->email)
        ->sum('detailspaiements.montant_paye');
        $totalRestant = Detailspaiements::leftJoin('paiements','paiements.reference','=','detailspaiements.reference_paiement')
        ->where('paiements.email',$user->email)
        ->sum('detailspaiements.montant_restant');

        $data = [
            'nbreInscription' => $nbreInscription,
            'nbreAttestation' => $nbreAttestation,
            'nbreParticipation' => $nbreParticipation,
            'nbreParticipationGratuit' => $nbreParticipationGratuit,
            'totalPaye' => $totalPaye,
            'totalRestant' => $totalRestant
        ];

        return  ResponseHandler::success("ok",$data);

    }
    

    public function getMode($codeFormation): \Illuminate\Http\JsonResponse
    {
        $modes = Tarificationsformation::join("grilletarifaires", "grilletarifaires.id", "=", "tarificationsformations.id_grilletarifaire")
                ->where("tarificationsformations.code_formation", $codeFormation)
                ->get(["tarificationsformations.id as code", "grilletarifaires.libelle as name"]);
            $response["modes"] = $modes; 
        return  ResponseHandler::success("ok",$modes);
    }
    

    public function updateMode(Request $request): \Illuminate\Http\JsonResponse
    {
       
        $new_tarification_id = $request->choix_mode;
        $anc_tarif_fm = Inscriptions::where("code_inscription", $request->code_inscription)
            ->where("code_formation", $request->code_formation)
            ->first();

        if (!empty($anc_tarif_fm->tarif_formation_id)) {
            # code...
            // vérifier le mode de la formation
            $formation = Formations::where("code_formation", $request->code_formation)->first();
            if ($formation->is_formation_dsc) {
                # code...
                //############################## contrôle spécifique pour les formations DSC #############################################

                // récupérer le montant par défaut
                $default_price = $anc_tarif_fm->getTarifFormation->getGrilletarifaire->montant;
                $details_paiement = Detailspaiements::where("code_inscription", $request->code_inscription)
                    ->where("id_tarification_formation", $anc_tarif_fm->tarif_formation_id)
                    ->first();

                $remiseDsc = 0 ; // valeur initiale
                $oldRemiseDsc = false;
                $codeRemiseDsc = ""; // ceci pour vérifier s'il sagit d'une remise concernant la bourse ou la tranche

                // vérifier si la formation DSC dispose d'une entrée de données dans la table detailspaiements
                if ($details_paiement) { 
                    // vérifier si la remise existe
                    if ($details_paiement->have_reduction_dsc) {
                        # code...
                        $oldRemiseDsc = true;
                        $codeRemiseDsc = $details_paiement->code_remise_dsc;
                    }
                } else {
                // cas ou la formation DSC ne dispose pas d'entrée dans la table detailspaiements
                // on fait une seconde vérification avec l'e-mail
                    $details_paiement = Detailspaiements::where("email", $anc_tarif_fm->email)
                        ->where("id_tarification_formation", $anc_tarif_fm->tarif_formation_id)
                        ->first();
                    if ($details_paiement) {
                        // vérifier si la remise existe
                        if ($details_paiement->have_reduction_dsc) {
                            # code...
                            $oldRemiseDsc = true;
                            $codeRemiseDsc = $details_paiement->code_remise_dsc;
                        }
                    } else {
                        // les données ne sont pas encore enregistrées dans la table détails paiement
                        // update inscription
                        $new_tarif = Tarificationsformation::find($new_tarification_id);
                        $anc_tarif_fm->setAttribute("type_formation", $new_tarif->getGrilletarifaire->type_tarif);
                        $anc_tarif_fm->setAttribute("tarif_formation_id", $new_tarification_id);
                        if ($new_tarif->getGrilletarifaire->type_tarif == "PRESENTIEL") {
                            # code...
                            $anc_tarif_fm->setAttribute("presentiel", 1);
                        } else {
                            $anc_tarif_fm->setAttribute("presentiel", 0);
                        }
                        $anc_tarif_fm->update();

                        // il faut procéder à la génération de la facture
        //********************************* enregistrement des informations dans la table paiement  *********************************/
                // enregistrement des informations dans la table de paiement

                # code...
                /************************ start *********************** */
                // récupérer l'objet auditeur 
                $auditeur = Auditeurs::whereEmail($anc_tarif_fm->email);
                $inscrit = Inscriptions::where("code_inscription", $anc_tarif_fm->code_inscription)->orderBy("id", "DESC")->get()->first();
                // vérifier si le paiement existe déjà
                $check_paiement = Paiements::where("email", $inscrit->email)->where("code_inscription", $inscrit->code_inscription)->exists();

                if ($check_paiement) {
                    # code...
                    # code... la formation a été déjà enregistrée
                } else {
                    # code...

                    // récupération des variables
                    $montant_total = 0;
                    $nom = $auditeur->nom;
                    $prenoms = $auditeur->prenoms;
                    $email = $auditeur->email;
                    $telephone = $auditeur->telephone;

                    // enregistrement des informations de tatification
                    $new_price_scolarite = $new_tarif->getGrilletarifaire->montant;
                    // récupérer le coût d'inscription
                    $getGrille = Grilletarifaires::where('type_tarif','INSCRIPTION')->first();
                    $priceInscription = Tarificationsformation::where("code_formation",$inscrit->code_formation)
                        ->where('id_grilletarifaire',$getGrille->id)
                        ->first();

                    $montant_total = $new_price_scolarite + $getGrille->montant;

                    // générer le numero de reference facture : exemple => ERS-2025-FAC-000001
                    $reference_paiement = $this->genarateurCodeService->genererNumeroPaiement();

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
                        $paiement->setAttribute('type', "DSC");

                        // enregistrement des détails de la facture

                        try {
                            //code...
                            $paiement->save();

                            // enregistrer les frais d'inscriptions  

                                $details_paiement = new Detailspaiements();
                                $details_paiement->setAttribute('reference_paiement', $paiement->reference);
                                $details_paiement->setAttribute('code_formation', $inscrit->code_formation);
                                $details_paiement->setAttribute('code_inscription', $paiement->code_inscription);
                                $details_paiement->setAttribute('montant_restant', $getGrille->montant);
                                $details_paiement->setAttribute('email', $email);
                                $details_paiement->setAttribute('id_tarification_formation', $priceInscription->id);
                                $details_paiement->setAttribute('statut_paiement', 'IMPAYE');
                                $details_paiement->save();

                                // frais formation
                                $details_paiement = new Detailspaiements();
                                $details_paiement->setAttribute('reference_paiement', $paiement->reference);
                                $details_paiement->setAttribute('code_formation', $inscrit->code_formation);
                                $details_paiement->setAttribute('code_inscription', $paiement->code_inscription);
                                $details_paiement->setAttribute('montant_restant', $new_price_scolarite);
                                $details_paiement->setAttribute('email', $email);
                                $details_paiement->setAttribute('id_tarification_formation', $new_tarif->id);
                                $details_paiement->setAttribute('statut_paiement', 'IMPAYE');
                                $details_paiement->save();

                                // vérifier si l'utilisateur bénéficie de réduction
                                if ($request->has('reduction_id') && !empty($request->input('reduction_id'))) {
                                    // Appliquer la réduction
                                    if($request->reduction_id == "TRANCHE_UNIQUE"){ // on n'applique 5% sur les frais de scolarité
                                        $remiseDsc = 5/100;
                                        $rem_mt_scolarite = 0;
                                        $getDetailsP = Detailspaiements::where("reference_paiement", $paiement->reference)->get();
                                        foreach($getDetailsP as $detail){ // mettre à jour les frais de scolarité avec la remise
                                            if($detail->id_tarification_formation == $new_tarif->id){
                                                // il s'agit des frais de scolarité
                                                $rem_mt_scolarite = $remiseDsc * $detail->montant_restant;
                                                $updDetail = Detailspaiements::find($detail->id);
                                                $updDetail->setAttribute('have_reduction_dsc', true);
                                                $updDetail->setAttribute('code_remise_dsc', trim($request->reduction_id));
                                                $updDetail->setAttribute('montant_restant', $detail->montant_restant - $rem_mt_scolarite);
                                                $updDetail->save();
                                            }
                                        }
                                        // mettre à jour le montant restant à payer sur la facture 
                                        $updPaiement = Paiements::find($paiement->id);
                                        $updPaiement->setAttribute('montant_restant',($paiement->montant_restant - $rem_mt_scolarite));
                                        $updPaiement->setAttribute('code_remise_dsc',$request->reduction_id);
                                        $updPaiement->update();

                                    } 

                                    if($request->reduction_id == "BOURSE_COMPLET"){ // on n'applique 100% sur les frais de scolarité
                                        $remiseDsc = 100/100;
                                        $totalRemiseDsc = 0;
                                        $getDetailsP = Detailspaiements::where("reference_paiement", $paiement->reference)->get();
                                        foreach($getDetailsP as $detail){ // mettre à jour les frais de scolarité et d'inscription avec la remise
                                                $rem_mt = 0;
                                                // il s'agit des frais de scolarité
                                                $rem_mt = $remiseDsc * $detail->montant_restant;
                                                $totalRemiseDsc = $totalRemiseDsc + $rem_mt;
                                                $updDetail = Detailspaiements::find($detail->id);
                                                $updDetail->setAttribute('have_reduction_dsc', true);
                                                $updDetail->setAttribute('code_remise_dsc', trim($request->reduction_id));
                                                $updDetail->setAttribute('montant_restant', $detail->montant_restant - $rem_mt);
                                                $updDetail->save();
                                            }
                                        
                                                // mettre à jour le montant restant à payer sur la facture 
                                                $updPaiement = Paiements::find($paiement->id);
                                                $updPaiement->setAttribute('montant_restant',($paiement->montant_restant - $totalRemiseDsc));
                                                $updPaiement->setAttribute('code_remise_dsc',$request->reduction_id);
                                                $updPaiement->update();

                                    
                                    }
                                }

                                } catch (\Exception $e) {
                            //throw $th;
                            echo $e->getMessage();
                        }

                    }

                    //##################### end start #################
                } 
        //********************************* end enregistrement des informations dans la table paiement ********************************/

                        $action = 'CHANGEMENT MODE FORMATION';
                        $description = "Changement mode de formation : ".$request->code_formation;
                        $this->genarateurCodeService->saveHistoriqueAction($action, $description, auth('api')->user()->id);
                        // don't send mail 

                        // return success msg 
                        return  ResponseHandler::success("Mise à jour effectuée avec succès ok");
                    }
 
                }

                // Cas ou detailspaiements contient déjà des données, on procède à la mise à jour
 
                $new_tarif = Tarificationsformation::find($new_tarification_id);
                $new_price = $new_tarif->getGrilletarifaire->montant; // montant scolarité 
                // vérifier si la commande est déjà payé 
                //$paiement = ModelsPaiement::where("code_inscription", $request->code_inscription)->first();
                $paiement = Paiements::where("reference", $details_paiement->reference_paiement)->first();
                $checkPaiementTranche = PaiementsTranche::where("reference","like","%{$details_paiement->reference_paiement}%")->exists();
                
                if ($paiement) {
                    # code...
                    if ($paiement->statut_paiement == "PAYE" || $checkPaiementTranche) {
                        # code...
                        // vous ne pouvez pas changer le mode de cette formation 
                        return ResponseHandler::success("Vous ne pouvez pas changer le mode de formation. Paiement déjà enregistré.");
                    } else { 
                        // vérifier si dans détail on a un paiement
                        $checkDetail = Detailspaiements::where("statut_paiement", "PAYE")->where("reference_paiement", $details_paiement->reference_paiement)->exists();

                        if ($checkDetail) {
                            # code...// vous ne pouvez pas changer le mode de cette formation
                        return ResponseHandler::success("Vous ne pouvez pas changer le mode de formation. Paiement déjà enregistré.");
                        }

                        if (!empty($details_paiement)) {
                            # code...
                            $reference_paiement = $details_paiement->reference_paiement;

                            // vérifier si l'utilisateur bénéficie de réduction
                                if ($request->has('reduction_id') && !empty($request->input('reduction_id'))) {
                                    // Appliquer la réduction
                                    if($request->reduction_id == "TRANCHE_UNIQUE"){ // on n'applique 5% sur les frais de scolarité
                                        $remiseDsc = 5/100;
                                        $rem_mt_scolarite = 0;
                                        $getDetailsP = Detailspaiements::where("reference_paiement", $paiement->reference)->get();
                                        
                                        foreach($getDetailsP as $detail){ // mettre à jour les frais de scolarité avec la remise
                                            if($detail->id_tarification_formation == $new_tarif->id){ // price for scolarite
                                                // il s'agit des frais de scolarité
                                                $rem_mt_scolarite = $remiseDsc * $new_price;
                                                $updDetail = Detailspaiements::find($detail->id);
                                                $updDetail->setAttribute("id_tarification_formation", $new_tarification_id); 
                                                $updDetail->setAttribute("id_reduction", null);
                                                $updDetail->setAttribute("montant_paye", 0); 
                                                $updDetail->setAttribute("statut_paiement", "IMPAYE");
                                                $updDetail->setAttribute("statut_traitement", "ATTENTE"); 
                                                $updDetail->setAttribute('have_reduction_dsc', true);
                                                $updDetail->setAttribute('code_remise_dsc', trim($request->reduction_id));
                                                $updDetail->setAttribute('montant_restant', $new_price - $rem_mt_scolarite);
                                                $updDetail->save();
                                            }
                                        }
                                        // récupérer les frais d'inscription en vigueur 
                                        $getGrille = Grilletarifaires::where('type_tarif', 'INSCRIPTION')->first();
                                        $priceInscription = Tarificationsformation::where("code_formation", $request->code_formation)
                                            ->where('id_grilletarifaire', $getGrille->id)
                                            ->first();
                                        $mtTotal = $new_price + $getGrille->montant;
                                        // mettre à jour les frais d'inscription
                                        $upInscriptionDetails = Detailspaiements::where("id", "!=", $details_paiement->id)
                                            ->where("reference_paiement", $details_paiement->reference_paiement)
                                            ->first();
                                        $updateDetailPaiementInsc = Detailspaiements::find($upInscriptionDetails->id);                                     
                                        $updateDetailPaiementInsc->setAttribute("id_tarification_formation", $priceInscription->id);
                                        $updateDetailPaiementInsc->setAttribute("montant_paye", 0);
                                        $updateDetailPaiementInsc->setAttribute("montant_restant", $getGrille->montant);
                                        $updateDetailPaiementInsc->setAttribute("id_reduction", null);
                                        $updateDetailPaiementInsc->setAttribute('have_reduction_dsc', false);
                                        $updateDetailPaiementInsc->setAttribute('code_remise_dsc',null);
                                        $updateDetailPaiementInsc->setAttribute("statut_paiement", "IMPAYE");
                                        $updateDetailPaiementInsc->setAttribute("statut_traitement", "ATTENTE");
                                        $updateDetailPaiementInsc->update();

                                        // mettre à jour le montant restant à payer sur la facture 
                                        $updPaiement = Paiements::find($paiement->id);
                                        $updPaiement->setAttribute('montant_restant',($mtTotal - $rem_mt_scolarite));
                                        $updPaiement->setAttribute('code_remise_dsc',$request->reduction_id);
                                        $updPaiement->setAttribute("montant_total", $mtTotal);
                                        $updPaiement->setAttribute("montant_paye", 0);
                                        $updPaiement->setAttribute("statut_paiement", "EN ATTENTE");
                                        $updPaiement->setAttribute("statut_traitement", "ATTENTE");
                                        $updPaiement->update();
                                        Log::info("Update tarif for existing paiement tranche unique ok : ".($mtTotal - $rem_mt_scolarite));  

                                    } 

                                    if($request->reduction_id == "BOURSE_COMPLET"){ // on n'applique 100% sur les frais de scolarité
                                        $remiseDsc = 100/100;
                                        $totalRemiseDsc = 0;
                                        $getDetailsP = Detailspaiements::where("reference_paiement", $paiement->reference)->get();
                                        // récupérer les frais d'inscription en vigueur 
                                        $getGrille = Grilletarifaires::where('type_tarif', 'INSCRIPTION')->first();
                                        $priceInscription = Tarificationsformation::where("code_formation", $request->code_formation)
                                            ->where('id_grilletarifaire', $getGrille->id)
                                            ->first();
                                        $mtTotal = $new_price + $getGrille->montant;

                                        foreach($getDetailsP as $detail){ // mettre à jour les frais de scolarité et d'inscription avec la remise
                                                $rem_mt = 0;
                                                if($detail->id_tarification_formation == $new_tarif->id){
                                                $rem_mt = $remiseDsc * $new_price;
                                                $totalRemiseDsc = $totalRemiseDsc + $rem_mt;
                                                    // il s'agit des frais de scolarité
                                                    $updDetail = Detailspaiements::find($detail->id);
                                                    $updDetail->setAttribute("id_tarification_formation", $new_tarification_id); 
                                                    $updDetail->setAttribute("id_reduction", null);
                                                    $updDetail->setAttribute("montant_paye", 0); 
                                                    $updDetail->setAttribute("statut_paiement", "IMPAYE");
                                                    $updDetail->setAttribute("statut_traitement", "ATTENTE"); 
                                                    $updDetail->setAttribute('have_reduction_dsc', true);
                                                    $updDetail->setAttribute('code_remise_dsc', trim($request->reduction_id));
                                                    $updDetail->setAttribute('montant_restant', $new_price - $rem_mt);
                                                    $updDetail->save();
                                                }else{ // il s'agit des frais d'inscription
                                                    $rem_mt = $remiseDsc * $getGrille->montant;
                                                    $totalRemiseDsc = $totalRemiseDsc + $rem_mt;
                                                    $updDetail = Detailspaiements::find($detail->id);
                                                    $updDetail->setAttribute("id_reduction", null);
                                                    $updDetail->setAttribute("montant_paye", 0); 
                                                    $updDetail->setAttribute("statut_paiement", "IMPAYE");
                                                    $updDetail->setAttribute("statut_traitement", "ATTENTE"); 
                                                    $updDetail->setAttribute('have_reduction_dsc', true);
                                                    $updDetail->setAttribute('code_remise_dsc', trim($request->reduction_id));
                                                    $updDetail->setAttribute('montant_restant', $getGrille->montant - $rem_mt);
                                                    $updDetail->save();
                                                }
                                            }
                                        
                                                // mettre à jour le montant restant à payer sur la facture 
                                                $updPaiement = Paiements::find($paiement->id);
                                                $updPaiement->setAttribute('montant_restant',($mtTotal - $totalRemiseDsc));
                                                $updPaiement->setAttribute('code_remise_dsc',$request->reduction_id);
                                                $updPaiement->setAttribute("montant_total", $mtTotal);
                                                $updPaiement->setAttribute("montant_paye", 0);
                                                $updPaiement->setAttribute("statut_paiement", "EN ATTENTE");
                                                $updPaiement->setAttribute("statut_traitement", "ATTENTE");
                                                $updPaiement->update(); 
                                        Log::info("Update tarif for existing paiement bourse complete ok"); 
                                                
                                    }

                                }else{ // pas de réduction
                                    // update ligne scolarité
                                    $details_paiement->setAttribute("id_tarification_formation", $new_tarification_id);
                                    $details_paiement->setAttribute("have_reduction_dsc", false);
                                    $details_paiement->setAttribute("id_reduction", null);
                                    $details_paiement->setAttribute("montant_paye", 0);
                                    $details_paiement->setAttribute("montant_restant", $new_price);
                                    $details_paiement->setAttribute("statut_paiement", "IMPAYE");
                                    $details_paiement->setAttribute("statut_traitement", "ATTENTE");
                                    $details_paiement->setAttribute('code_remise_dsc',null);
                                    $details_paiement->update();

                                    // delete paiement tranche 
                                    $deletePaiementTranche = PaiementsTranche::where("details_paiements_id", $details_paiement->id)->delete();

                                    $new_montant_total = $new_price;

                                    // update frais inscription
                                    $checkInscriptionDetails = Detailspaiements::where("id", "!=", $details_paiement->id)->where("reference_paiement", $details_paiement->reference_paiement)->exists();
                                    if ($checkInscriptionDetails) {
                                        # code...
                                        $upInscriptionDetails = Detailspaiements::where("id", "!=", $details_paiement->id)
                                            ->where("reference_paiement", $details_paiement->reference_paiement)
                                            ->first();
                                        $updateDetailPaiementInsc = Detailspaiements::find($upInscriptionDetails->id);
                                        $getGrille = Grilletarifaires::where('type_tarif', 'INSCRIPTION')->first();

                                        $priceInscription = Tarificationsformation::where("code_formation", $request->code_formation)
                                            ->where('id_grilletarifaire', $getGrille->id)
                                            ->first();

                                        $updateDetailPaiementInsc->setAttribute("id_tarification_formation", $priceInscription->id);
                                        $updateDetailPaiementInsc->setAttribute("montant_paye", 0);
                                        $updateDetailPaiementInsc->setAttribute("montant_restant", $getGrille->montant);
                                        $updateDetailPaiementInsc->setAttribute("statut_paiement", "IMPAYE");
                                        $updateDetailPaiementInsc->setAttribute("statut_traitement", "ATTENTE");
                                        $updateDetailPaiementInsc->setAttribute('code_remise_dsc',null); 
                                        $updateDetailPaiementInsc->setAttribute("have_reduction_dsc", false);
                                        $updateDetailPaiementInsc->setAttribute("id_reduction", null);
                                        $updateDetailPaiementInsc->update();

                                        // delete paiement tranche 
                                        $deletePaiementTranche = PaiementsTranche::where("details_paiements_id", $updateDetailPaiementInsc->id)->delete();

                                        $new_montant_total = $new_montant_total + $getGrille->montant;

                                    }

                                    // update paiement
                            //$update_price = $paiement->montant_total - $default_price;
                            //$new_price_update = $update_price = $new_price;
                            $paiement->setAttribute("montant_total", $new_montant_total);
                            $paiement->setAttribute("montant_paye", 0);
                            $paiement->setAttribute("montant_restant", $new_montant_total);
                            $paiement->setAttribute("statut_paiement", "EN ATTENTE");
                            $paiement->setAttribute("statut_traitement", "ATTENTE");
                            $paiement->setAttribute('code_remise_dsc',null); 
                            $paiement->update();
                            Log::info("Update tarif for existing paiement without ok"); 

                            // delete tranche
                            $deleteTranche = Tranches::where("code_inscription", $request->code_inscription)->delete();
 
  
                            }
                            
            
                            // update inscription
                            $anc_tarif_fm->setAttribute("type_formation", $new_tarif->getGrilletarifaire->type_tarif);
                            $anc_tarif_fm->setAttribute("tarif_formation_id", $new_tarification_id);
                            if ($new_tarif->getGrilletarifaire->type_tarif == "PRESENTIEL") {
                                # code...
                                $anc_tarif_fm->setAttribute("presentiel", 1);
                            } else {
                                $anc_tarif_fm->setAttribute("presentiel", 0);
                            }
                            $anc_tarif_fm->update();
                              // Send Mail for auditeur

                     // envoie de mail pour la refence de la facture
            $auditeur = Auditeurs::whereEmail($anc_tarif_fm->email)->first();
            $formation = Tarificationsformation::whereId($new_tarification_id)->pluck("id");
                           
            $contenu = [
                'nom' => $auditeur->nom,
                'prenoms' => $auditeur->prenoms,
                'formations' => $formation,
                'subject' => 'Inscription sur SIGWEB de ' . $auditeur->nom . ' ' . $auditeur->prenoms,
                'email' => $auditeur->email,
                'code_inscription' => $anc_tarif_fm->code_inscription,
                'url_proforma' => $this->genarateurCodeService->generateUrlSigned('pro-forma',['reference' =>$anc_tarif_fm->code_inscription])
            ];
 

                try {
                    # code...
                    $getP = Paiements::find($paiement->id);
                    if ($getP->montant_restant == 0) {
                        # code...
                        Mail::to($auditeur->email)->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new InscriptionGratuit($contenu));
                    } else {
                        # code...
                        Mail::to($auditeur->email)->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new Inscriptionpresentiel($contenu));
                    }
                } catch (\Exception $e) {
                    // Get error here

                }
           

            $contenu = [
                'nom' => $auditeur->nom,
                'prenoms' => $auditeur->prenoms,
                'reference_paiement' => $paiement->reference,
                'subject' => 'INFORMATION SUR VOTRE DEMANDE DE PAIEMENT',
                'email'=>$auditeur->email,
                'url_proforma' => $this->genarateurCodeService->generateUrlSigned('pro-forma',['reference' =>$anc_tarif_fm->code_inscription])
            ];

            try {
             # code...
            // Mail::to($auditeur->email)->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(  new Paiement($contenu));
            } catch(\Exception $e){
                // Get error here

            } 
                            // return success msg 
                        return ResponseHandler::success("Mise à jour effectuée");
                        } else {
                            # code...
                        return ResponseHandler::success("Vous ne pouvez pas changer le mode de formation. L'identifiant code inscription n'existe pas.."); 
                        }
                    }
                } else {
                    # code...
                    // code inscription n'existe pas 
                        return ResponseHandler::success("Vous ne pouvez pas changer le mode de formation. L'identifiant code inscription n'existe pas...");
                }


                //############################## end contrôle spécifique pour les formations DSC #############################################
            }
                //############################## contrôle spécifique pour les formations non DSC #############################################

            // récupérer le montant par défaut
            $default_price = $anc_tarif_fm->getTarifFormation->getGrilletarifaire->montant;
            $details_paiement = Detailspaiements::where("code_inscription", $request->code_inscription)
                ->where("id_tarification_formation", $anc_tarif_fm->tarif_formation_id)
                ->first();

            $remise = 0 / 100;
            if (!empty($details_paiement)) {
                // vérifier si la remise existe
                if (!empty($details_paiement->id_reduction)) {
                    # code...
                    $remise = $details_paiement->getReduction->pourcentage / 100;
                }
            } else {

                $details_paiement = Detailspaiements::where("email", $anc_tarif_fm->email)
                    ->where("id_tarification_formation", $anc_tarif_fm->tarif_formation_id)
                    ->first();
                if (!empty($details_paiement)) {
                    // vérifier si la remise existe
                    if (!empty($details_paiement->id_reduction)) {
                        # code...
                        $remise = $details_paiement->getReduction->pourcentage / 100;
                    }
                } else {
                    // les données ne sont pas encore enregistrées dans la table détails paiement
                    // update inscription
                    $new_tarif = Tarificationsformation::find($new_tarification_id);
                    $anc_tarif_fm->setAttribute("type_formation", $new_tarif->getGrilletarifaire->type_tarif);
                    $anc_tarif_fm->setAttribute("tarif_formation_id", $new_tarification_id);
                    if ($new_tarif->getGrilletarifaire->type_tarif == "PRESENTIEL") {
                        # code...
                        $anc_tarif_fm->setAttribute("presentiel", 1);
                    } else {
                        $anc_tarif_fm->setAttribute("presentiel", 0);
                    }
                    $anc_tarif_fm->update();

                    // généré les informations dans la table paiement
                    //#################### start ###############################"
                    $montant_formation = $anc_tarif_fm->getTarifFormation->getGrilletarifaire->montant;
                    // enregistrement des details de formation inscription et frais de scolarite 
                      
            if ($montant_formation > 0) {
                # code...
                
            // générer le numero de reference facture
            // procédure d'incrémentation du numéro inventaire
            $annee_actuelle = date('Y');
            $type_reference = "FACT";
            $indicatif = "ERS";
            $id_1 = "";

            // récupérer le dernier enregistrement
            $last_numero_paiement = Paiements::orderBy('id', 'desc')->first();

            if ($last_numero_paiement == NULL) {
                $last_id = "";
            } else {
                $last_id = $last_numero_paiement->reference;
            }

            if (!empty($last_id)) {
                $id_1 = explode('-', $last_id);
                //vérifier si nous somme dans une nouvelle année pour réinitialiser le compteur
                $get_date = $id_1[1];
                if ($annee_actuelle > $get_date) {
                    # code...
                    $numero = '0000000';
                } else {
                    # code...
                    $numero = $id_1[3];
                }
            } else {
                $id_1 = '000000';
                $numero = '000000';
            }

            $numero_fact = $numero + 1;

            $numero_fact_formatted = str_pad($numero_fact, 6, "0", STR_PAD_LEFT);
            $reference_paiement = $indicatif . '-' . $annee_actuelle . '-' . $type_reference . '-' . $numero_fact_formatted;

            // enregistrement du paiement 
            $statut_details_paiement = "IMPAYE";
            $statut_paiement = "EN ATTENTE";

            $paiement = new Paiements();
            $paiement->setAttribute('reference', $reference_paiement); 
            $paiement->setAttribute('email', $anc_tarif_fm->email);
            $paiement->setAttribute('telephone', 88888888);
            $paiement->setAttribute('code_inscription', $anc_tarif_fm->code_inscription);
            $paiement->setAttribute('montant_total', $montant_formation);
            $paiement->setAttribute('montant_restant', $montant_formation); 
            $paiement->setAttribute('statut_paiement', $statut_paiement); 
            $paiement->setAttribute('type', "FORMATION");
            $paiement->save();
            // enregistrement des détails de la facture
 
                    $details_paiement = new Detailspaiements();
                    $details_paiement->setAttribute('reference_paiement', $paiement->reference);
                    $details_paiement->setAttribute('code_formation', $anc_tarif_fm->code_formation);
                    $details_paiement->setAttribute('code_inscription', $anc_tarif_fm->code_inscription);
                    $details_paiement->setAttribute('email', $anc_tarif_fm->email);
                    $details_paiement->setAttribute('id_tarification_formation', $new_tarif->id);
                    $details_paiement->setAttribute('statut_paiement', 'IMPAYE'); 
                    $details_paiement->setAttribute('statut_traitement', 'ATTENTE');
                    $details_paiement->setAttribute('montant_restant', $montant_formation);
                    $details_paiement->save();

                     // mise à jour tarif formation
                        if ($request->has('reduction_id') && !empty($request->input('reduction_id'))) {

                            // check inscription
                            $check_inscription = Inscriptions::where("code_formation", $request->code_formation)
                                ->where("code_inscription", $request->code_inscription)
                                ->first();
                            if ($check_inscription) {
                                // check details paiements

                                $chec_details_paiements = Detailspaiements::where("code_formation", $request->code_formation)
                                    //->where("email",$request->email)
                                    ->where("code_inscription", $check_inscription->code_inscription)
                                    ->exists();

                                if ($chec_details_paiements) {
                                    $detail_paiement = Detailspaiements::where("code_formation", $request->code_formation)
                                        //->where("email",$request->email)
                                        ->where("code_inscription", $check_inscription->code_inscription)
                                        ->first();
                                    // check if reduction existe
                                    if ($detail_paiement->id_reduction) {
                                        return ResponseHandler::success("Opération effectuée avec succès. Echec d'application de la réduction. Une réduction existe déjà.");
                                    }
                                    $paiement = Paiements::where("reference", $detail_paiement->reference_paiement)->first();
                                    if ($paiement->montant_total > 0) {
                                        # code...
                                        // récupération montant actuel de la formation et mise à jour montant total dans la table paiement
                                        // en retirant le montant actuel de la formation du montant total dans la table paiement
                                        $montant_formation = $detail_paiement->getTarification->getGrilletarifaire->montant;
                                        $update_montant_paiement = $paiement->montant_total - $montant_formation;
                                        $reduction = Reductions::find($request->reduction_id);
                                        // application reduction au montant existant
                                        $montant_formation_reduction = $montant_formation - ($montant_formation * ($reduction->pourcentage / 100));
                                        $update_montant_paiement_final = $update_montant_paiement + $montant_formation_reduction;

                                        // update table details paiement 
                                        $detail_paiement->setAttribute("id_reduction", $request->reduction_id);
                                        $detail_paiement->update();
                                        // update table paiement
                                        $paiement->setAttribute("montant_total", $update_montant_paiement_final);
                                        $paiement->update();
                                    } else {
                                        # code...
                                        return ResponseHandler::success("Opération effectuée avec succès. Echec d'application de la réduction");
                                    }
                                }
                            }
                        }

                    // Send Mail for auditeur

                     // envoie de mail pour la refence de la facture
            $auditeur = Auditeurs::whereEmail($anc_tarif_fm->email)->first();
            $formation =  Tarificationsformation::whereId($new_tarification_id)->pluck("id");
        
            $contenu = [
                'nom' => $auditeur->nom,
                'prenoms' => $auditeur->prenoms,
                'formations' => $formation,
                'subject' => 'Inscription sur SIGWEB de ' . $auditeur->nom . ' ' . $auditeur->prenoms,
                'email' => $auditeur->email,
                'code_inscription' => $anc_tarif_fm->code_inscription,
                'url_proforma' => $this->genarateurCodeService->generateUrlSigned('pro-forma',['reference' =>$anc_tarif_fm->code_inscription])
            ];

                       

                try {
                    # code...
                    if ($montant_formation == 0) {
                        # code...
                        Mail::to($auditeur->email)->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new InscriptionGratuit($contenu));
                    } else {
                        # code...
                        Mail::to($auditeur->email)->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new Inscriptionpresentiel($contenu));
                    }
                } catch (\Exception $e) {
                    // Get error here

                }
           

            $contenu = [
                'nom' => $auditeur->nom,
                'prenoms' => $auditeur->prenoms,
                'reference_paiement' => $paiement->reference,
                'subject' => 'INFORMATION SUR VOTRE DEMANDE DE PAIEMENT',
                'email'=>$auditeur->email,
                'url_proforma' => $this->genarateurCodeService->generateUrlSigned('pro-forma',['reference' =>$anc_tarif_fm->code_inscription])
            ];

            try {
             # code...
             // Mail::to($auditeur->email)->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(  new Paiement($contenu));
            } catch(\Exception $e){
                // Get error here

            }


            } else {
                # code...

                 $auditeur = Auditeurs::whereEmail($anc_tarif_fm->email)->first(); 

            $contenu = [
                'nom' => $auditeur->nom,
                'prenoms' => $auditeur->prenoms,
                'formations' => $formation,
                'subject' => 'Inscription sur SIGWEB de ' . $auditeur->nom . ' ' . $auditeur->prenoms,
                'email' => $auditeur->email,
                'code_inscription' => $anc_tarif_fm->code_inscription,
                'url_proforma' => $this->genarateurCodeService->generateUrlSigned('pro-forma',['reference' =>$anc_tarif_fm->code_inscription])
            ]; 

                try {
                    # code...
                    if ($montant_formation == 0) {
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
            
                    //################### end #######################################

                    // return success msg
                    return ResponseHandler::success("Mise à jour effectuée avec succès");
                }

                //  return redirect()->back()->with("error", "Vous ne pouvez pas changer le mode de formation.. L'identifiant code inscription n'existe pas.");
            }

            $default_price = $default_price - ($default_price * $remise);
            $new_tarif = Tarificationsformation::find($new_tarification_id);
            $new_price = $new_tarif->getGrilletarifaire->montant;
            $new_price = $new_price - ($new_price * $remise);
            // vérifier si la commande est déjà payé 
            //$paiement = ModelsPaiement::where("code_inscription", $request->code_inscription)->first();
            $paiement = Paiements::where("reference", $details_paiement->reference_paiement)->first();

            if ($paiement) {
                # code...
                if ($paiement->statut_paiement == "PAYE") {
                    # code...
                    // vous ne pouvez pas changer le mode de cette formation
                    return ResponseHandler::success("Vous ne pouvez pas changer le mode de formation. Paiement déjà enregistré.");
                } else {
                    # code... 

                    if (!empty($details_paiement)) {
                        # code...
                        $reference_paiement = $details_paiement->reference_paiement;
                        $details_paiement->setAttribute("id_tarification_formation", $new_tarification_id);
                        $details_paiement->setAttribute("montant_paye", 0);
                        $details_paiement->setAttribute("montant_restant", $new_price);
                        $details_paiement->setAttribute("statut_paiement", "IMPAYE");
                        $details_paiement->setAttribute("statut_traitement", "ATTENTE");
                        $details_paiement->update();
                        // update paiement
                        $update_price = $paiement->montant_total - $default_price;
                        $new_price_update = $update_price + $new_price;
                        $paiement->setAttribute("montant_total", $new_price_update);
                        $paiement->setAttribute("montant_paye", 0);
                        $paiement->setAttribute("montant_restant", $new_price_update);
                        $paiement->setAttribute("statut_paiement", "EN ATTENTE");
                        $paiement->setAttribute("statut_traitement", "ATTENTE");
                        $paiement->update();

                        // update inscription
                        $anc_tarif_fm->setAttribute("type_formation", $new_tarif->getGrilletarifaire->type_tarif);
                        $anc_tarif_fm->setAttribute("tarif_formation_id", $new_tarification_id);
                        if ($new_tarif->getGrilletarifaire->type_tarif == "PRESENTIEL") {
                            # code...
                            $anc_tarif_fm->setAttribute("presentiel", 1);
                        } else {
                            $anc_tarif_fm->setAttribute("presentiel", 0);
                        }
                        $anc_tarif_fm->update();


                        //################# mise à jour reduction #################
                        // mise à jour tarif formation
                        if ($request->has('reduction_id') && !empty($request->input('reduction_id'))) {

                            // check inscription
                            $check_inscription = Inscriptions::where("code_formation", $request->code_formation)
                                ->where("code_inscription", $request->code_inscription)
                                ->first();
                            if ($check_inscription) {
                                // check details paiements

                                $chec_details_paiements = Detailspaiements::where("code_formation", $request->code_formation)
                                    //->where("email",$request->email)
                                    ->where("code_inscription", $check_inscription->code_inscription)
                                    ->exists();

                                if ($chec_details_paiements) {
                                    $detail_paiement = Detailspaiements::where("code_formation", $request->code_formation)
                                        //->where("email",$request->email)
                                        ->where("code_inscription", $check_inscription->code_inscription)
                                        ->first();
                                    // check if reduction existe
                                    if ($detail_paiement->id_reduction) {
                                        return ResponseHandler::success( "Opération effectuée avec succès. Echec d'application de la réduction. Une réduction existe déjà.");
                                    }
                                    $paiement = Paiements::where("reference", $detail_paiement->reference_paiement)->first();
                                    if ($paiement->montant_total > 0) {
                                        # code...
                                        // récupération montant actuel de la formation et mise à jour montant total dans la table paiement
                                        // en retirant le montant actuel de la formation du montant total dans la table paiement
                                        $montant_formation = $detail_paiement->getTarification->getGrilletarifaire->montant;
                                        $update_montant_paiement = $paiement->montant_total - $montant_formation;
                                        $reduction = Reductions::find($request->reduction_id);
                                        // application reduction au montant existant
                                        $montant_formation_reduction = $montant_formation - ($montant_formation * ($reduction->pourcentage / 100));
                                        $update_montant_paiement_final = $update_montant_paiement + $montant_formation_reduction;

                                        // update table details paiement 
                                        $detail_paiement->setAttribute("id_reduction", $request->reduction_id);
                                        $detail_paiement->update();
                                        // update table paiement
                                        $paiement->setAttribute("montant_total", $update_montant_paiement_final);
                                        $paiement->update();
                                    } else {
                                        # code...
                                        return ResponseHandler::success( "Opération effectuée avec succès. Echec d'application de la réduction");
                                    }
                                }
                            }
                        }
                        // ################# end mise à jour ####################

                          // Send Mail for auditeur

                     // envoie de mail pour la refence de la facture
            $auditeur = Auditeurs::whereEmail($anc_tarif_fm->email)->first();
            $formation =  Tarificationsformation::whereId($new_tarification_id)->pluck("id");
                       
            $contenu = [
                'nom' => $auditeur->nom,
                'prenoms' => $auditeur->prenoms,
                'formations' => $formation,
                'subject' => 'Inscription sur SIGWEB de ' . $auditeur->nom . ' ' . $auditeur->prenoms,
                'email' => $auditeur->email,
                'code_inscription' => $anc_tarif_fm->code_inscription,
                'url_proforma' => $this->genarateurCodeService->generateUrlSigned('pro-forma',['reference' =>$anc_tarif_fm->code_inscription])
            ];


                try {
                    # code...
                    if ($paiement->montant_total == 0) {
                        # code...
                        Mail::to($auditeur->email)->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new InscriptionGratuit($contenu));
                    } else {
                        # code...
                        Mail::to($auditeur->email)->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new Inscriptionpresentiel($contenu));
                    }
                } catch (\Exception $e) {
                    // Get error here

                }
           

            $contenu = [
                'nom' => $auditeur->nom,
                'prenoms' => $auditeur->prenoms,
                'reference_paiement' => $paiement->reference,
                'subject' => 'INFORMATION SUR VOTRE DEMANDE DE PAIEMENT',
                'email'=>$auditeur->email
            ];

            try {
             # code...
             // Mail::to($auditeur->email)->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(  new Paiement($contenu));
            } catch(\Exception $e){
                // Get error here

            }
            

                        // return success msg
                        return ResponseHandler::success("Mise à jour effectuée avec succès");
                    } else {
                        # code...

                        return ResponseHandler::success("Vous ne pouvez pas changer le mode de formation. L'identifiant code inscription n'existe pas..");
                    }
                }
            } else {
                # code...
                // code inscription n'existe pas
                return ResponseHandler::success("Vous ne pouvez pas changer le mode de formation. L'identifiant code inscription n'existe pas...");
            }
        } else {
            # code...
            // l'id de la tarification par défaut n'existe pas
            // return redirect()->back()->with("error", "Vous ne pouvez pas changer le mode de formation. L'identifiant id tarif formation n'existe pas.");
            return  ResponseHandler::success("ok");
        }
    }

    
    public function saveUpdateEmailAuditeur(Request $request): \Illuminate\Http\JsonResponse
    {  
 
         // Désactiver temporairement la vérification des clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // update table auditeur
        $auditeur = Auditeurs::find($request->identifiant);

        $check_auditeur = Auditeurs::where('email', $request->new_email)->exists();
        if ($check_auditeur) {
            // another user have the new email

            // return redirect()->route("auditeurs.index")->with("error","Echec lors de la modification, l'adresse mail existe déjà.");

            // update table user
            $check_user = User::where('email', $request->new_email)->exists();
            if ($check_user) {
            } else {
                $update = User::where('email', $request->anc_email)->update(['email' => $request->new_email]);
            }

            // update table details paiement
            $check_auditeur = Detailspaiements::where('email', $request->anc_email)->exists();
            if ($check_auditeur) {
                $update = Detailspaiements::where('email', $request->anc_email)->update(['email' => $request->new_email]);
            }

            // update table inscrits
            $check_auditeur = Inscriptions::where('email', $request->anc_email)->exists();
            if ($check_auditeur) {
                $update = Inscriptions::where('email', $request->anc_email)->update(['email' => $request->new_email]);
            }

            // update table paiement
            $check_auditeur = Paiements::where('email', $request->anc_email)->exists();
            if ($check_auditeur) {
                $update = Paiements::where('email', $request->anc_email)->update(['email' => $request->new_email]);
            }

            // update table participant
            $check_auditeur = Participants::where('email', $request->anc_email)->exists();
            if ($check_auditeur) {
                $update = Participants::where('email', $request->anc_email)->update(['email' => $request->new_email]);
            }

            // update table rapports
            $check_auditeur = Rapports::where('email', $request->anc_email)->exists();
            if ($check_auditeur) {
                $update = Rapports::where('email', $request->anc_email)->update(['email' => $request->new_email]);
            } 
            
            DB::statement('SET FOREIGN_KEY_CHECKS=1');  

            return  ResponseHandler::success("Mise à jour effectuée avec succès");
        } else {
            $auditeur->setAttribute('email', $request->new_email);
            $auditeur->update();

            // update table user
            $check_user = User::where('email', $request->new_email)->exists();
            if ($check_user) {
            } else {
                $update = User::where('email', $request->anc_email)->update(['email' => $request->new_email]);
            }

            // update table details paiement
            $check_auditeur = Detailspaiements::where('email', $request->anc_email)->exists();
            if ($check_auditeur) {
                $update = Detailspaiements::where('email', $request->anc_email)->update(['email' => $request->new_email]);
            }

            // update table inscrits
            $check_auditeur = Inscriptions::where('email', $request->anc_email)->exists();
            if ($check_auditeur) {
                $update = Inscriptions::where('email', $request->anc_email)->update(['email' => $request->new_email]);
            }

            // update table paiement
            $check_auditeur = Paiements::where('email', $request->anc_email)->exists();
            if ($check_auditeur) {
                $update = Paiements::where('email', $request->anc_email)->update(['email' => $request->new_email]);
            }

            // update table participant
            $check_auditeur = Participants::where('email', $request->anc_email)->exists();
            if ($check_auditeur) {
                $update = Participants::where('email', $request->anc_email)->update(['email' => $request->new_email]);
            }

            // update table rapports
            $check_auditeur = Rapports::where('email', $request->anc_email)->exists();
            if ($check_auditeur) {
                $update = Rapports::where('email', $request->anc_email)->update(['email' => $request->new_email]);
            }
            // Réactiver la vérification des clés étrangères
            DB::statement('SET FOREIGN_KEY_CHECKS=1'); 

            $action = 'CHANGEMENT MAIL AUDITEUR';
            $description = "Changement auditeur, anc_mail : ".$request->anc_email." new_mail ".$request->new_email;
            $this->genarateurCodeService->saveHistoriqueAction($action, $description, auth('api')->user()->id);
             return  ResponseHandler::success("Mise à jour effectuée avec succès");
        }
        

    }

    
    public function startDeleteCompteAuditeurOld(Request $request): \Illuminate\Http\JsonResponse
    {  
 
         // Désactiver temporairement la vérification des clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // update table auditeur
        $auditeur = Auditeurs::find($request->identifiant);
        $user = User::whereEmail($auditeur->email)->first(); 
        if ($user) {
            
                    try {
                        
                    $idUser = $user->id;
                    $idAuditeur = $auditeur->id;
                    $emailAuditeur = $auditeur->email;

                    DB::beginTransaction(); 
                        // suppression des paiements
                    $paiements = Paiements::whereEmail($emailAuditeur)->get();
                    foreach($paiements as $paiement){
                        // suppression des détails paiements selon le type de paiement
                        if($paiement->type == "OUVRAGE"){ // suppression des données relatives aux OUVRAGES
                                DB::table('detailpaiementouvrages')->where('paiementouvrage_id', $paiement->id)->delete();
                        }

                        if($paiement->type == "FORMATION"){ // suppression des données relatives aux FORMATIONS CLASSIQUES
                                DB::table('detailspaiements')->where('reference_paiement', $paiement->reference)->delete();
                        }

                        if($paiement->type == "DSC"){ // suppression des données relatives aux DSC
                                DB::table('paiements_tranches')->where('reference','like','%'.$paiement->reference.'%')->delete();
                                DB::table('tranches')->where('email',$paiement->email)->delete();
                                DB::table('detailspaiements')->where('reference_paiement', $paiement->reference)->delete();
                        }
                    }
                    // suppression des données dans la table paiements
                    DB::table('paiements')->where('email', $emailAuditeur)->delete(); 

                    // suppressions des rapports
                    DB::table('rapports')->where('email', $emailAuditeur)->delete(); 
                    // suppressions des participants
                    DB::table('participants')->where('email', $emailAuditeur)->delete(); 
                    // suppressions des autresparticipants
                    DB::table('autresparticipants')->where('username', $emailAuditeur)->delete(); 
                    // suppressions des auditeurs_pieces
                    DB::table('auditeurs_pieces')->where('created_by', $idUser)->delete(); 
                    // suppressions des panelistes
                    DB::table('panelistes')->where('email', $emailAuditeur)->delete();  

                    $inscriptions = Inscriptions::whereEmail($emailAuditeur)->get();
                    foreach ($inscriptions as $inscription){
                        // suppression des historiquetraitementdossiers
                        DB::table('historiquetraitementdossiers')->where('code_inscription', $inscription->code_inscription)->delete(); 
                        // suppression des inscriptions_dscs
                        DB::table('inscriptions_dscs')->where('code_inscription', $inscription->code_inscription)->delete(); 
                    }
                    // suppression des inscriptions
                    DB::table('inscriptions')->where('email', $emailAuditeur)->delete();  
                    // suppression des auditeurs
                    DB::table('auditeurs')->where('email', $emailAuditeur)->delete(); 
                    // suppression des roles
                    DB::table('roles_users')->where('user_id', $idUser)->delete(); 
                    // suppression des users
                    DB::table('users')->where('id', $idUser)->delete();  

                    DB::commit();

                        // Réactiver la vérification des clés étrangères
                        DB::statement('SET FOREIGN_KEY_CHECKS=1');  

                        return  ResponseHandler::success("Suppression effectuée avec succès");

                } catch (\Exception $e) {

                    DB::rollBack();

                        // Réactiver la vérification des clés étrangères
                        DB::statement('SET FOREIGN_KEY_CHECKS=1');  
                        Log::info($e->getMessage());
                        return  ResponseHandler::error("Erreur lors de la suppression"); 
                } 
                
        } else { 
            // Réactiver la vérification des clés étrangères
            DB::statement('SET FOREIGN_KEY_CHECKS=1');  
            return  ResponseHandler::error("Le compte n'existe pas");
        }
        

    }
    
    public function startDeleteCompteAuditeur(Request $request): \Illuminate\Http\JsonResponse
    {  
 
         // Désactiver temporairement la vérification des clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // update table auditeur
        $auditeur = Auditeurs::find($request->identifiant);
        $user = User::whereEmail($auditeur->email)->first(); 
        if ($user) {
            
                    try {
                        
                    $idUser = $user->id;
                    $idAuditeur = $auditeur->id;
                    $emailAuditeur = $auditeur->email;
                    $erreur = false;

                    DB::beginTransaction(); 
                        // suppression des paiements
                    $paiements = Paiements::whereEmail($emailAuditeur)->get();
                    foreach($paiements as $paiement){
                        // suppression des détails paiements selon le type de paiement
                        if($paiement->type == "OUVRAGE"){ // suppression des données relatives aux OUVRAGES
                            $check1 =    DB::table('detailpaiementouvrages')->where('paiementouvrage_id', $paiement->id)->exists();
                            if($check1){
                                $erreur = true;
                            }
                        }else{ // suppression des données relatives aux FORMATIONS CLASSIQUES
                              $check2 =   DB::table('detailspaiements')->where('reference_paiement', $paiement->reference)->exists();
                              if($check2){
                                $erreur = true;
                            }
                        } 
                    }
                    // suppression des données dans la table paiements
                   if(count($paiements) > 0){ $erreur = true; }

                    // suppressions des rapports
                    $check3 = DB::table('rapports')->where('email', $emailAuditeur)->exists(); 
                    if($check3){
                                $erreur = true;
                    }
                    // suppressions des participants
                    $check4 = DB::table('participants')->where('email', $emailAuditeur)->exists(); 
                    if($check4){
                                $erreur = true;
                    } 

                    $inscriptions = Inscriptions::whereEmail($emailAuditeur)->count();
                    if($inscriptions > 0 ){ $erreur = true; } 

                    if($erreur){
                        // Réactiver la vérification des clés étrangères
                        DB::statement('SET FOREIGN_KEY_CHECKS=1');  

                        return  ResponseHandler::error("Impossible de supprimer le compte, il dispose d'une formation",301);

                    }else{
                         // suppression des auditeurs
                    DB::table('auditeurs')->where('email', $emailAuditeur)->delete(); 
                    // suppression des roles
                    DB::table('roles_users')->where('user_id', $idUser)->delete(); 
                    // suppression des users
                    DB::table('users')->where('id', $idUser)->delete();  

                    DB::commit();

                        // Réactiver la vérification des clés étrangères
                        DB::statement('SET FOREIGN_KEY_CHECKS=1');  

                        return  ResponseHandler::success("Suppression effectuée avec succès");
                    }
                   

                } catch (\Exception $e) {

                    DB::rollBack();

                        // Réactiver la vérification des clés étrangères
                        DB::statement('SET FOREIGN_KEY_CHECKS=1');  
                        Log::info($e->getMessage());
                        return  ResponseHandler::error("Erreur lors de la suppression", 301); 
                } 
                
        } else { 
            // Réactiver la vérification des clés étrangères
            DB::statement('SET FOREIGN_KEY_CHECKS=1');  
            return  ResponseHandler::error("Le compte n'existe pas", 301);
        }
        

    }

    public function getMyAttestions(string $email): \Illuminate\Http\JsonResponse
    {
        $auditeur = Auditeurs::whereEmail($email)->first();
        $listeAttestation = Rapports::join("formations","formations.code_formation","=","rapports.code_formation")
        ->where("email", $auditeur->email) 
        ->where("attestation_send", true)
        ->get(["rapports.email", "formations.code_formation", "rapports.id","formations.theme"]);
            $response["listeAttestation"] = $listeAttestation; 
        return  ResponseHandler::success("ok",$listeAttestation);
    }

 public function rmInscriptionAuditeur(Request $request)
{
    $request->validate([
        'code_formation'   => 'required|string',
        'code_inscription' => 'required|string',
    ]);

    $codeFormation   = $request->code_formation;
    $codeInscription = $request->code_inscription;
    $userId          = auth('api')->id();

    try {
        DB::transaction(function () use ($codeFormation, $codeInscription, $userId) {

            $formation = Formations::where('code_formation', $codeFormation)->first();
            if (!$formation) {
                throw new \DomainException("Impossible d'annuler l'inscription, formation introuvable.");
            }

            $inscription = Inscriptions::where('code_inscription', $codeInscription)
                ->where('code_formation', $codeFormation)
                ->lockForUpdate()
                ->first();
            if (!$inscription) {
                throw new \DomainException("Inscription introuvable pour cette formation.");
            }

            $paiement = Paiements::where('code_inscription', $codeInscription)->lockForUpdate()->first();

            if (!$paiement) {
                $inscription->delete();
            } else {
                if (Inscriptions::where('code_inscription', $codeInscription)->count() > 1) {
                    throw new \DomainException("Vous ne pouvez pas annuler cette inscription car plusieurs formations sont associées.");
                }

                if ($this->paiementDejaEngage($paiement, (bool) $formation->is_formation_dsc)) {
                    throw new \DomainException("Vous ne pouvez pas supprimer cette inscription car un paiement est déjà enregistré.");
                }

                Detailspaiements::where('code_inscription', $codeInscription)->delete();
                $paiement->delete();
                $inscription->delete();
            }

            $this->genarateurCodeService->saveHistoriqueAction(
                'ANNULATION INSCRIPTION AUDITEUR',
                "Annulation de l'inscription code_formation : {$codeFormation} code_inscription : {$codeInscription}",
                $userId
            );
        });

    } catch (\DomainException $e) {
        // Refus métier : l'exception a annulé la transaction, rien n'est écrit
        return ResponseHandler::error($e->getMessage(), 301);

    } catch (\Throwable $e) {
        Log::error('Annulation inscription : exception', [
            'code_inscription' => $codeInscription,
            'error'            => $e->getMessage(),
        ]);
        return ResponseHandler::error("Une erreur est survenue lors de l'annulation de l'inscription", 301);
    }

    // Atteint UNIQUEMENT si la transaction s'est terminée sans exception
    return ResponseHandler::success("Annulation effectuée avec succès");
}

/**
 * Un paiement est "engagé" dès qu'il y a une trace de règlement :
 * transaction, montant payé, détail partiellement payé ou tranche DSC.
 */
private function paiementDejaEngage(Paiements $paiement, bool $estDsc): bool
{
    if (!empty($paiement->num_transaction)
        || !empty($paiement->statut_transaction)
        || (float) $paiement->montant_paye > 0) {
        return true;
    }

    // Un détail déjà (partiellement) payé
    $details = Detailspaiements::where('reference_paiement', $paiement->reference);
    if ((clone $details)->where('montant_paye', '>', 0)->exists()) {
        return true;
    }

    if ($estDsc) {
        $idsDetails = (clone $details)->pluck('id');
        Log::info($idsDetails);
 
    $prefixe = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $paiement->reference) . '\\_%';

    return PaiementsTranche::query()
        ->where(function ($q) use ($idsDetails, $paiement, $prefixe) {
            $q->whereIn('details_paiements_id', $idsDetails);
              //->orWhere('reference', $paiement->reference)     // paiement unique : REFERENCE
              //->orWhere('reference', 'like', $prefixe);        // tranches : REFERENCE_1, _2, ...
        })
        ->exists();
    }

    return false;
}


}
