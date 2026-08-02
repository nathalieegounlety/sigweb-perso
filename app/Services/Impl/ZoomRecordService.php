<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler; 
use App\Models\Enregistrementszooms; 
use App\Models\Formations;
use App\Models\Sessionformations;
use App\Services\Contracts\ZoomRecordInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ZoomRecordService implements ZoomRecordInterface
{
    /**
     * Récupère les enregistrements Zoom pour une formation donnée.
     */
    public function getDetails(Request $request)
    {
        // Récupérez la formation par code_formation
        $formation = Formations::where('code_formation', $request->code_formation)->firstOrFail();

        // Récupérez les enregistrements liés à cette formation
        $enregistrements = Enregistrementszooms::where('formation_id', $formation->id)
            ->with(['sessionFormation', 'formation', 'updatedBy'])
            ->get();

        return ResponseHandler::success('Liste des enregistrements', $enregistrements);
    }

    /**
     * Récupère les sessions de formation pour une formation donnée.
     */
    public function getSessions(string $code_formation)
    {
        // Récupérez les sessions liées à formation_id 
        // $sessions = Sessionformations::where('code_formation', $code_formation)
        //     ->get(['id', 'id_webinaire', 'duree_reel']);

        $sessions = Sessionformations::where('code_formation', $code_formation)
            ->get(["id as code", DB::raw("CONCAT(id_webinaire, ' - ', duree_reel) AS name")]);
            // 

        return ResponseHandler::success('Liste des sessions', $sessions);
    }

    /**
     * Enregistre un nouvel enregistrement Zoom.
     */
    public function store(Request $request)
    {
        // Vérifier si l'enregistrement existe déjà
        $formation = Formations::whereCodeFormation($request->code_formation)->first();
        $checkRecord = Enregistrementszooms::where('formation_id', $formation->id)
            ->where('titre', $request->titre)
            ->where('url_youtube', $request->url_youtube)
            ->exists();

        if ($checkRecord) {
            return ResponseHandler::error("L'enregistrement existe déjà", 409);
        }

        $enregistrement = new Enregistrementszooms();
        $enregistrement->setAttribute("formation_id",$formation->id);
        $enregistrement->setAttribute("code_formation",$formation->code_formation);
        $enregistrement->setAttribute("titre",$request->titre);
        if ($request->session_formation_id && !empty($request->session_formation_id)) {
            # code...
            $enregistrement->setAttribute("sessionformation_id",$request->session_formation_id);
        }
        $enregistrement->setAttribute("url_youtube",$request->url_youtube);
        $user = auth('api')->user();
        $enregistrement->setAttribute("updated_by",$user->id);
        $enregistrement->setAttribute("created_by",$user->id);  
        $enregistrement->save();

        return ResponseHandler::success('Enregistrement ajouté avec succès', $enregistrement);
    }

    /**
     * Met à jour un enregistrement Zoom.
     */
    public function update(Request $request)
    {
        $formation = Formations::whereCodeFormation($request->code_formation)->first();
        $enregistrement = Enregistrementszooms::findOrFail($request->id);

        // Vérifier si le nouvel enregistrement existe déjà (hors mise à jour)
        $checkRecord = Enregistrementszooms::where('id', '!=', $request->id)
            ->where('formation_id', $formation->id)
            ->where('titre', $request->titre)
            ->where('url_youtube', $request->url_youtube)
            ->exists();

        if ($checkRecord) {
            return ResponseHandler::error("L'enregistrement existe déjà", 409);
        }

        $enregistrement->setAttribute("formation_id",$formation->id);
        $enregistrement->setAttribute("code_formation",$formation->code_formation);
        $enregistrement->setAttribute("titre",$request->titre);
        if ($request->session_formation_id && !empty($request->session_formation_id)) {
            # code...
            $enregistrement->setAttribute("sessionformation_id",$request->session_formation_id);
        }
        $enregistrement->setAttribute("url_youtube",$request->url_youtube);
        $user = auth('api')->user();
        $enregistrement->setAttribute("updated_by",$user->id);  
        $enregistrement->update();

        return ResponseHandler::success('Enregistrement mis à jour avec succès', $enregistrement);
    }

    /**
     * Supprime un enregistrement Zoom.
     */
    public function remove(Request $request)
    {
        $enregistrement = Enregistrementszooms::findOrFail($request->id);
        $enregistrement->delete();

        return ResponseHandler::success('Enregistrement supprimé avec succès');
    }
}
