<?php

namespace App\Http\Controllers;
use App\Models\Formations;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class StatistiquesController extends Controller
{
    public function statsEtatInscrits()
    {
        $stat_formation = Formations::
            orderBy("formations.id", "desc")
            ->get([
                'formations.code_formation as code',
                'formations.id as id_formation',
                'formations.date_debut',
                'formations.theme',
                DB::raw("(SELECT count(i.id) FROM inscriptions as i
                    LEFT JOIN tarificationsformations as tf ON tf.id = i.tarif_formation_id
                    LEFT JOIN grilletarifaires as gt ON gt.id = tf.id_grilletarifaire
                    WHERE i.code_formation = code AND i.presentiel = 1) as nbre_presentiel"),
                DB::raw("(SELECT count(i.id) FROM inscriptions as i
                    LEFT JOIN tarificationsformations as tf ON tf.id = i.tarif_formation_id
                    LEFT JOIN grilletarifaires as gt ON gt.id = tf.id_grilletarifaire
                    WHERE i.code_formation = code AND i.presentiel = 0) as nbre_visio"),
                DB::raw("(SELECT count(r.id) FROM rapports as r
                    WHERE r.code_formation = code) as nbre_participant")
            ]);
    
        return response()->json($stat_formation);
    }
    
}
