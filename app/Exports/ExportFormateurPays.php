<?php

namespace App\Exports;

use App\Models\Paneliste;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;

class ExportFormateurPays implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
         //liste des formations par pays
         $paneliste = Paneliste::
         groupBy(DB::raw("pays"))  
         ->select(
             [  
             'pays as get_pays',
             DB::raw("(SELECT COUNT(DISTINCT t.email) from panelistes as t WHERE t.pays = get_pays ) as nbre")]
             ) 
         ->get();   

        return view('statistiques.export_formateur_pays', [
            'panelistes' => $paneliste, 
        ]);
    }
}
