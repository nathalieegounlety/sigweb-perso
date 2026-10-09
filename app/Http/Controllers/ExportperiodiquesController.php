<?php

namespace App\Http\Controllers;

use App\Exports\ExportsListeHistoriques;
use App\Exports\ExportsListeHistoriquesPeriode;
use App\Exports\ExportsListeRoles;
use App\Exports\Paiementsperiodique;
use App\Helpers\ResponseHandler;
use App\Models\Inscriptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use NumberFormatter;
use PDF;

class ExportperiodiquesController extends Controller
{
    public function listePaiements(Request $request)
    {

    try {
        //code... 
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin'   => 'required|date|after_or_equal:date_debut',
            'type'   => 'required',
        ]);

        if ($request->type == "EXCEL") {
            # code...
            $fileName = 'paiements_' . $request->date_debut . '_' . $request->date_fin . '.xlsx';

            return Excel::download(
                new Paiementsperiodique($request->date_debut, $request->date_fin),
                $fileName
            );
        
        } else {
            # code...

             set_time_limit(0);
        $queryData = Inscriptions::leftJoin('auditeurs', 'auditeurs.email', '=', 'inscriptions.email')
        ->leftJoin('detailspaiements as dp', function ($join) {
            $join->on('dp.id_tarification_formation', '=', 'inscriptions.tarif_formation_id')
                ->on('dp.email', '=', 'inscriptions.email')
                ->whereNotNull('dp.email');
        })
        ->leftJoin('paiements as p', function ($join) {
            $join->on('p.reference', '=', 'dp.reference_paiement');
        })
        ->leftJoin('modepaiements as mp', function ($join) {
            $join->on('mp.id', '=', 'p.id_modepaiement');
        })
        ->leftJoin('reductions', function ($join) {
            $join->on('reductions.id', '=', 'dp.id_reduction');
        })
        ->leftJoin('formations', function ($join) {
            $join->on('inscriptions.code_formation', '=', 'formations.code_formation');
        })
        ->selectRaw("
            auditeurs.email,
            inscriptions.tarif_formation_id,
            inscriptions.code_formation,
            CONCAT(auditeurs.prenoms, ' ', auditeurs.nom) as auditeur,
            CASE
                WHEN p.type = 'DSC' THEN (
                    SELECT SUM(COALESCE(dp2.montant_paye, 0) + COALESCE(dp2.montant_restant, 0))
                    FROM detailspaiements dp2
                    WHERE dp2.reference_paiement = dp.reference_paiement
                )
                ELSE (COALESCE(dp.montant_paye, 0) + COALESCE(dp.montant_restant, 0))
            END as montant,
            inscriptions.updated_at,
            inscriptions.id,
            inscriptions.code_inscription,
            dp.id as detailpaiement_id,
            dp.reference_paiement,
            dp.id_tarification_formation,
            dp.preuve_path,
            mp.libelle as modepaiement,
            mp.id as modepaiement_id,
            dp.statut_paiement,
            dp.id_reduction,
            p.type,
            p.id as paiement_id,
            reductions.id as reduction_ref_id,
            reductions.pourcentage,
            dp.have_reduction_dsc,
            dp.reference_paiement_partiel,
            p.num_transaction,
            dp.montant_restant,
            formations.is_formation_dsc,
            formations.theme
        ")
        ->selectRaw('EXISTS (
            SELECT 1 FROM paiements_tranches pt
            WHERE pt.details_paiements_id = dp.id
            AND pt.statut_traitement = "ATTENTE"
        ) as check_statut_ptranche')
        ->selectRaw('EXISTS (
            SELECT 1 FROM paiements_tranches pt
            WHERE pt.details_paiements_id = dp.id
            AND pt.statut_traitement = "VALIDER"
        ) as check_recu_ptranche');

    $srDB = $request->date_debut;
    $srDF = $request->date_fin;

    $queryData->where(function ($q) use ($srDB, $srDF) {
        $q->whereDate('inscriptions.updated_at', '>=', $srDB)
        ->whereDate('inscriptions.updated_at', '<=', $srDF);
    });

    $liste = $queryData->get();

     // Detailspaiement::where('reference_paiement','ERS-2021-FACT-000001');
    // return view('public.recu',compact("paiement","details_paiement"));
    $formatter = new NumberFormatter('fr', NumberFormatter::SPELLOUT);
    $formatter->setTextAttribute(NumberFormatter::DEFAULT_RULESET, "%spellout-ordinal");

    // share data to view
  view()->share([
        'liste' => $liste,
        'date_debut' => $request->date_debut,
        'date_fin' => $request->date_fin
]);

   $pdf = PDF::loadView('documents.pdf_liste_paiements_periodique');
   $customPaper = array(0,0,720,1440);
   $pdf->setPaper($customPaper, 'landscape');

    $dateDb = date('d_m_Y_His', strtotime($request->date_debut));
    $dateDf = date('d_m_Y_His', strtotime($request->date_fin));
   // download PDF file with download method
  return $pdf->download('SIGWEB_Liste_paiements_'.$dateDb.'_'.$dateDf.'.pdf');


        }
        


    } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }


    public function listeRoles(Request $request)
    {

    try {
        //code...   
            # code...
            $fileName = 'SIGWEB_LISTE_ROLES_' . date('dmY'). '.xlsx';
            Log::info('test log ok');
            return Excel::download(
                new ExportsListeRoles(),
                $fileName
            );  

    } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }


    
    public function listeHistoriques(Request $request)
    {

    try {
        //code...   
            # code...
            $fileName = 'SIGWEB_LISTE_HISTORIQUES_ACTIONS_' . date('dmY'). '.xlsx'; 
            return Excel::download(
                new ExportsListeHistoriques(),
                $fileName
            );  

    } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }


    
    public function listeHistoriquesPeriode(Request $request)
    {
        $request->validate([ 
            'date_debut'  => 'nullable|date',
            'date_fin'    => 'nullable|date|after_or_equal:date_debut', 
        ]);

        try { 
            $fileName = 'SIGWEB_LISTE_HISTORIQUES_ACTIONS_' . date('dmY'). '.xlsx';  
 
            return Excel::download(
                new ExportsListeHistoriquesPeriode($request->date_debut, $request->date_fin), 
                $fileName);
        } catch (\Exception $e) {
            return ResponseHandler::error("Erreur lors de l'export", 500, $e->getMessage());
        }


    }
}
