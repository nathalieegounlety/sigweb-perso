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
        $this->middleware('auth:api',['except' => ['listeDesPaiements','detailProcessPaiementDsc']]);
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

    

    public function listeCommandesOuvrages(Request $request){
        // récupérer la liste
        try {
            $resultat = $this->comptabiliteService->getCommandesOuvrages($request);
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

    public function detailCommandeFormation(Request $request){
        try {
            //
            return $data = $this->comptabiliteService->getDetailsCommandeFormation($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
    public function detailCommandeOuvrage(Request $request){
        try {
            //
            return $data = $this->comptabiliteService->getDetailsCommandeOuvrage($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
    public function detailPaiementOuvrage(Request $request){
        try {
            //
            return $data = $this->comptabiliteService->getDetailsPaiementOuvrage($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
    public function detailCommandeDsc(Request $request){
        try {
            //
            return $data = $this->comptabiliteService->getDetailsCommandeDsc($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
    public function detailPaiementDsc(Request $request){
        try {
            //
            return $data = $this->comptabiliteService->getDetailsPaiementDsc($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function detailPaiementValiderDsc(Request $request){
        try {
            //
            return $data = $this->comptabiliteService->getDetailsPaiementValiderDsc($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function deplacerCommandeFormation(Request $request){
        try {
            $validated = $request->validate([
                'statut_traitement' => 'required',
                'montant_total' => 'required',
                'modepaiement_id' => 'required',
            ]);

            return $data = $this->comptabiliteService->storeDeplacerCommandeFormation($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
    public function deplacerCommandeFormationDsc(Request $request){
        try {
            $validated = $request->validate([
                'statut_traitement' => 'required',
                'montant_total' => 'required',
                'modepaiement_id' => 'required',
                'get_frais_inscription' => 'required',
                'get_frais_scolarite' => 'required'
            ]);

            return $data = $this->comptabiliteService->storeDeplacerCommandeDsc($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
    public function declasserCommande(Request $request){
        try {

            $validated = $request->validate([
                'reference' => 'required|string|max:20'
            ]);

            return $data = $this->comptabiliteService->saveDeclassercommande($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
    public function listePaiementsEnAttente(Request $request){

        try {
            $resultat = $this->comptabiliteService->getPaiementsattente($request);
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
    public function traitementPaiement(Request $request){
        try {
            $validated = $request->validate([
                'statut_traitement' => 'required',
                'montant_total' => 'required',
                'modepaiement_id' => 'required',
                'num_transaction' => 'required',
                'reference' => 'required'
            ]);

            return $data = $this->comptabiliteService->validerPaiement($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
    public function traitementPaiementPartiel(Request $request){
        try { 
            $validated = $request->validate([
                'statut_traitement' => 'required',
                'montant_total' => 'required',
                'modepaiement_id' => 'required',
                'num_transaction' => 'required',
                'identifiant' => 'required',
                'reference' => 'required'
            ]);

            return $data = $this->comptabiliteService->validerPaiementPartiel($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
    public function traitementPaiementDsc(Request $request){
        try {
            $validated = $request->validate([
                'statut_traitement' => 'required',
                'montant_total' => 'required',
                'modepaiement_id' => 'required',
                'reference' => 'required',
                'num_transaction' => 'required',
                'listeTranches' => 'required'
            ]);

            return $data = $this->comptabiliteService->validerPaiementDsc($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
    public function listePaiementsValide(Request $request){

        try {
            $resultat = $this->comptabiliteService->getPaiementsvalides($request);
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
    public function listePaiementsOuvragesValide(Request $request){

        try {
            $resultat = $this->comptabiliteService->getPaiementsOuvragesvalides($request);
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
    public function listeDesPaiements(Request $request){

        try {
            $data = $this->comptabiliteService->getListePaiements($request);
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
    public function traitementProcessPaiementDsc(Request $request){
        try {
            $validated = $request->validate([
                'code_paiement' => 'required',
                'montant_total' => 'required',
                'modepaiement_id' => 'required',
                'reference' => 'required',
                'montant_total_recu' => 'required',
                'listeTranches' => 'required',
                'haveTranche' => 'required'
            ]);

            return $data = $this->comptabiliteService->validerProcessPaiementDsc($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
    public function detailProcessPaiementDsc(Request $request){
        try {
            //
            return $data = $this->comptabiliteService->getDetailsProcessPaiementDsc($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
}
