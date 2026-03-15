<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;

class ExportEvolutionFormation implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $formations_par_annees = DB::table('formations') 
        ->select(DB::raw("count(formations.code_formation) as nbre_formation, DATE_FORMAT(formations.date_debut,'%Y') as dates")) 
        ->groupBy('dates')
        ->orderBy('dates')
        ->get();

        $formations_par_annees_statique = DB::table('statistique') 
        ->select('statistique.formations as nbre_formation','statistique.annees as dates')  
        ->where('statistique.annees','!=',2021)
        ->get(); 

        return view('statistiques.export_evolution_formation', [
            'formations_par_annees' => $formations_par_annees,
            'formations_par_annees_statique' => $formations_par_annees_statique, 
        ]);
    }
}
