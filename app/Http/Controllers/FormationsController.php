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
        $this->middleware('auth:api', ['except' => ['recenteFormation','index']]);
        //$this->middleware('role:ADMINISTRATEUR,USER')->only(['index']);
        //$this->middleware('role:ADMIN')->only(['index']);
        //$this->middleware('permission:CREATE')->only(['index']);
        $this->formationsService = $formationsService;
    }
    public function index(Request $request)
    {

        $request = new Request([
            'formations_classique' => [
                [
                    'code_formation' => 'ERS2024-M4',
                    'tarif'     => '343',
                ],
                [
                    'code_formation' => 'SPO2024-S5',
                    'tarif'     => '360',
                ],
            ],
        ]);
        $c = 360;
        $email = 'blandine@yopmail.com';
        $contenu = [
            'nom' => 'DAYANA',
            'prenoms' => 'Blandine',
            'formations' => $request->formations_classique,
            'subject' => 'Inscription sur SIGWEB de DAYANA Blandine',
            'email' => $email,
            'code_inscription' => '22939299329',
        ];

        //dd($contenu['formations']);


        Mail::to($email)->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new Inscriptionpresentiel($contenu));


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
}
