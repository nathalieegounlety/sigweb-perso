<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Models\Auditeurs;
use App\Models\cr;
use App\Services\contracts\AuditeursInterface;
use Illuminate\Http\Request;

class AuditeursContoller extends Controller
{

    private AuditeursInterface $auditeurService;

    public function __construct(AUDITEURSInterface $auditeurService)
    {
        // restriction des accès
        $this->middleware('auth:api',['except' => ['store','getAuditeurByEmail','getListeFormations','inscriptionPaiement','listePays']]);
        $this->auditeurService = $auditeurService;
    }

    /**
     * Génère un mot de passe aléatoire.
     */
    private function genererPassword($length = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        return substr(str_shuffle($chars), 0, $length);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // récupérer la liste
        try {
            $perPage = $request->get('per_page',50); // par défaut 50 éléments
            $data = $this->auditeurService->getAuditeurs($request);

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
    public function store(Request $request)
    {
        try {
            // Validation des données
            $validatedData = $request->validate([
                'email' => 'required|email|unique:auditeurs,email|unique:users,email',
                'nom' => 'required|string|max:255',
                'prenoms' => 'required|string|max:255',
                'pays' => 'required',
                'telephone' => 'required|string|max:20',
                'ville' => 'nullable|string|max:255',
                'genre' => 'nullable|string|max:1',
                'secteur_activite' => 'nullable|string|max:255',
                'organisation' => 'nullable|string|max:255',
                'intitule_poste' => 'required|string|max:255',
            ]);
            // procéder à l'enregistrement
            return $processSave = $this->auditeurService->saveAuditeur($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function inscriptionFormation(Request $request)
    {
        try {
            // Validation des données
            $validatedData = $request->validate([
                'formations_classique' => 'required'
            ]);
            // procéder à l'enregistrement
            return $processSave = $this->auditeurService->storeInscriptionFormation($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation ".$request, 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
    public function getAuditeurByEmail(Request $request){
        try {
            //
            return $data = $this->auditeurService->getAuditeurByEmail($request->email);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
    public function getListeFormations(Request $request){
        try {

            return $data = $this->auditeurService->getListeFormationsByCodeInscription($request->code_inscription);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function inscriptionPaiement(Request $request)
    {
        try {
            // Validation des données
            $validatedData = $request->validate([
                'formations' => 'required',
                'montant_total' => 'required',
                'mode_paiement' => 'required',
                'option' => 'required',
                'email' => 'required',
            ]);
            // procéder à l'enregistrement
            return $processSave = $this->auditeurService->storeInscriptionPaiement($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation ".$request, 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function listePays(Request $request){
        try {
            //
            return $data = $this->auditeurService->getListePays();

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function threeFormation(Request $request){
        try {
            //
            return $data = $this->auditeurService->getLastThreeFormation();

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function indicateurs(Request $request){
        try {
            //
            return $data = $this->auditeurService->getIndicateurs();

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }


    public function listeInscriptions(Request $request){
        try {
            //
            return $data = $this->auditeurService->getListeInscriptions($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
}
