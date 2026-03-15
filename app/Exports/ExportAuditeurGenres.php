<?php

namespace App\Exports;

use App\Models\Inscription;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportAuditeurGenres implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        
        //liste des auditeurs par genre
        $auditeur_genre_masculin = Inscription:: 
        join("auditeurs","auditeurs.email","inscrits.email")
        ->join("rapports", function($join){ 
            $join->on('rapports.code_formation', '=', 'inscrits.code_formation');
            $join->on('rapports.email', '=', 'inscrits.email');
            $join->on('rapports.id_webinaire', '=', 'inscrits.id_webinaire');
        })  
        ->where("auditeurs.genre","M")
        ->get()
        ->count(); 
         //liste des auditeurs par feminin
         $auditeur_genre_feminin = Inscription:: 
         join("auditeurs","auditeurs.email","inscrits.email")
         ->join("rapports", function($join){ 
             $join->on('rapports.code_formation', '=', 'inscrits.code_formation');
             $join->on('rapports.email', '=', 'inscrits.email');
             $join->on('rapports.id_webinaire', '=', 'inscrits.id_webinaire');
         })  
         ->where("auditeurs.genre","F")
         ->get()
         ->count();  

         return view('statistiques.export_auditeur_genre', [
            'auditeur_genre_masculin' => $auditeur_genre_masculin,
            'auditeur_genre_feminin' => $auditeur_genre_feminin, 
        ]);

    }
}
