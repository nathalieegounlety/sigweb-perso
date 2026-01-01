<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Services\contracts\ModepaiementsInterface;
use Illuminate\Http\Request;

class ModepaiementsController extends Controller
{
private ModepaiementsInterface $modepaiementInterface;

    public function __construct(ModepaiementsInterface $modepaiementInterface)
    {
        $this->middleware('auth:api', ['except' => ['getModepaiementsObj']]);
        $this->modepaiementInterface = $modepaiementInterface;
    }

    public function index(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $perPage = $request->get('per_page',50); // par défaut 50 éléments
            $data = $this->modepaiementInterface->getModepaiements($perPage);

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

    public function getModepaiement(Request $request){
        try {
                    // validation des données du formulaire
                    $validated = $request->validate([
                        'idModepaiement' => 'required|integer',
                    ]);

                    // procéder à l'enregistrement
                    return $modepaiement = $this->modepaiementInterface->getModepaiement($request->idPermission);

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
            return $processSave = $this->modepaiementInterface->saveModepaiement($request);

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

            return $response = $this->modepaiementInterface->update($request);

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

           return  $this->permissionService->remove($request->id);

            // call update service
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
    public function getModepaiementsObj(Request $request){

        try {
            // procéder à l'enregistrement
            return $modepaiementsObj = $this->modepaiementInterface->getModepaiementsObj();

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
}
