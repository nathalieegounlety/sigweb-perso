<?php

namespace App\Services\contracts;

use Illuminate\Http\Request;

interface AfribapayInterface
{
    // déclaration des interfaces à utiliser
    public function getToken();
    public function generateToken(); 
    public function listeVariables(): \Illuminate\Http\JsonResponse; 
    public function payIn(Request $request): \Illuminate\Http\JsonResponse; 
    public function checkPayIn(Request $request, String $id): \Illuminate\Http\JsonResponse;  
    public function storePaiementPartielAFB(Request $request, String $id): \Illuminate\Http\JsonResponse;

}
