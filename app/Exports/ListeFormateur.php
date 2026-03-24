<?php

namespace App\Exports;

use App\Models\Formateurs;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class ListeFormateur implements FromView
{ 

    /**
     * @return \Illuminate\Support\Collection
     */
    public function view(): View
    {  
       $liste = Formateurs::orderBy('nom',"asc")->get();
        return view('exports.liste_formateur', [
            'liste' => $liste 
        ]);

    }   
}
