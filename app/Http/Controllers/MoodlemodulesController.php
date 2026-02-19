<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Services\contracts\MoodlesmodulesInterface;
use Illuminate\Http\Request;

class MoodlemodulesController extends Controller
{
    private MoodlesmodulesInterface $moodlesmodulesService;

    public function __construct(MoodlesmodulesInterface $moodlesmodulesService)
    {
        $this->moodlesmodulesService = $moodlesmodulesService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $moodlesmodules = $this->moodlesmodulesService->getMoodlesmodules($request);

            $response = [
                'current_page' => $moodlesmodules->currentPage(),
                'data' => $moodlesmodules->items(),
                'first_page_url' => $moodlesmodules->url(1),
                'last_page_url' => $moodlesmodules->url($moodlesmodules->lastPage()),
                'next_page_url' => $moodlesmodules->nextPageUrl(),
                'prev_page_url' => $moodlesmodules->previousPageUrl(),
                'per_page' => $moodlesmodules->perPage(),
                'total' => $moodlesmodules->total(),
            ];

            return ResponseHandler::success('Liste des modules récupérée avec succès', $response);
        } catch (\Exception $e) {
            return ResponseHandler::error("Erreur lors de la récupération des modules", 500, $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $response = $this->moodlesmodulesService->saveMoodlesmodule($request);
            return $response;
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        try {
            $moodlesmodule = $this->moodlesmodulesService->getMoodlesmodule($id);
            return ResponseHandler::success('Module récupéré avec succès', $moodlesmodule);
        } catch (\Exception $e) {
            return ResponseHandler::error("Erreur lors de la récupération du module", 404, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        try {
            $response = $this->moodlesmodulesService->update($request);
            return $response;
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|integer'
            ]);

            return $response = $this->moodlesmodulesService->remove($request->id);

            // call update service
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
}
