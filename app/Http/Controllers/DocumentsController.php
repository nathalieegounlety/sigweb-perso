<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use Illuminate\Http\Request;
use App\Services\contracts\DocumentsInterface;

class DocumentsController extends Controller
{
    private DocumentsInterface $documentService;

    public function __construct(DocumentsInterface $documentService){
        $this->documentService = $documentService;
        $this->middleware('auth:api');
    }

    public function showVisuelFormation(Request $request)
    {
        try {
            // validation des données du formulaire

            $validated = $request->validate([
                'idFormation' => 'required|integer',
            ]);

            return $visuel = $this->documentService->getFormationVisuel($request->idFormation);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }
}
