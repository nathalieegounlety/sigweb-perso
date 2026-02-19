<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Services\contracts\TypeformationsInterface;
use Illuminate\Http\Request;

class TypeformationsController extends Controller
{
    private TypeformationsInterface $typeformationService;

    public function __construct(TypeformationsInterface $typeformationService)
    {
       // $this->middleware('auth:api', ['except' => ['remove']]);
        $this->typeformationService = $typeformationService;
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page',50); // par défaut 50 éléments
            $data = $this->typeformationService->getTypeformations($request);

            // Transformer pour enlever le bloc 'links'
            $response = [
                'current_page' => $data->currentPage(),
                'data'         => $data->items(),
                'first_page_url' => $data->url(1),
                'last_page_url'  => $data->url($data->lastPage()),
                'next_page_url'  => $data->nextPageUrl(),
                'prev_page_url'  => $data->previousPageUrl(),
                'per_page'       => $data->perPage(),
                'total'          => $data->total(),
            ];
            return ResponseHandler::success('ok',$response);

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }

    public function getTypeformation(Request $request){
        try {

                    // validation des données du formulaire
                    $validated = $request->validate([
                        'idTypeformation' => 'required|integer',
                    ]);

                    // procéder à l'enregistrement
                    return $typeformation = $this->typeformationService->getTypeformation($request->idTypeformation);

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
                'description' => 'string'
            ]);

            // procéder à l'enregistrement
            return $processSave = $this->typeformationService->saveTypeformation($request);

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
                'description' => 'string',
                'id' => 'required|integer'
            ]);

            return $response = $this->typeformationService->update($request);

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

           return $response = $this->typeformationService->remove($request->id);

            // call update service
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
}
