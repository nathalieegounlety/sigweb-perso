<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Services\contracts\GrilletarifairesInterface;
use Illuminate\Http\Request;

class GrilletarifairesController extends Controller
{
    private GrilletarifairesInterface $grilletarifaireService;

    public function __construct(GrilletarifairesInterface $grilletarifaireService)
    {
        $this->middleware('auth:api', ['except' => ['remove']]);
        $this->grilletarifaireService = $grilletarifaireService;
    }

    public function objGrilleTarifaire(Request $request)
    {
        try {
            $perPage = $request->get('per_page',100); // par défaut 50 éléments
            $data = $this->grilletarifaireService->getGrilletarifairesObj($perPage);

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
}
