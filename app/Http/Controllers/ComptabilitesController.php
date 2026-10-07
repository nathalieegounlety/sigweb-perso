<?php

namespace App\Http\Controllers;

use App\Exports\ExportsListePaiements;
use App\Exports\PaiementsValidesExport;
use App\Helpers\ResponseHandler;
use App\Services\contracts\ComptabilitesInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;
use Carbon\Carbon;
use PDF;
use Maatwebsite\Excel\Facades\Excel;

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

    
    #[OA\Get(
        path: "/v1/comptabilites/paiements/valide",
        summary: "Liste des paiements validés",
        security: [["bearerAuth" => []]],
        tags: ["Paiements"],
        parameters: [
            new OA\Parameter(
                name: "code_formation",
                description: "Code de la formation",
                in: "query",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "page",
                description: "Numéro de page pour la pagination",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", default: 1)
            ),
            new OA\Parameter(
                name: "per_page",
                description: "Nombre d'éléments par page",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", default: 15)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Succès"
            ),
            new OA\Response(response: 401, description: "Non authentifié"),
        ]
    )]
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

    public function exportPaiementsValide(Request $request)
    {
        $request->validate([
            'format'      => 'required|in:pdf,excel',
            'date_debut'  => 'nullable|date',
            'date_fin'    => 'nullable|date|after_or_equal:date_debut',
            'type'        => 'nullable|in:FORMATION,FORMATION PARTIEL,DSC,OUVRAGE',
            'montant_min' => 'nullable|numeric',
            'montant_max' => 'nullable|numeric',
        ]);

        try {
            $rows = $this->comptabiliteService->getPaiementsvalidesExport($request);
            $nom  = 'SIGWEB_LISTE_PAIEMENTS_VALIDES_' . now()->format('Ymd_His');

            if ($request->format === 'pdf') {
                return PDF::loadView('exports.paiements_valides', [
                    'rows'    => $rows,
                    'total'   => $rows->sum('montant'),
                    'filtres' => $request->only(['date_debut','date_fin','type','montant_min','montant_max','search']),
                ])->setPaper('a4', 'landscape')->download("$nom.pdf");
            }

            return Excel::download(new PaiementsValidesExport($rows), "$nom.xlsx");
        } catch (\Exception $e) {
            return ResponseHandler::error("Erreur lors de l'export", 500, $e->getMessage());
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
 
    
private function libellesCriteres(Request $request): array
{
    $fmtDate    = fn ($d) => Carbon::parse($d)->format('d/m/Y');
    $fmtMontant = fn ($m) => number_format((float) $m, 0, ',', ' ') . ' F CFA';
    $criteres   = [];
 
    // Période
    if ($request->filled('date_debut') || $request->filled('date_fin')) {
        $criteres['Période'] = trim(
            ($request->filled('date_debut') ? 'du ' . $fmtDate($request->date_debut) . ' ' : '') .
            ($request->filled('date_fin')   ? 'au ' . $fmtDate($request->date_fin)          : '')
        );
    }
    if ($request->filled('filter_updated_at')) {
        $criteres['Date'] = $fmtDate($request->filter_updated_at);
    }
 
    // Formation (le filtre porte sur le code formation)
    if ($request->filled('formation_id')) {
        $theme = DB::table('formations')->where('code_formation', $request->formation_id)->value('theme');
        $criteres['Formation'] = $request->formation_id . ($theme ? " - {$theme}" : '');
    }
 
    // Mode de paiement : libellé plutôt que l'identifiant
    if ($request->filled('modepaiement_id')) {
        $criteres['Mode de paiement'] = DB::table('modepaiements')
            ->where('id', $request->modepaiement_id)->value('libelle') ?? $request->modepaiement_id;
    }
 
    if ($request->filled('statut_paiement')) {
        $criteres['Statut'] = $request->statut_paiement;
    }
 
    // Montant (min / max)
    if ($request->filled('montant_min') || $request->filled('montant_max')) {
        $criteres['Montant dû'] = trim(
            ($request->filled('montant_min') ? 'de ' . $fmtMontant($request->montant_min) . ' ' : '') .
            ($request->filled('montant_max') ? 'à '  . $fmtMontant($request->montant_max)        : '')
        );
    }
 
    if ($request->filled('a_participe')) {
        $criteres['A participé'] = (int) $request->a_participe === 1 ? 'Oui' : 'Non';
    }
 
    if ($request->filled('ecart')) {
        $criteres['Écart de contrôle'] = [
            'NUL'     => 'Sans écart (= 0)',
            'POSITIF' => 'Écart positif (> 0)',
            'NEGATIF' => 'Écart négatif (< 0)',
            'NON_NUL' => 'Tout écart (≠ 0)',
        ][$request->ecart] ?? $request->ecart;
    }
 
    if ($request->filled('search')) {
        $criteres['Recherche'] = '« ' . $request->search . ' »';
    }
 
    return $criteres;
}

    public function exportListePaiements(Request $request)
    {
        $request->validate([
            'format'      => 'required|in:pdf,excel',
            'date_debut'  => 'nullable|date',
            'date_fin'    => 'nullable|date|after_or_equal:date_debut',
            'statut_paiement'=> 'nullable|in:PAYE,IMPAYE',
            'montant_min' => 'nullable|numeric',
            'montant_max' => 'nullable|numeric',
            'modepaiement_id' => 'nullable|numeric',
            'formation_id' => 'nullable|numeric', 
        ]);

        try {
            $rows = $this->comptabiliteService->getListePaiementsExport($request);
            $nom  = 'SIGWEB_LISTE_PAIEMENTS_' . now()->format('Ymd_His');

            if ($request->format === 'pdf') {
                return PDF::loadView('exports.liste_paiements_tri', [
                    'rows'    => $rows,
                    'total'   => $rows->sum('montant'),
                    'filtres' => $request->only(['date_debut','date_fin','statut_paiement','formation_id','modepaiement_id','montant_min','montant_max','search']),
                ])->setPaper('a4', 'landscape')->download("$nom.pdf");
            }

            return Excel::download(new ExportsListePaiements($rows,'SIGWEB EXTRACTION DES PAIEMENTS', $this->libellesCriteres($request)), "$nom.xlsx");
        } catch (\Exception $e) {
            return ResponseHandler::error("Erreur lors de l'export", 500, $e->getMessage());
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
