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



    public function infoPaiementPartiel(Request $request){
        try {
            //
            return $data = $this->paiementsService->getInfoPaiementPartiel($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function savePaiementPartiel(Request $request)
    {
        try {
            // Validation des données
            $validatedData = $request->validate([
                'formations' => 'required',
                'montant_total' => 'required',
                'mode_paiement' => 'required',
                'reference_paiement' => 'required'
            ]);
            // procéder à l'enregistrement
            return $processSave = $this->paiementsService->enregistrerPaiementPartiel($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

}
