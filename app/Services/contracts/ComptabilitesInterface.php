<?php

namespace App\Services\contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface ComptabilitesInterface
{

    public function getCommandes(Request $request) ;//: LengthAwarePaginator;
    public function getPaiementsattente(Request $request); //: LengthAwarePaginator;
    public function getPaiementsvalides(Request $request);//: LengthAwarePaginator;
    public function getDetailsCommandeFormation(Request $request): \Illuminate\Http\JsonResponse;
    public function getDetailsCommandeOuvrage(Request $request): \Illuminate\Http\JsonResponse;
    public function getDetailsCommandeDsc(Request $request): \Illuminate\Http\JsonResponse;
    public function storeDeplacerCommandeFormation(Request $request): \Illuminate\Http\JsonResponse;
    public function storeDeplacerCommandeDsc(Request $request): \Illuminate\Http\JsonResponse;
    public function saveDeclassercommande(Request $request): \Illuminate\Http\JsonResponse;
    public function getDetailsPaiementOuvrage(Request $request): \Illuminate\Http\JsonResponse;
    public function getDetailsPaiementDsc(Request $request): \Illuminate\Http\JsonResponse;
    public function getDetailsPaiementValiderDsc(Request $request): \Illuminate\Http\JsonResponse;
    public function validerPaiement(Request $request): \Illuminate\Http\JsonResponse;
    public function validerPaiementPartiel(Request $request): \Illuminate\Http\JsonResponse;
    public function validerPaiementDsc(Request $request): \Illuminate\Http\JsonResponse;
    public function getListePaiements(Request $request); //: LengthAwarePaginator;
    public function getDetailsProcessPaiementDsc(Request $request): \Illuminate\Http\JsonResponse;
    public function validerProcessPaiementDsc(Request $request): \Illuminate\Http\JsonResponse;
}
