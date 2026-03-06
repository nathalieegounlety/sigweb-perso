<?php

namespace App\Exports;

use App\Models\Formations;
use App\Models\Inscription;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;


class ExportsInscritsFormations implements FromView
{ 

    public function __construct()
    {
        
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $stat_formation = Formations::    
            get(
               [   
                   'formations.code_formation as code', 
                   'formations.id as id_formation', 
                   'formations.date_debut', 
                   DB::raw("(SELECT count(i.id) FROM inscriptions as i  
                   INNER JOIN tarificationsformations as tf ON tf.id = i.tarif_formation_id 
                   INNER JOIN grilletarifaires as gt ON gt.id = tf.id_grilletarifaire 
                   WHERE i.code_formation = code 
                   AND i.presentiel = 1 
                    ) as nbre_presentiel"),
                    DB::raw("(SELECT count(i.id) FROM inscriptions as i  
                    INNER JOIN tarificationsformations as tf ON tf.id = i.tarif_formation_id 
                    INNER JOIN grilletarifaires as gt ON gt.id = tf.id_grilletarifaire 
                    WHERE i.code_formation = code 
                    AND i.presentiel = 0  
                     ) as nbre_visio"),
                     DB::raw("(SELECT count(r.id) FROM rapports as r   
                WHERE r.code_formation = code ) as nbre_participant")
    
               ]
            ) ;     
 
        return view('statistiques.export_inscrit_formation', [
            'stat_formation' => $stat_formation, 
        ]);

    }
}
