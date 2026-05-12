<?php

namespace App\Services\contracts;
 
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface CandidaturesInterface
{
    // déclaration des interfaces à utiliser 

    public function getListeCandidats(Request $request): LengthAwarePaginator; 
    
    public function saveUpdateInfoCandidat(Request $data): \Illuminate\Http\JsonResponse;
    
    public function getListeFormationsCandidat(string $email): \Illuminate\Http\JsonResponse;
    

}
