<?php

namespace App\Http\Controllers;
 
use App\Helpers\ResponseHandler;
use App\Services\contracts\FormateursInterface;
use Illuminate\Http\Request;

class FormateursController extends Controller
{
    
    private FormateursInterface $formateurService;

    public function __construct(FormateursInterface $formateurService)
    {
        $this->middleware('auth:api', ['except' => ['remove']]);
        $this->formateurService = $formateurService;
    } 

    public function index(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $perPage = $request->get('per_page',50); // par défaut 50 éléments
            $data = $this->formateurService->getFormateurs($request);

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

    public function store(Request $request)
    {

        try {

            // validation des données du formulaire
            $validated = $request->validate([
                'titre' => 'required|string',
                'nom' => 'required',
                'prenoms' => 'required',
                'email' => 'required',
                'genre' => 'required'
            ]);

            // procéder à l'enregistrement
            return $processSave = $this->formateurService->saveFormateur($request);

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
                'titre' => 'required|string',
                'nom' => 'required',
                'prenoms' => 'required',
                'email' => 'required',
                'genre' => 'required', 
                'id' => 'required'
            ]);

            return $response = $this->formateurService->update($request);

            // call update service
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
    
    public function getFormateur(Request $request){
        try {
            // validation des données du formulaire
            $validated = $request->validate([
                'id' => 'required',
            ]);

            // procéder à l'enregistrement
            return $data = $this->formateurService->getFormateur($request->id);

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

            return $response = $this->formateurService->remove($request->id);

            // call update service
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
}
