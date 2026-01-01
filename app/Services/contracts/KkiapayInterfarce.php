<?php

namespace App\Services\contracts;

use Illuminate\Http\Request;

interface KkiapayInterfarce
{
    public function storePaiementKkiapay(Request $request): \Illuminate\Http\JsonResponse;

}
