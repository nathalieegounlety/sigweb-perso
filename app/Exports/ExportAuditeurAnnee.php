<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;

class ExportAuditeurAnnee implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $auditeur_annees = DB::table('rapports')
        ->join('formations', 'formations.code_formation', '=', 'rapports.code_formation') 
        ->select(DB::raw("count(rapports.code_formation) as nbre_auditeur, DATE_FORMAT(formations.date_debut,'%Y') as dates")) 
        ->groupBy('dates')
        ->orderBy('dates')
        ->get();

        $auditeur_annees_statique = DB::table('statistique') 
        ->select('statistique.participants as nbre_auditeur','statistique.annees as dates')  
        ->where('statistique.annees','!=',2021)
        ->get();

        return view('statistiques.export_auditeur_annee', [
            'auditeur_annees' => $auditeur_annees,
            'auditeur_annees_statique' => $auditeur_annees_statique, 
        ]);

    }
}
