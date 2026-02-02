<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Services\contracts\KkiapayInterfarce;
use Illuminate\Http\Request;

class KkiapayController extends Controller
{
    private KkiapayInterfarce $kkiapayservice;

    public function __construct(KkiapayInterfarce $kkiapayservice)
    {
        $this->kkiapayservice = $kkiapayservice;
    }

    public function savePaiement(Request $request){
        try {
            $validated = $request->validate([
                'transaction_id' => 'required|string'
            ]);

            return $data = $this->kkiapayservice->storePaiementKkiapay($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function savePaiementPartiel(Request $request){
        try {
            $validated = $request->validate([
                'transaction_id' => 'required|string',
                'formations' => 'required',
                'montant_total' => 'required',
                'mode_paiement' => 'required',
                'reference_paiement' => 'required'
            ]);

            return $data = $this->kkiapayservice->storePaiementPartielKkiapay($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function savePaiementOuvrage(Request $request){
        try {
            $validated = $request->validate([
                'transaction_id' => 'required|string'
            ]);

            return $data = $this->kkiapayservice->storePaiementOuvrageKkiapay($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function savePaiementDsc(Request $request){
        try {
            $validated = $request->validate([
                'transaction_id' => 'required|string'
            ]);

            return $data = $this->kkiapayservice->storePaiementDscKkiapay($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
}
