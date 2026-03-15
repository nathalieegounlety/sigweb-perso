<?php

namespace App\Exports;

use App\Models\Inscription;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;

class ExportAuditeurPays implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        //
        //liste des auditeurs par pays
        $auditeur_pays = Inscription:: 
        leftjoin("auditeurs","auditeurs.email","inscrits.email")
        ->join("rapports", function($join){ 
            $join->on('rapports.code_formation', '=', 'inscrits.code_formation');
            $join->on('rapports.email', '=', 'inscrits.email');
            $join->on('rapports.id_webinaire', '=', 'inscrits.id_webinaire');
        })  
        ->groupBy(DB::raw("inscrits.pays"))     
        ->get(
           [   
            'auditeurs.pays as get_pays', 
            DB::raw("(SELECT COUNT(t.pays) FROM auditeurs as t INNER JOIN inscrits ON t.email = inscrits.email  INNER JOIN rapports
             ON inscrits.code_formation = rapports.code_formation
             WHERE inscrits.email = rapports.email 
             AND inscrits.id_webinaire = rapports.id_webinaire
             AND t.pays = get_pays) as nbre")
            ]
        ) ;  

        return view('statistiques.export_auditeur_pays', [
            'auditeurs' => $auditeur_pays, 
        ]);

    }
}
