<?php

namespace App\Exports;

use App\Models\Historiqueactions; 
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\View\View;

class ExportsListeHistoriques implements FromView
{ 

    public function __construct()
    { 
    } 

    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $queryData =  Historiqueactions::all();

        return view('exports.liste_historiques', [
            'liste' => $queryData, 
        ]);
    }
}
