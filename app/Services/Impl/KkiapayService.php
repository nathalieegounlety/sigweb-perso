<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Mail\NotificationComptable;
use App\Mail\NotificationPaiementOuvrageStaff;
use App\Mail\Paiementattente;
use App\Mail\Paiementattenteouvrage;
use App\Models\Auditeurs;
use App\Models\Detailspaiements;
use App\Models\Modepaiements;
use App\Models\Paiements;
use App\Models\User;
use App\Services\contracts\KkiapayInterfarce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Kkiapay\Kkiapay;

class KkiapayService implements  KkiapayInterfarce
{
    public function storePaiementKkiapay(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement storePaiementFedapay() method.

        $transactionId = $request->input('transaction_id');

        $statut_sandbox = env("KKIAPAY_SANDBOX");

        if ($statut_sandbox) {
            $public_key = env("KKIAPAY_SANDBOX_PUBLIC_KEY");
            $private_key = env("KKIAPAY_SANDBOX_PRIVATE_KEY");
            $secret = env("KKIAPAY_SANDBOX_SECRET_KEY");
        } else {

            $public_key = env("KKIAPAY_LIVE_PUBLIC_KEY");
            $private_key = env("KKIAPAY_LIVE_PRIVATE_KEY");
            $secret = env("KKIAPAY_LIVE_SECRET_KEY");
        }

        $kkiapay = new Kkiapay(
            $public_key,
            $private_key,
            $secret,
            $statut_sandbox // true en test
        );

        try {
            $transaction = $kkiapay->verifyTransaction($transactionId);

            if ($transaction->status === 'SUCCESS') {
                $reference = $request->reference;

                // mise à jour de la table paiement
                $getPaiement = Paiements::whereReference($reference)->first();
                $montant_payer = $getPaiement->montant_restant;
                // mise à jour du montant paye
                $paiement = Paiements::find($getPaiement->id);
                $paiement->setAttribute('num_transaction', $transactionId);
                $paiement->setAttribute('statut_transaction', $transactionId);
                $paiement->setAttribute('updated_at', date('Y-m-d H:i:s'));
                $paiement->setAttribute('id_modepaiement', 1);
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
                        'montant' => $paiement->montant_restant,
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
                        'montant' => $paiement->montant_restant,
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
                        'montant' => $paiement->montant_restant,
                        'mode_paiement' => $libelle_mode_paiement->libelle
                    ];
                    try {
                        # code...
                        Mail::to(env('MAIL_CC'))->cc($liste_comptable)->queue(new NotificationComptable($contenu));
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
            $dataResponse = [
                'reference' => $request->reference,
                'success' => false,
            ];

            return  ResponseHandler::success("Echec lors de l'enregistrement",$dataResponse);

        } catch (\Exception $e) {
            \Log::error('Erreur vérification KKiaPay', ['error' => $e->getMessage()]);

            $dataResponse = [
                'reference' => $request->reference,
                'success' => false,
            ];

            return  ResponseHandler::success("ok",$dataResponse);

        }

    }

    public function storePaiementPartielKkiapay(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement storePaiementPartielKkiapay() method.

        $transactionId = $request->input('transaction_id');

        $statut_sandbox = env("KKIAPAY_SANDBOX");

        if ($statut_sandbox) {
            $public_key = env("KKIAPAY_SANDBOX_PUBLIC_KEY");
            $private_key = env("KKIAPAY_SANDBOX_PRIVATE_KEY");
            $secret = env("KKIAPAY_SANDBOX_SECRET_KEY");
        } else {

            $public_key = env("KKIAPAY_LIVE_PUBLIC_KEY");
            $private_key = env("KKIAPAY_LIVE_PRIVATE_KEY");
            $secret = env("KKIAPAY_LIVE_SECRET_KEY");
        }

        $kkiapay = new Kkiapay(
            $public_key,
            $private_key,
            $secret,
            $statut_sandbox // true en test
        );

        try {
            $transaction = $kkiapay->verifyTransaction($transactionId);

            if ($transaction->status === 'SUCCESS') {
                $reference = $request->reference_paiement;
                $actuel_montant_paye = $request->montant_total;
                $mode_paiement = Modepaiements::whereLibelle($request->mode_paiement)->first();
                // mise à jour de la table paiement
                $paiement = Paiements::where('reference', $reference)->get()->first();
                $update_montant_restant = $paiement->montant_restant - $actuel_montant_paye;
                $update_montant_paye = $paiement->montant_paye + $actuel_montant_paye;

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
                    $update_paiement->setAttribute('num_transaction', $transactionId);
                    $update_paiement->setAttribute('statut_transaction', $transactionId);
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
                        $update_detailspaiement->setAttribute('reference_paiement_partiel', $transactionId);
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
                    'reference' => $paiement->reference_paiement,
                    'success' => true,
                ];

                return  ResponseHandler::success("ok",$dataResponse);


            }
            $dataResponse = [
                'reference' => $request->reference_paiement,
                'success' => false,
            ];

            return  ResponseHandler::success("Echec lors de l'enregistrement",$dataResponse);

        } catch (\Exception $e) {
            \Log::error('Erreur vérification KKiaPay', ['error' => $e->getMessage()]);

            $dataResponse = [
                'reference' => $request->reference_paiement,
                'success' => false,
            ];

            return  ResponseHandler::success("ok",$dataResponse);

        }
    }

