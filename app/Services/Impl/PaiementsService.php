<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Auditeurs;
use App\Models\Paiements;
use App\Services\contracts\PaiementsInterface;
use Illuminate\Http\Request;

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


}
