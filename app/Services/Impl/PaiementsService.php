<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Mail\NotificationComptable;
use App\Mail\Paiementattente;
use App\Models\Auditeurs;
use App\Models\Detailpaiementouvrages;
use App\Models\Detailspaiements;
use App\Models\Formations;
use App\Models\Inscriptions;
use App\Models\Modepaiements;
use App\Models\Paiements;
use App\Models\Rapports;
use App\Models\User;
use App\Services\contracts\PaiementsInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaiementsService implements PaiementsInterface
{

    public function getInfosPaiement(string $reference): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getInfosPaiement() method.
        $paiement = Paiements::leftjoin('modepaiements', 'modepaiements.id', '=', 'paiements.id_modepaiement')
            ->whereReference($reference)
            ->get(['paiements.id','num_transaction','is_paiement_partiel','reference','montant_total','montant_paye','montant_restant','email','code_inscription','paiements.id_modepaiement','modepaiements.libelle'])
            ->first();

        // récupération des clés pour les apis
        if(env('KKIAPAY_SANDBOX') == 1){
            $code_kkiapay = env('KKIAPAY_SANDBOX_PUBLIC_KEY');
            $code_fedapay = env('FEDAPAY_CLE_PUBLIQUE');
            $code_pawapay = env('PAWAPAY_SANDBOX_TOKEN_ID');
            $code_cinetpay = env('CINETPAY_APIKEY');
            $env_fedapay = env('FEDAPAY_ENVIRONNEMENT');
            $env_kkiapay = env('KKIAPAY_SANDBOX');
        }else{
            $code_kkiapay = env('KKIAPAY_LIVE_PUBLIC_KEY');
            $code_fedapay = env('FEDAPAY_CLE_PUBLIQUE');
            $code_pawapay = env('PAWAPAY_SANDBOX_TOKEN_ID');
            $code_cinetpay = env('CINETPAY_APIKEY');
            $env_fedapay = env('FEDAPAY_ENVIRONNEMENT');
            $env_kkiapay = env('KKIAPAY_SANDBOX');
        }

        if($paiement){
            $auditeur = Auditeurs::whereEmail($paiement->email)->first();
            $dataResponse = [
                'paiement' => $paiement,
                'auditeur' => $auditeur,
                'code_kkiapay' => $code_kkiapay,
                'code_fedapay' => $code_fedapay,
                'code_pawapay' => $code_pawapay,
                'code_cinetpay' => $code_cinetpay,
                'env_kkiapay' => $env_kkiapay,
                'env_fedapay' => $env_fedapay,
                'success' => true,
            ];
            return  ResponseHandler::success("ok",$dataResponse);
        }

        // paiement non retrouvé
        $dataResponse = [
            'success' => false,
        ];
        return  ResponseHandler::success("Paiement non retrouvé ou référence invalide",$dataResponse);
    }

    public function getInfoPaiementPartiel(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getInfoPaiementPartiel() method.
        $paiement = Paiements::whereReference($request->reference)->select('paiements.id','paiements.reference','paiements.montant_restant','paiements.telephone','paiements.email','paiements.num_transaction')->first();
        $auditeur = Auditeurs::whereEmail($paiement->email)->select('auditeurs.id','auditeurs.nom','auditeurs.prenoms','auditeurs.email','auditeurs.telephone')->first();
        $liste_modepaiements = Modepaiements::where('is_enable',true)->get(['id','libelle']);

        $detailPaiement = Detailspaiements::query()
            ->leftJoin('reductions','reductions.id','=','detailspaiements.id_reduction')
            ->leftJoin('formations','formations.code_formation','=','detailspaiements.code_formation')
            ->whereReferencePaiement($request->reference)
            ->where('statut_paiement','!=','PAYE')
            ->select('detailspaiements.id','detailspaiements.montant_restant','detailspaiements.code_formation','detailspaiements.montant_paye',
                'detailspaiements.id_reduction','reductions.pourcentage','detailspaiements.id_tarification_formation','detailspaiements.reference_paiement',
                'formations.theme')
            ->addSelect([
                // check reduction
                \DB::raw('CASE WHEN detailspaiements.id_reduction IS NOT NULL THEN 1 ELSE 0 END as checkReduction')
            ])
            ->get();

        // check details paiements
        $checkDetails = Detailspaiements::where('statut_paiement','!=', 'PAYE')->exists();
        $havePaiement = false;
        if($checkDetails){ $havePaiement = true; }
        if(!empty($paiement->num_transaction)){ $havePaiement = true; }

        // récupération des clés pour les apis
        if(env('KKIAPAY_SANDBOX') == 1){
            $code_kkiapay = env('KKIAPAY_SANDBOX_PUBLIC_KEY');
            $code_fedapay = env('FEDAPAY_CLE_PUBLIQUE');
            $code_pawapay = env('PAWAPAY_SANDBOX_TOKEN_ID');
            $code_cinetpay = env('CINETPAY_APIKEY');
            $env_fedapay = env('FEDAPAY_ENVIRONNEMENT');
            $env_kkiapay = env('KKIAPAY_SANDBOX');
        }else{
            $code_kkiapay = env('KKIAPAY_LIVE_PUBLIC_KEY');
            $code_fedapay = env('FEDAPAY_CLE_PUBLIQUE');
            $code_pawapay = env('PAWAPAY_SANDBOX_TOKEN_ID');
            $code_cinetpay = env('CINETPAY_APIKEY');
            $env_fedapay = env('FEDAPAY_ENVIRONNEMENT');
            $env_kkiapay = env('KKIAPAY_SANDBOX');
        }

        $dataResponse = [
            'liste_modepaiements' => $liste_modepaiements,
            'detail_paiement' => $detailPaiement,
            'auditeur' => $auditeur,
            'paiement' => $paiement,
            'havePaiement' => $havePaiement,
            'code_kkiapay' => $code_kkiapay,
            'code_fedapay' => $code_fedapay,
            'code_pawapay' => $code_pawapay,
            'code_cinetpay' => $code_cinetpay,
            'env_kkiapay' => $env_kkiapay,
            'env_fedapay' => $env_fedapay
        ];

        return  ResponseHandler::success("ok",$dataResponse);
    }

    public function enregistrerPaiementPartiel(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement enregistrerPaiementPartiel() method.
        // mise à jour de la table paiement
        $mode_paiement = Modepaiements::whereLibelle($request->mode_paiement)->first();
        $paiement = Paiements::where('reference', $request->reference_paiement)->get()->first();
        $update_montant_restant = $paiement->montant_restant - $request->montant_total;
        $update_montant_paye = $paiement->montant_paye + $request->montant_total;

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

        if ($update_montant_restant == 0 && $request->montant_total == $paiement->montant_restant) {
            # code...
            $update_paiement->setAttribute('statut_paiement',$statut_paiement);
            if ($paiement_partiel) {
                # code...
                $update_paiement->setAttribute('montant_paye', $update_montant_paye);
                $update_paiement->setAttribute('montant_restant', $update_montant_restant);
            }
            $update_paiement->setAttribute('id_modepaiement', $mode_paiement->id);
            $update_paiement->setAttribute('num_transaction', $request->preuve_reference);
            $update_paiement->setAttribute('statut_transaction', $request->preuve_reference);
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
            $update_paiement->setAttribute('updated_at', date('Y-m-d H:i:s'));

            $update_paiement->update();
        }

        // update détails paiements
        $details_paiement = $request->formations;
        foreach ($details_paiement as $key => $data) {
            # code...
            $update_detailspaiement = Detailspaiements::find($data['detail_id']);
            if ($paiement_partiel) {
                # code...
                $update_detailspaiement->setAttribute('is_paiement_partiel', true);
                $update_detailspaiement->setAttribute('modepaiement_id', $mode_paiement->id);
                $update_detailspaiement->setAttribute('reference_paiement_partiel', $request->preuve_reference);
                $update_detailspaiement->setAttribute('statut_paiement', "PAYE");

            }else {
                # code...
                $update_detailspaiement->setAttribute('statut_paiement', $statut_details_paiement);
            }
            // $get_preuve = $request->preuve_paiement;
            // enregistrement de la preuve si ça existe
            if ($request->hasFile('preuve') && $request->file('preuve')->isValid()) {
                $extension = $request->preuve->getClientOriginalExtension();
                $preuve_name = date('Ymd-His') . '.' . $extension;
                $request->preuve->storeAs('preuve', $preuve_name, 'public');
                $update_detailspaiement->setAttribute('preuve_path', $preuve_name);
            }
            $update_detailspaiement->update();
        }

        // send mail
        $auditeur = Auditeurs::where("email",$paiement->email)->first();

        if ($auditeur) {
            # code...
            $contenu = [
                'nom' => $auditeur->nom,
                'prenoms' => $auditeur->prenoms,
                'reference_paiement' => $request->reference_paiement,
                'subject' => 'INFORMATION SUR VOTRE PAIEMENT '.$request->reference_paiement,
                'email' => $paiement->email,
                'montant' => $request->montant_total,
                'mode_paiement' => $mode_paiement->libelle
            ];
        } else {
            # code...
            $contenu = [
                'nom' => "",
                'prenoms' => "",
                'reference_paiement' => $request->reference_paiement,
                'subject' => 'INFORMATION SUR VOTRE PAIEMENT '.$request->reference_paiement,
                'email' => $paiement->email,
                'montant' => $request->montant_total,
                'mode_paiement' => $mode_paiement->libelle
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
                'reference_paiement' => $paiement->reference_paiement,
                'email_auditeur' => $paiement->email,
                'nom_auditeur' => $auditeur->prenoms.' '.$auditeur->nom,
                'subject' => 'NOUVEAU PAIEMENT ' . date('d-m-Y H:i:s'),
                'email' => '',
                'montant' => $request->montant_total,
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
            'success' => true,
        ];

        return ResponseHandler::success("Opération effectuée avec succès",$dataResponse);

    }


    public function getInfosPaiementOuvrage(string $reference): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getInfosPaiementOuvrage() method.
        // TODO: Implement getInfosPaiement() method.
        $paiement = Paiements::leftjoin('modepaiements', 'modepaiements.id', '=', 'paiements.id_modepaiement')
            ->whereReference($reference)
            ->get(['paiements.id','num_transaction','is_paiement_partiel','reference','montant_total','montant_paye','montant_restant','email','code_inscription','paiements.id_modepaiement','modepaiements.libelle'])
            ->first();
        $details = Detailpaiementouvrages::join('ouvrages','ouvrages.id','=','detailpaiementouvrages.ouvrage_id')
            ->where('detailpaiementouvrages.paiementouvrage_id',$paiement->id)
            ->get(['detailpaiementouvrages.id','detailpaiementouvrages.montant','detailpaiementouvrages.reference','ouvrages.libelle']);
        // récupération des clés pour les apis
        if(env('KKIAPAY_SANDBOX') == 1){
            $code_kkiapay = env('KKIAPAY_SANDBOX_PUBLIC_KEY');
            $code_fedapay = env('FEDAPAY_CLE_PUBLIQUE');
            $code_pawapay = env('PAWAPAY_SANDBOX_TOKEN_ID');
            $code_cinetpay = env('CINETPAY_APIKEY');
            $env_fedapay = env('FEDAPAY_ENVIRONNEMENT');
            $env_kkiapay = env('KKIAPAY_SANDBOX');
        }else{
            $code_kkiapay = env('KKIAPAY_LIVE_PUBLIC_KEY');
            $code_fedapay = env('FEDAPAY_CLE_PUBLIQUE');
            $code_pawapay = env('PAWAPAY_SANDBOX_TOKEN_ID');
            $code_cinetpay = env('CINETPAY_APIKEY');
            $env_fedapay = env('FEDAPAY_ENVIRONNEMENT');
            $env_kkiapay = env('KKIAPAY_SANDBOX');
        }

        if($paiement){
            $auditeur = Auditeurs::whereEmail($paiement->email)->first();
            $dataResponse = [
                'paiement' => $paiement,
                'auditeur' => $auditeur,
                'code_kkiapay' => $code_kkiapay,
                'code_fedapay' => $code_fedapay,
                'code_pawapay' => $code_pawapay,
                'code_cinetpay' => $code_cinetpay,
                'env_kkiapay' => $env_kkiapay,
                'env_fedapay' => $env_fedapay,
                'details' => $details,
                'success' => true,
            ];
            return  ResponseHandler::success("ok",$dataResponse);
        }

        // paiement non retrouvé
        $dataResponse = [
            'success' => false,
        ];
        return  ResponseHandler::success("Paiement non retrouvé ou référence invalide",$dataResponse);
    }

    public function getRecapitulatifPaiements(string $codeFormation): \Illuminate\Http\JsonResponse
    {
        // Information sur la formation en cours
        $formation = Formations::where("code_formation", $codeFormation)->first();
        $totalInscGratuite = Detailspaiements::where("code_formation",$codeFormation)->where("id_reduction",3)->count();

        // Détails des participants
       $liste_participants = DB::table('inscriptions')
            ->leftJoin('auditeurs', 'inscriptions.email', '=', 'auditeurs.email')
            ->leftJoin('paiements', 'paiements.code_inscription', '=', 'inscriptions.code_inscription')
            ->leftJoin(
                DB::raw('
            (
                SELECT
                    dp.reference_paiement,
                    MAX(r.pourcentage) as reduction_pourcentage,
                    SUM(
                        CASE
                            WHEN gt.type_tarif = "INSCRIPTION"
                            THEN dp.montant_paye
                            ELSE 0
                        END
                    ) as frais_inscription_paye,
                    SUM(
                        CASE
                            WHEN gt.type_tarif = "INSCRIPTION"
                            THEN dp.montant_restant
                            ELSE 0
                        END
                    ) as frais_inscription_restant,
                    SUM(
                        CASE
                            WHEN gt.type_tarif <> "INSCRIPTION"
                            THEN dp.montant_paye
                            ELSE 0
                        END
                    ) as frais_scolarite_paye,

                    SUM(
                        CASE
                            WHEN gt.type_tarif <> "INSCRIPTION"
                            THEN dp.montant_restant
                            ELSE 0
                        END
                    ) as frais_scolarite_restant

                FROM detailspaiements dp
                INNER JOIN tarificationsformations tt
                    ON tt.id = dp.id_tarification_formation
                INNER JOIN grilletarifaires gt
                    ON gt.id = tt.id_grilletarifaire
                LEFT JOIN reductions r
                    ON r.id = dp.id_reduction

                GROUP BY dp.reference_paiement
            ) as dp_calc
        '),
                function ($join) {
                    $join->on('dp_calc.reference_paiement', '=', 'paiements.reference')
                        ->where('paiements.type', '=', 'DSC');
                }
            )

            /*
            |--------------------------------------------------------------------------
            | 2️⃣ Flag toujours retourné (is_paiement_dsc)
            |--------------------------------------------------------------------------
            */
            ->leftJoin(
                DB::raw('
            (
                SELECT
                    reference_paiement,
                    MAX(have_reduction_dsc) as have_reduction_dsc
                FROM detailspaiements
                GROUP BY reference_paiement
            ) as dp_flag
        '),
                'dp_flag.reference_paiement',
                '=',
                'paiements.reference'
            )

            ->select(
                'inscriptions.email',
                'auditeurs.nom as nom',
                'auditeurs.prenoms as prenoms',
                'auditeurs.telephone as telephone',
                'auditeurs.pays as pays',
                'inscriptions.tarif_formation_id',
                'inscriptions.type_formation',
                'inscriptions.presentiel',
                'inscriptions.observations',

                'paiements.montant_total',
                'paiements.montant_restant',
                'paiements.montant_paye',
                'paiements.statut_paiement',
                
                // Frais calculés via grille
                DB::raw('COALESCE(dp_calc.frais_inscription_paye, 0) as frais_inscription_paye'),
                DB::raw('COALESCE(dp_calc.frais_inscription_restant, 0) as frais_inscription_restant'),
                DB::raw('COALESCE(dp_calc.frais_scolarite_paye, 0) as frais_scolarite_paye'),
                DB::raw('COALESCE(dp_calc.frais_scolarite_restant, 0) as frais_scolarite_restant'),

                // Réduction
                DB::raw('COALESCE(dp_calc.reduction_pourcentage, 0) as reduction_pourcentage'),

                // Toujours retourné
                DB::raw('COALESCE(dp_flag.have_reduction_dsc, 0) as have_reduction_dsc')
            )
            ->selectRaw(
                '(EXISTS (
                    SELECT 1 FROM rapports
                    WHERE rapports.email = inscriptions.email 
                    AND rapports.code_formation = ?
                )) as a_participe',
                [$codeFormation]
            )
            ->where('inscriptions.code_formation', $codeFormation)
            ->orderBy('auditeurs.nom', 'ASC')
            ->get(); 
            /*->leftJoin('detailspaiements as dp', function ($join) {
                $join->on('dp.reference_paiement', '=', 'paiements.reference')
                    ->where('paiements.type', '=', 'DSC');
            })
            ->select(
                'inscriptions.email',
                'auditeurs.nom as nom',
                'auditeurs.prenoms as prenoms',
                'auditeurs.telephone as telephone',
                'auditeurs.pays as pays',
                'inscriptions.tarif_formation_id',
                'inscriptions.type_formation',
                'inscriptions.presentiel',
                'paiements.montant_total',
                'paiements.montant_restant',
                'paiements.montant_paye',
            )
            ->where('inscriptions.code_formation', $codeFormation)
            ->orderBy('auditeurs.nom', 'ASC')
            ->get();*/

        // Total des participants
        $total_participant = Rapports::where("code_formation", $codeFormation)->count();

        // Total des participants en présentiel
        $total_participant_presentiel = DB::table('rapports')
            ->join('inscriptions', 'inscriptions.email', '=', 'rapports.email')
            ->leftJoin('auditeurs', 'inscriptions.email', '=', 'auditeurs.email')
            ->where('rapports.code_formation', $codeFormation)
            ->where('inscriptions.code_formation', $codeFormation)
            ->where('rapports.temps_presence', 0)
            ->count();

        $totalPaiementAttestation = DB::table('tarificationsformations')
            ->join('detailspaiements', 'detailspaiements.id_tarification_formation', '=', 'tarificationsformations.id')
            ->join('grilletarifaires', 'grilletarifaires.id', '=', 'tarificationsformations.id_grilletarifaire')
            ->join('paiements', 'paiements.reference', '=', 'detailspaiements.reference_paiement')
            ->join('modepaiements', 'modepaiements.id', '=', 'paiements.id_modepaiement')
            ->select('detailspaiements.*','paiements.email as email_paiement','grilletarifaires.type_tarif','grilletarifaires.montant','modepaiements.libelle as modepaiement')
            ->where('tarificationsformations.code_formation',$codeFormation)
            ->where('detailspaiements.statut_paiement',"PAYE")
            ->count();

        // Total des inscriptions
        $total_inscrit = Inscriptions::where("code_formation", $codeFormation)->count();

        // Total des inscriptions en visio
        $total_inscrit_visio = Inscriptions::where("code_formation", $codeFormation)->where("presentiel", 0)->count();

        // Total des inscriptions en présentiel
        $total_inscrit_presentiel = Inscriptions::where("code_formation", $codeFormation)->where("presentiel", 1)->count();

        $totalGratuitPresentiel = Detailspaiements::where("detailspaiements.code_formation",$codeFormation)
            ->where("id_reduction",3)
            ->join("inscriptions","inscriptions.email","detailspaiements.email")
            ->where("inscriptions.type_formation","PRESENTIEL")
            ->where("inscriptions.code_formation",$codeFormation)->count();

        $totalGratuitVisio = Detailspaiements::where("detailspaiements.code_formation",$codeFormation)
            ->where("id_reduction",3)
            ->join("inscriptions","inscriptions.email","detailspaiements.email")
            ->where("inscriptions.type_formation","VISIO")
            ->where("inscriptions.code_formation",$codeFormation)->count();

                      $fm_gratuite = false;
                  foreach($formation->getTarification($formation->code_formation) as $tarification){
                      if ($tarification->get_grille_libelle == "GRATUIT"){
                          $fm_gratuite = true;
                      }
                  }

        // Calcul des statistiques
        $stats = [
            'inscriptions_presentiel' => $total_inscrit_presentiel,
            'gratuits_presentiel' => $totalGratuitPresentiel,
            'payants_presentiel' => $total_inscrit_presentiel, // À ajuster selon votre logique
            'attestations_presentiel' => $totalPaiementAttestation, // À calculer selon votre logique
            'participants_presentiel' => $total_participant_presentiel,

            'inscriptions_visio' => $total_inscrit_visio,
            'gratuits_visio' => $totalGratuitVisio, // À calculer selon votre logique
            'payants_visio' => $total_inscrit_visio, // À ajuster selon votre logique
            'attestations_visio' => 0, // À calculer selon votre logique
            'participants_visio' => $total_participant - $total_participant_presentiel,

            'total_inscriptions' => $total_inscrit,
            'total_gratuits' => 0, // À calculer selon votre logique
            'total_payants' => $total_inscrit, // À ajuster selon votre logique
            'total_attestations' => 0, // À calculer selon votre logique
            'total_participants' => $total_participant,
            'totalInscGratuite' => $totalInscGratuite
        ];



        // Retourner les données sous forme de JSON
        return response()->json([
            'success' => true,
            'formation' => $formation,
            'stats' => $stats,
            'participants' => $liste_participants,
            'fm_gratuite' => $fm_gratuite
        ]);
    }


}
