<?php

namespace App\Http\Controllers;

use App\Models\Autresparticipants;
use App\Models\Formations;
use App\Models\Panelistes;
use App\Models\Participants;
use Illuminate\Http\Request;

class RapportsController extends Controller
{
    public function panelistes()
{
    $liste_formations = Formations::where('statut', 'INACTIF')
        ->where('enable', true)
        ->orWhere('presentiel', 1)
        ->orderBy("id", "DESC")
        ->get();
    return response()->json($liste_formations);
}

public function searchPanelistes(Request $request)
{
    $code_formation = $request->input('code_formation');
    $query = Panelistes::query();

    if ($code_formation !== 'all') {
        $query->where('code_formation', $code_formation);
    }

    $panelistes = $query->get();
    $nbr_total = $panelistes->count();

    return response()->json([
        'panelistes' => $panelistes,
        'nbr_total' => $nbr_total,
        'code_formation' => $code_formation,
    ]);
}
public function participants()
{
    $liste_formations = Formations::where('statut', 'INACTIF')
        ->where('enable', true)
        ->orWhere('presentiel', 1)
        ->orderBy("id", "DESC")
        ->get();
    return response()->json($liste_formations);
}

public function searchParticipants(Request $request)
{
    $code_formation = $request->input('code_formation');
    $query = Participants::query();

    if ($code_formation !== 'all') {
        $query->where('code_formation', $code_formation);
    }

    $participants = $query->get();
    $nbr_total = $participants->count();

    return response()->json([
        'participants' => $participants,
        'nbr_total' => $nbr_total,
        'code_formation' => $code_formation,
    ]);
}
public function autresparticipants()
{
    $liste_formations = Formations::where('statut', 'INACTIF')
        ->where('enable', true)
        ->orWhere('presentiel', 1)
        ->orderBy("id", "DESC")
        ->get();
    return response()->json($liste_formations);
}

public function searchAutresparticipants(Request $request)
{
    $code_formation = $request->input('code_formation');
    $query = Autresparticipants::query();

    if ($code_formation !== 'all') {
        $query->where('code_formation', $code_formation);
    }

    $autresparticipants = $query->get();
    $nbr_total = $autresparticipants->count();

    return response()->json([
        'autresparticipants' => $autresparticipants,
        'nbr_total' => $nbr_total,
        'code_formation' => $code_formation,
    ]);
}
}
