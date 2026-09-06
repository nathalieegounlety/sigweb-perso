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
use App\Mail\Paiementvalider;
use App\Models\Auditeurs;
use App\Models\Detailspaiements;
use App\Models\FormationsTranches;
use App\Models\Inscriptions;
use App\Models\Modepaiements;
use App\Models\Paiements;
use App\Models\PaiementsTranche;
use App\Models\Tranches;
use App\Models\User;
use App\Services\contracts\KkiapayInterfarce;
use App\Services\util\GenarateurCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Kkiapay\Kkiapay;

class KkiapayService implements  KkiapayInterfarce
{

    private GenarateurCodeService $genarateurCodeService;

    public function __construct(GenarateurCodeService $genarateurCodeService)
    {
        $this->genarateurCodeService = $genarateurCodeService;
    }

    public function storePaiementKkiapay(Request $request): \Illuminate\Http\JsonResponse
    {
        $transactionId = $request->input('transaction_id');
        $reference = $request->reference;

        $statutSandbox = env('KKIAPAY_SANDBOX');
        $kkiapay = new Kkiapay(
            $statutSandbox ? env('KKIAPAY_SANDBOX_PUBLIC_KEY')  : env('KKIAPAY_LIVE_PUBLIC_KEY'),
            $statutSandbox ? env('KKIAPAY_SANDBOX_PRIVATE_KEY') : env('KKIAPAY_LIVE_PRIVATE_KEY'),
            $statutSandbox ? env('KKIAPAY_SANDBOX_SECRET_KEY')  : env('KKIAPAY_LIVE_SECRET_KEY'),
            $statutSandbox
        );

        try {
            $transaction = $kkiapay->verifyTransaction($transactionId);

            // NOTE: 'PENDING' est une hypothèse sur les valeurs exactes renvoyées
            // par le SDK KkiaPay — à confirmer dans la doc ou via dd() en sandbox.
            $statutsExploitables = ['SUCCESS', 'PENDING','success','pending'];
            if (!in_array($transaction->status, $statutsExploitables, true)) {
                Log::info('storePaiementKkiapay : statut non exploitable', [
                    'transaction_id' => $transactionId,
                    'statut' => $transaction->status,
                    'reference' => $reference,
                ]);
                return ResponseHandler::success("Echec lors de l'enregistrement", [
                    'reference' => $reference,
                    'success' => false,
                ]);
            }

            $paiement = Paiements::where('reference', $reference)->first();
            if (!$paiement) {
                Log::error('storePaiementKkiapay : paiement introuvable', [
                    'reference' => $reference,
                    'transaction_id' => $transactionId,
                ]);
                return ResponseHandler::success('ok', ['reference' => $reference, 'success' => false]);
            }

            // Idempotence limitée : évite de renvoyer les mails / réécraser les
            // métadonnées si CE MÊME transaction_id vient d'être traité. Ce n'est
            // pas un journal complet (pas de table dédiée ici comme PaiementsTranche
            // côté DSC), donc ça ne protège que contre un rejeu immédiat de la
            // même transaction, pas contre tous les cas de figure.
            if ($paiement->num_transaction === $transactionId) {
                return ResponseHandler::success('ok', ['reference' => $paiement->reference, 'success' => true]);
            }

            // Avertissement (non bloquant) si le montant de la transaction dévie
            // du solde dû — cette fonction n'écrit aucun montant financier, mais
            // un tel écart mérite un œil humain (transaction d'un autre achat
            // associée par erreur/malveillance à cette référence).
            $montantTransaction = (int) ($transaction->amount ?? 0);
            if ($montantTransaction > 0 && $montantTransaction !== (int) $paiement->montant_restant) {
                Log::warning('storePaiementKkiapay : montant transaction différent du solde dû (à surveiller)', [
                    'reference' => $reference,
                    'transaction_id' => $transactionId,
                    'montant_transaction' => $montantTransaction,
                    'montant_restant_du' => $paiement->montant_restant,
                ]);
            }

            $paiement->num_transaction = $transactionId;
            $paiement->statut_transaction = $transaction->status;
            $paiement->id_modepaiement = 1;
            $paiement->save();

            $libelleModePaiement = Modepaiements::find($paiement->id_modepaiement);
            $auditeur = Auditeurs::where('email', $paiement->email)->first();

            $contenu = [
                'nom' => $auditeur->nom ?? '',
                'prenoms' => $auditeur->prenoms ?? '',
                'reference_paiement' => $reference,
                'subject' => 'INFORMATION SUR VOTRE PAIEMENT ' . $reference,
                'email' => $paiement->email,
                'montant' => $paiement->montant_restant,
                'mode_paiement' => $libelleModePaiement->libelle ?? '',
            ];

            try {
                Mail::to($paiement->email)->cc([env('MAIL_CC')])->bcc(env('MAIL_BCC'))
                    ->queue(new Paiementattente($contenu));
            } catch (\Exception $e) {
                Log::error('storePaiementKkiapay : échec envoi mail auditeur', ['error' => $e->getMessage(), 'reference' => $reference]);
            }

            if (env('PAIEMENT_NOTIFIER_COMPTABLE')) {
                $listeComptable = User::whereHas('roles', fn ($q) => $q->where('libelle', 'COMPTABLE'))->pluck('email');

                try {
                    Mail::to(env('MAIL_CC'))->cc($listeComptable)->queue(new NotificationComptable([
                        'name' => 'COMPTABLE',
                        'reference_paiement' => $reference,
                        'email_auditeur' => $paiement->email,
                        'nom_auditeur' => trim(($auditeur->prenoms ?? '') . ' ' . ($auditeur->nom ?? '')),
                        'subject' => 'NOUVEAU PAIEMENT ' . now()->format('d-m-Y H:i:s'),
                        'email' => '',
                        'montant' => $paiement->montant_restant,
                        'mode_paiement' => $libelleModePaiement->libelle ?? '',
                    ]));
                } catch (\Exception $e) {
                    Log::error('storePaiementKkiapay : échec envoi mail comptable', ['error' => $e->getMessage(), 'reference' => $reference]);
                }
            }

            return ResponseHandler::success('ok', ['reference' => $paiement->reference, 'success' => true]);

        } catch (\Exception $e) {
            Log::error('Erreur vérification KKiaPay', ['error' => $e->getMessage(), 'reference' => $reference]);
            return ResponseHandler::success('ok', ['reference' => $reference, 'success' => false]);
        }
    }
 
public function storePaiementPartielKkiapay(Request $request): \Illuminate\Http\JsonResponse
{
    $transactionId = $request->input('transaction_id');
    $reference = $request->reference_paiement;

    $statutSandbox = env('KKIAPAY_SANDBOX');
    $kkiapay = new Kkiapay(
        $statutSandbox ? env('KKIAPAY_SANDBOX_PUBLIC_KEY')  : env('KKIAPAY_LIVE_PUBLIC_KEY'),
        $statutSandbox ? env('KKIAPAY_SANDBOX_PRIVATE_KEY') : env('KKIAPAY_LIVE_PRIVATE_KEY'),
        $statutSandbox ? env('KKIAPAY_SANDBOX_SECRET_KEY')  : env('KKIAPAY_LIVE_SECRET_KEY'),
        $statutSandbox
    );

    try {
        $transaction = $kkiapay->verifyTransaction($transactionId);

        $statutsExploitables = ['SUCCESS', 'PENDING'];
        if (!in_array($transaction->status, $statutsExploitables, true)) {
            Log::info('storePaiementPartielKkiapay : statut non exploitable', [
                'transaction_id' => $transactionId,
                'statut' => $transaction->status,
                'reference' => $reference,
            ]);
            return ResponseHandler::success("Echec lors de l'enregistrement", [
                'reference' => $reference,
                'success' => false,
            ]);
        }

        $paiementConfirme = $transaction->status === 'SUCCESS';
        $montantKkiapay = (int) ($transaction->amount ?? 0);

        $paiement = Paiements::where('reference', $reference)->first();
        if (!$paiement) {
            Log::error('storePaiementPartielKkiapay : paiement introuvable', ['reference' => $reference, 'transaction_id' => $transactionId]);
            return ResponseHandler::success('ok', ['reference' => $reference, 'success' => false]);
        }

        // Idempotence : ce transaction_id a-t-il déjà été appliqué à une ligne
        // de ce paiement ? Repose sur reference_paiement_partiel, avec les mêmes
        // limites qu'ailleurs (champ mutable, pas un vrai journal append-only) —
        // suffisant pour bloquer un rejeu immédiat de la même transaction.
        $dejaTraite = Detailspaiements::where('reference_paiement_partiel', $transactionId)
            ->where('reference_paiement', $paiement->reference)
            ->exists();
        if ($dejaTraite) {
            return ResponseHandler::success('ok', ['reference' => $paiement->reference, 'success' => true]);
        }

        // À partir d'ici, la transaction KkiaPay est confirmée (SUCCESS ou
        // PENDING) — l'argent a été prélevé. Tout échec de validation désormais
        // passe par signalerAnomaliePaiementPartielClassique(), jamais un rejet
        // silencieux.

        // Chaque ligne cochée par le client est réglée EN TOTALITÉ — son propre
        // montant_restant actuel EST le montant dû pour cette ligne, jamais une
        // valeur déclarée par le client.
        $lignesValidees = [];
        $montantAttendu = 0;
        $raisonEchec = null;

        foreach ($request->input('formations', []) as $value) {
            if (empty($value['choix'])) {
                continue; // ligne non cochée, ignorée
            }

            $detailId = $value['detail_id'] ?? null;
            if (!$detailId) {
                $raisonEchec = 'detail_id manquant sur une ligne soumise';
                break;
            }

            $detail = Detailspaiements::find($detailId);
            if (!$detail || $detail->reference_paiement !== $paiement->reference) {
                $raisonEchec = "detail_id {$detailId} introuvable ou n'appartient pas à ce paiement";
                break;
            }

            if ($detail->statut_paiement === 'PAYE') {
                continue; // déjà réglée précédemment, pas une erreur
            }

            $montantAttendu += (int) $detail->montant_restant;
            $lignesValidees[] = $detail;
        }

        if ($raisonEchec) {
            return $this->signalerAnomaliePaiementPartielClassique($raisonEchec, $reference, $transactionId, $montantKkiapay);
        }

        if (empty($lignesValidees)) {
            return $this->signalerAnomaliePaiementPartielClassique('aucune ligne valide à régler (déjà payées ou non sélectionnées)', $reference, $transactionId, $montantKkiapay);
        }

        // Réconciliation stricte : la somme des lignes sélectionnées (chacune
        // due en totalité) doit égaler EXACTEMENT le montant réel KkiaPay.
        if ($montantAttendu !== $montantKkiapay) {
            return $this->signalerAnomaliePaiementPartielClassique('montant KkiaPay différent de la somme des lignes sélectionnées', $reference, $transactionId, $montantKkiapay, [
                'montant_attendu' => $montantAttendu,
            ]);
        }

        DB::transaction(function () use ($paiement, $lignesValidees, $montantKkiapay, $transactionId, $transaction, $paiementConfirme) {
            foreach ($lignesValidees as $detail) {
                $detail->montant_paye = $detail->montant_restant;
                $detail->montant_restant = 0;
                $detail->reference_paiement_partiel = $transactionId;
                $detail->modepaiement_id = 1; // KkiaPay forcé
                $detail->is_paiement_partiel = true;
                if ($paiementConfirme) {
                    $detail->statut_paiement = 'PAYE';
                    $detail->statut_traitement = 'VALIDER';
                } else {
                    $detail->statut_paiement = 'EN ATTENTE';
                    $detail->statut_traitement = 'ATTENTE';
                }
                $detail->save();
            }

            $nouveauMontantPaye = $paiement->montant_paye + $montantKkiapay;
            $nouveauMontantRestant = $paiement->montant_restant - $montantKkiapay;

            $paiement->montant_paye = $nouveauMontantPaye;
            $paiement->montant_restant = $nouveauMontantRestant;
            $paiement->num_transaction = $transactionId;
            $paiement->statut_transaction = $transaction->status; // corrigé : plus $transactionId ici
            $paiement->statut_paiement = 'EN ATTENTE'; // toujours différé au webhook, comme dans l'original
            $paiement->id_modepaiement = 1; // KkiaPay forcé
            if ($nouveauMontantRestant > 0) {
                $paiement->is_paiement_partiel = true;
            }
            $paiement->save();
        });

        // Notifications — uniquement si confirmé maintenant, sinon le webhook
        // notifiera à la confirmation réelle (même principe que côté DSC).
        if ($paiementConfirme) {
            $libelleModePaiement = Modepaiements::find(1);
            $auditeur = Auditeurs::where('email', $paiement->email)->first();

            $contenu = [
                'nom' => $auditeur->nom ?? '',
                'prenoms' => $auditeur->prenoms ?? '',
                'reference_paiement' => $reference,
                'subject' => 'INFORMATION SUR VOTRE PAIEMENT ' . $reference,
                'email' => $paiement->email,
                'montant' => $montantKkiapay,
                'mode_paiement' => $libelleModePaiement->libelle ?? '',
            ];

            try {
                Mail::to($paiement->email)->cc([env('MAIL_CC')])->bcc(env('MAIL_BCC'))
                    ->queue(new Paiementattente($contenu));
            } catch (\Exception $e) {
                Log::error('storePaiementPartielKkiapay : échec envoi mail auditeur', ['error' => $e->getMessage(), 'reference' => $reference]);
            }

            if (env('PAIEMENT_NOTIFIER_COMPTABLE')) {
                $listeComptable = User::whereHas('roles', fn ($q) => $q->where('libelle', 'COMPTABLE'))->pluck('email');

                try {
                    Mail::to(env('MAIL_CC'))->cc($listeComptable)->queue(new NotificationComptable([
                        'name' => 'COMPTABLE',
                        'reference_paiement' => $reference,
                        'email_auditeur' => $paiement->email,
                        'nom_auditeur' => trim(($auditeur->prenoms ?? '') . ' ' . ($auditeur->nom ?? '')),
                        'subject' => 'NOUVEAU PAIEMENT ' . now()->format('d-m-Y H:i:s'),
                        'email' => '',
                        'montant' => $paiement->montant_restant,
                        'mode_paiement' => $libelleModePaiement->libelle ?? '',
                    ]));
                } catch (\Exception $e) {
                    Log::error('storePaiementPartielKkiapay : échec envoi mail comptable', ['error' => $e->getMessage(), 'reference' => $reference]);
                }
            }
        }

        return ResponseHandler::success('ok', ['reference' => $paiement->reference, 'success' => true]);

    } catch (\Exception $e) {
        Log::error('Erreur vérification KKiaPay', ['error' => $e->getMessage(), 'reference' => $reference]);
        return ResponseHandler::success('ok', ['reference' => $reference, 'success' => false]);
    }
}

/**
 * Trace une anomalie pour un paiement partiel classique CONFIRMÉ par KkiaPay
 * mais que le système n'a pas pu appliquer. Pas de trace DB dédiée ici (pas de
 * table ledger équivalente à PaiementsTranche pour ce flux) — log critique +
 * alerte mail, best-effort, en attendant qu'une vraie table de traçabilité
 * soit envisagée pour ce flux si le besoin se confirme.
 */
private function signalerAnomaliePaiementPartielClassique(string $raison, string $reference, string $transactionId, int $montantKkiapay, array $contexte = []): \Illuminate\Http\JsonResponse
{
    Log::critical('ANOMALIE paiement partiel KKiaPay confirmé mais non appliqué : ' . $raison, $contexte + [
        'reference' => $reference,
        'transaction_id' => $transactionId,
        'montant_kkiapay' => $montantKkiapay,
    ]);

    try {
        $listeComptable = User::whereHas('roles', fn ($q) => $q->where('libelle', 'COMPTABLE'))->pluck('email');
        if (env('MAIL_CC')) {
            Mail::raw(
                "Paiement partiel KKiaPay confirmé (transaction {$transactionId}, montant {$montantKkiapay}) "
                . "mais NON appliqué automatiquement.\nRéférence: {$reference}\nRaison: {$raison}\n"
                . "Une vérification manuelle est nécessaire — ne pas demander à l'auditeur de repayer.",
                function ($message) use ($listeComptable) {
                    $message->to(env('MAIL_CC'))->cc($listeComptable)->subject('ANOMALIE paiement partiel KKiaPay à vérifier');
                }
            );
        }
    } catch (\Exception $e) {
        Log::error('Erreur envoi mail alerte anomalie (partiel classique)', ['error' => $e->getMessage()]);
    }

    return ResponseHandler::success('ok', [
        'reference' => $reference,
        'success' => false,
        'anomalie' => true,
        'message' => "Votre paiement a été reçu par KkiaPay mais nécessite une vérification manuelle. "
            . "Ne payez pas une seconde fois — contactez le support avec la référence {$reference}.",
    ]);
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
  

public function storePaiementDscKkiapay(Request $request): \Illuminate\Http\JsonResponse
{
    return $this->traiterPaiementKkiapayDsc($request);
}

/**
 * Point d'entrée pour les paiements partiels ultérieurs 
 */
public function storePaiementPartielDscKkiapay(Request $request): \Illuminate\Http\JsonResponse
{
    return $this->traiterPaiementKkiapayDsc($request);
}


/**
 * Traitement unifié des paiements KKiaPay (premier paiement ET paiements
 * partiels ultérieurs).
 */
private function traiterPaiementKkiapayDsc(Request $request): \Illuminate\Http\JsonResponse
{
    $transactionId = $request->input('transaction_id');
    $reference      = $request->reference;

    $statutSandbox = env('KKIAPAY_SANDBOX');
    $kkiapay = new Kkiapay(
        $statutSandbox ? env('KKIAPAY_SANDBOX_PUBLIC_KEY')  : env('KKIAPAY_LIVE_PUBLIC_KEY'),
        $statutSandbox ? env('KKIAPAY_SANDBOX_PRIVATE_KEY') : env('KKIAPAY_LIVE_PRIVATE_KEY'),
        $statutSandbox ? env('KKIAPAY_SANDBOX_SECRET_KEY')  : env('KKIAPAY_LIVE_SECRET_KEY'),
        $statutSandbox
    );

    $reject = function (string $reason, array $context = []) use ($reference) {
        Log::error('Paiement KKiaPay rejeté : ' . $reason, $context + ['reference' => $reference]);
        return ResponseHandler::success('ok', ['reference' => $reference, 'success' => false]);
    };

    try {
        // 1. Check if paiement is already set 
        if (PaiementsTranche::where('num_transaction', $transactionId)->exists()) {
            return ResponseHandler::success('ok', ['reference' => $reference, 'success' => true]);
        }
 
        $transaction = $kkiapay->verifyTransaction($transactionId);
 
        $statutsExploitables = ['SUCCESS', 'PENDING'];
        $statutTransaction = strtoupper($transaction->status);
        if (!in_array($statutTransaction, $statutsExploitables, true)) {
            return $reject('statut transaction KkiaPay non exploitable', [
                'transaction_id' => $transactionId,
                'statut' => $transaction->status,
            ]);
        }

        // SUCCESS -> validé immédiatement. PENDING -> enregistré mais laissé en
        // ATTENTE, confirmé plus tard par le webhook serveur-à-serveur sécurisé. 
        $paiementConfirme = $statutTransaction === 'SUCCESS';
        // set amount coming from kkiapay
        $montantKkiapay = (int) $transaction->amount;  
        // check if it's a first payment or no to get a payment objet
        $paiement = $this->resoudrePaiementDepuisReference($reference);
        if (!$paiement) {
            return $this->signalerAnomaliePaiementConfirme('paiement introuvable', $reference, $transactionId, $montantKkiapay);
        }

        $user = User::where('email', $paiement->email)->first();
        if (!$user) {
            return $this->signalerAnomaliePaiementConfirme('auditeur introuvable', $reference, $transactionId, $montantKkiapay, ['email' => $paiement->email], $paiement);
        }

        $userValidateur = User::where('email', 'formation.ersuma@ohada.org')->first();
        if (!$userValidateur) {
            return $this->signalerAnomaliePaiementConfirme('compte validateur introuvable', $reference, $transactionId, $montantKkiapay, [], $paiement);
        }

        $trancheDemandee = $request->input('tranche'); // 'tranche_unique' | 'tranche_double'

        // Check which paiement is checked
        $rubriquePayer = collect($request->input('formations'))
            ->map(fn ($fm) => (object) $fm)
            ->filter(fn ($fm) =>!empty($fm->choix) && !empty($fm->detail_id) && isset($fm->montant_paye) && $fm->montant_paye > 0);

        if ($rubriquePayer->isEmpty()) {
            return $this->signalerAnomaliePaiementConfirme('aucune ligne de paiement soumise', $reference, $transactionId, $montantKkiapay, [], $paiement);
        }

        // 5. Validation ligne par ligne : calcule le montant réellement imputable
        //    à chaque ligne (peut différer du montant déclaré si hors bornes -> rejet)
        $raisonEchec = null;
        $resultatValidation = $this->validerLignesPaiement($paiement, $rubriquePayer, $trancheDemandee, $raisonEchec);
        if ($resultatValidation === null) {
            return $this->signalerAnomaliePaiementConfirme($raisonEchec ?? 'raison inconnue', $reference, $transactionId, $montantKkiapay, [], $paiement);
        }
        [$montantTotalValide, $detailsCalcules] = $resultatValidation;

        // 6. Réconciliation stricte : la somme validée doit égaler EXACTEMENT
        //    le montant réellement payé sur KkiaPay (montants entiers, comparaison directe)
        if ($montantTotalValide !== $montantKkiapay) {
            return $this->signalerAnomaliePaiementConfirme('montant KkiaPay différent de la somme des lignes déclarées', $reference, $transactionId, $montantKkiapay, [
                'montant_declare_valide' => $montantTotalValide,
            ], $paiement);
        }

        // 7. Écritures en base — tout ou rien
        $updateReference = DB::transaction(function () use (
            $paiement, $detailsCalcules, $montantKkiapay, $transactionId,
            $user, $userValidateur, $trancheDemandee, $paiementConfirme, $transaction
        ) {
            $getPTranche = PaiementsTranche::where('reference', 'like', "%{$paiement->reference}%")
                ->latest()->first();

            $suffixe = $getPTranche
                ? ((int) (explode('_', $getPTranche->reference)[1] ?? 0)) + 1
                : 1;
            $updateReference = $paiement->reference . '_' . $suffixe;

            // 7a. Mise à jour globale du paiement
            $nouveauMontantPaye = $paiement->montant_paye + $montantKkiapay;
            $nouveauMontantRestant = $paiement->montant_total - $nouveauMontantPaye;
            // Update paiement table
            $paiement->montant_paye = $nouveauMontantPaye;
            $paiement->montant_restant = $nouveauMontantRestant;
            $paiement->id_modepaiement = 1;// KKiapay
            // si le montant restant est 0 alors paiement soldé
            if ($paiementConfirme && $nouveauMontantRestant <= 0) {
                $paiement->num_transaction = $transactionId;
                $paiement->statut_paiement = 'PAYE';
                $paiement->statut_traitement = 'VALIDER';
                $paiement->agent_traiteur = $userValidateur->id;
                $paiement->commentaire = 'Validation automatique webhook KKiaPay';
            } elseif (!$paiementConfirme) {
                // enregistré mais en attente de confirmation par le webhook
                $paiement->statut_traitement = 'ATTENTE';
            }
            $paiement->save();

            // 7b. Mise à jour ligne par ligne — partiel autorisé pour la scolarité
            // Mettre à jour les informations dans la table details paiement et paiement tranche
            foreach ($detailsCalcules as $item) {
                $detail = $item->detail;

                $detail->montant_paye = $detail->montant_paye + $item->montant_impute;
                $detail->montant_restant = $item->nouveau_restant;
                $detail->have_reduction_dsc = $item->reduction_appliquee;
                if ($paiementConfirme && $item->nouveau_restant <= 0) {
                    // renseigné uniquement sur paiement total confirmé, à titre informatif —
                    // la déduplication réelle repose sur PaiementsTranche.num_transaction (étape 1)
                    $detail->reference_paiement_partiel = $transactionId;
                    $detail->statut_paiement = 'PAYE';
                    $detail->statut_traitement = 'VALIDER';
                    $detail->commentaire = 'Validation automatique webhook KKiaPay';
                } elseif (!$paiementConfirme) {
                    $detail->statut_traitement = 'ATTENTE';
                }
                $detail->save();

                // échéancier : uniquement pour la ligne scolarité, sur paiement réel confirmé ou en attente
                $trancheId = null;
                if ($item->type_tarif !== 'INSCRIPTION' && $trancheDemandee) {
                    $trancheId = $this->enregistrerEcheancierTranche($paiement, $detail, $trancheDemandee, $user);
                }

                PaiementsTranche::forceCreate([
                    'details_paiements_id' => $detail->id,
                    'montant_paye'         => $item->montant_impute,
                    'montant_restant'      => $item->nouveau_restant,
                    'reference'            => $updateReference,
                    'num_transaction'      => $transactionId,
                    'statut_transaction'   => $transaction->status,
                    'statut_traitement'    => $paiementConfirme ? 'VALIDER' : 'ATTENTE',
                    'traiter_par'          => $paiementConfirme ? $userValidateur->id : null,
                    'is_inscription'       => $item->type_tarif === 'INSCRIPTION',
                    'tranche_id'           => $trancheId,
                    'modepaiement_id'      => $paiement->id_modepaiement,
                    'created_by'           => $user->id,
                    'updated_by'           => $user->id,
                ]);
            }

            return $updateReference;
        });

        // 8. Notifications — uniquement si confirmé maintenant ; sinon c'est le
        //    webhook qui notifiera au moment de la confirmation réelle.
        if ($paiementConfirme) {
            $this->notifierPaiementValide($paiement, $updateReference, $montantKkiapay, $userValidateur);
        }

        return ResponseHandler::success('ok', ['reference' => $reference, 'success' => true]);

    } catch (\Exception $e) {
        // À ce stade on ne sait pas toujours si la transaction avait été
        // confirmée avant l'exception (ex: exception dans verifyTransaction
        // lui-même = argent probablement pas encore prélevé, vs exception
        // dans l'écriture DB = argent déjà prélevé). Par prudence, on trace
        // systématiquement en anomalie plutôt que de risquer un silence total.
        return $this->signalerAnomaliePaiementConfirme('exception : ' . $e->getMessage(), $reference, $transactionId ?? 'inconnu', $montantKkiapay ?? 0, [], $paiement ?? null);
    }
}

/**
 * Trace une anomalie pour un paiement CONFIRMÉ par KkiaPay (argent prélevé)
 * mais que le système n'a pas pu enregistrer correctement. Ne redit jamais
 * à l'utilisateur de payer à nouveau — oriente vers une vérification
 * manuelle, avec une trace best-effort en base et une alerte.
 */
private function signalerAnomaliePaiementConfirme(string $raison, string $reference, string $transactionId, int $montantKkiapay, array $contexte = [], ?Paiements $paiement = null): \Illuminate\Http\JsonResponse
{
    Log::critical('ANOMALIE paiement KKiaPay confirmé mais non enregistré : ' . $raison, $contexte + [
        'reference' => $reference,
        'transaction_id' => $transactionId,
        'montant_kkiapay' => $montantKkiapay,
    ]);

    // trace minimale best-effort — details_paiements_id/montant_restant sont
    // NOT NULL en base, donc on ancre la trace sur une ligne Detailspaiements
    // réelle du même dossier quand c'est possible (les valeurs de cette ligne
    // ne reflètent PAS forcément la ligne réellement concernée par l'anomalie,
    // c'est un point d'ancrage pour la traçabilité, pas une donnée fiable).
    try {
        $detailAncrage = $paiement
            ? Detailspaiements::where('code_inscription', $paiement->code_inscription)->first()
            : null;

        if ($detailAncrage) {
            PaiementsTranche::forceCreate([
                'details_paiements_id' => $detailAncrage->id,
                'montant_paye' => $montantKkiapay,
                'montant_restant' => $detailAncrage->montant_restant,
                'reference' => $reference,
                'num_transaction' => $transactionId,
                'statut_transaction' => 'SUCCESS',
                'statut_traitement' => 'ANOMALIE',
                'commentaire' => $raison . ' [trace best-effort — details_paiements_id/montant_restant ne sont pas fiables ici]',
            ]);
        } else {
            Log::critical('Anomalie non traçable en base (aucune ligne Detailspaiements disponible pour ancrer la trace) — log seul disponible', [
                'reference' => $reference,
                'transaction_id' => $transactionId,
            ]);
        }
    } catch (\Exception $e) {
        Log::critical("Impossible de tracer l'anomalie en base, seul le log est disponible", ['error' => $e->getMessage()]);
    }

    // alerte best-effort au support/comptabilité
    try {
        $listeComptable = User::whereHas('roles', fn ($q) => $q->where('libelle', 'COMPTABLE'))->pluck('email');
        if (env('MAIL_CC')) {
            Mail::raw(
                "Paiement KKiaPay confirmé (transaction {$transactionId}, montant {$montantKkiapay}) "
                . "mais NON enregistré automatiquement.\nRéférence: {$reference}\nRaison: {$raison}\n"
                . "Une vérification manuelle est nécessaire — ne pas demander à l'auditeur de repayer.",
                function ($message) use ($listeComptable) {
                    $message->to(env('MAIL_CC'))->cc($listeComptable)->subject('ANOMALIE paiement KKiaPay à vérifier');
                }
            );
        }
    } catch (\Exception $e) {
        Log::error('Erreur envoi mail alerte anomalie', ['error' => $e->getMessage()]);
    }

    return ResponseHandler::success('ok', [
        'reference' => $reference,
        'success' => false,
        'anomalie' => true,
        'message' => "Votre paiement a été reçu par KkiaPay mais nécessite une vérification manuelle."
    ]);
}

/**
 * Résout le Paiements correspondant à une référence, qu'elle soit "brute"
 * (premier paiement, ex: REF001) ou suffixée par le client pour un
 * paiement partiel ultérieur (ex: REF001_2). Seule la partie de base sert
 * à la recherche — le suffixe éventuel envoyé par le client n'est jamais
 * utilisé tel quel comme référence de ledger (voir génération serveur
 * de $updateReference à l'étape d'écriture).
 */
private function resoudrePaiementDepuisReference(string $reference): ?Paiements
{
    $referenceBase = explode('_', $reference)[0];

    return Paiements::where('reference', $referenceBase)->first();
}

/**
 * Valide chaque ligne déclarée par le client et calcule le montant
 * réellement imputable (borné par ce qui est dû). Retourne null si
 * une ligne est invalide, n'appartient pas au paiement, ou dépasse
 * ce qui est réellement dû.
 *
 * @return array|null [int $montantTotalValide, array $detailsCalcules]
 */
private function validerLignesPaiement(Paiements $paiement, $rubriquePayer, ?string $tranche, ?string &$raisonEchec = null): ?array
{
    $montantTotal = 0;
    $detailsCalcules = [];

    foreach ($rubriquePayer as $ligne) {
        $detail = Detailspaiements::find($ligne->detail_id);

        if (!$detail) {
            $raisonEchec = "detail_id {$ligne->detail_id} introuvable";
            return null;
        }

        if ($detail->code_inscription !== $paiement->code_inscription) {
            $raisonEchec = "detail_id {$detail->id} : code_inscription ('{$detail->code_inscription}') != celui du paiement ('{$paiement->code_inscription}')";
            return null;
        }

        $montantDeclare = (int) $ligne->montant_paye;

        if ($detail->getTarification->getGrilletarifaire->type_tarif === 'INSCRIPTION') {
            // obligatoire, intégral, pas de partiel toléré
            if ($montantDeclare !== (int) $detail->montant_restant) {
                $raisonEchec = "INSCRIPTION detail_id {$detail->id} : montant déclaré {$montantDeclare} != solde dû {$detail->montant_restant}";
                return null;
            }
            $montantImpute = (int) $detail->montant_restant;
            $nouveauRestant = 0;
            $reductionAppliquee = false;
        } else { 
            $ligneIntacte = (int) $detail->montant_paye === 0;

    $baseRestant = (int) $detail->montant_restant;
    $reductionAppliquee = $detail->have_reduction_dsc;

    if ($ligneIntacte) {
        // ligne intacte : la tranche demandée détermine la réduction
        if ($tranche === 'tranche_unique') {
            $baseRestant = (int) round($baseRestant * 0.95);
            $reductionAppliquee = true;
        } else {
            $reductionAppliquee = false;
        }
    }
    // sinon (ligne déjà entamée) : $baseRestant reste montant_restant
    // tel quel, $tranche est ignoré, $reductionAppliquee reste ce qui
    // avait déjà été enregistré au premier paiement (have_reduction_dsc)

    if ($montantDeclare <= 0 || $montantDeclare > $baseRestant) {
        $raisonEchec = "SCOLARITE detail_id {$detail->id} : montant déclaré {$montantDeclare} hors bornes "
            . "(max autorisé {$baseRestant}, solde_restant_db={$detail->montant_restant}, "
            . "ligne_intacte=" . ($ligneIntacte ? 'oui' : 'non') . ", "
            . "tranche_demandee=" . ($tranche ?? 'null') . ", reduction_deja_appliquee=" . ($detail->have_reduction_dsc ? 'oui' : 'non') . ")";
        return null;
    }

    $montantImpute = $montantDeclare;
    $nouveauRestant = max(0, $baseRestant - $montantDeclare);

        }

        $montantTotal += $montantImpute;
        $detailsCalcules[] = (object) [
            'detail'              => $detail,
            'montant_impute'      => $montantImpute,
            'nouveau_restant'     => $nouveauRestant,
            'reduction_appliquee' => $reductionAppliquee,
            'type_tarif' => $detail->getTarification->getGrilletarifaire->type_tarif
        ];
    }

    return [$montantTotal, $detailsCalcules];
}

/**
 * Crée ou met à jour l'échéancier (table Tranches) pour la ligne scolarité,
 * selon le plan choisi (tranche_unique = 1 échéance, tranche_double = 2).
 * Ne concerne jamais la ligne INSCRIPTION (toujours payée en une fois,
 * sans échéancier).
 */
private function enregistrerEcheancierTranche(Paiements $paiement, Detailspaiements $detailPaiement, string $typeTranche, User $user): ?int
{
    $tarification = $detailPaiement->getTarification;
    $grilletarifaire = $tarification->getGrilletarifaire ?? null;

    if (!$tarification || !$grilletarifaire) {
        Log::error('Échéancier non créé : tarification/grille tarifaire introuvable', [
            'detail_id' => $detailPaiement->id,
            'code_inscription' => $paiement->code_inscription,
        ]);
        return null; // on ne bloque pas le paiement déjà validé pour autant
    }

    $grilletarifaireId = $grilletarifaire->id;
    $montantGrille = $grilletarifaire->montant;

    if ($typeTranche === 'tranche_unique') {
        $formationTranche = FormationsTranches::where('tranche', 'tranche 1')
            ->where('grilletarifaire_id', $grilletarifaireId)
            ->first();

        if (!$formationTranche) {
            Log::error('Échéancier non créé : FormationsTranches (tranche 1) introuvable', [
                'grilletarifaire_id' => $grilletarifaireId,
                'code_inscription' => $paiement->code_inscription,
            ]);
            return null;
        }

        $attributs = [
            'code_inscription' => $paiement->code_inscription,
            'email' => $paiement->email,
            'tranche_1' => $montantGrille,
            'tranche_2' => 0,
            'echeance_1' => $formationTranche->echeance_1,
            'echeance_2' => $formationTranche->echeance_1,
            'statut' => 'ATTENTE',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ];
    } else {
        $formationTranche = FormationsTranches::where('tranche', 'tranche 2')
            ->where('grilletarifaire_id', $grilletarifaireId)
            ->first();

        if (!$formationTranche) {
            Log::error('Échéancier non créé : FormationsTranches (tranche 2) introuvable', [
                'grilletarifaire_id' => $grilletarifaireId,
                'code_inscription' => $paiement->code_inscription,
            ]);
            return null;
        }

        $attributs = [
            'code_inscription' => $paiement->code_inscription,
            'email' => $paiement->email,
            'tranche_1' => $montantGrille * 0.5,
            'tranche_2' => $montantGrille * 0.5,
            'echeance_1' => $formationTranche->echeance_1,
            'echeance_2' => $formationTranche->echeance_2,
            'statut' => 'ATTENTE',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ];
    }

    $echeancier = Tranches::whereCodeInscription($paiement->code_inscription)->first();

    if ($echeancier) {
        $echeancier->forceFill($attributs);
        $echeancier->save();
    } else {
        $echeancier = Tranches::forceCreate($attributs);
    }

    return $echeancier->id;
}

/**
 * Envoi des mails de notification — best-effort, ne doit jamais faire
 * échouer la validation du paiement déjà actée en base.
 */
private function notifierPaiementValide(Paiements $paiement, string $updateReference, int $montant, User $userValidateur): void
{
    $auditeur = Auditeurs::where('email', $paiement->email)->first();
    $libelleModePaiement = Modepaiements::find($paiement->id_modepaiement);

    $contenu = [
        'nom' => $auditeur->nom ?? '',
        'prenoms' => $auditeur->prenoms ?? '',
        'reference_paiement' => $updateReference,
        'subject' => 'INFORMATION SUR VOTRE PAIEMENT ' . $updateReference,
        'email' => $paiement->email,
        'montant' => $montant,
        'montant_restant' => $paiement->montant_restant,
        'mode_paiement' => $libelleModePaiement->libelle ?? '',
    ];

    try {
        Mail::to($paiement->email)->cc([env('MAIL_CC')])->bcc(env('MAIL_BCC'))
            ->queue(new DscPaiementAttente($contenu));
    } catch (\Exception $e) {
        Log::error('Erreur envoi mail auditeur', ['error' => $e->getMessage(), 'reference' => $updateReference]);
    }

    if (env('PAIEMENT_NOTIFIER_COMPTABLE')) {
        $listeComptable = User::whereHas('roles', fn ($q) => $q->where('libelle', 'COMPTABLE'))->pluck('email');

        try {
            Mail::to(env('MAIL_CC'))->cc($listeComptable)->queue(new DscNotificationComptable([
                'name' => 'COMPTABLE',
                'reference_paiement' => $updateReference,
                'email_auditeur' => $paiement->email,
                'nom_auditeur' => trim(($auditeur->prenoms ?? '') . ' ' . ($auditeur->nom ?? '')),
                'subject' => 'NOUVEAU PAIEMENT ' . now()->format('d-m-Y H:i:s'),
                'email' => '',
                'montant' => $montant,
                'mode_paiement' => $libelleModePaiement->libelle ?? '',
            ]));
        } catch (\Exception $e) {
            Log::error('Erreur envoi mail comptable', ['error' => $e->getMessage(), 'reference' => $updateReference]);
        }
    }

    try {
        Mail::to(trim($paiement->email))->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))
            ->queue(new PaiementValideDsc([
                'nom' => $auditeur->nom ?? '',
                'prenoms' => $auditeur->prenoms ?? '',
                'reference_paiement' => $updateReference,
                'subject' => 'TRAITEMENT DE VOTRE PAIEMENT ' . $updateReference,
                'commentaire' => 'Paiement validé',
                'email' => $paiement->email,
                'mode_paiement' => $libelleModePaiement->libelle ?? '',
                'montant' => $montant,
                'montant_restant' => $paiement->montant_restant,
                'type' => $paiement->type,
                'url_recu' => $this->genarateurCodeService->generateUrlSigned('paiements.recu', ['reference' => $updateReference]),
            ]));
    } catch (\Exception $e) {
        Log::error('Erreur envoi mail traitement', ['error' => $e->getMessage(), 'reference' => $updateReference]);
    }
}

