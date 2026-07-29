<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Models\Operateurs;
use App\Services\contracts\OperateursInterface;
use Illuminate\Http\Request;

class OperateursController extends Controller
{
    private OperateursInterface $operateurInterface;

    public function __construct(OperateursInterface $operateurInterface)
    {
       // $this->middleware('auth:api', ['except' => ['remove']]);
        $this->operateurInterface = $operateurInterface;
    }

    public function index(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $perPage = $request->get('per_page',50); // par défaut 50 éléments
            $data = $this->operateurInterface->getOperateurs($request);

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

    public function getOperateur(Request $request){
        try {
                    // validation des données du formulaire
                    $validated = $request->validate([
                        'idOperateur' => 'required|integer',
                    ]);

                    // procéder à l'enregistrement
                    return $data = $this->operateurInterface->getOneOperateurs($request->idOperateur);

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
                'modepays_id' => 'required|integer',
                'operateur' => 'required|string', 
                'logo_path' => 'string'
            ]);

            // procéder à l'enregistrement
            return $processSave = $this->operateurInterface->saveOperateurs($request);

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
                'modepays_id' => 'required|integer',
                'operateur' => 'required|string',  
                'id' => 'required|integer'
            ]);

            return $response = $this->operateurInterface->update($request);

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

           return  $this->operateurInterface->remove($request->id);

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
            return $data = $this->operateurInterface->getOperateursObj();

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
}
