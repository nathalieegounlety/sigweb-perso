<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Services\contracts\OuvragesInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule as ValidationRule;

class OuvragesController extends Controller
{

    private OuvragesInterface $ouvragesService;

    public function __construct(OuvragesInterface $ouvragesService)
    {
        // restriction des accès
         $this->middleware('auth:api', ['except' => ['getObjOuvrage']]);

        $this->ouvragesService = $ouvragesService;
        //$this->middleware('role:ADMINISTRATEUR,USER')->only(['index']);
        //$this->middleware('role:ADMIN')->only(['index']);
        //$this->middleware('permission:CREATE')->only(['index']);

    }

    public function index(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $perPage = $request->get('per_page',50); // par défaut 50 éléments
            $data = $this->ouvragesService->getOuvrages($request);

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

    public function getOuvrage(Request $request){
        try {
            // validation des données du formulaire
            $validated = $request->validate([
                'id' => 'required|integer',
            ]);

            // procéder à l'enregistrement
            return $data = $this->ouvragesService->getOuvrage($request->id);

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
                'montant' => 'required',
                'description' => 'string'
            ]);

            // procéder à l'enregistrement
            return $processSave = $this->ouvragesService->saveOuvrage($request);

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
                'montant' => 'required',
                'description' => 'string',
                'id' => 'required|integer'
            ]);

            return $response = $this->ouvragesService->update($request);

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

            return $response = $this->ouvragesService->remove($request->id);

            // call update service
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function getObjOuvrage(Request $request){
        try {
            // procéder à l'enregistrement
            return $data = $this->ouvragesService->objOuvrage();

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
    public function storeAchat(Request $request)
    {
        try {
            $validated = $request->validate([
                'liste_ouvrages' => ['required', 'array', 'min:1'],
                'liste_ouvrages.*.ouvrage_id' => ['required'],
                'liste_ouvrages.*.montant' => ['required'],
                'liste_ouvrages.*.reference' => ['required'],
                'montant_total' => 'required'
            ]);

            return $response = $this->ouvragesService->saveChoixOuvrages($request);

            // call update service
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
}
