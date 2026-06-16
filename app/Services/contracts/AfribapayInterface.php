<?php

namespace App\Services\contracts;  

interface AfribapayInterface
{
    // déclaration des interfaces à utiliser
    public function getToken();
    public function generateToken(); 
    public function listeVariables(): \Illuminate\Http\JsonResponse; 

}
