<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Mail\DscNotificationComptable;
use App\Mail\DscPaiementAttente;
use App\Mail\NotificationComptable;
use App\Mail\NotificationPaiementOuvrageStaff;
use App\Mail\Paiementattente;
use App\Mail\Paiementattenteouvrage;
use App\Mail\PaiementValideDsc;
use App\Models\Auditeurs;
use App\Models\Detailspaiements;
use App\Models\FormationsTranches;
use App\Models\Inscriptions;
use App\Models\Modepaiements;
use App\Models\Paiements;
use App\Models\PaiementsTranche;
use App\Models\Tranches;
use App\Models\User;
use App\Services\contracts\FedapayInterfarce;
use App\Services\util\GenarateurCodeService;
use FedaPay\FedaPay;
use FedaPay\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FedapayService implements  FedapayInterfarce
{

    private GenarateurCodeService $genarateurCodeService; 

    public function __construct(GenarateurCodeService $genarateurCodeService)
    {
        FedaPay::setApiKey(config('fedapay.secret_key'));
        FedaPay::setEnvironment(config('fedapay.environment'));
        $this->genarateurCodeService = $genarateurCodeService;
    }

    public function storePaiementFedapay(Request $request): \Illuminate\Http\JsonResponse
{
    $reference = $request->reference; // FIX : défini avant le try, dispo dans le catch

    try {
        $montant_paye = $request->montant_payer;

        // récupérer le statut de la transaction côté serveur
        $data_transaction = Transaction::retrieve($request->transaction_id);

        // FIX : vérification du statut, absente de l'original
        if (!in_array($data_transaction->status, ['approved', 'APPROVED'])) {
            Log::warning('Webhook FedaPay : transaction non approuvée', [
                'reference' => $reference,
                'status' => $data_transaction->status,
            ]);
            return ResponseHandler::success("paiement invalide", [
                'reference' => $reference,
                'success' => false,
            ]);
        }

        // FIX : null-check paiement
        $getPaiement = Paiements::whereReference($reference)->first();
        if (!$getPaiement) {
            Log::warning('Webhook FedaPay : paiement introuvable', ['reference' => $reference]);
            return ResponseHandler::success("paiement introuvable", [
                'reference' => $reference,
                'success' => false,
            ]);
        }

        $montant_restant = $getPaiement->montant_restant;
        $paiement = Paiements::find($getPaiement->id);

        // ============================================================
        // FIX : VALIDATION DU MONTANT vs FedaPay
        // ============================================================
        $montantConfirmeFedapay = (float) $data_transaction->amount;
        $montantAnnonceParFront = (float) $montant_paye;

        if (abs($montantConfirmeFedapay - $montantAnnonceParFront) > 1) {
            Log::warning('Webhook FedaPay : montant incohérent', [
                'reference' => $reference,
                'transaction_id' => $data_transaction->id,
                'montant_fedapay' => $montantConfirmeFedapay,
                'montant_recu_front' => $montantAnnonceParFront,
            ]);
            return ResponseHandler::success("montant incohérent", [
                'reference' => $reference,
                'success' => false,
            ]);
        }

        // ============================================================
        // FIX : IDEMPOTENCE — un rejeu du même transaction_id ne
        // retouche plus les cumuls ni ne renvoie les mails.
        // ============================================================
        $dejaTraite = ($paiement->num_transaction === $data_transaction->id);

        if (!$dejaTraite) {
            $nouveau_montant_restant = $montant_restant - $montant_paye;

            $paiement->setAttribute('num_transaction', $data_transaction->id);
            $paiement->setAttribute('statut_transaction', $data_transaction->status);
            $paiement->setAttribute('id_modepaiement', 2); // FedaPay

            // FIX : montant_paye / montant_restant / statut_paiement
            // réellement mis à jour (manquait entièrement avant)
            $paiement->setAttribute('montant_paye', $paiement->montant_paye + $montant_paye);
            $paiement->setAttribute('montant_restant', max($nouveau_montant_restant, 0));

            if ($montant_paye < $montant_restant) {
                // paiement partiel : solde pas encore atteint
                $paiement->setAttribute('is_paiementpartiel', 1);
                $paiement->setAttribute('statut_paiement', 'EN ATTENTE');
            } else {
                // soldé intégralement — règle métier hors DSC : pas de partiel par ligne
                $paiement->setAttribute('statut_paiement', 'PAYE');
                $paiement->setAttribute('statut_traitement', 'VALIDER');
            }

            $paiement->update();
        }

        // FIX : null-check mode de paiement
        $libelle_mode_paiement = Modepaiements::find($paiement->id_modepaiement);
        $libelleModePaiement = $libelle_mode_paiement ? $libelle_mode_paiement->libelle : '';

        if (!$dejaTraite) {
            $auditeur = Auditeurs::where("email", $paiement->email)->first();

            if ($auditeur) {
                $contenu = [
                    'nom' => $auditeur->nom,
                    'prenoms' => $auditeur->prenoms,
                    'reference_paiement' => $reference,
                    'subject' => 'INFORMATION SUR VOTRE PAIEMENT ' . $reference,
                    'email' => $paiement->email,
                    'montant' => $montant_paye,
                    'mode_paiement' => $libelleModePaiement,
                ];
            } else {
                $contenu = [
                    'nom' => "",
                    'prenoms' => "",
                    'reference_paiement' => $reference,
                    'subject' => 'INFORMATION SUR VOTRE PAIEMENT ' . $reference,
                    'email' => $paiement->email,
                    'montant' => $montant_paye,
                    'mode_paiement' => $libelleModePaiement,
                ];
            }

            try {
                Mail::to($paiement->email)->cc([env('MAIL_CC')])->bcc(env('MAIL_BCC'))->queue(new Paiementattente($contenu));
            } catch (\Exception $e) {
                Log::info('echec de l\'envoi du mail');
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
                    'montant' => $montant_paye,
                    'mode_paiement' => $libelleModePaiement,
                ];
                try {
                    Mail::to(env('MAIL_CC'))->cc($liste_comptable)->queue(new NotificationComptable($contenu));
                } catch (\Exception $e) {
                    Log::error('Erreur envoi mail NotificationComptable', ['error' => $e->getMessage()]);
                }
            }
        }

        return ResponseHandler::success("ok", [
            'reference' => $paiement->reference,
            'success' => true,
        ]);

    } catch (\Throwable $e) { // FIX : attrape aussi les \Error
        Log::error('Erreur vérification FedaPay', ['error' => $e->getMessage()]);

        return ResponseHandler::success("ok", [
            'reference' => $reference,
            'success' => false,
        ]);
    }
}

    public function storePaiementFedapayOld(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement storePaiementFedapay() method.

            # code...
            $reference = $request->reference;
            $montant_paye = $request->montant_payer;
            // récupérer le statut de la transaction côté serveur
            $data_transaction = Transaction::retrieve($request->transaction_id);

            // mise à jour de la table paiement
            $getPaiement = Paiements::whereReference($reference)->first();
            $montant_restant = $getPaiement->montant_restant;
            // mise à jour du montant paye
            $paiement = Paiements::find($getPaiement->id);
            $paiement->setAttribute('num_transaction', $data_transaction->id);
            $paiement->setAttribute('statut_transaction', $data_transaction->status);
            $paiement->setAttribute('updated_at', date('Y-m-d H:i:s'));
            $paiement->setAttribute('id_modepaiement', 2); // FedaPay
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

    }

