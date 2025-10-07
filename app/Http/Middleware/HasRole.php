<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class HasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $roles): Response
    {
        // 1️⃣ Vérifier le token JWT
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'Token expiré'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'Token invalide'], 401);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        //$user = auth('api')->user();

        if (!$user) {
            return response()->json(['message' => 'Accès refusé : utilisateur non authentifié'], 403);
        }

        $rolesArray = explode(',', $roles);
        $userRoles = $user->roles->pluck('libelle')->toArray(); // utilise ta relation

        if (!count(array_intersect($rolesArray, $userRoles))) {
            return response()->json(['message' => 'Accès refusé : rôle requis'], 403);
        }

        return $next($request);
    }
}
