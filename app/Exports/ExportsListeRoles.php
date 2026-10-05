<?php

namespace App\Exports;

use App\Models\Roles;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\View\View;

class ExportsListeRoles implements FromView
{ 

    public function __construct()
    { 
    } 

    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $queryData =  Roles::all();

        return view('exports.liste_roles', [
            'liste' => $queryData, 
        ]);
    }
}