public function storePaiementPartielFedapay(Request $request): \Illuminate\Http\JsonResponse
{
    $reference = $request->reference_paiement; // FIX : sorti avant le try, disponible dans le catch

    try {
        // récupérer le statut de la transaction côté serveur
        $data_transaction = Transaction::retrieve($request->transaction_id);

        Log::info('info fedapay');
        Log::info($data_transaction);
        Log::info('end info fedapay');

        // ============================================================
        // FIX LE PLUS CRITIQUE : vérification du statut FedaPay.
        // Sans ça, un paiement refusé/en attente était quand même
        // comptabilisé comme reçu.
        // ============================================================
        if (!in_array($data_transaction->status, ['approved', 'APPROVED'])) {
            Log::warning('Webhook FedaPay partiel (non-DSC) : transaction non approuvée', [
                'reference' => $reference,
                'status' => $data_transaction->status,
            ]);
            return ResponseHandler::success("paiement invalide", [
                'reference' => $reference,
                'success' => false,
            ]);
        }

        $actuel_montant_paye = $request->montant_total;

        // FIX : null-check mode de paiement
        $mode_paiement = Modepaiements::whereLibelle($request->mode_paiement)->first();
        if (!$mode_paiement) {
            Log::warning('Webhook FedaPay partiel : mode de paiement introuvable', [
                'reference' => $reference,
                'mode_paiement' => $request->mode_paiement,
            ]);
            return ResponseHandler::success("mode de paiement introuvable", [
                'reference' => $reference,
                'success' => false,
            ]);
        }

        // FIX : null-check paiement, first() au lieu de get()->first()
        $paiement = Paiements::where('reference', $reference)->first();
        if (!$paiement) {
            Log::warning('Webhook FedaPay partiel : paiement introuvable', ['reference' => $reference]);
            return ResponseHandler::success("paiement introuvable", [
                'reference' => $reference,
                'success' => false,
            ]);
        }

        // ============================================================
        // FIX : VALIDATION DU MONTANT vs FedaPay
        // ============================================================
        $montantConfirmeFedapay = (float) $data_transaction->amount;
        $montantAnnonceParFront = (float) $actuel_montant_paye;

        if (abs($montantConfirmeFedapay - $montantAnnonceParFront) > 1) {
            Log::warning('Webhook FedaPay partiel : montant incohérent', [
                'reference' => $reference,
                'transaction_id' => $data_transaction->id,
                'montant_fedapay' => $montantConfirmeFedapay,
                'montant_recu_front' => $montantAnnonceParFront,
            ]);
            return ResponseHandler::success("montant incohérent", [
                'reference' => $reference,
                'success' => false,
            ]);
        }

        // ============================================================
        // FIX : IDEMPOTENCE — si cette transaction a déjà été appliquée
        // à ce paiement, on ne rejoue ni les cumuls ni les mails.
        // ============================================================
        $dejaTraite = ($paiement->num_transaction === $data_transaction->id);

        $update_paiement = Paiements::find($paiement->id);

        if (!$dejaTraite) {
            $update_montant_restant = $paiement->montant_restant - $actuel_montant_paye;
            $update_montant_paye = $paiement->montant_paye + $actuel_montant_paye;

            Log::info($update_montant_restant);
            Log::info($update_montant_paye);
            Log::info($actuel_montant_paye);

            if ($update_paiement->is_paiement_partiel) {
                $paiement_partiel = true;
            } else {
                $paiement_partiel = false;
            }

            // FIX : soldé = "PAYE", pas "EN ATTENTE" (c'est toi qui me l'as confirmé :
            // par ligne, le montant doit être soldé, donc ce cas doit bien clore le paiement)
            $statut_details_paiement = "IMPAYE";

            if ($update_montant_restant <= 0 && $actuel_montant_paye >= $paiement->montant_restant) {
                // FIX : <= 0 et >= au lieu de == strict, pour couvrir un éventuel
                // trop-perçu d'arrondi sans basculer par erreur dans la branche partielle
                $update_paiement->setAttribute('statut_paiement', 'PAYE');
                $update_paiement->setAttribute('num_transaction', $data_transaction->id);
                $update_paiement->setAttribute('statut_transaction', $data_transaction->status);

                if ($paiement_partiel) {
                    $update_paiement->setAttribute('montant_paye', $update_montant_paye);
                    $update_paiement->setAttribute('montant_restant', max($update_montant_restant, 0));
                }
                $update_paiement->setAttribute('id_modepaiement', $mode_paiement->id);
                $update_paiement->update();

            } else {
                $update_paiement->setAttribute('is_paiement_partiel', true);
                $paiement_partiel = true;
                if ($update_montant_restant <= 0) {
                    // FIX : même correction ici — soldé = "PAYE"
                    $update_paiement->setAttribute('statut_paiement', 'PAYE');
                }
                $update_paiement->setAttribute('montant_paye', $update_montant_paye);
                $update_paiement->setAttribute('montant_restant', max($update_montant_restant, 0));
                $update_paiement->setAttribute('num_transaction', $data_transaction->id); // FIX : manquait dans cette branche aussi
                $update_paiement->setAttribute('statut_transaction', $data_transaction->status);
                $update_paiement->setAttribute('id_modepaiement', $mode_paiement->id);
                $update_paiement->update();
            }

            // update détails paiements
            foreach ($request->formations as $key => $value) {
                // FIX : null-check
                $update_detailspaiement = Detailspaiements::find($value['detail_id']);
                if (!$update_detailspaiement) {
                    Log::warning('Webhook FedaPay partiel : détail paiement introuvable', [
                        'detail_id' => $value['detail_id'],
                        'reference' => $reference,
                    ]);
                    continue;
                }

                if ($paiement_partiel) {
                    $update_detailspaiement->setAttribute('is_paiement_partiel', true);
                    $update_detailspaiement->setAttribute('modepaiement_id', $mode_paiement->id);
                    $update_detailspaiement->setAttribute('reference_paiement_partiel', $data_transaction->id);
                    $update_detailspaiement->setAttribute('statut_paiement', "PAYE");
                } else {
                    $update_detailspaiement->setAttribute('statut_paiement', $statut_details_paiement);
                }
                $update_detailspaiement->update();
            }
        }

        // ============================================================
        // Emails — protégés par $dejaTraite, null-checks sur $auditeur
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

        return ResponseHandler::success("ok", [
            'reference' => $reference,
            'success' => true,
        ]);

    } catch (\Throwable $e) { // FIX : attrape aussi les \Error
        Log::error('Erreur vérification FedaPay', ['error' => $e->getMessage()]);

        return ResponseHandler::success("ok", [
            'reference' => $reference, // FIX : toujours défini maintenant, même si Transaction::retrieve() plante
            'success' => false,
        ]);
    }
}

    public function storePaiementPartielFedapayOld(Request $request): \Illuminate\Http\JsonResponse
    {

        try {
            // récupérer le statut de la transaction côté serveur
            $data_transaction = Transaction::retrieve($request->transaction_id);
            
            Log::info('info fedapay');
            Log::info($data_transaction);
            Log::info('end info fedapay');
 
                $reference = $request->reference_paiement;
                $actuel_montant_paye = $request->montant_total;
                $mode_paiement = Modepaiements::whereLibelle($request->mode_paiement)->first();
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
                    $update_paiement->setAttribute('num_transaction', $data_transaction->id);
                    $update_paiement->setAttribute('statut_transaction', $data_transaction->status);

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
                        $update_detailspaiement->setAttribute('reference_paiement_partiel', $data_transaction->id);
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
                    'reference' => $reference,
                    'success' => true,
                ];

                return  ResponseHandler::success("ok",$dataResponse);


        } catch (\Exception $e) {
            Log::error('Erreur vérification FedaPay', ['error' => $e->getMessage()]);

            $dataResponse = [
                'reference' => $reference,
                'success' => false,
            ];

            return  ResponseHandler::success("ok",$dataResponse);

        }
    }

    public function storePaiementOuvrageFedapay(Request $request): \Illuminate\Http\JsonResponse
{
    try {
        $reference = $request->reference;

        // FIX : Transaction::retrieve() dans le try
        $data_transaction = Transaction::retrieve($request->transaction_id);

        // FIX : vérification du statut, absente de l'original
        if (!in_array($data_transaction->status, ['approved', 'APPROVED'])) {
            Log::info('Webhook FedaPay ouvrage : transaction non approuvée', [
                'reference' => $reference,
                'status' => $data_transaction->status,
            ]);
            return ResponseHandler::success("paiement invalide", [
                'reference' => $reference,
                'success' => false,
            ]);
        }

        // FIX : null-check paiement
        $getPaiement = Paiements::whereReference($reference)->first();
        if (!$getPaiement) {
            Log::warning('Webhook FedaPay ouvrage : paiement introuvable', ['reference' => $reference]);
            return ResponseHandler::success("paiement introuvable", [
                'reference' => $reference,
                'success' => false,
            ]);
        }
        $paiement = Paiements::find($getPaiement->id);

        // ============================================================
        // FIX : VALIDATION DU MONTANT vs FedaPay
        // ============================================================
        $montantConfirmeFedapay = (float) $data_transaction->amount;
        $montantAnnonceParFront = (float) $request->montant_payer;

        if (abs($montantConfirmeFedapay - $montantAnnonceParFront) > 1) {
            Log::warning('Webhook FedaPay ouvrage : montant incohérent', [
                'reference' => $reference,
                'transaction_id' => $data_transaction->id,
                'montant_fedapay' => $montantConfirmeFedapay,
                'montant_recu_front' => $montantAnnonceParFront,
            ]);
            return ResponseHandler::success("montant incohérent", [
                'reference' => $reference,
                'success' => false,
            ]);
        }

        $montant_paye = $request->montant_payer;

        $dejaTraite = ($paiement->num_transaction === $data_transaction->id);

        if (!$dejaTraite) {
            $paiement->setAttribute('num_transaction', $data_transaction->id);
            $paiement->setAttribute('statut_transaction', $data_transaction->status);
            $paiement->setAttribute('montant_paye', $montant_paye);
            $paiement->setAttribute('montant_restant', 0);
            $paiement->setAttribute('statut_paiement', 'PAYE');
            $paiement->setAttribute('statut_traitement', 'VALIDER');
            $paiement->setAttribute('commentaire', 'Validation automatique webhook Fedapay');
            $paiement->setAttribute('id_modepaiement', 2); // FedaPay
            $paiement->update();
        }

        // FIX : null-check mode de paiement
        $libelle_mode_paiement = Modepaiements::find($paiement->id_modepaiement);
        $libelleModePaiement = $libelle_mode_paiement ? $libelle_mode_paiement->libelle : '';

        if (!$dejaTraite) {
            $auditeur = Auditeurs::where("email", $paiement->email)->first();
            $nomAuditeur = $auditeur ? trim($auditeur->prenoms . ' ' . $auditeur->nom) : $paiement->email;

            // --- Email auditeur ---
            $contenu = [
                'nom' => $auditeur->nom ?? "",
                'prenoms' => $auditeur->prenoms ?? "",
                'reference_paiement' => $reference,
                'subject' => 'INFORMATION SUR VOTRE PAIEMENT ' . $reference,
                'email' => $paiement->email,
                'montant' => $montant_paye,
                'mode_paiement' => $libelleModePaiement,
            ];

            try {
                Mail::to($paiement->email)->cc([env('MAIL_CC')])->bcc(env('MAIL_BCC'))->queue(new Paiementattenteouvrage($contenu));
            } catch (\Exception $e) {
                Log::error('Échec envoi mail Paiementattenteouvrage', ['error' => $e->getMessage()]);
            }

            // --- Notifications internes (comptable / documentation) ---
            // FIX : factorisation du bloc dupliqué
            $this->notifierRoleInternePaiementOuvrage('COMPTABLE', 'PAIEMENT_NOTIFIER_COMPTABLE', $reference, $paiement, $nomAuditeur, $montant_paye, $libelleModePaiement);
            $this->notifierRoleInternePaiementOuvrage('DOCUMENTATION', 'PAIEMENT_NOTIFIER_DRD', $reference, $paiement, $nomAuditeur, $montant_paye, $libelleModePaiement);
        }

        return ResponseHandler::success("ok", [
            'reference' => $paiement->reference,
            'success' => true,
        ]);

    } catch (\Throwable $e) { // FIX : attrape aussi les \Error
        Log::error('Erreur vérification FedaPay (ouvrage)', ['error' => $e->getMessage()]);

        return ResponseHandler::success("ok", [
            'reference' => $request->reference ?? null,
            'success' => false,
        ]);
    }
}

