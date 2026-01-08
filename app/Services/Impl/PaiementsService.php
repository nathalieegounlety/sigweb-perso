<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Mail\NotificationComptable;
use App\Mail\Paiementattente;
use App\Models\Auditeurs;
use App\Models\Detailspaiements;
use App\Models\Modepaiements;
use App\Models\Paiements;
use App\Models\User;
use App\Services\contracts\PaiementsInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaiementsService implements PaiementsInterface
{

    public function getInfosPaiement(string $reference): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getInfosPaiement() method.
        $paiement = Paiements::leftjoin('modepaiements', 'modepaiements.id', '=', 'paiements.id_modepaiement')
            ->whereReference($reference)
            ->get(['paiements.id','num_transaction','is_paiementpartiel','reference','montant_total','montant_paye','montant_restant','email','code_inscription','paiements.id_modepaiement','modepaiements.libelle'])
            ->first();

        if($paiement){
            $auditeur = Auditeurs::whereEmail($paiement->email)->first();
            $dataResponse = [
                'paiement' => $paiement,
                'auditeur' => $auditeur,
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
        $auditeur = Auditeurs::whereEmail($paiement->email)->select('auditeurs.id','auditeurs.nom','auditeurs.prenoms','auditeurs.email')->first();
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

        $dataResponse = [
            'liste_modepaiements' => $liste_modepaiements,
            'detail_paiement' => $detailPaiement,
            'auditeur' => $auditeur,
            'paiement' => $paiement,
            'havePaiement' => $havePaiement
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
                'reference_paiement' => $paiement->reference_paiement,
                'subject' => 'INFORMATION SUR VOTRE PAIEMENT '.$paiement->reference_paiement,
                'email' => $paiement->email,
                'montant' => $request->montant_total,
                'mode_paiement' => $mode_paiement->libelle
            ];
        } else {
            # code...
            $contenu = [
                'nom' => "",
                'prenoms' => "",
                'reference_paiement' => $paiement->reference_paiement,
                'subject' => 'INFORMATION SUR VOTRE PAIEMENT '.$paiement->reference_paiement,
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


}
