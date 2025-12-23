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
        $default_path = 'visuel_formation/visuel_not_found.png';
        try {
            $checkImg = true;
            if (!Storage::disk('public')->exists($path)) {
                //return ResponseHandler::error('Fichier non trouvé : ' . $doc->visuel, 404);
                $checkImg = false;
            }
            if($checkImg){
                return Storage::disk('public')->response($path);
            }else{
                return Storage::disk('public')->response($default_path);

            }
        }catch (\Exception $exception){
            return Storage::disk('public')->response($default_path);
        }

    }
}
