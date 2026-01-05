<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Services\contracts\ComptabilitesInterface;
use Illuminate\Http\Request;

class ComptabilitesController extends Controller
{
    private ComptabilitesInterface $comptabiliteService;

    public function __construct(ComptabilitesInterface $comptabiliteService)
    {
        // restriction des accès
        $this->middleware('auth:api',['except' => ['listeCommandes']]);
        $this->comptabiliteService = $comptabiliteService;
    }

    public function listeCommandes(Request $request){
        // récupérer la liste
        try {
            $resultat = $this->comptabiliteService->getCommandes($request);
            $data = $resultat['pagination'];
            $montant_total = $resultat['montant_total'];
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
                'montant_total'   => $montant_total
            ];
            return ResponseHandler::success('ok',$response);

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }
}