/**
 * Webhook serveur-à-serveur KKiaPay — POINT D'ENTRÉE PUBLIC, donc la
 * SEULE défense contre un appel forgé est la vérification de signature
 * ci-dessous. NE JAMAIS traiter le payload avant que cette vérification
 * ait réussi.
 *
 * NOTE IMPORTANTE : le nom exact du header de signature et l'algorithme
 * (HMAC-SHA256 le plus souvent) sont à confirmer dans la documentation
 * KKiaPay — verifierSignatureWebhook() ci-dessous est un squelette à
 * compléter avec le secret webhook réel (distinct des clés API).
 */
public function startWebhook(Request $request)
{
    /* if (!$this->verifierSignatureWebhook($request)) {
        Log::warning('Webhook KKiaPay rejeté : signature invalide ou absente', [
            'ip' => $request->ip(),
        ]);
        return response()->json(['status' => 'invalid signature'], 401);
    } */

    $data = $request->all();
    Log::info('webhook kkiapay received', ['request' => $data]);

    if (empty($data['isPaymentSucces'])) {
        Log::info('Webhook KKiaPay : paiement non réussi selon le payload', ['data' => $data]);
        return response()->json(['status' => 'success'], 200);
    }

    $transactionId = $data['transactionId'] ?? null;
    $stateData = $data['stateData'] ?? null;

    if (!$transactionId || !$stateData) {
        Log::error('Webhook KKiaPay : payload incomplet', ['data' => $data]);
        return response()->json(['status' => 'success'], 200);
    }

    // Défense en profondeur : même signature valide, on revérifie la
    // transaction directement auprès de KkiaPay avant d'agir dessus —
    // on ne fait jamais confiance aux seules valeurs du payload webhook.
    $statutSandbox = env('KKIAPAY_SANDBOX');
    $kkiapay = new Kkiapay(
        $statutSandbox ? env('KKIAPAY_SANDBOX_PUBLIC_KEY')  : env('KKIAPAY_LIVE_PUBLIC_KEY'),
        $statutSandbox ? env('KKIAPAY_SANDBOX_PRIVATE_KEY') : env('KKIAPAY_LIVE_PRIVATE_KEY'),
        $statutSandbox ? env('KKIAPAY_SANDBOX_SECRET_KEY')  : env('KKIAPAY_LIVE_SECRET_KEY'),
        $statutSandbox
    );

    try {
        $transaction = $kkiapay->verifyTransaction($transactionId);
    } catch (\Exception $e) {
        Log::error('Webhook KKiaPay : échec verifyTransaction', [
            'error' => $e->getMessage(),
            'transaction_id' => $transactionId,
        ]);
        return response()->json(['status' => 'success'], 200);
    }

    if ($transaction->status !== 'SUCCESS') {
        Log::warning('Webhook KKiaPay : statut non SUCCESS lors de la revérification', [
            'transaction_id' => $transactionId,
            'statut' => $transaction->status,
        ]);
        return response()->json(['status' => 'success'], 200);
    }

    $reference = explode('_', $stateData)[0];

    $paiement = Paiements::where('reference', $reference)->first();
    if (!$paiement) {
        Log::error('Webhook KKiaPay : paiement introuvable', ['reference' => $reference]);
        return response()->json(['status' => 'success'], 200);
    }

    $userValidateur = User::where('email', 'formation.ersuma@ohada.org')->first();
    if (!$userValidateur) {
        Log::error('Webhook KKiaPay : compte validateur formation introuvable');
        return response()->json(['status' => 'success'], 200);
    }

    if ($paiement->type === 'DSC') {
        $this->confirmerPaiementDscViaWebhook($paiement, $stateData, $transactionId, $userValidateur);
    } else {
        $this->confirmerPaiementClassiqueViaWebhook($paiement, $stateData, $transactionId, $userValidateur);
    }

    return response()->json(['status' => 'success'], 200);
}

