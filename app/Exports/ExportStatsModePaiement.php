<?php

namespace App\Exports;

use App\Models\Formations;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;  
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportStatsModePaiement implements FromView
{ 
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
         // nbre de formation en presentiel
         $md_formations_presentielle = Formations::where('statut','INACTIF')->where('fm_presentielle',1)->whereNull('fm_visio')->count();
         // nbre de formation en visio
         $md_formations_visio = Formations::where('statut','INACTIF')->where('fm_visio',1)->whereNull('fm_presentielle')->count();
         // nbre de formation en bimodale
         $md_formations_bimodale = Formations::where('statut','INACTIF')->where('fm_visio',1)->where('fm_presentielle',1)->count();

        return view('statistiques.export_mode_formation', [
            'md_formations_presentielle' => $md_formations_presentielle,
            'md_formations_visio' => $md_formations_visio,
            'md_formations_bimodale' => $md_formations_bimodale,
        ]);
        
        /*
        // nbre de formation en presentiel
        $md_formations_presentielle = Formations::where('statut','INACTIF')->where('fm_presentielle',1)->whereNull('fm_visio')->count();
        // nbre de formation en visio
        $md_formations_visio = Formations::where('statut','INACTIF')->where('fm_visio',1)->whereNull('fm_presentielle')->count();
        // nbre de formation en bimodale
        $md_formations_bimodale = Formations::where('statut','INACTIF')->where('fm_visio',1)->where('fm_presentielle',1)->count();
*/
       // return Formations::all(); 
     
    
    }

    public function headings(): array
    {
        return [
            'Présentielle',
            'Visio',
            'Bimodal', 
        ];
    }
}
