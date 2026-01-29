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
use App\Services\contracts\FedapayInterfarce;
use FedaPay\FedaPay;
use FedaPay\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FedapayService implements  FedapayInterfarce
{

    public function __construct()
    {
        FedaPay::setApiKey(config('fedapay.secret_key'));
        FedaPay::setEnvironment(config('fedapay.environment'));
    }

    public function storePaiementFedapay(Request $request): \Illuminate\Http\JsonResponse
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
        // TODO: Implement storePaiementPartielKkiapay() method.


        try {
            // récupérer le statut de la transaction côté serveur
            $data_transaction = Transaction::retrieve($request->transaction_id);

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
                    $paiement->setAttribute('num_transaction', $data_transaction->id);
                    $paiement->setAttribute('statut_transaction', $data_transaction->status);

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
                    'reference' => $paiement->reference_paiement,
                    'success' => true,
                ];

                return  ResponseHandler::success("ok",$dataResponse);


        } catch (\Exception $e) {
            \Log::error('Erreur vérification KKiaPay', ['error' => $e->getMessage()]);

            $dataResponse = [
                'reference' => $request->reference_paiement,
                'success' => false,
            ];

            return  ResponseHandler::success("ok",$dataResponse);

        }
    }

    public function storePaiementOuvrageFedapay(Request $request): \Illuminate\Http\JsonResponse
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
}

