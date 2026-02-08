<?php

namespace App\Exports;

use App\Models\Formations;
use App\Models\Inscriptions;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportListePresence implements FromView
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

        // information sur la formation en cours
        $formation = Formations::where("code_formation",$code_formation)->get()->first();
        // liste des inscrits en présentiel

        $liste_inscrits_presentiel = Inscriptions::
        leftjoin("auditeurs","auditeurs.email","=","inscriptions.email")
            ->leftjoin("tarificationsformations","tarificationsformations.id","=","inscriptions.tarif_formation_id")
            ->leftjoin("formations","formations.code_formation","=","inscriptions.code_formation")
            ->leftjoin("grilletarifaires","grilletarifaires.id","=","tarificationsformations.id_grilletarifaire")
            ->where("inscriptions.presentiel",1)
            ->where("inscriptions.code_formation",$code_formation)
            ->orderBy("auditeurs.nom","asc")
            ->get(["inscriptions.code_formation","auditeurs.prenoms","auditeurs.nom","auditeurs.intitule_poste","auditeurs.code_pays","auditeurs.nationalite","auditeurs.telephone","auditeurs.email","auditeurs.genre"]);

        return view('statistiques.export_liste_presence', [
            'formation' => $formation,
            'liste_inscrits_presentiel' => $liste_inscrits_presentiel,
        ]);

    }
}
