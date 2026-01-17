<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use Illuminate\Http\Request;
use App\Services\contracts\DocumentsInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DocumentsController extends Controller
{
    private DocumentsInterface $documentService;

    public function __construct(DocumentsInterface $documentService){
        $this->documentService = $documentService;
        $this->middleware('auth:api',['except' => ['recuPaiement','proforma','consultationAttestationParticipant',
            'telechargerAttestationParticipant','listeParticipantVisio','listeParticipantPresentiel','proformaDsc',
            'exportAllAttestationParticipant']]);
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

    public function recuPaiement(Request $request, $reference)
    {
        try {

            return $data = $this->documentService->downloadRecuPaiement($reference);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }

    public function urlRecuPaiement(Request $request)
    {
        try{
            $validated = $request->validate([
                'reference' => 'required|string',
            ]);

            return $this->documentService->getUrlSignedRecuPaiement($request->reference);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function urlRecuPaiementOuvrage(Request $request)
    {
        try{
            $validated = $request->validate([
                'reference' => 'required|string',
            ]);

            return $this->documentService->getUrlSignedRecuPaiementOuvrage($request->reference);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function proforma(Request $request, $reference)
    {
        try {

            return $data = $this->documentService->downloadProforma($reference);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }

    public function proformaDsc(Request $request, $reference)
    {
        try {
            return $data = $this->documentService->downloadProformaDsc($reference);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }

    public function genererUrlProformaSigned(Request $request)
    {
        try{
            $validated = $request->validate([
                'reference' => 'required|string',
            ]);

        return $this->documentService->getUrlProformaSigned($request->reference);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function genererUrlProformaDscSigned(Request $request)
    {
        try{
            $validated = $request->validate([
                'reference' => 'required|string',
            ]);
            return $this->documentService->getUrlProformaDscSigned($request->reference);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function listeAttestationParticipant(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $resp = $this->documentService->getListeAttestationParticipant($request, $request->code_formation);
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
                'total'          => $data->total(),
                'nbr_duree_enregistrement' => $resp['nbr_duree_enregistrement'],
                'duree_formation' => $resp['duree_formation']
            ];
            return ResponseHandler::success('ok',$response);

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }

    public function consultationAttestationParticipant(Request $request,$rapport_id)
    {
        try{
            return $this->documentService->showAttestationParticipant($rapport_id);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function urlConsultationAttestation(Request $request){

        try{
            $validated = $request->validate([
                'rapport_id' => 'required',
            ]);

            return $this->documentService->getUrlConsultationAttestation($request->rapport_id);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function sendAttestationParticipant(Request $request){

        try{
            $validated = $request->validate([
                'rapport_id' => 'required',
            ]);

            return $this->documentService->sendMailAttestationParticipant($request->rapport_id);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }


    public function telechargerAttestationParticipant($rapport_id)
    {
        try {

            return $data = $this->documentService->downloadAttestationParticipant($rapport_id);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }

    public function listeParticipantVisio($code_formation)
    {
        try {
            Log::info($code_formation);
            return $data = $this->documentService->downloadListeParticipantVisio($code_formation);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }

    public function listeParticipantPresentiel($code_formation)
    {
        try {
            return $data = $this->documentService->downloadListeParticipantPresentiel($code_formation);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }

    public function urlListeParticipantPresentiel(Request $request){

        try{
            $validated = $request->validate([
                'code_formation' => 'required',
            ]);

            return $this->documentService->getUrlListeParticipantPresentiel($request->code_formation);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }


    public function urlListeParticipantVisio(Request $request){

        try{
            $validated = $request->validate([
                'code_formation' => 'required',
            ]);

            return $this->documentService->getUrlListeParticipantVisio($request->code_formation);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function urlAllAttestationParticipant(Request $request){

        try{
            $validated = $request->validate([
                'code_formation' => 'required',
            ]);

            return $this->documentService->getUrlAllAttestationParticipant($request->code_formation);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function exportAllAttestationParticipant($code_formation)
    {
        try {
            return $data = $this->documentService->downloadAllAttestationParticipant($code_formation);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }

    public function sendAllAttestationParticipant(Request $request){

        try{
            $validated = $request->validate([
                'listeAuditeurs' => 'required',
                'code_formation' => 'required'
            ]);

            return $this->documentService->sendAllMailAttestationParticipant($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function listeAttestationFormateurs(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $resp = $this->documentService->getListeAttestationFormateur($request, $request->code_formation);
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
