<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permissions): Response
    {
        $user = auth('api')->user(); // récupération de l'utilisateur via JWT

        if (!$user) {
            return response()->json([
                'message' => 'Accès refusé : utilisateur non authentifié',
            ], 403);
        }

        // Transformer la chaîne en tableau
        $permissionsArray = explode(',', $permissions);

        // Récupérer toutes les permissions de l'utilisateur via ses rôles
        $userPermissions = $user->roles
            ->flatMap->permissions   // récupère toutes les permissions de tous les rôles
            ->pluck('libelle')       // ne garde que le libellé
            ->toArray();

        // Vérifie qu'au moins une permission demandée est possédée par l'utilisateur
        if (!count(array_intersect($permissionsArray, $userPermissions))) {
            return response()->json([
                'message' => 'Accès refusé : permission requise',
            ], 403);
        }


        return $next($request);
    }
}
