<?php

namespace App\Exports;
use App\Models\Inscriptions;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView; 

class ExportsInscritsDscSR implements FromView
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
            ->where('inscriptions.code_formation', $code_formation)
            ->where('inscriptions.is_delete', false)
            ->where('inscriptions.traiter', true)
            ->where('inscriptions.statut_dossier','SOUS RESERVE')
            ->get(['inscriptions.id','inscriptions.sync_zoom','inscriptions.code_inscription', 'inscriptions.created_at', 'auditeurs.nom', 'auditeurs.prenoms', 'formations.fm_presentielle', 'formations.fm_visio', "auditeurs.intitule_poste","auditeurs.code_pays","auditeurs.nationalite","auditeurs.telephone","auditeurs.email","auditeurs.genre", 'grilletarifaires.type_tarif as mode', 'inscriptions.type_formation as mode','grilletarifaires.montant', 'inscriptions.type_formation', 'detailspaiements.statut_paiement', 'auditeurs.id as id_auditeur', 'inscriptions.is_formation_dsc', 'inscriptions.is_delete','inscriptions.code_formation']);

        return view('statistiques.export_inscrit_dsc', [
            'liste_inscrits' => $liste_inscrits,
        ]);

    }
}
