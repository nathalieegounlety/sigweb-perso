<?php

namespace App\Services\contracts;

use Illuminate\Http\Request;

interface PaiementsInterface
{
    public function getInfosPaiement(string $reference): \Illuminate\Http\JsonResponse;
    public function getInfoPaiementPartiel(Request $request): \Illuminate\Http\JsonResponse;
    public function enregistrerPaiementPartiel(Request $request): \Illuminate\Http\JsonResponse;


}
