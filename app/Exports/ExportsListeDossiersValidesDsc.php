<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\Inscriptions;
use Illuminate\Support\Facades\DB;

class ExportsListeDossiersValidesDsc implements FromView
{
     private $code_formation;

    public function __construct($code_formation)
    {
        $this->code_formation = $code_formation;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function view(): View
    {

        $code_formation = $this->code_formation;

        // liste des inscrits
        $liste_inscrits = Inscriptions::leftjoin('auditeurs', 'inscriptions.email', '=', 'auditeurs.email')
            ->leftjoin('tarificationsformations', 'inscriptions.tarif_formation_id', '=', 'tarificationsformations.id')
            ->leftjoin('formations', 'inscriptions.code_formation', '=', 'formations.code_formation')
            ->leftjoin('grilletarifaires', 'tarificationsformations.id_grilletarifaire', '=', 'grilletarifaires.id')
            ->leftjoin("detailspaiements", function ($leftjoin) {
                $leftjoin->on('detailspaiements.email', '=', 'inscriptions.email');
                $leftjoin->on('detailspaiements.id_tarification_formation', '=', 'inscriptions.tarif_formation_id');
            }) 
            ->leftJoin('paiements', 'paiements.code_inscription', '=', 'inscriptions.code_inscription')
            ->where('inscriptions.code_formation', $code_formation)
            ->where('inscriptions.is_delete', false)
            ->where('inscriptions.traiter', true) 
            ->where('inscriptions.statut_dossier','VALIDEE')

            ->get(['inscriptions.id','inscriptions.sync_zoom','inscriptions.code_inscription','inscriptions.observations', 'inscriptions.created_at', 'auditeurs.nom', 'auditeurs.prenoms', 'formations.fm_presentielle', 
            'formations.fm_visio', "auditeurs.intitule_poste","auditeurs.code_pays","auditeurs.nationalite",
            "auditeurs.telephone","auditeurs.email","auditeurs.genre", 'grilletarifaires.type_tarif as mode', 
            'inscriptions.type_formation as mode','grilletarifaires.montant', 'inscriptions.type_formation', 
            'detailspaiements.statut_paiement', 'auditeurs.id as id_auditeur', 'inscriptions.is_formation_dsc', 
            'inscriptions.is_delete','inscriptions.code_formation', 
        DB::raw('COALESCE(paiements.montant_total, 0) as montant_total_a_payer'), 
        DB::raw("(
            SELECT COALESCE(SUM(pt.montant_paye), 0)
            FROM paiements_tranches pt
            WHERE pt.statut_traitement = 'VALIDER'
              AND (
                    pt.reference = paiements.reference
                 OR pt.reference LIKE CONCAT(REPLACE(REPLACE(paiements.reference, '!', '!!'), '_', '!_'), '!_%') ESCAPE '!'
              )
        ) as montant_total_paye"),]);

        return view('statistiques.export_liste_dossier_valide_dsc', [
            'liste_inscrits' => $liste_inscrits,
        ]);

    }
}