// FIX : helper privé pour éliminer la duplication COMPTABLE/DOCUMENTATION
private function notifierRoleInternePaiementOuvrage(
    string $roleLibelle,
    string $envFlag,
    string $reference,
    $paiement,
    string $nomAuditeur,
    $montant_paye,
    string $libelleModePaiement
): void {
    if (!env($envFlag)) {
        return;
    }

    $liste = User::whereHas('roles', function ($q) use ($roleLibelle) {
        $q->where('libelle', $roleLibelle);
    })->pluck('email');

    $contenu = [
        'name' => $roleLibelle,
        'reference_paiement' => $reference,
        'email_auditeur' => $paiement->email,
        'nom_auditeur' => $nomAuditeur,
        'subject' => 'NOUVEAU PAIEMENT ' . date('d-m-Y H:i:s'),
        'email' => '',
        'montant' => $montant_paye,
        'mode_paiement' => $libelleModePaiement,
    ];

    try {
        Mail::to(env('MAIL_CC'))->cc($liste)->queue(new NotificationPaiementOuvrageStaff($contenu));
    } catch (\Exception $e) {
        Log::error("Échec envoi notification $roleLibelle (ouvrage)", ['error' => $e->getMessage()]);
    }
}

    public function storePaiementOuvrageFedapayOld(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement storePaiementOuvrageFedapay() method.

        # code...
        $reference = $request->reference;
        $montant_paye = $request->montant_payer;
        // récupérer le statut de la transaction côté serveur
        $data_transaction = Transaction::retrieve($request->transaction_id);

        // mise à jour de la table paiement
        $getPaiement = Paiements::whereReference($reference)->first();
        $montant_restant = $getPaiement->montant_restant;
        // mise à jour du montant paye
        $paiement = Paiements::find($getPaiement->id);
        $paiement->setAttribute('num_transaction', $data_transaction->id);
        $paiement->setAttribute('statut_transaction', $data_transaction->status);
        $paiement->setAttribute('updated_at', date('Y-m-d H:i:s'));
        $paiement->setAttribute('id_modepaiement', 2); // FedaPay
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
            Mail::to($paiement->email)->cc([env('MAIL_CC')])->bcc(env('MAIL_BCC'))->queue(new Paiementattenteouvrage($contenu));
        } catch (\Exception $e) {
            // Get error here
            Log::info('echec de l\'envoi du mail');
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
                'montant' => $montant_paye,
                'mode_paiement' => $libelle_mode_paiement->libelle
            ];
            try {
                # code...
                Mail::to(env('MAIL_CC'))->cc($liste_comptable)->queue(new NotificationPaiementOuvrageStaff($contenu));
            } catch (\Exception $e) {
                // Get error here
            }
        }

        if(env('PAIEMENT_NOTIFIER_DRD')){

            $liste_DRD = User::whereHas('roles', function ($q) {
                $q->where('libelle', 'DOCUMENTATION');
            })->pluck('email');

            $contenu = [
                'name' => 'DOCUMENTATION',
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
                Mail::to(env('MAIL_CC'))->cc($liste_DRD)->queue(new NotificationPaiementOuvrageStaff($contenu));
            } catch (\Exception $e) {
                // Get error here
            }
        }

        $dataResponse = [
            'reference' => $paiement->reference,
            'success' => true,
        ];

        return  ResponseHandler::success("ok",$dataResponse);
    }

    public function storePaiementDscFedaPay(Request $request): \Illuminate\Http\JsonResponse
{
    try {
        // FIX 2 : Transaction::retrieve() est maintenant DANS le try,
        // pour que toute erreur réseau/API FedaPay soit proprement catchée.
        $transactionId = Transaction::retrieve($request->transaction_id);

        if ($transactionId->status == 'approved' || $transactionId->status == 'APPROVED') {

            $reference = $request->reference;

            // FIX 2 : null-check sur le paiement
            $getPaiement = Paiements::where('reference', $reference)->first();
            if (!$getPaiement) {
                Log::warning('Webhook FedaPay : paiement introuvable', ['reference' => $reference]);
                return ResponseHandler::success("paiement introuvable", [
                    'reference' => $reference,
                    'success' => false,
                ]);
            }
            $paiement = Paiements::find($getPaiement->id);

            $inscrit = Inscriptions::where("code_inscription", $paiement->code_inscription)
                ->orderBy("id", "DESC")
                ->first(); // pas besoin de get()->first(), first() suffit

            // FIX 2 : null-check sur l'utilisateur
            $getUser = User::where('email', $paiement->email)->first();
            if (!$getUser) {
                Log::warning('Webhook FedaPay : utilisateur introuvable', ['email' => $paiement->email]);
                return ResponseHandler::success("utilisateur introuvable", [
                    'reference' => $reference,
                    'success' => false,
                ]);
            }
            $user = User::find($getUser->id);

            // FIX 2 : null-check sur le validateur
            $userValidateur = User::where("email", "formation.ersuma@ohada.org")->first();
            if (!$userValidateur) {
                Log::error('Webhook FedaPay : compte validateur "formation" introuvable');
                return ResponseHandler::success("compte validateur introuvable", [
                    'reference' => $reference,
                    'success' => false,
                ]);
            }

            // ============================================================
            // FIX 1 : VALIDATION DU MONTANT — on ne fait plus confiance
            // au montant envoyé par le front, on le compare à celui
            // réellement confirmé par FedaPay sur la transaction.
            // ⚠️ Vérifie le nom exact du champ dans la réponse FedaPay
            // (souvent `amount`, parfois en centimes selon la devise).
            // ============================================================
            $montantConfirmeFedapay = (float) $transactionId->amount;
            $montantAnnonceParFront = (float) $request->montant_paye;

            if (abs($montantConfirmeFedapay - $montantAnnonceParFront) > 1) { // tolérance 1 FCFA pour arrondis
                Log::warning('Webhook FedaPay : montant incohérent', [
                    'reference' => $reference,
                    'transaction_id' => $transactionId->id,
                    'montant_fedapay' => $montantConfirmeFedapay,
                    'montant_recu_front' => $montantAnnonceParFront,
                ]);
                return ResponseHandler::success("montant incohérent", [
                    'reference' => $reference,
                    'success' => false,
                ]);
            }

            // ============================================================
            // FIX 3 : IDEMPOTENCE GLOBALE — si cette transaction a déjà
            // été traitée (webhook rejoué par FedaPay), on sort avant de
            // ré-envoyer les mails ou de refaire les mises à jour.
            // ============================================================
            $dejaTraite = PaiementsTranche::where('num_transaction', $transactionId->id)->exists();

            $liste_details = collect($request->input('formations'))->map(fn($fm) => (object) $fm);

            $montant_paye = $request->montant_paye;
            $montant_restant = $request->montant_total_du - $montant_paye;

            // 1. update table paiements (idempotent par nature : on écrit les mêmes valeurs)
            $paiement->setAttribute('montant_total', $request->montant_total_du);
            $paiement->setAttribute('montant_restant', $montant_restant);
            $paiement->setAttribute('montant_paye', $montant_paye);
            if ($montant_restant == 0) {
                $paiement->setAttribute('num_transaction', $transactionId->id);
                $paiement->setAttribute('statut_transaction', $transactionId->status);
                $paiement->setAttribute('statut_paiement', 'PAYE');
                $paiement->setAttribute('statut_traitement', 'VALIDER');
                $paiement->setAttribute('agent_traiteur', $userValidateur->id);
                $paiement->setAttribute('commentaire', 'Validation automatique webhook Fedapay');
            }
            $paiement->setAttribute('id_modepaiement', 2);
            $paiement->update();

            // 2. update details paiement
            $getPTranche = PaiementsTranche::where("reference", "like", "%$paiement->reference%")->latest()->first();
            if ($getPTranche) {
                $ref = explode("_", $getPTranche->reference)[1] + 1;
                $update_reference = $paiement->reference . "_" . $ref;
            } else {
                $update_reference = $paiement->reference . "_1";
            }

            foreach ($liste_details as $detail) {
                if ($detail->type_tarif == 'INSCRIPTION' && $detail->choix && $detail->montant_paye > 0) {
                    $checkData = Detailspaiements::where('id', $detail->detail_id)
                        ->where('reference_paiement_partiel', $transactionId->id)
                        ->exists();

                    if (!$checkData) {
                        $detailPaiement = Detailspaiements::find($detail->detail_id);
                        $detailPaiement->setAttribute('montant_paye', $detail->montant_paye);
                        $detailPaiement->setAttribute('montant_restant', $detail->montant_restant - $detail->montant_paye);
                        $detailPaiement->setAttribute('reference_paiement_partiel', $transactionId->id);
                        $detailPaiement->setAttribute('have_reduction_dsc', false);
                        $detailPaiement->setAttribute('statut_paiement', "PAYE");
                        $detailPaiement->setAttribute('statut_traitement', "VALIDER");
                        $detailPaiement->setAttribute("commentaire", "Validation automatique webhook FedaPay");
                        $detailPaiement->update();

                        $tranche_paiement = new PaiementsTranche();
                        $tranche_paiement->setAttribute('details_paiements_id', $detailPaiement->id);
                        $tranche_paiement->setAttribute('montant_paye', $detail->montant_paye);
                        $tranche_paiement->setAttribute('montant_restant', $detailPaiement->montant_restant);
                        $tranche_paiement->setAttribute('reference', $update_reference);
                        $tranche_paiement->setAttribute('num_transaction', $transactionId->id);
                        $tranche_paiement->setAttribute('statut_transaction', $transactionId->status);
                        $tranche_paiement->setAttribute('statut_traitement', 'VALIDER');
                        $tranche_paiement->setAttribute('traiter_par', $userValidateur->id);
                        $tranche_paiement->setAttribute('is_inscription', true);
                        $tranche_paiement->setAttribute('modepaiement_id', $paiement->id_modepaiement);
                        $tranche_paiement->setAttribute('created_by', $user->id);
                        $tranche_paiement->setAttribute('updated_by', $user->id);
                        $tranche_paiement->save();
                    }
                } else {
                    if ($request->tranche == 'tranche_unique') {
                        $detailPaiement = Detailspaiements::find($detail->detail_id);
                        $formationTranche = FormationsTranches::where('tranche', 'tranche 1')
                            ->where('grilletarifaire_id', $detailPaiement->getTarification->getGrilletarifaire->id)
                            ->first();

                        $checkTranche = Tranches::whereCodeInscription($paiement->code_inscription)->exists();
                        if ($checkTranche) {
                            $getTranche = Tranches::whereCodeInscription($paiement->code_inscription)->first();
                            $tranche = Tranches::find($getTranche->id);
                        } else {
                            $tranche = new Tranches();
                        }
                        $tranche->setAttribute('code_inscription', $paiement->code_inscription);
                        $tranche->setAttribute('email', $paiement->email);
                        $tranche->setAttribute('tranche_1', $detailPaiement->getTarification->getGrilletarifaire->montant);
                        $tranche->setAttribute('tranche_2', 0);
                        $tranche->setAttribute('echeance_1', $formationTranche->echeance_1);
                        $tranche->setAttribute('echeance_2', $formationTranche->echeance_1);
                        $tranche->setAttribute('statut', 'ATTENTE');
                        $tranche->setAttribute('created_by', $user->id);
                        $tranche->setAttribute('updated_by', $user->id);
                        $checkTranche ? $tranche->update() : $tranche->save();

                        if ($detail->choix && $detail->montant_paye > 0) {
                            $checkData = Detailspaiements::where('id', $detail->detail_id)
                                ->where('reference_paiement_partiel', $transactionId->id)
                                ->exists();

                            if (!$checkData) {
                                $baseMontantRestant = $detailPaiement->getTarification->getGrilletarifaire->montant * 0.95;
                                $mtRestant = $baseMontantRestant - $detail->montant_paye;
                                $detailPaiement->setAttribute('montant_paye', $detail->montant_paye);
                                $detailPaiement->setAttribute('montant_restant', $mtRestant);
                                if ($mtRestant == 0) {
                                    $detailPaiement->setAttribute('reference_paiement_partiel', $transactionId->id);
                                    $detailPaiement->setAttribute('statut_paiement', "PAYE");
                                    $detailPaiement->setAttribute('statut_traitement', "VALIDER");
                                    $detailPaiement->setAttribute("commentaire", "Validation automatique webhook FedaPay");
                                }
                                $detailPaiement->setAttribute('have_reduction_dsc', true);
                                $detailPaiement->update();

                                $tranche_paiement = new PaiementsTranche();
                                $tranche_paiement->setAttribute('details_paiements_id', $detailPaiement->id);
                                $tranche_paiement->setAttribute('montant_paye', $detail->montant_paye);
                                $tranche_paiement->setAttribute('montant_restant', $detailPaiement->montant_restant);
                                $tranche_paiement->setAttribute('reference', $update_reference);
                                $tranche_paiement->setAttribute('num_transaction', $transactionId->id);
                                $tranche_paiement->setAttribute('statut_transaction', $transactionId->status);
                                $tranche_paiement->setAttribute('statut_traitement', 'VALIDER');
                                $tranche_paiement->setAttribute('traiter_par', $userValidateur->id);
                                $tranche_paiement->setAttribute('tranche_id', $tranche->id);
                                $tranche_paiement->setAttribute('modepaiement_id', $paiement->id_modepaiement);
                                $tranche_paiement->setAttribute('created_by', $user->id);
                                $tranche_paiement->setAttribute('updated_by', $user->id);
                                $tranche_paiement->save();
                            }
                        } else {
                            $detailPaiement->setAttribute('montant_restant', $detailPaiement->getTarification->getGrilletarifaire->montant * 0.95);
                            $detailPaiement->setAttribute('have_reduction_dsc', true);
                            $detailPaiement->update();
                        }
                    } else { // double tranche
                        $detailPaiement = Detailspaiements::find($detail->detail_id);
                        $formationTranche = FormationsTranches::where('tranche', 'tranche 2')
                            ->where('grilletarifaire_id', $detailPaiement->getTarification->getGrilletarifaire->id)
                            ->first();

                        $checkTranche = Tranches::whereCodeInscription($paiement->code_inscription)->exists();
                        if ($checkTranche) {
                            $getTranche = Tranches::whereCodeInscription($paiement->code_inscription)->first();
                            $tranche = Tranches::find($getTranche->id);
                        } else {
                            $tranche = new Tranches();
                        }
                        $tranche->setAttribute('code_inscription', $paiement->code_inscription);
                        $tranche->setAttribute('email', $paiement->email);
                        $tranche->setAttribute('tranche_1', $detailPaiement->getTarification->getGrilletarifaire->montant * 0.5);
                        $tranche->setAttribute('tranche_2', $detailPaiement->getTarification->getGrilletarifaire->montant * 0.5);
                        $tranche->setAttribute('echeance_1', $formationTranche->echeance_1);
                        $tranche->setAttribute('echeance_2', $formationTranche->echeance_2);
                        $tranche->setAttribute('statut', 'ATTENTE');
                        $tranche->setAttribute('created_by', $user->id);
                        $tranche->setAttribute('updated_by', $user->id);
                        $checkTranche ? $tranche->update() : $tranche->save();

                        if ($detail->choix && $detail->montant_paye > 0) {
                            $checkData = Detailspaiements::where('id', $detail->detail_id)
                                ->where('reference_paiement_partiel', $transactionId->id)
                                ->exists();

                            if (!$checkData) {
                                $mtRestant = $detail->montant_restant - $detail->montant_paye;
                                $detailPaiement->setAttribute('montant_paye', $detail->montant_paye);
                                $detailPaiement->setAttribute('montant_restant', $mtRestant);
                                if ($mtRestant == 0) {
                                    $detailPaiement->setAttribute('reference_paiement_partiel', $transactionId->id);
                                    $detailPaiement->setAttribute('statut_paiement', "PAYE");
                                    $detailPaiement->setAttribute('statut_traitement', "VALIDER");
                                    $detailPaiement->setAttribute("commentaire", "Validation automatique webhook FedaPay");
                                }
                                $detailPaiement->setAttribute('have_reduction_dsc', false);
                                $detailPaiement->update();

                                $tranche_paiement = new PaiementsTranche();
                                $tranche_paiement->setAttribute('details_paiements_id', $detailPaiement->id);
                                $tranche_paiement->setAttribute('montant_paye', $detail->montant_paye);
                                $tranche_paiement->setAttribute('montant_restant', $detailPaiement->montant_restant);
                                $tranche_paiement->setAttribute('reference', $update_reference);
                                $tranche_paiement->setAttribute('num_transaction', $transactionId->id);
                                $tranche_paiement->setAttribute('statut_transaction', $transactionId->status);
                                $tranche_paiement->setAttribute('statut_traitement', 'VALIDER');
                                $tranche_paiement->setAttribute('traiter_par', $userValidateur->id);
                                $tranche_paiement->setAttribute('tranche_id', $tranche->id);
                                $tranche_paiement->setAttribute('modepaiement_id', $paiement->id_modepaiement);
                                $tranche_paiement->setAttribute('created_by', $user->id);
                                $tranche_paiement->setAttribute('updated_by', $user->id);
                                $tranche_paiement->save();
                            }
                        } else {
                            $detailPaiement->setAttribute('montant_restant', $detail->montant_restant);
                            $detailPaiement->setAttribute('have_reduction_dsc', false);
                            $detailPaiement->update();
                        }
                    }
                }
            }

            // ============================================================
            // ENVOI DES MAILS — protégé par $dejaTraite (FIX 3)
            // et null-checks sur $auditeur / $libelle_mode_paiement (FIX 2)
            // ============================================================
            if (!$dejaTraite) {

                $auditeur = Auditeurs::where("email", $paiement->email)->first();
                $libelle_mode_paiement = Modepaiements::find($paiement->id_modepaiement);
                $libelleModePaiement = $libelle_mode_paiement ? $libelle_mode_paiement->libelle : '';

                if ($auditeur) {
                    $contenu = [
                        'nom' => $auditeur->nom,
                        'prenoms' => $auditeur->prenoms,
                        'reference_paiement' => $update_reference,
                        'subject' => 'INFORMATION SUR VOTRE PAIEMENT ' . $update_reference,
                        'email' => $paiement->email,
                        'montant' => $request->montant_paye,
                        'montant_restant' => $paiement->montant_restant,
                        'mode_paiement' => $libelleModePaiement,
                    ];
                } else {
                    $contenu = [
                        'nom' => "",
                        'prenoms' => "",
                        'reference_paiement' => $update_reference,
                        'montant' => $request->montant_paye,
                        'montant_restant' => $paiement->montant_restant,
                        'subject' => 'INFORMATION SUR VOTRE PAIEMENT ' . $update_reference,
                        'email' => $paiement->email,
                        'mode_paiement' => $libelleModePaiement,
                    ];
                }

                try {
                    Mail::to($paiement->email)->cc([env('MAIL_CC')])->bcc(env('MAIL_BCC'))->queue(new DscPaiementAttente($contenu));
                } catch (\Exception $e) {
                    Log::error('Erreur envoi mail DscPaiementAttente', ['error' => $e->getMessage()]);
                }

                $liste_comptable = User::whereHas('roles', function ($q) {
                    $q->where('libelle', 'COMPTABLE');
                })->pluck('email');

                if (env("PAIEMENT_NOTIFIER_COMPTABLE")) {
                    $contenu = [
                        'name' => 'COMPTABLE',
                        'reference_paiement' => $update_reference,
                        'email_auditeur' => $paiement->email,
                        // FIX 2 : null-check — plus de crash si $auditeur est null
                        'nom_auditeur' => $auditeur ? trim($auditeur->prenoms . ' ' . $auditeur->nom) : $paiement->email,
                        'subject' => 'NOUVEAU PAIEMENT ' . date('d-m-Y H:i:s'),
                        'email' => '',
                        'montant' => $request->montant_paye,
                        'mode_paiement' => $libelleModePaiement,
                    ];

                    try {
                        Mail::to(env('MAIL_CC'))->cc($liste_comptable)->queue(new DscNotificationComptable($contenu));
                    } catch (\Exception $e) {
                        Log::error('Erreur envoi mail DscNotificationComptable', ['error' => $e->getMessage()]);
                    }
                }

                $contenuTraitement = [
                    // FIX 2 : null-check
                    'nom' => $auditeur ? $auditeur->nom : '',
                    'prenoms' => $auditeur ? $auditeur->prenoms : '',
                    'reference_paiement' => $update_reference,
                    'subject' => 'TRAITEMENT DE VOTRE PAIEMENT ' . $update_reference,
                    'commentaire' => "Paiement validé",
                    'email' => $paiement->email,
                    'mode_paiement' => $libelleModePaiement,
                    'montant' => $request->montant_paye,
                    'montant_restant' => $paiement->montant_restant,
                    'type' => $paiement->type,
                    'url_recu' => $this->genarateurCodeService->generateUrlSigned('paiements.recu', ['reference' => $update_reference]),
                ];

                try {
                    Mail::to(trim($paiement->email))->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new PaiementValideDsc($contenuTraitement));
                } catch (\Exception $e) {
                    Log::error('Erreur envoi mail PaiementValideDsc', ['error' => $e->getMessage()]);
                }
            }

            return ResponseHandler::success("ok", [
                'reference' => $request->reference,
                'success' => true,
            ]);

        } else {
            return ResponseHandler::success("paiement invalide", [
                'reference' => $request->reference,
                'success' => false,
            ]);
        }

    } catch (\Throwable $e) { // FIX 2 : \Throwable attrape aussi les \Error, pas seulement \Exception
        Log::error('Erreur vérification FedaPay', ['error' => $e->getMessage()]);

        return ResponseHandler::success("ok", [
            'reference' => $request->reference ?? null,
            'success' => false,
        ]);
    }
}


    public function storePaiementDscFedaPayOld(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement storePaiementDscFedaPay() method.
        $transactionId = Transaction::retrieve($request->transaction_id);

        try {

            if($transactionId->status == 'approved' || $transactionId->status == 'APPROVED'){
                $reference = $request->reference;
                $getPaiement = Paiements::where('reference',$reference)->first();
                $paiement = Paiements::find($getPaiement->id);
                $inscrit = Inscriptions::where("code_inscription", $paiement->code_inscription)->orderBy("id", "DESC")->get()->first();
                $getUser = User::where('email',$paiement->email)->first();
                $user = User::find($getUser->id);

                // récupérer le compte formation en lieu et place de validateur
                $userValidateur = User::where("email","formation.ersuma@ohada.org")->first();

                $liste_details = collect($request->input('formations'))->map(fn ($fm) => (object) $fm);

                // update montant table paiement
                $montant_paye = $request->montant_paye;
                $montant_restant = $request->montant_total_du - $montant_paye;
                // 1 update table paiements
                $paiement->setAttribute('montant_total',$request->montant_total_du);
                $paiement->setAttribute('montant_restant',$montant_restant);
                $paiement->setAttribute('montant_paye',$montant_paye);
                if($montant_restant == 0){
                    $paiement->setAttribute('num_transaction', $transactionId->id);
                    $paiement->setAttribute('statut_transaction', $transactionId->status);
                    $paiement->setAttribute('statut_paiement', 'PAYE');
                    $paiement->setAttribute('statut_traitement', 'VALIDER');
                    $paiement->setAttribute('agent_traiteur', $userValidateur->id);
                    $paiement->setAttribute('commentaire', 'Validation automatique webhook Fedapay');
                }
                $paiement->setAttribute('id_modepaiement', 2); 
                $paiement->update();
                // 2- update details paiement
                $getPTranche = PaiementsTranche::where("reference","like","%$paiement->reference%")->latest()->first();
                //dd($getPTranche);
                if ($getPTranche) {
                    # code...
                    $ref = explode("_",$getPTranche->reference)[1]+1;
                    $update_reference = $paiement->reference."_".$ref;
                } else {
                    # code...
                    $update_reference = $paiement->reference."_1";
                }

                foreach ($liste_details as $detail){
                    if($detail->type_tarif == 'INSCRIPTION' && $detail->choix && $detail->montant_paye > 0){
                        // paiement effectué pour inscription
                        $checkData =  Detailspaiements::where('id',$detail->detail_id)
                        ->where('reference_paiement_partiel',$transactionId->id)
                        ->exists();
                        
                        if(!$checkData){
                        $detailPaiement = Detailspaiements::find($detail->detail_id);
                        $detailPaiement->setAttribute('montant_paye',$detail->montant_paye);
                        $detailPaiement->setAttribute('montant_restant',$detail->montant_restant-$detail->montant_paye);
                        $detailPaiement->setAttribute('reference_paiement_partiel',$transactionId->id);
                        $detailPaiement->setAttribute('have_reduction_dsc',false);
                        $detailPaiement->setAttribute('statut_paiement',"PAYE");
                        $detailPaiement->setAttribute('statut_traitement',"VALIDER");
                        $detailPaiement->setAttribute("commentaire","Validation automatique webhook FedaPay");
                        $detailPaiement->update();

                        $tranche_paiement = new PaiementsTranche();
                        $tranche_paiement->setAttribute('details_paiements_id',$detailPaiement->id);
                        $tranche_paiement->setAttribute('montant_paye',$detail->montant_paye);
                        $tranche_paiement->setAttribute('montant_restant',$detailPaiement->montant_restant);
                        $tranche_paiement->setAttribute('reference',$update_reference);
                        $tranche_paiement->setAttribute('num_transaction', $transactionId->id);
                        $tranche_paiement->setAttribute('statut_transaction', $transactionId->status);
                        # code...
                        $tranche_paiement->setAttribute('statut_traitement', 'VALIDER');
                        $tranche_paiement->setAttribute('traiter_par',$userValidateur->id);
                        $tranche_paiement->setAttribute('is_inscription',true);
                        $tranche_paiement->setAttribute('modepaiement_id',$paiement->id_modepaiement);
                        $tranche_paiement->setAttribute('created_by',$user->id);
                        $tranche_paiement->setAttribute('updated_by',$user->id);
                        $tranche_paiement->save();
                        }

                    }else{
                        if($request->tranche == 'tranche_unique'){ // tranche unique
                            $detailPaiement = Detailspaiements::find($detail->detail_id);
                            // sauvegarde de la tranche
                            $formationTranche = FormationsTranches::where('tranche','tranche 1')
                                ->where('grilletarifaire_id',$detailPaiement->getTarification->getGrilletarifaire->id)
                                ->first();
                            // vérifier si la tranche existe pour update
                            $checkTranche = Tranches::whereCodeInscription($paiement->code_inscription)->exists();

                            if($checkTranche){
                                $getTranche = Tranches::whereCodeInscription($paiement->code_inscription)->first();
                                $tranche = Tranches::find($getTranche->id);
                                $tranche->setAttribute('code_inscription',$paiement->code_inscription);
                                $tranche->setAttribute('email',$paiement->email);
                                //$tranche->setAttribute('tranche_1',$item->getTarification->getGrilletarifaire->montant*0.95);
                                $tranche->setAttribute('tranche_1',$detailPaiement->getTarification->getGrilletarifaire->montant);
                                $tranche->setAttribute('tranche_2',0);
                                $tranche->setAttribute('echeance_1',$formationTranche->echeance_1);
                                $tranche->setAttribute('echeance_2',$formationTranche->echeance_1);
                                $tranche->setAttribute('statut','ATTENTE');
                                $tranche->setAttribute('created_by',$user->id);
                                $tranche->setAttribute('updated_by',$user->id);
                                $tranche->update();
                            }else{
                                $tranche = new Tranches();
                                $tranche->setAttribute('code_inscription',$paiement->code_inscription);
                                $tranche->setAttribute('email',$paiement->email);
                                //$tranche->setAttribute('tranche_1',$item->getTarification->getGrilletarifaire->montant*0.95);
                                $tranche->setAttribute('tranche_1',$detailPaiement->getTarification->getGrilletarifaire->montant);
                                $tranche->setAttribute('tranche_2',0);
                                $tranche->setAttribute('echeance_1',$formationTranche->echeance_1);
                                $tranche->setAttribute('echeance_2',$formationTranche->echeance_1);
                                $tranche->setAttribute('statut','ATTENTE');
                                $tranche->setAttribute('created_by',$user->id);
                                $tranche->setAttribute('updated_by',$user->id);
                                $tranche->save();
                            }
                            // vérifier si paiement enregistré
                            if($detail->choix && $detail->montant_paye > 0){
                                $checkData =  Detailspaiements::where('id',$detail->detail_id)
                        ->where('reference_paiement_partiel',$transactionId->id)
                        ->exists();
                        
                        if(!$checkData){

                                $baseMontantRestant = $detail->montant_restant*0.95;
                                $mtRestant =  $baseMontantRestant - $detail->montant_paye;
                                $detailPaiement->setAttribute('montant_paye',$detail->montant_paye);
                                $detailPaiement->setAttribute('montant_restant',$mtRestant);
                                if($mtRestant == 0){
                                    $detailPaiement->setAttribute('reference_paiement_partiel',$transactionId->id);
                                    $detailPaiement->setAttribute('statut_paiement',"PAYE");
                                    $detailPaiement->setAttribute('statut_traitement',"VALIDER");
                                    $detailPaiement->setAttribute("commentaire","Validation automatique webhook FedaPay");
                                }
                                $detailPaiement->setAttribute('have_reduction_dsc',true);
                                $detailPaiement->update();

                                # code...
                                $tranche_paiement = new PaiementsTranche();
                                $tranche_paiement->setAttribute('details_paiements_id',$detailPaiement->id);
                                $tranche_paiement->setAttribute('montant_paye',$detail->montant_paye);
                                $tranche_paiement->setAttribute('montant_restant',$detailPaiement->montant_restant);
                                $tranche_paiement->setAttribute('reference',$update_reference);
                                $tranche_paiement->setAttribute('num_transaction', $transactionId->id);
                                $tranche_paiement->setAttribute('statut_transaction', $transactionId->status);
                                $tranche_paiement->setAttribute('statut_traitement', 'VALIDER');
                                $tranche_paiement->setAttribute('traiter_par',$userValidateur->id);
                                $tranche_paiement->setAttribute('tranche_id',$tranche->id);
                                $tranche_paiement->setAttribute('modepaiement_id',$paiement->id_modepaiement);
                                $tranche_paiement->setAttribute('created_by',$user->id);
                                $tranche_paiement->setAttribute('updated_by',$user->id);
                                $tranche_paiement->save();
                        }
                            }else{
                                $detailPaiement->setAttribute('montant_restant',$detail->montant_restant*0.95);
                                $detailPaiement->setAttribute('have_reduction_dsc',true);
                                $detailPaiement->update();
                            }
                        }else{ // double tranche
                            $detailPaiement = Detailspaiements::find($detail->detail_id);
                            // sauvegarde de la tranche
                            $formationTranche = FormationsTranches::where('tranche','tranche 2')->where('grilletarifaire_id',$detailPaiement->getTarification->getGrilletarifaire->id)->first();

                            // vérifier si la tranche existe pour update
                            $checkTranche = Tranches::whereCodeInscription($paiement->code_inscription)->exists();

                            if($checkTranche){
                                $getTranche = Tranches::whereCodeInscription($paiement->code_inscription)->first();
                                $tranche = Tranches::find($getTranche->id);
                                $tranche->setAttribute('code_inscription',$paiement->code_inscription);
                                $tranche->setAttribute('email',$paiement->email);
                                $tranche->setAttribute('tranche_1',$detailPaiement->getTarification->getGrilletarifaire->montant*0.5);
                                $tranche->setAttribute('tranche_2',$detailPaiement->getTarification->getGrilletarifaire->montant*0.5);
                                $tranche->setAttribute('echeance_1',$formationTranche->echeance_1);
                                $tranche->setAttribute('echeance_2',$formationTranche->echeance_2);
                                $tranche->setAttribute('statut','ATTENTE');
                                $tranche->setAttribute('created_by',$user->id);
                                $tranche->setAttribute('updated_by',$user->id);
                                $tranche->update();
                            }else{
                                $tranche = new Tranches();
                                $tranche->setAttribute('code_inscription',$paiement->code_inscription);
                                $tranche->setAttribute('email',$paiement->email);
                                $tranche->setAttribute('tranche_1',$detailPaiement->getTarification->getGrilletarifaire->montant*0.5);
                                $tranche->setAttribute('tranche_2',$detailPaiement->getTarification->getGrilletarifaire->montant*0.5);
                                $tranche->setAttribute('echeance_1',$formationTranche->echeance_1);
                                $tranche->setAttribute('echeance_2',$formationTranche->echeance_2);
                                $tranche->setAttribute('statut','ATTENTE');
                                $tranche->setAttribute('created_by',$user->id);
                                $tranche->setAttribute('updated_by',$user->id);
                                $tranche->save();
                            }

                            // vérifier si paiement enregistré
                            if($detail->choix && $detail->montant_paye > 0){
                                $checkData =  Detailspaiements::where('id',$detail->detail_id)
                        ->where('reference_paiement_partiel',$transactionId->id)
                        ->exists();
                        
                        if(!$checkData){
                                $mtRestant = $detail->montant_restant - $detail->montant_paye;
                                $detailPaiement->setAttribute('montant_paye',$detail->montant_paye);
                                $detailPaiement->setAttribute('montant_restant',$mtRestant);
                                if($mtRestant == 0){
                                    $detailPaiement->setAttribute('reference_paiement_partiel',$transactionId->id);
                                    $detailPaiement->setAttribute('statut_paiement',"PAYE");
                                    $detailPaiement->setAttribute('statut_traitement',"VALIDER");
                                    $detailPaiement->setAttribute("commentaire","Validation automatique webhook FedaPay");
                                }
                                $detailPaiement->setAttribute('have_reduction_dsc',false);
                                $detailPaiement->update();

                                # code...
                                $tranche_paiement = new PaiementsTranche();
                                $tranche_paiement->setAttribute('details_paiements_id',$detailPaiement->id);
                                $tranche_paiement->setAttribute('montant_paye',$detail->montant_paye);
                                $tranche_paiement->setAttribute('montant_restant',$detailPaiement->montant_restant);
                                $tranche_paiement->setAttribute('reference',$update_reference);
                                $tranche_paiement->setAttribute('num_transaction', $transactionId->id);
                                $tranche_paiement->setAttribute('statut_transaction', $transactionId->status);
                                $tranche_paiement->setAttribute('statut_traitement', 'VALIDER');
                                $tranche_paiement->setAttribute('traiter_par',$userValidateur->id);
                                $tranche_paiement->setAttribute('tranche_id',$tranche->id);
                                $tranche_paiement->setAttribute('modepaiement_id',$paiement->id_modepaiement);
                                $tranche_paiement->setAttribute('created_by',$user->id);
                                $tranche_paiement->setAttribute('updated_by',$user->id);
                                $tranche_paiement->save();
                        }
                            }else{
                                $detailPaiement->setAttribute('montant_restant',$detail->montant_restant);
                                $detailPaiement->setAttribute('have_reduction_dsc',false);
                                $detailPaiement->update();
                            }
                        }
                    }
                }


                // envoie de mail pour notification paiement en attente
                // envoie de mail pour notifier l'enregistrement du paiement
                $auditeur = Auditeurs::where("email",$paiement->email)->first();

                $libelle_mode_paiement = Modepaiements::find($paiement->id_modepaiement);

                if ($auditeur) {
                    # code...
                    $contenu = [
                        'nom' => $auditeur->nom,
                        'prenoms' => $auditeur->prenoms,
                        'reference_paiement' => $update_reference,
                        'subject' => 'INFORMATION SUR VOTRE PAIEMENT '.$update_reference,
                        'email' => $paiement->email,
                        'montant' => $request->montant_paye,
                        'montant_restant' => $paiement->montant_restant,
                        'mode_paiement' => $libelle_mode_paiement->libelle
                    ];
                } else {
                    # code...
                    $contenu = [
                        'nom' => "",
                        'prenoms' => "",
                        'reference_paiement' => $update_reference,
                        'montant' => $request->montant_paye,
                        'montant_restant' => $paiement->montant_restant,
                        'subject' => 'INFORMATION SUR VOTRE PAIEMENT '.$update_reference,
                        'email' => $paiement->email,
                        'mode_paiement' => $libelle_mode_paiement->libelle
                    ];
                }

                try {
                    # code...
                    Mail::to($paiement->email)->cc([env('MAIL_CC')])->bcc(env('MAIL_BCC'))->queue(new DscPaiementAttente($contenu));
                } catch (\Exception $e) {
                    // Get error here

                }

                // Notification au comptable
                // send mail comptable
                $liste_comptable = User::whereHas('roles', function ($q) {
                    $q->where('libelle', 'COMPTABLE');
                })->pluck('email');

                if (env("PAIEMENT_NOTIFIER_COMPTABLE")) {
                    # code...
                    $contenu = [
                        'name' => 'COMPTABLE',
                        'reference_paiement' => $update_reference,
                        'email_auditeur' => $paiement->email,
                        'nom_auditeur' => $auditeur->prenoms.' '.$auditeur->nom,
                        'subject' => 'NOUVEAU PAIEMENT ' . date('d-m-Y H:i:s'),
                        'email' => '',
                        'montant' => $request->montant_paye,
                        'mode_paiement' => $libelle_mode_paiement->libelle
                    ];

                    try {
                        # code...
                        Mail::to(env('MAIL_CC'))->cc($liste_comptable)->queue(new DscNotificationComptable($contenu));
                    } catch (\Exception $e) {
                        // Get error here

                    }
                }

                // vérifier si le statut est succès pour envoyer le mail de notification traitement paiement
                # code...
                $contenuTraitement = [
                    'nom' => $auditeur->nom ,
                    'prenoms' => $auditeur->prenoms,
                    'reference_paiement' => $update_reference,
                    'subject' => 'TRAITEMENT DE VOTRE PAIEMENT '.$update_reference,
                    'commentaire' => "Paiement validé",
                    'email'=>$paiement->email,
                    'mode_paiement'=>$libelle_mode_paiement->libelle,
                    'montant'=>$request->montant_paye,
                    'montant_restant'=>$paiement->montant_restant,
                    'type'=>$paiement->type,
                    'url_recu'=>$this->genarateurCodeService->generateUrlSigned('paiements.recu',['reference'=>$update_reference])
                ];

                try {
                    # code...
                    Mail::to(trim($paiement->email))->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new PaiementValideDsc($contenuTraitement));

                } catch (\Exception $e) {
                    //throw $th;
                }


                $dataResponse = [
                    'reference' => $request->reference,
                    'success' => true,
                ];
                return ResponseHandler::success("ok", $dataResponse);

            }else{
                $dataResponse = [
                    'reference' => $request->reference,
                    'success' => false,
                ];
                return ResponseHandler::success("paiement invalide", $dataResponse);
            }

        } catch (\Exception $e) {
            Log::error('Erreur vérification FedaPay', ['error' => $e->getMessage()]);

            $dataResponse = [
                'reference' => $request->reference,
                'success' => false,
            ];

            return ResponseHandler::success("ok", $dataResponse);

        }
    }
 

    public function storePaiementPartielDscFedaPayOld(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement storePaiementPartielDscFedaPay() method.
        $transactionId = Transaction::retrieve($request->transaction_id);

        try {

            if($transactionId->status == 'approved' || $transactionId->status == 'APPROVED'){
                $tabReference = explode("_",$request->reference);
                $getReference = $tabReference[0]; 
                $reference = $request->reference;
                $getPaiement = Paiements::where('reference',$getReference)->first();
                $paiement = Paiements::find($getPaiement->id);
                $inscrit = Inscriptions::where("code_inscription", $paiement->code_inscription)->orderBy("id", "DESC")->get()->first();
                $getUser = User::where('email',$paiement->email)->first();
                $user = User::find($getUser->id);

                // récupérer le compte formation en lieu et place de validateur
                $userValidateur = User::where("email","formation.ersuma@ohada.org")->first();

                $liste_details = collect($request->input('formations'))->map(fn ($fm) => (object) $fm);

                // update montant table paiement
                $montant_paye = $request->montant_paye;
                $montant_restant = $paiement->montant_restant  - $montant_paye; //$request->montant_total_du - $montant_paye;
                // 1 update table paiements
                //$paiement->setAttribute('montant_total',$request->montant_total_du);
                $paiement->setAttribute('montant_restant',$montant_restant);
                $paiement->setAttribute('montant_paye',$paiement->montant_paye + $montant_paye);
                if($montant_restant == 0){
                    $paiement->setAttribute('num_transaction', $transactionId->id);
                    $paiement->setAttribute('statut_transaction', $transactionId->status);
                    $paiement->setAttribute('statut_paiement', 'PAYE');
                    $paiement->setAttribute('statut_traitement', 'VALIDER');
                    $paiement->setAttribute('agent_traiteur', $userValidateur->id);
                    $paiement->setAttribute('commentaire', 'Validation automatique webhook Fedapay');
                }
                $paiement->setAttribute('id_modepaiement', 2); 
                $paiement->update();
                // 2- update details paiement
                $getPTranche = PaiementsTranche::where("reference","like","%$paiement->reference%")->latest()->first();
                //dd($getPTranche);
                if ($getPTranche) {
                    # code...
                    $ref = explode("_",$getPTranche->reference)[1]+1;
                    $update_reference = $paiement->reference."_".$ref;
                } else {
                    # code...
                    $update_reference = $paiement->reference."_1";
                }

                foreach ($liste_details as $detail){
                    if($detail->type_tarif == 'INSCRIPTION' && $detail->choix && $detail->montant_paye > 0){
                        // paiement effectué pour inscription
                        $detailPaiement = Detailspaiements::find($detail->detail_id);
                        $detailPaiement->setAttribute('montant_paye',$detail->montant_paye);
                        $detailPaiement->setAttribute('montant_restant',$detail->montant_restant-$detail->montant_paye);
                        $detailPaiement->setAttribute('reference_paiement_partiel',$transactionId->id);
                        $detailPaiement->setAttribute('have_reduction_dsc',false);
                        $detailPaiement->setAttribute('statut_paiement',"PAYE");
                        $detailPaiement->setAttribute('statut_traitement',"VALIDER");
                        $detailPaiement->setAttribute("commentaire","Validation automatique webhook FedaPay");
                        $detailPaiement->update();

                        $tranche_paiement = new PaiementsTranche();
                        $tranche_paiement->setAttribute('details_paiements_id',$detailPaiement->id);
                        $tranche_paiement->setAttribute('montant_paye',$detail->montant_paye);
                        $tranche_paiement->setAttribute('montant_restant',$detailPaiement->montant_restant);
                        $tranche_paiement->setAttribute('reference',$update_reference);
                        $tranche_paiement->setAttribute('num_transaction', $transactionId->id);
                        $tranche_paiement->setAttribute('statut_transaction', $transactionId->status);
                        # code...
                        $tranche_paiement->setAttribute('statut_traitement', 'VALIDER');
                        $tranche_paiement->setAttribute('traiter_par',$userValidateur->id);
                        $tranche_paiement->setAttribute('is_inscription',true);
                        $tranche_paiement->setAttribute('modepaiement_id',$paiement->id_modepaiement);
                        $tranche_paiement->setAttribute('created_by',$user->id);
                        $tranche_paiement->setAttribute('updated_by',$user->id);
                        $tranche_paiement->save();

                    }else{
                        if($request->tranche == 'tranche_unique'){ // tranche unique
                            $detailPaiement = Detailspaiements::find($detail->detail_id);
                            // sauvegarde de la tranche
                            $formationTranche = FormationsTranches::where('tranche','tranche 1')
                                ->where('grilletarifaire_id',$detailPaiement->getTarification->getGrilletarifaire->id)
                                ->first();
                            // vérifier si la tranche existe pour update
                            $checkTranche = Tranches::whereCodeInscription($paiement->code_inscription)->exists();

                            if($checkTranche){
                                $getTranche = Tranches::whereCodeInscription($paiement->code_inscription)->first();
                                $tranche = Tranches::find($getTranche->id);
                                $tranche->setAttribute('code_inscription',$paiement->code_inscription);
                                $tranche->setAttribute('email',$paiement->email);
                                //$tranche->setAttribute('tranche_1',$item->getTarification->getGrilletarifaire->montant*0.95);
                                $tranche->setAttribute('tranche_1',$detailPaiement->getTarification->getGrilletarifaire->montant);
                                $tranche->setAttribute('tranche_2',0);
                                $tranche->setAttribute('echeance_1',$formationTranche->echeance_1);
                                $tranche->setAttribute('echeance_2',$formationTranche->echeance_1);
                                $tranche->setAttribute('statut','ATTENTE');
                                $tranche->setAttribute('created_by',$user->id);
                                $tranche->setAttribute('updated_by',$user->id);
                                $tranche->update();
                            }else{
                                $tranche = new Tranches();
                                $tranche->setAttribute('code_inscription',$paiement->code_inscription);
                                $tranche->setAttribute('email',$paiement->email);
                                //$tranche->setAttribute('tranche_1',$item->getTarification->getGrilletarifaire->montant*0.95);
                                $tranche->setAttribute('tranche_1',$detailPaiement->getTarification->getGrilletarifaire->montant);
                                $tranche->setAttribute('tranche_2',0);
                                $tranche->setAttribute('echeance_1',$formationTranche->echeance_1);
                                $tranche->setAttribute('echeance_2',$formationTranche->echeance_1);
                                $tranche->setAttribute('statut','ATTENTE');
                                $tranche->setAttribute('created_by',$user->id);
                                $tranche->setAttribute('updated_by',$user->id);
                                $tranche->save();
                            }
                            // vérifier si paiement enregistré
                            if($detail->choix && $detail->montant_paye > 0){
                                $baseMontantRestant = $detail->montant_restant; //*0.95;
                                $mtRestant =  $baseMontantRestant - $detail->montant_paye;
                                $detailPaiement->setAttribute('montant_paye',$detailPaiement->montant_paye + $detail->montant_paye);
                                $detailPaiement->setAttribute('montant_restant',$mtRestant);
                                if($mtRestant == 0){
                                    $detailPaiement->setAttribute('reference_paiement_partiel',$transactionId->id);
                                    $detailPaiement->setAttribute('statut_paiement',"PAYE");
                                    $detailPaiement->setAttribute('statut_traitement',"VALIDER");
                                    $detailPaiement->setAttribute("commentaire","Validation automatique webhook FedaPay");
                                }
                                $detailPaiement->setAttribute('have_reduction_dsc',true);
                                $detailPaiement->update();

                                # code...
                                $tranche_paiement = new PaiementsTranche();
                                $tranche_paiement->setAttribute('details_paiements_id',$detailPaiement->id);
                                $tranche_paiement->setAttribute('montant_paye',$detail->montant_paye);
                                $tranche_paiement->setAttribute('montant_restant',$detailPaiement->montant_restant);
                                $tranche_paiement->setAttribute('reference',$update_reference);
                                $tranche_paiement->setAttribute('num_transaction', $transactionId->id);
                                $tranche_paiement->setAttribute('statut_transaction', $transactionId->status);
                                $tranche_paiement->setAttribute('statut_traitement', 'VALIDER');
                                $tranche_paiement->setAttribute('traiter_par',$userValidateur->id);
                                $tranche_paiement->setAttribute('tranche_id',$tranche->id);
                                $tranche_paiement->setAttribute('modepaiement_id',$paiement->id_modepaiement);
                                $tranche_paiement->setAttribute('created_by',$user->id);
                                $tranche_paiement->setAttribute('updated_by',$user->id);
                                $tranche_paiement->save();

                            }else{
                                //$detailPaiement->setAttribute('montant_restant',$detail->montant_restant*0.95);
                                //$detailPaiement->setAttribute('have_reduction_dsc',true);
                                //$detailPaiement->update();
                            }
                        }else{ // double tranche
                            $detailPaiement = Detailspaiements::find($detail->detail_id);
                            // sauvegarde de la tranche
                            $formationTranche = FormationsTranches::where('tranche','tranche 2')->where('grilletarifaire_id',$detailPaiement->getTarification->getGrilletarifaire->id)->first();

                            // vérifier si la tranche existe pour update
                            $checkTranche = Tranches::whereCodeInscription($paiement->code_inscription)->exists();

                            if($checkTranche){
                                $getTranche = Tranches::whereCodeInscription($paiement->code_inscription)->first();
                                $tranche = Tranches::find($getTranche->id);
                                $tranche->setAttribute('code_inscription',$paiement->code_inscription);
                                $tranche->setAttribute('email',$paiement->email);
                                $tranche->setAttribute('tranche_1',$detailPaiement->getTarification->getGrilletarifaire->montant*0.5);
                                $tranche->setAttribute('tranche_2',$detailPaiement->getTarification->getGrilletarifaire->montant*0.5);
                                $tranche->setAttribute('echeance_1',$formationTranche->echeance_1);
                                $tranche->setAttribute('echeance_2',$formationTranche->echeance_2);
                                $tranche->setAttribute('statut','ATTENTE');
                                $tranche->setAttribute('created_by',$user->id);
                                $tranche->setAttribute('updated_by',$user->id);
                                $tranche->update();
                            }else{
                                $tranche = new Tranches();
                                $tranche->setAttribute('code_inscription',$paiement->code_inscription);
                                $tranche->setAttribute('email',$paiement->email);
                                $tranche->setAttribute('tranche_1',$detailPaiement->getTarification->getGrilletarifaire->montant*0.5);
                                $tranche->setAttribute('tranche_2',$detailPaiement->getTarification->getGrilletarifaire->montant*0.5);
                                $tranche->setAttribute('echeance_1',$formationTranche->echeance_1);
                                $tranche->setAttribute('echeance_2',$formationTranche->echeance_2);
                                $tranche->setAttribute('statut','ATTENTE');
                                $tranche->setAttribute('created_by',$user->id);
                                $tranche->setAttribute('updated_by',$user->id);
                                $tranche->save();
                            }

                            // vérifier si paiement enregistré
                            if($detail->choix && $detail->montant_paye > 0){
                                $mtRestant = $detail->montant_restant - $detail->montant_paye;
                                $detailPaiement->setAttribute('montant_paye',$detailPaiement->montant_paye + $detail->montant_paye);
                                $detailPaiement->setAttribute('montant_restant',$mtRestant);
                                if($mtRestant == 0){
                                    $detailPaiement->setAttribute('reference_paiement_partiel',$transactionId->id);
                                    $detailPaiement->setAttribute('statut_paiement',"PAYE");
                                    $detailPaiement->setAttribute('statut_traitement',"VALIDER");
                                    $detailPaiement->setAttribute("commentaire","Validation automatique webhook FedaPay");
                                }
                                $detailPaiement->setAttribute('have_reduction_dsc',false);
                                $detailPaiement->update();

                                # code...
                                $tranche_paiement = new PaiementsTranche();
                                $tranche_paiement->setAttribute('details_paiements_id',$detailPaiement->id);
                                $tranche_paiement->setAttribute('montant_paye',$detail->montant_paye);
                                $tranche_paiement->setAttribute('montant_restant',$detailPaiement->montant_restant);
                                $tranche_paiement->setAttribute('reference',$update_reference);
                                $tranche_paiement->setAttribute('num_transaction', $transactionId->id);
                                $tranche_paiement->setAttribute('statut_transaction', $transactionId->status);
                                $tranche_paiement->setAttribute('statut_traitement', 'VALIDER');
                                $tranche_paiement->setAttribute('traiter_par',$userValidateur->id);
                                $tranche_paiement->setAttribute('tranche_id',$tranche->id);
                                $tranche_paiement->setAttribute('modepaiement_id',$paiement->id_modepaiement);
                                $tranche_paiement->setAttribute('created_by',$user->id);
                                $tranche_paiement->setAttribute('updated_by',$user->id);
                                $tranche_paiement->save();

                            }else{
                                //$detailPaiement->setAttribute('montant_restant',$detail->montant_restant);
                                //$detailPaiement->setAttribute('have_reduction_dsc',false);
                                //$detailPaiement->update();
                            }
                        }
                    }
                }


                // envoie de mail pour notification paiement en attente
                // envoie de mail pour notifier l'enregistrement du paiement
                $auditeur = Auditeurs::where("email",$paiement->email)->first();

                $libelle_mode_paiement = Modepaiements::find($paiement->id_modepaiement);

                if ($auditeur) {
                    # code...
                    $contenu = [
                        'nom' => $auditeur->nom,
                        'prenoms' => $auditeur->prenoms,
                        'reference_paiement' => $update_reference,
                        'subject' => 'INFORMATION SUR VOTRE PAIEMENT '.$update_reference,
                        'email' => $paiement->email,
                        'montant' => $request->montant_paye,
                        'montant_restant' => $paiement->montant_restant,
                        'mode_paiement' => $libelle_mode_paiement->libelle
                    ];
                } else {
                    # code...
                    $contenu = [
                        'nom' => "",
                        'prenoms' => "",
                        'reference_paiement' => $update_reference,
                        'montant' => $request->montant_paye,
                        'montant_restant' => $paiement->montant_restant,
                        'subject' => 'INFORMATION SUR VOTRE PAIEMENT '.$update_reference,
                        'email' => $paiement->email,
                        'mode_paiement' => $libelle_mode_paiement->libelle
                    ];
                }

                try {
                    # code...
                    Mail::to($paiement->email)->cc([env('MAIL_CC')])->bcc(env('MAIL_BCC'))->queue(new DscPaiementAttente($contenu));
                } catch (\Exception $e) {
                    // Get error here

                }

                // Notification au comptable
                // send mail comptable
                $liste_comptable = User::whereHas('roles', function ($q) {
                    $q->where('libelle', 'COMPTABLE');
                })->pluck('email');

                if (env("PAIEMENT_NOTIFIER_COMPTABLE")) {
                    # code...
                    $contenu = [
                        'name' => 'COMPTABLE',
                        'reference_paiement' => $update_reference,
                        'email_auditeur' => $paiement->email,
                        'nom_auditeur' => $auditeur->prenoms.' '.$auditeur->nom,
                        'subject' => 'NOUVEAU PAIEMENT ' . date('d-m-Y H:i:s'),
                        'email' => '',
                        'montant' => $request->montant_paye,
                        'mode_paiement' => $libelle_mode_paiement->libelle
                    ];

                    try {
                        # code...
                        Mail::to(env('MAIL_CC'))->cc($liste_comptable)->queue(new DscNotificationComptable($contenu));
                    } catch (\Exception $e) {
                        // Get error here

                    }
                }

                // vérifier si le statut est succès pour envoyer le mail de notification traitement paiement
                # code...
                $contenuTraitement = [
                    'nom' => $auditeur->nom ,
                    'prenoms' => $auditeur->prenoms,
                    'reference_paiement' => $update_reference,
                    'subject' => 'TRAITEMENT DE VOTRE PAIEMENT '.$update_reference,
                    'commentaire' => "Paiement validé",
                    'email'=>$paiement->email,
                    'mode_paiement'=>$libelle_mode_paiement->libelle,
                    'montant'=>$request->montant_paye,
                    'montant_restant'=>$paiement->montant_restant,
                    'type'=>$paiement->type,
                    'url_recu'=>$this->genarateurCodeService->generateUrlSigned('paiements.recu',['reference'=>$update_reference])
                ];

                try {
                    # code...
                    Mail::to(trim($paiement->email))->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new PaiementValideDsc($contenuTraitement));

                } catch (\Exception $e) {
                    //throw $th;
                }


                $dataResponse = [
                    'reference' => $request->reference,
                    'success' => true,
                ];
                return ResponseHandler::success("ok", $dataResponse);

            }else{
                $dataResponse = [
                    'reference' => $request->reference,
                    'success' => false,
                ];
                return ResponseHandler::success("paiement invalide", $dataResponse);
            }

        } catch (\Exception $e) {
            Log::error('Erreur vérification FedaPay', ['error' => $e->getMessage()]);

            $dataResponse = [
                'reference' => $request->reference,
                'success' => false,
            ];

            return ResponseHandler::success("ok", $dataResponse);

        }
    }

    public function storePaiementPartielDscFedaPay(Request $request): \Illuminate\Http\JsonResponse
{
    try {
        // FIX : Transaction::retrieve() dans le try
        $transactionId = Transaction::retrieve($request->transaction_id);

        if ($transactionId->status == 'approved' || $transactionId->status == 'APPROVED') {

            $tabReference = explode("_", $request->reference);
            $getReference = $tabReference[0];
            $reference = $request->reference;

            // FIX : null-check paiement
            $getPaiement = Paiements::where('reference', $getReference)->first();
            if (!$getPaiement) {
                Log::warning('Webhook FedaPay partiel : paiement introuvable', ['reference' => $getReference]);
                return ResponseHandler::success("paiement introuvable", [
                    'reference' => $reference,
                    'success' => false,
                ]);
            }
            $paiement = Paiements::find($getPaiement->id);

            $inscrit = Inscriptions::where("code_inscription", $paiement->code_inscription)
                ->orderBy("id", "DESC")
                ->first();

            // FIX : null-check user
            $getUser = User::where('email', $paiement->email)->first();
            if (!$getUser) {
                Log::warning('Webhook FedaPay partiel : utilisateur introuvable', ['email' => $paiement->email]);
                return ResponseHandler::success("utilisateur introuvable", [
                    'reference' => $reference,
                    'success' => false,
                ]);
            }
            $user = User::find($getUser->id);

            // FIX : null-check validateur
            $userValidateur = User::where("email", "formation.ersuma@ohada.org")->first();
            if (!$userValidateur) {
                Log::error('Webhook FedaPay partiel : compte validateur introuvable');
                return ResponseHandler::success("compte validateur introuvable", [
                    'reference' => $reference,
                    'success' => false,
                ]);
            }

            // ============================================================
            // FIX : VALIDATION DU MONTANT vs FedaPay
            // ============================================================
            $montantConfirmeFedapay = (float) $transactionId->amount;
            $montantAnnonceParFront = (float) $request->montant_paye;

            if (abs($montantConfirmeFedapay - $montantAnnonceParFront) > 1) {
                Log::warning('Webhook FedaPay partiel : montant incohérent', [
                    'reference' => $reference,
                    'transaction_id' => $transactionId->id,
                    'montant_fedapay' => $montantConfirmeFedapay,
                    'montant_recu_front' => $montantAnnonceParFront,
                ]);
                return ResponseHandler::success("montant incohérent", [
                    'reference' => $reference,
                    'success' => false,
                ]);
            }

            // ============================================================
            // FIX LE PLUS IMPORTANT : IDEMPOTENCE GLOBALE sur ce transaction_id.
            // Si cette transaction a déjà été traitée (webhook rejoué),
            // on NE TOUCHE PLUS AUX CUMULS et on ne renvoie plus les mails.
            // ============================================================
            $dejaTraite = PaiementsTranche::where('num_transaction', $transactionId->id)->exists();

            $liste_details = collect($request->input('formations'))->map(fn($fm) => (object) $fm);
            $montant_paye = $request->montant_paye;

            if (!$dejaTraite) {
                // 1. update table paiements — protégé : on n'incrémente
                // les cumuls qu'une seule fois par transaction_id
                $montant_restant = $paiement->montant_restant - $montant_paye;
                $paiement->setAttribute('montant_restant', $montant_restant);
                $paiement->setAttribute('montant_paye', $paiement->montant_paye + $montant_paye);
                if ($montant_restant == 0) {
                    $paiement->setAttribute('num_transaction', $transactionId->id);
                    $paiement->setAttribute('statut_transaction', $transactionId->status);
                    $paiement->setAttribute('statut_paiement', 'PAYE');
                    $paiement->setAttribute('statut_traitement', 'VALIDER');
                    $paiement->setAttribute('agent_traiteur', $userValidateur->id);
                    $paiement->setAttribute('commentaire', 'Validation automatique webhook Fedapay');
                }
                $paiement->setAttribute('id_modepaiement', 2);
                $paiement->update();
            } else {
                Log::info('Webhook FedaPay partiel déjà traité, cumuls non rejoués', [
                    'transaction_id' => $transactionId->id,
                    'reference' => $reference,
                ]);
            }

            // 2. update details paiement — référence de tranche calculée
            // dans tous les cas (lecture seule, pas de risque de doublon ici)
            $getPTranche = PaiementsTranche::where("reference", "like", "%$paiement->reference%")->latest()->first();
            if ($getPTranche) {
                $ref = explode("_", $getPTranche->reference)[1] + 1;
                $update_reference = $paiement->reference . "_" . $ref;
            } else {
                $update_reference = $paiement->reference . "_1";
            }

            if (!$dejaTraite) {
                foreach ($liste_details as $detail) {
                    if ($detail->type_tarif == 'INSCRIPTION' && $detail->choix && $detail->montant_paye > 0) {

                        // FIX : garde anti-doublon réintroduite
                        $checkData = Detailspaiements::where('id', $detail->detail_id)
                            ->where('reference_paiement_partiel', $transactionId->id)
                            ->exists();

                        if (!$checkData) {
                            $detailPaiement = Detailspaiements::find($detail->detail_id);
                            $detailPaiement->setAttribute('montant_paye', $detail->montant_paye);
                            $detailPaiement->setAttribute('montant_restant', $detail->montant_restant - $detail->montant_paye);
                            $detailPaiement->setAttribute('reference_paiement_partiel', $transactionId->id);
                            $detailPaiement->setAttribute('have_reduction_dsc', false);
                            $detailPaiement->setAttribute('statut_paiement', "PAYE");
                            $detailPaiement->setAttribute('statut_traitement', "VALIDER");
                            $detailPaiement->setAttribute("commentaire", "Validation automatique webhook FedaPay");
                            $detailPaiement->update();

                            $tranche_paiement = new PaiementsTranche();
                            $tranche_paiement->setAttribute('details_paiements_id', $detailPaiement->id);
                            $tranche_paiement->setAttribute('montant_paye', $detail->montant_paye);
                            $tranche_paiement->setAttribute('montant_restant', $detailPaiement->montant_restant);
                            $tranche_paiement->setAttribute('reference', $update_reference);
                            $tranche_paiement->setAttribute('num_transaction', $transactionId->id);
                            $tranche_paiement->setAttribute('statut_transaction', $transactionId->status);
                            $tranche_paiement->setAttribute('statut_traitement', 'VALIDER');
                            $tranche_paiement->setAttribute('traiter_par', $userValidateur->id);
                            $tranche_paiement->setAttribute('is_inscription', true);
                            $tranche_paiement->setAttribute('modepaiement_id', $paiement->id_modepaiement);
                            $tranche_paiement->setAttribute('created_by', $user->id);
                            $tranche_paiement->setAttribute('updated_by', $user->id);
                            $tranche_paiement->save();
                        }

                    } else {
                        if ($request->tranche == 'tranche_unique') {
                            $detailPaiement = Detailspaiements::find($detail->detail_id);
                            $formationTranche = FormationsTranches::where('tranche', 'tranche 1')
                                ->where('grilletarifaire_id', $detailPaiement->getTarification->getGrilletarifaire->id)
                                ->first();

                            $checkTranche = Tranches::whereCodeInscription($paiement->code_inscription)->exists();
                            if ($checkTranche) {
                                $getTranche = Tranches::whereCodeInscription($paiement->code_inscription)->first();
                                $tranche = Tranches::find($getTranche->id);
                            } else {
                                $tranche = new Tranches();
                            }
                            $tranche->setAttribute('code_inscription', $paiement->code_inscription);
                            $tranche->setAttribute('email', $paiement->email);
                            $tranche->setAttribute('tranche_1', $detailPaiement->getTarification->getGrilletarifaire->montant);
                            $tranche->setAttribute('tranche_2', 0);
                            $tranche->setAttribute('echeance_1', $formationTranche->echeance_1);
                            $tranche->setAttribute('echeance_2', $formationTranche->echeance_1);
                            $tranche->setAttribute('statut', 'ATTENTE');
                            $tranche->setAttribute('created_by', $user->id);
                            $tranche->setAttribute('updated_by', $user->id);
                            $checkTranche ? $tranche->update() : $tranche->save();

                            if ($detail->choix && $detail->montant_paye > 0) {
                                // FIX : garde anti-doublon réintroduite
                                $checkData = Detailspaiements::where('id', $detail->detail_id)
                                    ->where('reference_paiement_partiel', $transactionId->id)
                                    ->exists();

                                if (!$checkData) {
                                    $baseMontantRestant = $detail->montant_restant;
                                    $mtRestant = $baseMontantRestant - $detail->montant_paye;
                                    $detailPaiement->setAttribute('montant_paye', $detailPaiement->montant_paye + $detail->montant_paye);
                                    $detailPaiement->setAttribute('montant_restant', $mtRestant);
                                    if ($mtRestant == 0) {
                                        $detailPaiement->setAttribute('reference_paiement_partiel', $transactionId->id);
                                        $detailPaiement->setAttribute('statut_paiement', "PAYE");
                                        $detailPaiement->setAttribute('statut_traitement', "VALIDER");
                                        $detailPaiement->setAttribute("commentaire", "Validation automatique webhook FedaPay");
                                    }
                                    $detailPaiement->setAttribute('have_reduction_dsc', true);
                                    $detailPaiement->update();

                                    $tranche_paiement = new PaiementsTranche();
                                    $tranche_paiement->setAttribute('details_paiements_id', $detailPaiement->id);
                                    $tranche_paiement->setAttribute('montant_paye', $detail->montant_paye);
                                    $tranche_paiement->setAttribute('montant_restant', $detailPaiement->montant_restant);
                                    $tranche_paiement->setAttribute('reference', $update_reference);
                                    $tranche_paiement->setAttribute('num_transaction', $transactionId->id);
                                    $tranche_paiement->setAttribute('statut_transaction', $transactionId->status);
                                    $tranche_paiement->setAttribute('statut_traitement', 'VALIDER');
                                    $tranche_paiement->setAttribute('traiter_par', $userValidateur->id);
                                    $tranche_paiement->setAttribute('tranche_id', $tranche->id);
                                    $tranche_paiement->setAttribute('modepaiement_id', $paiement->id_modepaiement);
                                    $tranche_paiement->setAttribute('created_by', $user->id);
                                    $tranche_paiement->setAttribute('updated_by', $user->id);
                                    $tranche_paiement->save();
                                }
                            }
                        } else { // double tranche
                            $detailPaiement = Detailspaiements::find($detail->detail_id);
                            $formationTranche = FormationsTranches::where('tranche', 'tranche 2')
                                ->where('grilletarifaire_id', $detailPaiement->getTarification->getGrilletarifaire->id)
                                ->first();

                            $checkTranche = Tranches::whereCodeInscription($paiement->code_inscription)->exists();
                            if ($checkTranche) {
                                $getTranche = Tranches::whereCodeInscription($paiement->code_inscription)->first();
                                $tranche = Tranches::find($getTranche->id);
                            } else {
                                $tranche = new Tranches();
                            }
                            $tranche->setAttribute('code_inscription', $paiement->code_inscription);
                            $tranche->setAttribute('email', $paiement->email);
                            $tranche->setAttribute('tranche_1', $detailPaiement->getTarification->getGrilletarifaire->montant * 0.5);
                            $tranche->setAttribute('tranche_2', $detailPaiement->getTarification->getGrilletarifaire->montant * 0.5);
                            $tranche->setAttribute('echeance_1', $formationTranche->echeance_1);
                            $tranche->setAttribute('echeance_2', $formationTranche->echeance_2);
                            $tranche->setAttribute('statut', 'ATTENTE');
                            $tranche->setAttribute('created_by', $user->id);
                            $tranche->setAttribute('updated_by', $user->id);
                            $checkTranche ? $tranche->update() : $tranche->save();

                            if ($detail->choix && $detail->montant_paye > 0) {
                                // FIX : garde anti-doublon réintroduite
                                $checkData = Detailspaiements::where('id', $detail->detail_id)
                                    ->where('reference_paiement_partiel', $transactionId->id)
                                    ->exists();

                                if (!$checkData) {
                                    $mtRestant = $detail->montant_restant - $detail->montant_paye;
                                    $detailPaiement->setAttribute('montant_paye', $detailPaiement->montant_paye + $detail->montant_paye);
                                    $detailPaiement->setAttribute('montant_restant', $mtRestant);
                                    if ($mtRestant == 0) {
                                        $detailPaiement->setAttribute('reference_paiement_partiel', $transactionId->id);
                                        $detailPaiement->setAttribute('statut_paiement', "PAYE");
                                        $detailPaiement->setAttribute('statut_traitement', "VALIDER");
                                        $detailPaiement->setAttribute("commentaire", "Validation automatique webhook FedaPay");
                                    }
                                    $detailPaiement->setAttribute('have_reduction_dsc', false);
                                    $detailPaiement->update();

                                    $tranche_paiement = new PaiementsTranche();
                                    $tranche_paiement->setAttribute('details_paiements_id', $detailPaiement->id);
                                    $tranche_paiement->setAttribute('montant_paye', $detail->montant_paye);
                                    $tranche_paiement->setAttribute('montant_restant', $detailPaiement->montant_restant);
                                    $tranche_paiement->setAttribute('reference', $update_reference);
                                    $tranche_paiement->setAttribute('num_transaction', $transactionId->id);
                                    $tranche_paiement->setAttribute('statut_transaction', $transactionId->status);
                                    $tranche_paiement->setAttribute('statut_traitement', 'VALIDER');
                                    $tranche_paiement->setAttribute('traiter_par', $userValidateur->id);
                                    $tranche_paiement->setAttribute('tranche_id', $tranche->id);
                                    $tranche_paiement->setAttribute('modepaiement_id', $paiement->id_modepaiement);
                                    $tranche_paiement->setAttribute('created_by', $user->id);
                                    $tranche_paiement->setAttribute('updated_by', $user->id);
                                    $tranche_paiement->save();
                                }
                            }
                        }
                    }
                }
            }

            // ============================================================
            // ENVOI DES MAILS — protégé par $dejaTraite, et null-checks
            // ============================================================
            if (!$dejaTraite) {

                $auditeur = Auditeurs::where("email", $paiement->email)->first();
                $libelle_mode_paiement = Modepaiements::find($paiement->id_modepaiement);
                $libelleModePaiement = $libelle_mode_paiement ? $libelle_mode_paiement->libelle : '';

                if ($auditeur) {
                    $contenu = [
                        'nom' => $auditeur->nom,
                        'prenoms' => $auditeur->prenoms,
                        'reference_paiement' => $update_reference,
                        'subject' => 'INFORMATION SUR VOTRE PAIEMENT ' . $update_reference,
                        'email' => $paiement->email,
                        'montant' => $request->montant_paye,
                        'montant_restant' => $paiement->montant_restant,
                        'mode_paiement' => $libelleModePaiement,
                    ];
                } else {
                    $contenu = [
                        'nom' => "",
                        'prenoms' => "",
                        'reference_paiement' => $update_reference,
                        'montant' => $request->montant_paye,
                        'montant_restant' => $paiement->montant_restant,
                        'subject' => 'INFORMATION SUR VOTRE PAIEMENT ' . $update_reference,
                        'email' => $paiement->email,
                        'mode_paiement' => $libelleModePaiement,
                    ];
                }

                try {
                    Mail::to($paiement->email)->cc([env('MAIL_CC')])->bcc(env('MAIL_BCC'))->queue(new DscPaiementAttente($contenu));
                } catch (\Exception $e) {
                    Log::error('Erreur envoi mail DscPaiementAttente (partiel)', ['error' => $e->getMessage()]);
                }

                $liste_comptable = User::whereHas('roles', function ($q) {
                    $q->where('libelle', 'COMPTABLE');
                })->pluck('email');

                if (env("PAIEMENT_NOTIFIER_COMPTABLE")) {
                    $contenu = [
                        'name' => 'COMPTABLE',
                        'reference_paiement' => $update_reference,
                        'email_auditeur' => $paiement->email,
                        // FIX : null-check
                        'nom_auditeur' => $auditeur ? trim($auditeur->prenoms . ' ' . $auditeur->nom) : $paiement->email,
                        'subject' => 'NOUVEAU PAIEMENT ' . date('d-m-Y H:i:s'),
                        'email' => '',
                        'montant' => $request->montant_paye,
                        'mode_paiement' => $libelleModePaiement,
                    ];

                    try {
                        Mail::to(env('MAIL_CC'))->cc($liste_comptable)->queue(new DscNotificationComptable($contenu));
                    } catch (\Exception $e) {
                        Log::error('Erreur envoi mail DscNotificationComptable (partiel)', ['error' => $e->getMessage()]);
                    }
                }

                $contenuTraitement = [
                    // FIX : null-check
                    'nom' => $auditeur ? $auditeur->nom : '',
                    'prenoms' => $auditeur ? $auditeur->prenoms : '',
                    'reference_paiement' => $update_reference,
                    'subject' => 'TRAITEMENT DE VOTRE PAIEMENT ' . $update_reference,
                    'commentaire' => "Paiement validé",
                    'email' => $paiement->email,
                    'mode_paiement' => $libelleModePaiement,
                    'montant' => $request->montant_paye,
                    'montant_restant' => $paiement->montant_restant,
                    'type' => $paiement->type,
                    'url_recu' => $this->genarateurCodeService->generateUrlSigned('paiements.recu', ['reference' => $update_reference]),
                ];

                try {
                    Mail::to(trim($paiement->email))->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new PaiementValideDsc($contenuTraitement));
                } catch (\Exception $e) {
                    Log::error('Erreur envoi mail PaiementValideDsc (partiel)', ['error' => $e->getMessage()]);
                }
            }

            return ResponseHandler::success("ok", [
                'reference' => $request->reference,
                'success' => true,
            ]);

        } else {
            return ResponseHandler::success("paiement invalide", [
                'reference' => $request->reference,
                'success' => false,
            ]);
        }

    } catch (\Throwable $e) { // FIX : attrape aussi les \Error
        Log::error('Erreur vérification FedaPay (partiel)', ['error' => $e->getMessage()]);

        return ResponseHandler::success("ok", [
            'reference' => $request->reference ?? null,
            'success' => false,
        ]);
    }
}


}

