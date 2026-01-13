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
        $formations = Formations::whereEnable(true)
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
            ->where('is_formation_dsc',false)
           ->orderBy('date_debut', 'DESC')
            ->paginate(9, [
                'id', 'code_formation', 'theme as titre', 'date_debut', 'date_fin',
                'date_limite', 'lieu', 'visuel', 'fm_presentielle', 'fm_visio', 'cout_formation'
            ]);
        return response()->json($formations);
    }

    public function detailsformations($id)
    {
        // Récupérer la formation par son ID
        $formation = Formations::find($id);

        // Si la formation n'existe pas, retourner une erreur 404
        if (!$formation) {
            return response()->json([
                'message' => 'Formation non trouvée',
            ], 404);
        }

        // Retourner les détails de la formation
        return response()->json($formation);
    }
}
