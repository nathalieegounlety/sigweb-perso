<?php

namespace App\Services\contracts;

use Illuminate\Http\Request;

interface FedapayInterfarce
{
    public function storePaiementFedapay(Request $request): \Illuminate\Http\JsonResponse;

}
