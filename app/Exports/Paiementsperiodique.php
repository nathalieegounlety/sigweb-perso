<?php

namespace App\Exports;

use App\Models\Inscriptions;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\View\View;

class Paiementsperiodique implements FromView
{
    private $date_debut;
    private $date_fin;

    public function __construct($date_debut, $date_fin)
    {
        $this->date_debut = $date_debut;
        $this->date_fin = $date_fin;
    } 

    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
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

    $srDB = $this->date_debut;
    $srDF = $this->date_fin;

    $queryData->where(function ($q) use ($srDB, $srDF) {
        $q->whereDate('inscriptions.updated_at', '>=', $srDB)
        ->whereDate('inscriptions.updated_at', '<=', $srDF);
    });

    $liste = $queryData->get();

    return view('exports.liste_paiements', [
        'liste' => $liste,
        'date_debut' => $srDB,
        'date_fin' => $srDF
    ]);
    }
}
