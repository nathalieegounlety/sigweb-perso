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

    public function getListeNoteFinaleCandidats(Request $request, string $code_formation); 
    // 
    public function getListeNotesBrutes(Request $data): \Illuminate\Http\JsonResponse;
    // 
    public function doCalculMg(string $code_formation): \Illuminate\Http\JsonResponse;

    public function getRegistreNotesCandidats(Request $request, string $code_formation); 
    
    public function infoConsultationDiplome(string $code_user, string $code_formation): \Illuminate\Http\JsonResponse;

}
