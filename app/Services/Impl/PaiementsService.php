<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Auditeurs;
use App\Models\Paiements;
use App\Services\contracts\PaiementsInterface;

class PaiementsService implements PaiementsInterface
{

    public function getInfosPaiement(string $reference): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getInfosPaiement() method.
        $paiement = Paiements::whereReference($reference)->get(['id','reference','email','code_inscription'])->first();
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
