<?php

namespace App\Http\Controllers;

use App\Exports\ExportsStatsParticipants;
use App\Helpers\ResponseHandler;
use App\Models\Formations;
use App\Services\contracts\PaiementsInterface;
use App\Services\Impl\PaiementsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PDF;
use Maatwebsite\Excel\Facades\Excel;

class PaiementsController extends Controller
{
    private PaiementsInterface $paiementsService;

    public function __construct(PaiementsInterface $paiementsService)
    {
        $this->paiementsService = $paiementsService;
    }

    public function infosPaiement(Request $request){
        try {
            //
            return $data = $this->paiementsService->getInfosPaiement($request->reference);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function infosPaiementOuvrage(Request $request){
        try {
            //
            return $data = $this->paiementsService->getInfosPaiementOuvrage($request->reference);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }



    public function infoPaiementPartiel(Request $request){
        try {
            //
            return $data = $this->paiementsService->getInfoPaiementPartiel($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function savePaiementPartiel(Request $request)
    {
        try {
            // Validation des données
            $validatedData = $request->validate([
                'formations' => 'required',
                'montant_total' => 'required',
                'mode_paiement' => 'required',
                'reference_paiement' => 'required'
            ]);
            // procéder à l'enregistrement
            return $processSave = $this->paiementsService->enregistrerPaiementPartiel($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function getRecapitulatifPaiements($code_formation)
    {
        $paiementsService = new PaiementsService();
        return $paiementsService->getRecapitulatifPaiements($code_formation);
    }

    public function statsParticipantsFormation(Request $request, string $codeFormation)
    {
        $paiementsService = new PaiementsService();
        return $this->paiementsService->getStatsParticipantsFormation($codeFormation, $request);
    }

    
public function statsExportParticipantsFormation(Request $request, string $codeFormation)
{ 
    $request->validate([
        'format'          => 'required|in:pdf,excel',
        'type_formation'  => 'nullable|in:PRESENTIEL,VISIO,PROFESSIONNELS,ETUDIANTS,INSCRIPTION',
        'statut_paiement' => 'nullable|in:PAYE,NON_PAYE',
        'a_participe'     => 'nullable|in:0,1',
        'type_reduction'  => 'nullable|in:BOURSE_COMPLET,REDUCTION_5,SANS',
    ]);
 
    $formation = Formations::where('code_formation', $codeFormation)->first();
    if (!$formation) {
        return ResponseHandler::success("Formation introuvable", [
            'success' => false,
            'message' => "La formation {$codeFormation} n'existe pas.",
        ]);
    }
 
    try {
          $paiementsService = new PaiementsService();
         $recap  = $this->paiementsService->calculerStatsRecapitulatif($formation);
        $lignes = $this->paiementsService->lignesRecapitulatif($recap['stats'], $recap['fm_gratuite']);
 
        // Participants : mêmes filtres et même tri que la liste affichée
        $rows = $this->paiementsService->getStatsParticipantsFormationExport($codeFormation, $request);
        
        
        $filtres = $request->only(['type_formation', 'statut_paiement', 'a_participe', 'type_reduction', 'search']);
        $nom     = 'SIGWEB_STATS_PARTICIPANTS_' . $codeFormation . '_' . now()->format('Ymd_His');
 
        if ($request->format === 'pdf') {
            return PDF::loadView('exports.stats_participants_formation', [
                'formation'    => $formation,
                'fmGratuite'   => $recap['fm_gratuite'],
                'lignesRecap'  => $lignes,
                'rows'         => $rows,
                'filtres'      => $filtres,
                'totalPaye'    => $rows->sum(fn ($r) => (float) $r->montant_paye),
                'totalRestant' => $rows->sum(fn ($r) => (float) $r->montant_restant),
            ])->setPaper('a4', 'landscape')->download("$nom.pdf");
        }
 
        return Excel::download(
            new ExportsStatsParticipants($formation, $lignes, $recap['fm_gratuite'], $rows),
            "$nom.xlsx"
        );
 
 
    } catch (\Throwable $e) {
        Log::error('Export état de paiement formation : ' . $e->getMessage());
        return ResponseHandler::error("Erreur lors de l'export", 500, $e->getMessage());
    }
      
} 
    

}
