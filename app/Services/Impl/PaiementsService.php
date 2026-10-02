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
use Illuminate\Database\Eloquent\ModelNotFoundException;
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


    /**
     * Liste paginée + filtrée des participants d'une formation.
     *
     * Paramètres acceptés :
     *  - page, per_page, sort_field, sort_order (asc par défaut)
     *  - search          : nom, prénoms, e-mail, téléphone
     *  - type_formation  : PRESENTIEL | VISIO
     *  - statut_paiement : PAYE | NON_PAYE
     *  - a_participe     : 1 | 0
     */
    public function getStatsParticipantsFormation(string $codeFormation, Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $query = $this->participantsFormationQuery($codeFormation, $request);

            // Totaux sur l'ensemble filtré, calculés AVANT tri et pagination
            $totaux = (clone $query)
                ->selectRaw('
                    COUNT(*) as nb_participants,
                    COALESCE(SUM(t.montant_paye), 0)    as total_paye,
                    COALESCE(SUM(t.montant_restant), 0) as total_restant
                ')
                ->first();

            $page = $this->trierParticipants($query, $request)
                ->paginate((int) $request->input('per_page', 10));

            return ResponseHandler::success('ok', [
                'current_page' => $page->currentPage(),
                'data'         => $page->items(),
                'per_page'     => $page->perPage(),
                'total'        => $page->total(),
                'totaux'       => $totaux,
            ]);

        } catch (ModelNotFoundException $e) {
            return ResponseHandler::success("Formation introuvable", [
                'success' => false,
                'message' => "La formation {$codeFormation} n'existe pas.",
            ]);

        } catch (\Throwable $e) {
            Log::error('Participants formation : ' . $e->getMessage());
            return ResponseHandler::error("Erreur lors de la récupération des participants", 500, $e->getMessage());
        }
    }


    /**
     * Même requête, sans pagination : pour l'export Excel / PDF.
     */
    public function getStatsParticipantsFormationExport(string $codeFormation, Request $request)
    {
        return $this->trierParticipants($this->participantsFormationQuery($codeFormation, $request), $request)->get();
    }

 
/**
 * Requête filtrée commune à la liste et à l'export (sans tri ni pagination).
 */
private function participantsFormationQuery(string $codeFormation, Request $request)
{
    $formation = Formations::where('code_formation', $codeFormation)->firstOrFail();
    $estDsc    = (bool) $formation->is_formation_dsc;
 
    $base = $estDsc
        ? $this->baseParticipantsDsc($codeFormation)
        : $this->baseParticipantsClassique($codeFormation);
 
    /* ---------- Filtres sur colonnes réelles (index utilisés) ---------- */
    if ($search = trim((string) $request->input('search'))) {
        $base->where(function ($q) use ($search, $estDsc) {
            $like = "%{$search}%";
            $q->where('auditeurs.nom', 'like', $like)
              ->orWhere('auditeurs.prenoms', 'like', $like)
              ->orWhere('inscriptions.email', 'like', $like)
              ->orWhere('auditeurs.telephone', 'like', $like)
              ->orWhereRaw("CONCAT(auditeurs.prenoms, ' ', auditeurs.nom) LIKE ?", [$like])
              ->orWhereRaw("CONCAT(auditeurs.nom, ' ', auditeurs.prenoms) LIKE ?", [$like]);
 
            if ($estDsc) {
                $q->orWhere('paiements.code_remise_dsc', 'like', $like);   // table jointe en DSC seulement
            }
        });
    }
 
    $base->when($request->filled('type_formation'),
        fn ($q) => $q->where('inscriptions.type_formation', $request->type_formation));
 
    /* ---------- Enveloppe : filtres sur les colonnes calculées ---------- */
    return DB::query()
        ->fromSub($base, 't')
        ->when($request->filled('statut_paiement'), function ($q) use ($request) {
            if ($request->statut_paiement === 'PAYE') {
                $q->where('t.statut_paiement', 'PAYE');
            } else {
                // Non payé : tout ce qui n'est pas PAYE, y compris sans paiement (NULL)
                $q->where(fn ($w) => $w->whereNull('t.statut_paiement')->orWhere('t.statut_paiement', '<>', 'PAYE'));
            }
        })
        ->when($request->filled('a_participe'),
            fn ($q) => $q->where('t.a_participe', (int) $request->a_participe))
        ->when($request->filled('type_reduction'), function ($q) use ($request) {
            match ($request->type_reduction) {
                'BOURSE_COMPLET' => $q->where('t.have_reduction_dsc', 1)
                                      ->where('t.code_remise_dsc', 'BOURSE_COMPLET'),
                'REDUCTION_5'    => $q->where('t.have_reduction_dsc', 1)
                                      ->where(fn ($w) => $w->whereNull('t.code_remise_dsc')
                                                           ->orWhere('t.code_remise_dsc', '<>', 'BOURSE_COMPLET')),
                'SANS'           => $q->where('t.have_reduction_dsc', 0),
                default          => null,
            };
        });
}
 
 
/**
 * Tri : liste blanche, ascendant par défaut, départage par id pour une pagination stable.
 */
