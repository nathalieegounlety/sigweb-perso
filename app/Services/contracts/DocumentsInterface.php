<?php

namespace App\Services\contracts;

use App\Models\Formations;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface DocumentsInterface
{
    public function getFormationVisuel(int $id);
    public function downloadRecuPaiement(string $reference);
    public function getUrlSignedRecuPaiement(string $reference): \Illuminate\Http\JsonResponse;
    public function getUrlSignedRecuPaiementDsc(string $reference): \Illuminate\Http\JsonResponse;
    public function getUrlSignedRecuPaiementOuvrage(string $reference): \Illuminate\Http\JsonResponse;
    public function downloadProforma(string $reference);
    public function getUrlProformaSigned(string $reference): \Illuminate\Http\JsonResponse;
    public function getUrlProformaDscSigned(string $reference): \Illuminate\Http\JsonResponse;
    public function downloadProformaDsc(string $reference);
    public function getListeAttestationParticipant(Request $request, string $code_formation); //: LengthAwarePaginator;
    public function showAttestationParticipantVisio($rapport_id);
    public function getUrlConsultationAttestationVisio($rapport_id);
}
