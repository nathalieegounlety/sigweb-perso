<?php

namespace App\Http\Controllers;
  
use App\Helpers\ResponseHandler;  
use App\Services\contracts\CandidaturesInterface;
use Illuminate\Http\Request;

class CandidaturesController extends Controller
{
    private CandidaturesInterface $candidatureService;

    public function __construct(CandidaturesInterface $candidatureService)
    {
        // restriction des accès
        //$this->middleware('auth:api',['except' => ['']]);
        $this->candidatureService = $candidatureService;
    }

    /**
     * Display a listing of the resource.
     */
    public function listeCandidats(Request $request)
    {
        // récupérer la liste
        try {
            $perPage = $request->get('per_page',50); // par défaut 50 éléments
            $data = $this->candidatureService->getListeCandidats($request);

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
    

    /**
     * Store a newly created resource in storage.
     */
    public function updateInfoCandidat(Request $request)
    {
        try {
            // Validation des données
            $validatedData = $request->validate([
                'nom' => 'required',
                'prenoms' => 'required',
                'email' => 'required',
                'identifiant' => 'required'
            ]);
            // procéder à l'enregistrement
            return $processSave = $this->candidatureService->saveUpdateInfoCandidat($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    
    public function listeFormationsCandidat(Request $request){
        try {
            $validatedData = $request->validate([ 
                'email' => 'required'
            ]);
            return $data = $this->candidatureService->getListeFormationsCandidat($request->email);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function listeNoteFinaleCandidats(Request $request)
    {
        // récupérer la liste
        try {

            $perPage = $request->get('per_page',50); // par défaut 50 éléments
            $resp = $this->candidatureService->getListeNoteFinaleCandidats($request, $request->code_formation);
            $data = $resp['pagination'];
            $check_moyenne_generale = $resp['check_moyenne_generale'];
            $code_formation = $resp['code_formation'];

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
                'check_moyenne_generale' => $check_moyenne_generale,
                'code_formation' => $code_formation
            ];

            return ResponseHandler::success('ok',$response);

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }

    
    public function listeNotesBrutes(Request $request){
        try {
            $validatedData = $request->validate([ 
                'email_candidat' => 'required',
                'code_formation' => 'required',
                'code_module' => 'required'
            ]);
            return $data = $this->candidatureService->getListeNotesBrutes($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    
    public function calculMg(Request $request){
        try {
            $validatedData = $request->validate([  
                'code_formation' => 'required'
            ]);
            return $data = $this->candidatureService->doCalculMg($request->code_formation);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function registreNotesCandidats(Request $request)
    {
        // récupérer la liste
        try {

            $perPage = $request->get('per_page',50); // par défaut 50 éléments
            $resp = $this->candidatureService->getRegistreNotesCandidats($request, $request->code_formation);
            $data = $resp['pagination']; 
            $code_formation = $resp['code_formation'];

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
                'code_formation' => $code_formation
            ];

            return ResponseHandler::success('ok',$response);

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }

    
    
    public function infoDiplomes(Request $request){
        try {
            $validatedData = $request->validate([ 
                'code_user' => 'required',
                'code_formation' => 'required', 
            ]);
            
            return $data = $this->candidatureService->infoConsultationDiplome($request->code_user, $request->code_formation);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
}