private function trierParticipants($query, Request $request)
{
    $allowed = [
        'nom', 'prenoms', 'email', 'type_formation', 'statut_paiement',
        'montant_total', 'montant_paye', 'montant_restant', 'a_participe', 'observations',
        'frais_inscription', 'frais_participation', 'reduction_affichee', 'code_remise_dsc',
    ];
 
    $field = in_array($request->input('sort_field'), $allowed, true) ? $request->input('sort_field') : 'nom';
    $order = $request->input('sort_order') === 'desc' ? 'desc' : 'asc';
 
    return $query->orderBy("t.$field", $order)->orderBy('t.id', $order);
}


/**
 * Base DSC.
 * Les montants affichés par la vue DSC sont calculés ICI, pour que tri,
 * filtres, totaux et export utilisent exactement les mêmes valeurs.
 */
private function baseParticipantsDsc(string $codeFormation)
{
    // Catégories DSC pour lesquelles payé / restant viennent des frais détaillés
    $categoriesDetaillees = "'PROFESSIONNELS', 'ETUDIANTS', 'INSCRIPTION'";
 
    return DB::table('inscriptions')
        ->leftJoin('auditeurs', 'inscriptions.email', '=', 'auditeurs.email')
        ->leftJoin('paiements', 'paiements.code_inscription', '=', 'inscriptions.code_inscription')
        ->leftJoin(DB::raw('
            (
                SELECT
                    dp.reference_paiement,
                    MAX(r.pourcentage) as reduction_pourcentage,
                    SUM(CASE WHEN gt.type_tarif = "INSCRIPTION"  THEN dp.montant_paye    ELSE 0 END) as frais_inscription_paye,
                    SUM(CASE WHEN gt.type_tarif = "INSCRIPTION"  THEN dp.montant_restant ELSE 0 END) as frais_inscription_restant,
                    SUM(CASE WHEN gt.type_tarif <> "INSCRIPTION" THEN dp.montant_paye    ELSE 0 END) as frais_scolarite_paye,
                    SUM(CASE WHEN gt.type_tarif <> "INSCRIPTION" THEN dp.montant_restant ELSE 0 END) as frais_scolarite_restant
                FROM detailspaiements dp
                INNER JOIN tarificationsformations tt ON tt.id = dp.id_tarification_formation
                INNER JOIN grilletarifaires gt        ON gt.id = tt.id_grilletarifaire
                LEFT JOIN reductions r                ON r.id  = dp.id_reduction
                GROUP BY dp.reference_paiement
            ) as dp_calc
        '), function ($join) {
            $join->on('dp_calc.reference_paiement', '=', 'paiements.reference')
                 ->where('paiements.type', '=', 'DSC');
        })
        ->leftJoin(DB::raw('
            (
                SELECT reference_paiement, MAX(have_reduction_dsc) as have_reduction_dsc
                FROM detailspaiements
                GROUP BY reference_paiement
            ) as dp_flag
        '), 'dp_flag.reference_paiement', '=', 'paiements.reference')
        ->select(
            'inscriptions.id',
            'inscriptions.email',
            'auditeurs.nom',
            'auditeurs.prenoms',
            'auditeurs.telephone',
            'auditeurs.pays',
            'inscriptions.tarif_formation_id',
            'inscriptions.type_formation',
            'inscriptions.presentiel',
            'inscriptions.observations',
            'paiements.statut_paiement',
            'paiements.code_remise_dsc',
 
            // Montant total : celui du paiement (comme la vue d'origine)
            DB::raw('COALESCE(paiements.montant_total, 0) as montant_total'),
 
            // Payé / restant : depuis les frais détaillés pour les catégories DSC, sinon depuis le paiement
            DB::raw("CASE WHEN inscriptions.type_formation IN ({$categoriesDetaillees})
                          THEN COALESCE(dp_calc.frais_inscription_paye, 0) + COALESCE(dp_calc.frais_scolarite_paye, 0)
                          ELSE COALESCE(paiements.montant_paye, 0)
                     END as montant_paye"),
            DB::raw("CASE WHEN inscriptions.type_formation IN ({$categoriesDetaillees})
                          THEN COALESCE(dp_calc.frais_inscription_restant, 0) + COALESCE(dp_calc.frais_scolarite_restant, 0)
                          ELSE COALESCE(paiements.montant_restant, 0)
                     END as montant_restant"),
 
            // Frais affichés (payé + restant)
            DB::raw('COALESCE(dp_calc.frais_inscription_paye, 0) + COALESCE(dp_calc.frais_inscription_restant, 0) as frais_inscription'),
            DB::raw('COALESCE(dp_calc.frais_scolarite_paye, 0)   + COALESCE(dp_calc.frais_scolarite_restant, 0)   as frais_participation'),
 
            // Détail des frais (conservé pour compatibilité)
            DB::raw('COALESCE(dp_calc.frais_inscription_paye, 0)    as frais_inscription_paye'),
            DB::raw('COALESCE(dp_calc.frais_inscription_restant, 0) as frais_inscription_restant'),
            DB::raw('COALESCE(dp_calc.frais_scolarite_paye, 0)      as frais_scolarite_paye'),
            DB::raw('COALESCE(dp_calc.frais_scolarite_restant, 0)   as frais_scolarite_restant'),
 
            // Réduction
            DB::raw('COALESCE(dp_calc.reduction_pourcentage, 0) as reduction_pourcentage'),
            DB::raw('COALESCE(dp_flag.have_reduction_dsc, 0)    as have_reduction_dsc'),
            DB::raw("CASE WHEN COALESCE(dp_flag.have_reduction_dsc, 0) = 1
                          THEN (CASE WHEN paiements.code_remise_dsc = 'BOURSE_COMPLET' THEN 100 ELSE 5 END)
                          ELSE COALESCE(dp_calc.reduction_pourcentage, 0)
                     END as reduction_affichee")
        )
        ->selectRaw('
            (EXISTS (
                SELECT 1 FROM rapports
                WHERE rapports.email = inscriptions.email
                  AND rapports.code_formation = ?
            )) as a_participe
        ', [$codeFormation])
        ->where('inscriptions.code_formation', $codeFormation);
}
 
 
/**
 * Base formation classique.
 * Les colonnes DSC sont présentes avec des valeurs neutres (0 / NULL).
 */
private function baseParticipantsClassique(string $codeFormation)
{
    return DB::table('inscriptions')
        ->leftJoin('auditeurs', 'inscriptions.email', '=', 'auditeurs.email')
        ->leftJoin('detailspaiements', function ($join) {
            $join->on('detailspaiements.code_formation', '=', 'inscriptions.code_formation')
                 ->on('detailspaiements.code_inscription', '=', 'inscriptions.code_inscription');
        })
        ->leftJoin('reductions', 'reductions.id', '=', 'detailspaiements.id_reduction')
        ->select(
            'inscriptions.id',
            'inscriptions.email',
            'auditeurs.nom',
            'auditeurs.prenoms',
            'auditeurs.telephone',
            'auditeurs.pays',
            'inscriptions.tarif_formation_id',
            'inscriptions.type_formation',
            'inscriptions.presentiel',
            'inscriptions.observations',
            'detailspaiements.montant_restant',
            'detailspaiements.montant_paye',
            'detailspaiements.statut_paiement',
            'reductions.pourcentage',
            DB::raw('COALESCE(detailspaiements.montant_paye, 0) + COALESCE(detailspaiements.montant_restant, 0) as montant_total'),
 
            // Colonnes DSC neutres : mêmes colonnes dans les deux bases
            DB::raw('NULL as code_remise_dsc'),
            DB::raw('0 as frais_inscription'),
            DB::raw('0 as frais_participation'),
            DB::raw('0 as have_reduction_dsc'),
            DB::raw('COALESCE(reductions.pourcentage, 0) as reduction_affichee')
        )
        ->selectRaw('
            (EXISTS (
                SELECT 1 FROM rapports
                WHERE rapports.email = inscriptions.email
                  AND rapports.code_formation = ?
            )) as a_participe
        ', [$codeFormation])
        ->where('inscriptions.code_formation', $codeFormation);
}

/**
 * Récapitulatif d'une formation : formation + statistiques globales.
 * (La liste des participants est servie par getParticipantsFormation.)
 */
public function getRecapitulatifPaiements(string $codeFormation): \Illuminate\Http\JsonResponse
{
    $formation = Formations::where('code_formation', $codeFormation)->first();
 
    if (!$formation) {
        return response()->json([
            'success' => false,
            'message' => "La formation {$codeFormation} n'existe pas.",
        ]);
    }
 
    $recap = $this->calculerStatsRecapitulatif($formation);
 
    // Même format de réponse qu'avant
    return response()->json([
        'success'     => true,
        'formation'   => $formation,
        'stats'       => $recap['stats'],
        'fm_gratuite' => $recap['fm_gratuite'],
    ]);
}
 
 
/**
 * Calcul des statistiques d'une formation.
 * Utilisé par l'écran (getRecapitulatifPaiements) ET par l'export.
 *
 * @return array{stats: array, fm_gratuite: bool}
 */
public function calculerStatsRecapitulatif(Formations $formation): array
{
    $codeFormation = $formation->code_formation;
 
    $totalInscGratuite = Detailspaiements::where('code_formation', $codeFormation)
        ->where('id_reduction', 3)
        ->count();
 
    // Participants (présents dans les rapports)
    $total_participant = Rapports::where('code_formation', $codeFormation)->count();
 
    $total_participant_presentiel = DB::table('rapports')
        ->join('inscriptions', 'inscriptions.email', '=', 'rapports.email')
        ->where('rapports.code_formation', $codeFormation)
        ->where('inscriptions.code_formation', $codeFormation)
        ->where('rapports.temps_presence', 0)
        ->count();
 
    // Attestations payées
    $totalPaiementAttestation = DB::table('tarificationsformations')
        ->join('detailspaiements', 'detailspaiements.id_tarification_formation', '=', 'tarificationsformations.id')
        ->join('paiements', 'paiements.reference', '=', 'detailspaiements.reference_paiement')
        ->join('modepaiements', 'modepaiements.id', '=', 'paiements.id_modepaiement')
        ->where('tarificationsformations.code_formation', $codeFormation)
        ->where('detailspaiements.statut_paiement', 'PAYE')
        ->count();
 
    // Inscriptions
    $total_inscrit            = Inscriptions::where('code_formation', $codeFormation)->count();
    $total_inscrit_visio      = Inscriptions::where('code_formation', $codeFormation)->where('presentiel', 0)->count();
    $total_inscrit_presentiel = Inscriptions::where('code_formation', $codeFormation)->where('presentiel', 1)->count();
 
    // Gratuits par mode
    $totalGratuitPresentiel = $this->compterGratuits($codeFormation, 'PRESENTIEL');
    $totalGratuitVisio      = $this->compterGratuits($codeFormation, 'VISIO');
 
    // Formation gratuite ?
    $fm_gratuite = collect($formation->getTarification($codeFormation))
        ->contains(fn ($tarification) => $tarification->get_grille_libelle === 'GRATUIT');
 
    return [
        'fm_gratuite' => $fm_gratuite,
        'stats'       => [
            'inscriptions_presentiel' => $total_inscrit_presentiel,
            'gratuits_presentiel'     => $totalGratuitPresentiel,
            'payants_presentiel'      => $total_inscrit_presentiel,     // À ajuster selon votre logique
            'attestations_presentiel' => $totalPaiementAttestation,     // À calculer selon votre logique
            'participants_presentiel' => $total_participant_presentiel,
 
            'inscriptions_visio'      => $total_inscrit_visio,
            'gratuits_visio'          => $totalGratuitVisio,
            'payants_visio'           => $total_inscrit_visio,          // À ajuster selon votre logique
            'attestations_visio'      => 0,                             // À calculer selon votre logique
            'participants_visio'      => $total_participant - $total_participant_presentiel,
 
            'total_inscriptions'      => $total_inscrit,
            'total_gratuits'          => 0,                             // À calculer selon votre logique
            'total_payants'           => $total_inscrit,                // À ajuster selon votre logique
            'total_attestations'      => 0,                             // À calculer selon votre logique
            'total_participants'      => $total_participant,
            'totalInscGratuite'       => $totalInscGratuite,
        ],
    ];
}
 
 
/**
 * Lignes du tableau récapitulatif (PRESENTIEL / VISIO / TOTAL), avec 
 * Format :  PDF et l'Excel.
 *
 * @return array<int, array{libelle: string, inscrits: int, gratuits: int, payants: int, attestations: int, participants: int}>
 */
public function lignesRecapitulatif(array $s, bool $fmGratuite): array
{
    return [
        [
            'libelle'      => 'PRESENTIEL',
            'inscrits'     => $s['inscriptions_presentiel'],
            'gratuits'     => $fmGratuite ? $s['participants_presentiel'] : $s['gratuits_presentiel'],
            'payants'      => $fmGratuite ? 0 : $s['inscriptions_presentiel'] - $s['gratuits_presentiel'],
            'attestations' => $s['attestations_presentiel'],
            'participants' => $s['participants_presentiel'],
        ],
        [
            'libelle'      => 'VISIO',
            'inscrits'     => $s['inscriptions_visio'],
            'gratuits'     => $fmGratuite ? $s['total_participants'] - $s['participants_presentiel'] : $s['gratuits_visio'],
            'payants'      => $fmGratuite ? 0 : $s['inscriptions_visio'] - $s['gratuits_visio'],
            'attestations' => $s['attestations_visio'],
            'participants' => $s['participants_visio'],
        ],
        [
            'libelle'      => 'TOTAL',
            'inscrits'     => $s['total_inscriptions'],
            'gratuits'     => $fmGratuite ? $s['total_participants'] : $s['totalInscGratuite'],
            'payants'      => $fmGratuite ? 0 : $s['total_inscriptions'] - $s['totalInscGratuite'],
            'attestations' => $s['total_attestations'],
            'participants' => $s['total_participants'],
        ],
    ];
}
 
 
/**
 * Nombre d'inscriptions gratuites (réduction id 3) pour un mode donné.
 */
private function compterGratuits(string $codeFormation, string $typeFormation): int
{
    return Detailspaiements::where('detailspaiements.code_formation', $codeFormation)
        ->where('detailspaiements.id_reduction', 3)
        ->join('inscriptions', 'inscriptions.email', '=', 'detailspaiements.email')
        ->where('inscriptions.type_formation', $typeFormation)
        ->where('inscriptions.code_formation', $codeFormation)
        ->count();
}


}
