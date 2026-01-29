<?php

namespace App\Services\contracts;

use Illuminate\Http\Request;

interface KkiapayInterfarce
{
    public function storePaiementKkiapay(Request $request): \Illuminate\Http\JsonResponse;
    public function storePaiementPartielKkiapay(Request $request): \Illuminate\Http\JsonResponse;
    public function storePaiementOuvrageKkiapay(Request $request): \Illuminate\Http\JsonResponse;

}
