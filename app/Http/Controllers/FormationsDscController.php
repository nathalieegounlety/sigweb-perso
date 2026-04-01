<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Models\FormationsDsc;
use App\Services\contracts\FormationsdscInterface;
use Illuminate\Http\Request;

class FormationsDscController extends Controller
{
    private FormationsdscInterface $formationdscService;

    public function __construct(FormationsdscInterface $formationdscService)
    {
        // restriction des accès
        $this->middleware('auth:api',['except' => ['getObFormationDsc','index']]);

        $this->formationdscService = $formationdscService;
        //$this->middleware('role:ADMINISTRATEUR,USER')->only(['index']);
        //$this->middleware('role:ADMIN')->only(['index']);
        //$this->middleware('permission:CREATE')->only(['index']);

    }

    public function index(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $perPage = $request->get('per_page',50); // par défaut 50 éléments
            $data = $this->formationdscService->getFormationsDsc($request);

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

    public function getFormationdsc(Request $request){
        try {
            // validation des données du formulaire
            $validated = $request->validate([
                'id' => 'required|integer',
            ]);

            // procéder à l'enregistrement
            return $data = $this->formationdscService->getFormationDsc($request->id);

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
                'code_formation' => 'required',
                'visuel_modalite' => 'required|file|mimes:pdf|max:10240',
                'duree' => 'required',
                'date_paiement' => 'required',
                'date_inscription' => 'required',
                'date_diplome' => 'required',
            ]);

            // procéder à l'enregistrement
            return $processSave = $this->formationdscService->saveFormationdsc($request);

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
                'code_formation' => 'required',
                'visuel_modalite' => 'file|mimes:pdf|max:10240',
                'duree' => 'required',
                'date_paiement' => 'required',
                'date_inscription' => 'required',
                'date_diplome' => 'required',
                'id' => 'required',
            ]);

            return $response = $this->formationdscService->update($request);

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

            return $response = $this->formationdscService->remove($request->id);

            // call update service
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function getObFormationDsc(Request $request){
        try {
            // procéder à l'enregistrement
            return $data = $this->formationdscService->objFormationDsc();

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    

        public function listeDossiersAttente(Request $request)
    {
         
        try {
            $resp = $this->formationdscService->getListeDossiersAttente($request, $request->code_formation);
            $data = $resp['pagination'];
            $is_dsc = $resp['is_dsc'];
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
                'is_dsc' => $is_dsc
            ];
            return ResponseHandler::success('ok',$response);

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }

    

    public function traitementsDossier(Request $request){

        try{
            $validated = $request->validate([
                'listeAuditeurs' => 'required',
                'code_formation' => 'required',
                'decision' => 'required',
                'motif' => 'required'
            ]);

            return $this->formationdscService->validerDossiersCandidature($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

}
