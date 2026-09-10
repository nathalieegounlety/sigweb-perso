<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Mail\Inscriptionpresentiel;
use App\Mail\NotificationCompte;
use App\Services\contracts\FormationsInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use OpenApi\Attributes as OA;

class FormationsController extends Controller
{

    private FormationsInterface $formationsService;

    public function __construct(FormationsInterface $formationsService){
        // restriction des accès
        $this->middleware('auth:api', ['except' => ['recenteFormation','index','detailFormation','listeFormationAttestation','formationsDsc','detailFormationDsc','objReductionFormation']]);
        //$this->middleware('role:ADMINISTRATEUR,USER')->only(['index']);
        //$this->middleware('role:ADMIN')->only(['index']);
        //$this->middleware('permission:CREATE')->only(['index']);
        $this->formationsService = $formationsService;
    }

    #[OA\Get(
        path: "/v1/formations",
        summary: "Liste des formations",
        security: [["bearerAuth" => []]],
        tags: ["Formations"],
        parameters: [ 
            new OA\Parameter(
                name: "page",
                description: "Numéro de page pour la pagination",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", default: 1)
            ),
            new OA\Parameter(
                name: "per_page",
                description: "Nombre d'éléments par page",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", default: 15)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Succès",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/Formations")
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié"),
        ]
    )]
    public function index(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $data = $this->formationsService->getFormations($request);

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
            $request->validate([
                'code_formation'=>'required|unique:formations',
                'theme'=>'required',
                'description'=>'required',
                'cout_formation'=>'required',
                'date_debut'=>'required',
                'date_fin'=>'required',
                'typeformation_id' => 'required',
                'is_event' => 'required',
                'is_coorganisation' => 'required',
                'visuel' => 'mimes:jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP|nullable',
                'cout_formation'=>'required'
                // 'file'=>'required|mimes:jpg,jpeg,png,JPG,JPEG,PNG'
            ]);

            // procéder à l'enregistrement
            return $processSave = $this->formationsService->saveFormation($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }


    public function update(Request $request)
    {

        try {

            // validation des données du formulaire
            $request->validate([
                'id' => 'required',
                'code_formation'=>'required',
                'theme'=>'required',
                'description'=>'required',
                'cout_formation'=>'required',
                'date_debut'=>'required',
                'date_fin'=>'required',
                'typeformation_id' => 'required',
                'is_event' => 'required',
                'is_coorganisation' => 'required',
                'visuel' => 'mimes:jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP|nullable',
                'cout_formation'=>'required',
                // 'file'=>'required|mimes:jpg,jpeg,png,JPG,JPEG,PNG'
            ]);

            // procéder à l'enregistrement
            return $processSave = $this->formationsService->update($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }

    public function recenteFormation(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $data = $this->formationsService->getRecentFormations($request);
            return ResponseHandler::success('ok',$data);

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }
    public function detailFormation(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $request->validate([
                'code_formation'=>'required',
            ]);
            $data = $this->formationsService->getDetailsFormation($request);
            return ResponseHandler::success('ok',$data);

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }
    public function listeFormationAttestation(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $data = $this->formationsService->getFormationsAttestation($request);
            return ResponseHandler::success('ok',$data);

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }
    
    public function listeFormationDscObj(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $data = $this->formationsService->getFormationsDscObj($request);
            return ResponseHandler::success('ok',$data);

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }
    

    public function formationsDsc(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $data = $this->formationsService->getFormationsDsc($request);
            return ResponseHandler::success('ok',$data);

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }
    public function detailFormationDsc(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $request->validate([
                'code_formation'=>'required',
            ]);
            $data = $this->formationsService->getDetailFormationDsc($request);
            return ResponseHandler::success('ok',$data);

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }
    public function formationsobj(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $data = $this->formationsService->getFormationsObj($request);
            return ResponseHandler::success('ok',$data);

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }

    
    #[OA\Get(
        path: "/v1/formations/liste/inscrits",
        summary: "Liste des inscrits",
        security: [["bearerAuth" => []]],
        tags: ["Formations"],
        parameters: [
            new OA\Parameter(
                name: "code_formation",
                description: "Code de la formation",
                in: "query",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "page",
                description: "Numéro de page pour la pagination",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", default: 1)
            ),
            new OA\Parameter(
                name: "per_page",
                description: "Nombre d'éléments par page",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", default: 15)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Succès", 
            ),
            new OA\Response(response: 401, description: "Non authentifié"),
        ]
    )]
    public function listeIncrits(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $resp = $this->formationsService->getListeInscrits($request, $request->code_formation);
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
    
    public function addReductions(Request $request)
    {
        // récupérer la liste des rôles
        try {
            return $data = $this->formationsService->saveReduction($request); 

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }
    
    public function deleteReduction(Request $request)
    {
        // récupérer la liste des rôles
        try {
           return $data = $this->formationsService->saveDeleteReduction($request); 

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }
    
    public function listeReductions(Request $request)
    {
        // récupérer la liste des rôles
        try {
           return  $data = $this->formationsService->getListeReductions($request); 

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }
    
    public function objReductionFormation(Request $request)
    {
        // récupérer la liste des rôles
        try {
           return  $data = $this->formationsService->getReductionFormation($request); 

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }

    
    
    public function addLogos(Request $request)
    {
        // récupérer la liste des rôles
        try {
            return $data = $this->formationsService->saveLogos($request); 

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }
    
    public function deleteLogo(Request $request)
    {
        // récupérer la liste des rôles
        try {
           return $data = $this->formationsService->saveDeleteLogo($request); 

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }
    
    public function listeLogos(Request $request)
    {
        // 
        try {
           return  $data = $this->formationsService->getListeLogos($request); 

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }
    public function editLogos(Request $request)
    {
        // 
        try {
            return $data = $this->formationsService->saveEditLogos($request); 

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }
}
