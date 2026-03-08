<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    public function __construct(){ 
        $this->middleware('auth:api');
    }

    public function show(Request $request, string $path): StreamedResponse
    {
        // Vérifier que le fichier existe
        if (!Storage::disk('pieces_auditeur')->exists($path)) {
            abort(404);
        }

        // Optionnel : vérifier que l'utilisateur a accès à CE fichier 

        $mime = Storage::disk('pieces_auditeur')->mimeType($path);
        $size = Storage::disk('pieces_auditeur')->size($path);

        return response()->stream(function () use ($path) {
            $stream = Storage::disk('pieces_auditeur')->readStream($path);
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type'        => $mime,
            'Content-Length'      => $size,
            'Content-Disposition' => 'inline', // "inline" = prévisualisation, "attachment" = téléchargement
            'Cache-Control'       => 'private, max-age=3600',
        ]);
    }
}
