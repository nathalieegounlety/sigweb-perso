<?php

namespace App\Http\Controllers;

use App\Models\Formations;
use App\Models\FormationsDsc;
use App\Models\points_de_paiements;
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
        $date_now = date('Y-m-d');
        // $date_now = date('Y-m-d',strtotime($date_now . "-45 days"));
        $date_actuelle = date('m/d/Y');
        $get_date = date('Y-m-d',strtotime($date_actuelle . "+60 days"));

        $queryFormations = Formations::select('formations.*')
            ->with('getTypeformation:id,libelle')
            ->with('tarifications')
            ->whereDate('formations.date_fin','>=',$date_now)
            ->where('formations.is_formation_dsc',false)
            ->where('formations.statut','ACTIF')
            ->where('formations.enable',true)
            ->orderBy("formations.date_debut","ASC")
            ->paginate(9) ;
        return response()->json($queryFormations);
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


    public function detailsformationsdsc($code_formation)
{
    $formation = Formations::where("code_formation", $code_formation)->first();

    if (!$formation) {
        return response()->json(['message' => 'Formation non trouvée'], 404);
    }

    $formations_dsc = FormationsDsc::where('formation_id', $formation->id)->get();

    return response()->json([
        'formation' => $formation,
        'formations_dsc' => $formations_dsc,
    ]);
}


    public function getPointsPaiements()
    {
        $liste_pointsdepaiements = points_de_paiements::select('point_de_paiements.*')
            ->whereEnable(true)
            ->orderBy("pays", "ASC")
            ->get();

        // Retourne une réponse JSON avec les données
        return response()->json([
            'success' => true,
            'message' => 'Liste des moyens de paiement récupérée avec succès',
            'data' => $liste_pointsdepaiements
        ]);
    }
}
