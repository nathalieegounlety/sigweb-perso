<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Services\contracts\FedapayInterfarce;
use Illuminate\Http\Request;

class FedapayController extends Controller
{
    private FedapayInterfarce $fedapayInterferce;

    public function __construct(FedapayInterfarce $fedapayInterferce)
    {
        $this->fedapayInterferce = $fedapayInterferce;
    }

    public function savePaiement(Request $request){
        try {
            $validated = $request->validate([
                'reference' => 'required|string',
                'transaction_id' => 'required|string'
            ]);

            return $data = $this->fedapayInterferce->storePaiementFedapay($request);

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

            return $data = $this->fedapayInterferce->storePaiementPartielFedapay($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function savePaiementOuvrage(Request $request){
        try {
            $validated = $request->validate([
                'reference' => 'required|string',
                'transaction_id' => 'required|string'
            ]);

            return $data = $this->fedapayInterferce->storePaiementOuvrageFedapay($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

}
