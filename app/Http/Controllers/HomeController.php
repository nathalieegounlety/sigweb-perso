<?php

namespace App\Http\Controllers;

use App\Models\Formations;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    //
    // Récupérer les 6 dernières formations (accueil)
    public function FormationsRecentes()
    {
             //   $formations = Formations::where('date_limite', '>=', now())
        $formations = Formations::where('statut', 'ACTIF')
            ->where('enable', 1)
            ->where('afficher', 1)
          //  ->orderBy('date_limite', 'asc')
            ->take(6)
            ->get([
                'id', 'code_formation', 'theme as titre', 'date_debut', 'date_fin',
                'date_limite', 'lieu', 'visuel', 'fm_presentielle', 'fm_visio', 'cout_formation'
            ]);
        return response()->json($formations);
    }

    // Récupérer toutes les formations (catalogue, avec pagination)
    public function catalogue(Request $request)
    {
        $formations = Formations::
            //where('date_limite', '>=', now())
            where('statut', 'ACTIF')
            ->where('enable', 1)
            ->where('afficher', 1)
           // ->orderBy('date_limite', 'asc')
            ->paginate(9, [
                'id', 'code_formation', 'theme as titre', 'date_debut', 'date_fin',
                'date_limite', 'lieu', 'visuel', 'fm_presentielle', 'fm_visio', 'cout_formation'
            ]);
        return response()->json($formations);
    }
}
