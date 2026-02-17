<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Services\contracts\InscritstypepiecesInterface;
use Illuminate\Http\Request;

class InscritsTypePiecesController extends Controller
{
    private InscritstypepiecesInterface $inscritstypepieceService;

    public function __construct(InscritstypepiecesInterface $inscritstypepieceService)
    {
       $this->middleware('auth:api', ['except' => ['remove']]);
        $this->inscritstypepieceService = $inscritstypepieceService;
    }

    public function index(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $perPage = $request->get('per_page',50); // par défaut 50 éléments
            $inscritstypepieces = $this->inscritstypepieceService->getInscritstypepieces($request);

            // Transformer pour enlever le bloc 'links'
            $response = [
                'current_page' => $inscritstypepieces->currentPage(),
                'data'         => $inscritstypepieces->items(),
                'first_page_url' => $inscritstypepieces->url(1),
                'last_page_url'  => $inscritstypepieces->url($inscritstypepieces->lastPage()),
                'next_page_url'  => $inscritstypepieces->nextPageUrl(),
                'prev_page_url'  => $inscritstypepieces->previousPageUrl(),
                'per_page'       => $inscritstypepieces->perPage(),
                'total'          => $inscritstypepieces->total(),
            ];
            return ResponseHandler::success('ok',$response);

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }

    public function getInscritstypepiece(Request $request){
        try {

                    // validation des données du formulaire
                    $validated = $request->validate([
                        'idInscritstypepiece' => 'required|integer',
                    ]);

                    // procéder à l'enregistrement
                    return $inscritstypepiece = $this->inscritstypepieceService->getInscritstypepiece($request->idInscritstypepiece);

                } catch (\Illuminate\Validation\ValidationException $e) {
                    return ResponseHandler::error("Erreur de validation", 422, $e->errors());
                } catch (\Exception $e) {
                    return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
                }
    }

    public function store(Request $request)
    {

        try {

            // validation des données du formulaire
            $validated = $request->validate([
                'libelle' => 'required|string',
            ]);

            // procéder à l'enregistrement
            return $processSave = $this->inscritstypepieceService->saveInscritstypepiece($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'libelle' => 'required|string',
                'id' => 'required|integer'
            ]);

            return $response = $this->inscritstypepieceService->update($request);

            // call update service
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function remove(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|integer'
            ]);

           return $response = $this->inscritstypepieceService->remove($request->id);

            // call update service
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
}