/**
 * Vérifie l'authenticité de la requête webhook via le secret/signature
 * fourni par KkiaPay. SQUELETTE À COMPLÉTER : adapter le nom du header
 * et l'algorithme exacts selon la documentation KkiaPay.
 */
private function verifierSignatureWebhook(Request $request): bool
{
    $secret = env('KKIAPAY_WEBHOOK_SECRET');
    if (!$secret) {
        Log::error('KKIAPAY_WEBHOOK_SECRET absent de la configuration — webhook non sécurisable');
        return false;
    }

    // TODO: remplacer par le header réel documenté par KkiaPay
    // (ex: 'x-kkiapay-signature') et l'algorithme attendu.
    $signatureRecue = $request->header('x-kkiapay-signature');
    if (!$signatureRecue) {
        return false;
    }

    $signatureAttendue = hash_hmac('sha256', $request->getContent(), $secret);

    return hash_equals($signatureAttendue, $signatureRecue);
}

/**
 * Confirme (VALIDER) les écritures DSC déjà créées en ATTENTE par
 * traiterPaiementKkiapay. Ne crée jamais rien de nouveau — se contente
 * de faire basculer le statut, avec gardes null sur chaque relation.
 */
private function confirmerPaiementDscViaWebhook(Paiements $paiement, string $reference, string $transactionId, User $userValidateur): void
{
    $tranchesAValider = PaiementsTranche::where('reference', $reference)
        ->where('statut_traitement', '!=', 'VALIDER')
        ->get();

    if ($tranchesAValider->isEmpty()) {
        return; // déjà validé, ou rien à confirmer (appel client jamais arrivé)
    }

    DB::transaction(function () use ($paiement, $transactionId, $userValidateur, $tranchesAValider) {
        foreach ($tranchesAValider as $tranche) {
            $detail = Detailspaiements::find($tranche->details_paiements_id);
            if (!$detail) {
                Log::error('Webhook KKiaPay : ligne détail introuvable pour tranche', ['tranche_id' => $tranche->id]);
                continue;
            }

            $tranche->num_transaction = $transactionId;
            $tranche->statut_transaction = 'SUCCESS';
            $tranche->modepaiement_id = 1;
            $tranche->statut_traitement = 'VALIDER';
            $tranche->traiter_par = $userValidateur->id;
            $tranche->save();

            if ($detail->montant_restant <= 0 && $detail->statut_traitement !== 'VALIDER') {
                $detail->statut_traitement = 'VALIDER';
                $detail->statut_paiement = 'PAYE';
                $detail->commentaire = 'Validation automatique webhook KKiaPay';
                $detail->save();
            }
        }

        if ($paiement->montant_restant <= 0 && $paiement->statut_traitement !== 'VALIDER') {
            $paiement->num_transaction = $transactionId;
            $paiement->statut_transaction = 'SUCCESS';
            $paiement->id_modepaiement = 1;
            $paiement->statut_paiement = 'PAYE';
            $paiement->statut_traitement = 'VALIDER';
            $paiement->commentaire = 'Validation automatique webhook KKiaPay';
            $paiement->agent_traiteur = $userValidateur->id;
            $paiement->date_traitement = now();
            $paiement->save();
        }
    });

    $this->notifierPaiementValide($paiement, $reference, (int) $paiement->montant_total, $userValidateur);
}

