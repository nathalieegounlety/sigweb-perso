<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Services\contracts\FormationsInterface;
use Illuminate\Http\Request;

class FormationsController extends Controller
{
    private FormationsInterface $formationsService;

    public function __construct(FormationsInterface $formationsService){
        $this->formationsService = $formationsService;
    }
    public function index(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $perPage = $request->get('per_page',50); // par défaut 50 éléments
            $roles = $this->formationsService->getFormations($request);

            // Transformer pour enlever le bloc 'links'
            $response = [
                'current_page' => $roles->currentPage(),
                'data'         => $roles->items(),
                'first_page_url' => $roles->url(1),
                'last_page_url'  => $roles->url($roles->lastPage()),
                'next_page_url'  => $roles->nextPageUrl(),
                'prev_page_url'  => $roles->previousPageUrl(),
                'per_page'       => $roles->perPage(),
                'total'          => $roles->total(),
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
}
