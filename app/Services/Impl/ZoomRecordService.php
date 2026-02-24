<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Enregistrementszoom; // Utilisez UNIQUEMENT ce nom (sans "s")
use App\Models\Enregistrementszooms;
use App\Models\SessionFormation;    // Utilisez UNIQUEMENT ce nom (sans "s")
use App\Models\Formations;
use App\Models\Sessionformations;
use App\Services\Contracts\ZoomRecordInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function getSessions(Request $request)
    {
        // Récupérez les sessions liées à formation_id
        $sessions = Sessionformations::where('formation_id', $request->formation_id)
            ->get(['id', 'id_webinaire', 'duree_reelle']);

        return ResponseHandler::success('Liste des sessions', $sessions);
    }

    /**
     * Enregistre un nouvel enregistrement Zoom.
     */
    public function store(Request $request)
    {
        // Vérifier si l'enregistrement existe déjà
        $checkRecord = Enregistrementszoom::where('formation_id', $request->formation_id)
            ->where('titre', $request->titre)
            ->where('url_youtube', $request->url_youtube)
            ->exists();

        if ($checkRecord) {
            return ResponseHandler::error("L'enregistrement existe déjà", 409);
        }

        $enregistrement = new Enregistrementszoom();
        $enregistrement->fill($request->all());
        $enregistrement->updated_by = Auth::id();
        $enregistrement->created_by = Auth::id();
        $enregistrement->save();

        return ResponseHandler::success('Enregistrement ajouté avec succès', $enregistrement);
    }

    /**
     * Met à jour un enregistrement Zoom.
     */
    public function update(Request $request)
    {
        $enregistrement = Enregistrementszoom::findOrFail($request->id);

        // Vérifier si le nouvel enregistrement existe déjà (hors mise à jour)
        $checkRecord = Enregistrementszoom::where('id', '!=', $request->id)
            ->where('formation_id', $request->formation_id)
            ->where('titre', $request->titre)
            ->where('url_youtube', $request->url_youtube)
            ->exists();

        if ($checkRecord) {
            return ResponseHandler::error("L'enregistrement existe déjà", 409);
        }

        $enregistrement->update($request->all());
        $enregistrement->updated_by = Auth::id();
        $enregistrement->save();

        return ResponseHandler::success('Enregistrement mis à jour avec succès', $enregistrement);
    }

    /**
     * Supprime un enregistrement Zoom.
     */
    public function remove(Request $request)
    {
        $enregistrement = Enregistrementszoom::findOrFail($request->id);
        $enregistrement->delete();

        return ResponseHandler::success('Enregistrement supprimé avec succès');
    }
}
