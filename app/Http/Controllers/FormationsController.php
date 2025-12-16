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
}
