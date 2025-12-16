<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Formations;
use App\Services\contracts\DocumentsInterface;
use Illuminate\Support\Facades\Storage;

class DocumentsService implements DocumentsInterface
{

    public function getFormationVisuel(int $id)
    {
        // TODO: Implement getFormationVisuel() method.
        // Récupérer le visuel de la formation par son ID
        $doc = Formations::find($id);

        if (!$doc) {
            return ResponseHandler::error('Document non trouvé', 404);
        }

        $path = 'visuel_formation/' . $doc->visuel;

        if (!Storage::disk('public')->exists($path)) {
            return ResponseHandler::error('Fichier non trouvé : ' . $doc->visuel, 404);
        }

        // Option 1 : Forcer le téléchargement (le navigateur propose "Enregistrer sous")
        // return Storage::disk('public')->download($path, $doc->visuel);

        // Option 2 : Afficher directement dans le navigateur (ex: pour images ou PDF)
        //     sans forcer le download
        return Storage::disk('public')->response($path);
    }
}