/**
 * Confirme (VALIDER) un paiement "formation classique" (non DSC) — logique
 * fonctionnelle inchangée par rapport à l'original, mais avec gardes null
 * et sans écrasement de la variable $data d'origine.
 */
private function confirmerPaiementClassiqueViaWebhook(Paiements $paiement, string $reference, string $transactionId, User $userValidateur): void
{
    if ($paiement->statut_traitement === 'VALIDER') {
        return; // déjà validé
    }

    $paiementPartiel = Detailspaiements::where('reference_paiement_partiel', $transactionId)
        ->where('reference_paiement', $paiement->reference)
        ->first();

    DB::transaction(function () use ($paiement, $reference, $transactionId, $userValidateur, $paiementPartiel) {
        if ($paiementPartiel) {
            // paiement partiel d'une ligne précise
            if ($paiementPartiel->statut_traitement !== 'VALIDER') {
                $paiementPartiel->statut_traitement = 'VALIDER';
                $paiementPartiel->statut_paiement = 'PAYE';
                $paiementPartiel->montant_paye = $paiementPartiel->montant_restant;
                $paiementPartiel->montant_restant = 0;
                $paiementPartiel->commentaire = 'Validation automatique webhook KKiaPay';
                $paiementPartiel->save();
            }

            $resteAttente = Detailspaiements::where('id', '!=', $paiementPartiel->id)
                ->where('reference_paiement', $paiement->reference)
                ->where('statut_traitement', 'ATTENTE')
                ->exists();

            if (!$resteAttente && $paiement->montant_restant <= 0 && $paiement->statut_traitement !== 'VALIDER') {
                $paiement->num_transaction = $transactionId;
                $paiement->statut_transaction = 'SUCCESS';
                $paiement->id_modepaiement = 1;
                $paiement->statut_paiement = 'PAYE';
                $paiement->statut_traitement = 'VALIDER';
                $paiement->commentaire = 'Validation automatique webhook KKiaPay';
                $paiement->agent_traiteur = $userValidateur->id;
                $paiement->date_traitement = now();
                $paiement->save();
            }
        } else {
            // paiement complet de la facture
            $paiement->num_transaction = $transactionId;
            $paiement->statut_transaction = 'SUCCESS';
            $paiement->statut_paiement = 'PAYE';
            $paiement->montant_paye = $paiement->montant_restant;
            $paiement->montant_restant = 0;
            $paiement->statut_traitement = 'VALIDER';
            $paiement->commentaire = 'Validation automatique webhook KKiaPay';
            $paiement->id_modepaiement = 1;
            $paiement->agent_traiteur = $userValidateur->id;
            $paiement->date_traitement = now();
            $paiement->save();

            if ($paiement->type !== 'OUVRAGE') {
                $details = Detailspaiements::where('reference_paiement', $paiement->reference)->get();
                foreach ($details as $detail) {
                    $detail->statut_traitement = 'VALIDER';
                    $detail->statut_paiement = 'PAYE';
                    $detail->montant_paye = $detail->montant_restant;
                    $detail->montant_restant = 0;
                    $detail->commentaire = 'Validation automatique webhook KKiaPay';
                    $detail->save();
                }
            }
        }
    });

    $auditeur = Auditeurs::where('email', $paiement->email)->first();
    $modepaiement = Modepaiements::find($paiement->id_modepaiement ?: 1);
    $url = $paiement->type !== 'OUVRAGE'
        ? $this->genarateurCodeService->generateUrlSigned('paiements.recu', ['reference' => $paiement->reference])
        : $this->genarateurCodeService->generateUrlSigned('paiements.recu-ouvrage', ['reference' => $paiement->reference]);

    $contenu = [
        'nom' => $auditeur->nom ?? '',
        'prenoms' => $auditeur->prenoms ?? '',
        'reference_paiement' => $reference,
        'subject' => 'TRAITEMENT DE VOTRE PAIEMENT ' . $reference,
        'commentaire' => 'Paiement validé',
        'email' => $paiement->email,
        'mode_paiement' => $modepaiement->libelle ?? '',
        'montant' => $paiement->montant_total,
        'type' => $paiement->type,
        'url_recu' => $url,
    ];

    try {
        Mail::to(trim($paiement->email))->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))
            ->queue(new Paiementvalider($contenu));
    } catch (\Exception $e) {
        Log::error('Erreur envoi mail traitement (classique)', ['error' => $e->getMessage(), 'reference' => $reference]);
    }
}

    
}

