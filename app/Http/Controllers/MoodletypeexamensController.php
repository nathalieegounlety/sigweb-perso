<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Services\contracts\MoodletypeexamensInterface;
use Illuminate\Http\Request;

class MoodletypeexamensController extends Controller
{
    //
    private MoodletypeexamensInterface $moodletypeexamensService;

    public function __construct(MoodletypeexamensInterface $moodletypeexamensService)
    {
        $this->moodletypeexamensService = $moodletypeexamensService;
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
            $moodletypeexamen = $this->moodletypeexamensService->getMoodletypeexamens($request);

            $response = [
                'current_page' => $moodletypeexamen->currentPage(),
                'data' => $moodletypeexamen->items(),
                'first_page_url' => $moodletypeexamen->url(1),
                'last_page_url' => $moodletypeexamen->url($moodletypeexamen->lastPage()),
                'next_page_url' => $moodletypeexamen->nextPageUrl(),
                'prev_page_url' => $moodletypeexamen->previousPageUrl(),
                'per_page' => $moodletypeexamen->perPage(),
                'total' => $moodletypeexamen->total(),
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
            $response = $this->moodletypeexamensService->saveMoodletypeexamen($request);
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
            $moodletypeexamen = $this->moodletypeexamenService->getMoodletypeexamen($id);
            return ResponseHandler::success('Module récupéré avec succès', $moodletypeexamen);
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
            $response = $this->moodletypeexamensService->update($request);
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
    public function destroy(int $id)
    {
        try {
            $response = $this->moodletypeexamensService->remove($id);
            return $response;
        } catch (\Exception $e) {
            return ResponseHandler::error("Erreur lors de la suppression du module", 500, $e->getMessage());
        }
    }
}
