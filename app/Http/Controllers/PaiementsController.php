<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Services\contracts\PaiementsInterface;
use Illuminate\Http\Request;

class PaiementsController extends Controller
{
    private PaiementsInterface $paiementsService;

    public function __construct(PaiementsInterface $paiementsService)
    {
        $this->paiementsService = $paiementsService;
    }

    public function infosPaiement(Request $request){
        try {
            //
            return $data = $this->paiementsService->getInfosPaiement($request->reference);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
}
