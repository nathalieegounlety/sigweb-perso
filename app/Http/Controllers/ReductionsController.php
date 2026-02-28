<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Models\Reductions;
use App\Services\contracts\ReductionsInterface;
use Illuminate\Http\Request;

class ReductionsController extends Controller
{
    private ReductionsInterface $reductionsService;

    public function __construct( ReductionsInterface $reductionsService)
    {
        // restriction des accès
        // $this->middleware('auth:api', ['except' => ['store','index','update','remove']]);

        $this->reductionsService = $reductionsService;
        //$this->middleware('role:ADMINISTRATEUR,USER')->only(['index']);
        //$this->middleware('role:ADMIN')->only(['index']);
        //$this->middleware('permission:CREATE')->only(['index']);

    }

    public function index(Request $request)
    {
      //  $reduction = Reductions::with('getUserUpdated')->find(1); 
   //  dd($reduction->getUserUpdated); 

        // récupérer la liste des rôles
        try {
            $perPage = $request->get('per_page',50); // par défaut 50 éléments
            $data = $this->reductionsService->getReductions($request);

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
            return ResponseHandler::success('success',$response);

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }

    public function store(Request $request)
    {
      //  dd($request->all());
        try {

            // validation des données du formulaire
            $this->validate($request, [
                'pourcentage' => 'required|numeric',
                'libelle' => 'required'
            ]);

            // procéder à l'enregistrement
            return $processSave = $this->reductionsService->saveReduction($request);

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
                'pourcentage' => 'required',
                'libelle' => 'required'
            ]);

            return $response = $this->reductionsService->update($request);

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

            return $response = $this->reductionsService->remove($request->id);

            // call update service
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
    public function getReductionObj(Request $request){
        try {
            // procéder à l'enregistrement
            return $data = $this->reductionsService->getReductionObjet();

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
}
