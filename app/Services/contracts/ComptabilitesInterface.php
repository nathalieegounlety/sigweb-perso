<?php

namespace App\Services\contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface ComptabilitesInterface
{

    public function getCommandes(Request $request) ;//: LengthAwarePaginator;
    public function getPaiementsattente(Request $request); //: LengthAwarePaginator;
    public function getPaiementsvalides(Request $request): LengthAwarePaginator;
    public function getDetailsCommandeFormation(Request $request): \Illuminate\Http\JsonResponse;
    public function getDetailsCommandeOuvrage(Request $request): \Illuminate\Http\JsonResponse;
    public function getDetailsCommandeDsc(Request $request): \Illuminate\Http\JsonResponse;
    public function storeDeplacerCommandeFormation(Request $request): \Illuminate\Http\JsonResponse;
    public function storeDeplacerCommandeDsc(Request $request): \Illuminate\Http\JsonResponse;
    public function saveDeclassercommande(Request $request): \Illuminate\Http\JsonResponse;
}
