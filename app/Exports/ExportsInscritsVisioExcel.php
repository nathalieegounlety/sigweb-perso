<?php

namespace App\Exports;

use App\Models\Inscriptions;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportsInscritsVisioExcel implements FromView
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

        // liste des inscrits en visio
        $liste_inscrits_visio = Inscriptions::
        leftjoin("auditeurs","auditeurs.email","=","inscriptions.email")
            ->leftjoin("tarificationsformations","tarificationsformations.id","=","inscriptions.tarif_formation_id")
            ->leftjoin("formations","formations.code_formation","=","inscriptions.code_formation")
            ->leftjoin("grilletarifaires","grilletarifaires.id","=","tarificationsformations.id_grilletarifaire")
            //->where("grilletarifaires.type_tarif","PRESENTIEL")
            ->where(function($q) {
                $q->where([['grilletarifaires.type_tarif','=',"VISIO"]])
                    ->orWhere([['grilletarifaires.type_tarif','=',"GRATUIT"]]);
            })
            //->orWhere("grilletarifaires.type_tarif","GRATUIT")
            ->where("formations.fm_visio",true)
            ->where("inscriptions.code_formation",$code_formation)
            ->orderBy("auditeurs.nom","asc")
            ->get(['inscriptions.created_at','auditeurs.nom','auditeurs.prenoms','auditeurs.email']);

        return view('statistiques.export_inscrit_visio', [
            'liste_inscrits_visio' => $liste_inscrits_visio,
        ]);

    }
}
