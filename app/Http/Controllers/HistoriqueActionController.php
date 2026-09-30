<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Services\contracts\HistoriqueActionServiceInterface;
use Illuminate\Http\Request;

class HistoriqueActionController extends Controller
{
    private HistoriqueActionServiceInterface $historiqueService;

    public function __construct(HistoriqueActionServiceInterface $historiqueService)
    {
        $this->middleware('auth:api', ['except' => ['remove']]);
        $this->historiqueService = $historiqueService;
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 50);
            $historiques = $this->historiqueService->getHistoriques($perPage);

            $response = [
                'current_page'   => $historiques->currentPage(),
                'data'           => $historiques->items(),
                'first_page_url' => $historiques->url(1),
                'last_page_url'  => $historiques->url($historiques->lastPage()),
                'next_page_url'  => $historiques->nextPageUrl(),
                'prev_page_url'  => $historiques->previousPageUrl(),
                'per_page'       => $historiques->perPage(),
                'total'          => $historiques->total(),
            ];
            return ResponseHandler::success('ok', $response);

        } catch (\Exception $e) {
            return ResponseHandler::error("Erreur lors de la récupération de l'historique", 500, $e->getMessage());
        }
    }

    public function getHistorique(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
            ]);

            return $this->historiqueService->getHistorique($request->id);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'action'      => 'required|string|max:255',
                'description' => 'required|string',
                'ip_adresse'  => 'nullable|string|max:20',
                'mac_adresse' => 'nullable|string|max:20',
            ]);

            return $this->historiqueService->saveHistorique($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'id'          => 'required|integer',
                'action'      => 'required|string|max:255',
                'description' => 'required|string',
                'ip_adresse'  => 'nullable|string|max:20',
                'mac_adresse' => 'nullable|string|max:20',
            ]);

            return $this->historiqueService->update($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function remove(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer'
            ]);

            return $this->historiqueService->remove($request->id);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
}
