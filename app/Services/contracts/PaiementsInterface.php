<?php

namespace App\Services\contracts;

interface PaiementsInterface
{
    public function getInfosPaiement(string $reference): \Illuminate\Http\JsonResponse;

}
