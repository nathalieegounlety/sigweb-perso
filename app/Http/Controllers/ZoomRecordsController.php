<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Services\contracts\ZoomRecordInterface;
use Illuminate\Http\Request;

class ZoomRecordsController extends Controller
{
    private ZoomRecordInterface $zoomRecordService;

    public function __construct(ZoomRecordInterface $zoomRecordService)
    {
        $this->zoomRecordService = $zoomRecordService;
    }

    /**
     * Récupère les enregistrements Zoom pour une formation donnée.
     */
    public function getDetails(Request $request)
    {
        try {
            $validated = $request->validate([
                'code_formation' => 'required|string',
            ]);

            return $this->zoomRecordService->getDetails($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Erreur lors de la récupération des enregistrements", 500, $e->getMessage());
        }
    }

    /**
     * Récupère les sessions de formation pour une formation donnée.
     */
    public function getSessions(Request $request, $code_formation)
{
    try { 

        return $this->zoomRecordService->getSessions($code_formation);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return ResponseHandler::error("Erreur de validation", 422, $e->errors());
    } catch (\Exception $e) {
        return ResponseHandler::error("Erreur lors de la récupération des sessions", 500, $e->getMessage());
    }
}

    /**
     * Enregistre un nouvel enregistrement Zoom.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([ 
                'titre' => 'required|string|max:255',
                'url_youtube' => 'required|string',
                'session_formation_id' => 'nullable|integer',
                'code_formation' => 'required|string',
            ]);

            return $this->zoomRecordService->store($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Erreur lors de l'enregistrement", 500, $e->getMessage());
        }
    }

    /**
     * Met à jour un enregistrement Zoom.
     */
    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|integer',
                'titre' => 'required|string|max:255',
                'url_youtube' => 'required|string',
                'session_formation_id' => 'nullable|integer',
            ]);

            return $this->zoomRecordService->update($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Erreur lors de la mise à jour", 500, $e->getMessage());
        }
    }

    /**
     * Supprime un enregistrement Zoom.
     */
    public function remove(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|integer',
            ]);

            return $this->zoomRecordService->remove($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Erreur lors de la suppression", 500, $e->getMessage());
        }
    }
}
