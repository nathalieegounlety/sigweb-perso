<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Models\Tarificationsformation;
use Illuminate\Http\Request;

class TarificationsformationController extends Controller
{
     /**
     * Display a listing of the resource.
     */
    public function getTarif(Request $request)
    {
        // récupérer la liste
        try { 
            $liste_tarif = Tarificationsformation::join("grilletarifaires","grilletarifaires.id","=","tarificationsformations.id_grilletarifaire")
            ->where("tarificationsformations.code_formation",$request->code_formation)
            ->get(["tarificationsformations.id as id_tarif","grilletarifaires.type_tarif","grilletarifaires.montant"]);

            $data = [
                "liste_tarif" => $liste_tarif
            ]; 

            return ResponseHandler::success('ok',$data);

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }
}
