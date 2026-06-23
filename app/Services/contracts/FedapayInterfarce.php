<?php

namespace App\Services\contracts;

use Illuminate\Http\Request;

interface FedapayInterfarce
{
    public function storePaiementFedapay(Request $request): \Illuminate\Http\JsonResponse;
    public function storePaiementPartielFedapay(Request $request): \Illuminate\Http\JsonResponse;
    public function storePaiementOuvrageFedapay(Request $request): \Illuminate\Http\JsonResponse;
    public function storePaiementDscFedaPay(Request $request): \Illuminate\Http\JsonResponse;
    public function storePaiementPartielDscFedaPay(Request $request): \Illuminate\Http\JsonResponse;

}
