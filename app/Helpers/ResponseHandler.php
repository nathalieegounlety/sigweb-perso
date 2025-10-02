<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ResponseHandler
{
    /**
     * Génère une réponse JSON standard
     */
    public static function generateResponse(string $message, int $status, $data = null): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'status'  => $status,
            'data'    => $data,
        ], $status);
    }

    /**
     * Réponse pour succès
     */
    public static function success(string $message, $data = null, int $status = 200): JsonResponse
    {
        return self::generateResponse($message, $status, $data);
    }

    /**
     * Réponse pour erreurs
     */
    public static function error(string $message, int $status = 400, $data = null): JsonResponse
    {
        return self::generateResponse($message, $status, $data);
    }
}
