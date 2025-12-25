<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Services\contracts\TypeformationsInterface;
use Illuminate\Http\Request;

class TypeformationsController extends Controller
{
    private TypeformationsInterface $typeformationService;

    public function __construct(TypeformationsInterface $typeformationService)
    {
        $this->middleware('auth:api', ['except' => ['remove']]);
        $this->typeformationService = $typeformationService;
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page',50); // par défaut 50 éléments
            $data = $this->typeformationService->getTypeformations($request);

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
