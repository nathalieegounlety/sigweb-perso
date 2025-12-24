<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MaintenanceController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }


    /**
     *
     *@param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try{

            $request->validate([
                'identifiant' => 'required|numeric',
                'table' => 'required|string|max:100',
            ]);
            # code...
            $champUpdate = 'enable';
            if (Schema::hasColumn(trim($request->table), $champUpdate)) {
                // La colonne existe
                $champUpdate = 'enable';
            } elseif (Schema::hasColumn(trim($request->table), 'actif')) {
                // La colonne existe
                $champUpdate = 'actif';
                $update_table = DB::table(trim($request->table))->where('id', $request->identifiant)
                    ->update([$champUpdate =>$request->actif, 'updated_at' => new \DateTime()]);
                return response()->json([
                    'message' => "Mise à jour effectuée avec succès.",
                    'status' => true
                ],200);
            }else {
                // La colonne n'existe pas
                $champUpdate = 'is_enable';
            }
            $update_table = DB::table(trim($request->table))->where('id', $request->identifiant)
                ->update([$champUpdate =>$request->actif, 'updated_by' => auth()->user()->id, 'updated_at' => new \DateTime()]);
            return response()->json([
                'message' => "Mise à jour effectuée avec succès.",
                'status' => true
            ],200);


        } catch (\Exception $e) {

            return response()->json([
                'message' => "Echec lors de la mise à jour. <br> Veuillez procéder à la vérification des champs obligatoires " . $e->getMessage()
            ], 500);

        }
    }
}
