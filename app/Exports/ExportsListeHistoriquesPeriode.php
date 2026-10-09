<?php

namespace App\Exports;

use App\Models\Historiqueactions;  
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\View\View;

class ExportsListeHistoriquesPeriode implements FromView
{ 

    public function __construct( 
        private $date_debut,
        private  $date_fin
    )
    { 
    } 

    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $queryData =  Historiqueactions::whereDate('created_at','>=',$this->date_debut)->whereDate('created_at','<=',$this->date_fin)->get();

        return view('exports.liste_historiques', [
            'liste' => $queryData, 
            'date_debut' => $this->date_debut,
            'date_fin' => $this->date_fin
        ]);
    }
}
