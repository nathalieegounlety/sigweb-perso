<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Mail\Inscriptionpresentiel;
use App\Mail\NotificationCompte;
use App\Services\contracts\FormationsInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class FormationsController extends Controller
{

    private FormationsInterface $formationsService;

    public function __construct(FormationsInterface $formationsService){
        // restriction des accès
        $this->middleware('auth:api', ['except' => ['recenteFormation','index','detailFormation','listeFormationAttestation','formationsDsc','detailFormationDsc']]);
        //$this->middleware('role:ADMINISTRATEUR,USER')->only(['index']);
        //$this->middleware('role:ADMIN')->only(['index']);
        //$this->middleware('permission:CREATE')->only(['index']);
        $this->formationsService = $formationsService;
    }
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
                'visuel' => 'mimes:jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP|nullable'
                // 'date_limite'=>'required',
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
                'visuel' => 'mimes:jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP|nullable'
                // 'date_limite'=>'required',
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

    public function listeIncrits(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $resp = $this->formationsService->getListeInscrits($request, $request->code_formation);
            $data = $resp['pagination'];
            // Transformer pour enlever le bloc 'links'
            $response = [
                'current_page' => $data->currentPage(),
                'data'         => $data->items(),
                'first_page_url' => $data->url(1),
                'last_page_url'  => $data->url($data->lastPage()),
                'next_page_url'  => $data->nextPageUrl(),
                'prev_page_url'  => $data->previousPageUrl(),
                'per_page'       => $data->perPage(),
                'total'          => $data->total()
            ];
            return ResponseHandler::success('ok',$response);

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }
}