    public function storePaiementOuvrageKkiapay(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement storePaiementOuvrageKkiapay() method.


        $transactionId = $request->input('transaction_id');

        $statut_sandbox = env("KKIAPAY_SANDBOX");

        if ($statut_sandbox) {
            $public_key = env("KKIAPAY_SANDBOX_PUBLIC_KEY");
            $private_key = env("KKIAPAY_SANDBOX_PRIVATE_KEY");
            $secret = env("KKIAPAY_SANDBOX_SECRET_KEY");
        } else {

            $public_key = env("KKIAPAY_LIVE_PUBLIC_KEY");
            $private_key = env("KKIAPAY_LIVE_PRIVATE_KEY");
            $secret = env("KKIAPAY_LIVE_SECRET_KEY");
        }

        $kkiapay = new Kkiapay(
            $public_key,
            $private_key,
            $secret,
            $statut_sandbox // true en test
        );

        try {
            $transaction = $kkiapay->verifyTransaction($transactionId);

            if ($transaction->status === 'SUCCESS') {
                $reference = $request->reference;

                // mise à jour de la table paiement
                $getPaiement = Paiements::whereReference($reference)->first();
                $montant_payer = $getPaiement->montant_restant;
                // mise à jour du montant paye
                $paiement = Paiements::find($getPaiement->id);
                $paiement->setAttribute('num_transaction', $transactionId);
                $paiement->setAttribute('statut_transaction', $transactionId);
                $paiement->setAttribute('updated_at', date('Y-m-d H:i:s'));
                $paiement->setAttribute('id_modepaiement', 1);
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
                        'montant' => $paiement->montant_restant,
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
                        'montant' => $paiement->montant_restant,
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

                    //foreach ($liste_comptable as $comptable) {
                    $contenu = [
                        'name' => 'COMPTABLE',
                        'reference_paiement' => $reference,
                        'email_auditeur' => $paiement->email,
                        'nom_auditeur' => $auditeur->prenoms.' '.$auditeur->nom,
                        'subject' => 'NOUVEAU PAIEMENT ' . date('d-m-Y H:i:s'),
                        'email' => '',
                        'montant' => $paiement->montant_restant,
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
                        'montant' => $paiement->montant_restant,
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
            $dataResponse = [
                'reference' => $request->reference,
                'success' => false,
            ];

            return  ResponseHandler::success("Echec lors de l'enregistrement",$dataResponse);

        } catch (\Exception $e) {
            \Log::error('Erreur vérification KKiaPay', ['error' => $e->getMessage()]);

            $dataResponse = [
                'reference' => $request->reference,
                'success' => false,
            ];

            return  ResponseHandler::success("ok",$dataResponse);

        }
    }
}

