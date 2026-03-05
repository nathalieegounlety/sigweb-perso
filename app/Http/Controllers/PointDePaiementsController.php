<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Services\contracts\PointDePaiementsInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PointDePaiementsController extends Controller
{
    private PointDePaiementsInterface $pointsdepaiementsService;

    public function __construct(PointDePaiementsInterface $pointsdepaiementsService)
    {
       // $this->middleware('auth:api', ['except' => ['remove']]); 
        $this->pointsdepaiementsService = $pointsdepaiementsService;
    }

    
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 50); 
            $pointsdepaiements = $this->pointsdepaiementsService->getPointdepaiements($request);

            $response = [
                'current_page' => $pointsdepaiements->currentPage(),
                'data' => $pointsdepaiements->items(),
                'first_page_url' => $pointsdepaiements->url(1),
                'last_page_url' => $pointsdepaiements->url($pointsdepaiements->lastPage()),
                'next_page_url' => $pointsdepaiements->nextPageUrl(),
                'prev_page_url' => $pointsdepaiements->previousPageUrl(),
                'per_page' => $pointsdepaiements->perPage(),
                'total' => $pointsdepaiements->total(),
            ];
            return ResponseHandler::success('Liste des points de paiement récupérée', $response);

        } catch (\Exception $e) {
            return ResponseHandler::error("Erreur lors de la récupération des points de paiement", 500, $e->getMessage());
        }
    }

  
    public function show(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|integer',
            ]);

            return $this->pointsdepaiementsService->getPointdepaiement($request->id);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

   
    public function store(Request $request)
{
    try {
        \Log::debug('Store called', ['payload' => $request->all()]);

        $validated = $request->validate([
            'nom_organisme' => 'required|string|max:255|unique:point_de_paiements,nom_organisme',
            'adresse' => 'required|string|min:3',
            'ville' => 'required|string',
            'pays' => 'nullable|string',
            'telephone' => 'nullable|string',
            'courriel' => 'required|email',
            'contact1' => 'nullable|string|min:3',
            'telephone1' => 'nullable|string',
            'courriel1' => 'nullable|email',
            'contact2' => 'nullable|string|min:3',
            'telephone2' => 'nullable|string',
            'courriel2' => 'nullable|email',
            'description' => 'nullable|string',
        ]);

        \Log::debug('Validation OK', $validated);
        return $this->pointsdepaiementsService->savePointdepaiement($request);

    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Validation error', $e->errors());
        return ResponseHandler::error("Erreur de validation", 422, $e->errors());
    } catch (\Exception $e) {
        \Log::error('Internal error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
    }
}

   
    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|integer',
                'nom_organisme' => 'required|string|max:255',
                'adresse' => 'nullable|string',
                'ville' => 'nullable|string',
                'pays' => 'nullable|string',
                'telephone' => 'nullable|string',
                'courriel' => 'nullable|email',
                'contact1' => 'nullable|string',
                'telephone1' => 'nullable|string',
                'courriel1' => 'nullable|email',
                'contact2' => 'nullable|string',
                'telephone2' => 'nullable|string',
                'courriel2' => 'nullable|email',
                'description' => 'nullable|string',
            ]);

            return $this->pointsdepaiementsService->update($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    /**
     * Supprime un point de paiement.
     */
    public function remove(Request $request)
    {
        Log::info($request);
        try {
            $validated = $request->validate([
                'id' => 'required',
            ]);

            return $response = $this->pointsdepaiementsService->remove($request->id);

            // call update service
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
}
