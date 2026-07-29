<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler; 
use App\Services\contracts\ModepaysInterface;
use Illuminate\Http\Request;

class ModepaysController extends Controller
{
    private ModepaysInterface $modepaysInterface;

    public function __construct(ModepaysInterface $modepaysInterface)
    {
       // $this->middleware('auth:api', ['except' => ['remove']]);
        $this->modepaysInterface = $modepaysInterface;
    }

    public function index(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $perPage = $request->get('per_page',50); // par défaut 50 éléments
            $data = $this->modepaysInterface->getModepays($request);

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

    public function getModepays(Request $request){
        try {
                    // validation des données du formulaire
                    $validated = $request->validate([
                        'idModepays' => 'required|integer',
                    ]);

                    // procéder à l'enregistrement
                    return $modepays = $this->modepaysInterface->getOneModepays($request->idModepays);

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
                'pays_id' => 'required|integer',
                'modepaiement_id' => 'required|integer'
            ]);

            // procéder à l'enregistrement
            return $processSave = $this->modepaysInterface->saveModepays($request);

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
                'pays_id' => 'required|integer',
                'modepaiement_id' => 'required|integer', 
                'id' => 'required|integer'
            ]);

            return $response = $this->modepaysInterface->update($request);

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

           return  $this->modepaysInterface->remove($request->id);

            // call update service
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
    public function getModepaysObj(Request $request){

        try {
            // procéder à l'enregistrement
            return $data = $this->modepaysInterface->getModepaysObj();

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
}
