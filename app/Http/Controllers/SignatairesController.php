<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Services\contracts\SignatairesInterface;
use Illuminate\Http\Request;

class SignatairesController extends Controller
{
    private SignatairesInterface $signatairesService;

    public function __construct(SignatairesInterface $signatairesService)
    {
        // restriction des accès
        // $this->middleware('auth:api', ['except' => ['store','index','update','remove']]);

        $this->signatairesService = $signatairesService;
        //$this->middleware('role:ADMINISTRATEUR,USER')->only(['index']);
        //$this->middleware('role:ADMIN')->only(['index']);
        //$this->middleware('permission:CREATE')->only(['index']);

    }

    public function index(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $perPage = $request->get('per_page',50); // par défaut 50 éléments
            $data = $this->signatairesService->getOuvrages($request);

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
            return $data = $this->signatairesService->getSignataire($request->id);

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
            $this->validate($request, [
                'nom' => 'required',
                'prenoms' => 'required',
                'titre' => 'required',
                'numero_ordre' => 'required|unique:signataires,ordre',
                'date_debut'=> 'required',
                'signature' => 'mimes:png,jpg,jpeg'
            ]);

            // procéder à l'enregistrement
            return $processSave = $this->signatairesService->saveSignataire($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }

    public function update(Request $request)
    {
        try {

            $this->validate($request, [
                'nom' => 'required',
                'prenoms' => 'required',
                'titre' => 'required',
                'numero_ordre' => 'required',
                'date_debut'=> 'required',
                'signature' => 'mimes:png,jpg,jpeg'
            ]);
            return $response = $this->signatairesService->update($request);

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

            return $response = $this->signatairesService->remove($request->id);

            // call update service
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
}
