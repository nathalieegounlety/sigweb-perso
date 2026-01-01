<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Mail\NotificationComptable;
use App\Mail\Paiementattente;
use App\Models\Auditeurs;
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
}

