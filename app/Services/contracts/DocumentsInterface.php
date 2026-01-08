<?php

namespace App\Services\contracts;

use App\Models\Formations;

interface DocumentsInterface
{
    public function getFormationVisuel(int $id);
    public function downloadRecuPaiement(string $reference);
    public function getUrlSignedRecuPaiement(string $reference): \Illuminate\Http\JsonResponse;
    public function getUrlSignedRecuPaiementDsc(string $reference): \Illuminate\Http\JsonResponse;
    public function getUrlSignedRecuPaiementOuvrage(string $reference): \Illuminate\Http\JsonResponse;
    public function downloadProforma(string $reference);
    public function getUrlProformaSigned(string $reference): \Illuminate\Http\JsonResponse;
}
