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
    public function showAttestationParticipant($rapport_id);
    public function getUrlConsultationAttestation($rapport_id);
    public function sendMailAttestationParticipant($rapport_id): \Illuminate\Http\JsonResponse;
    public function downloadAttestationParticipant($rapport_id);
    public function downloadListeParticipantVisio($code_formation);
    public function downloadListeParticipantPresentiel($code_formation);
    public function getUrlListeParticipantPresentiel($code_formation);
    public function getUrlListeParticipantVisio($code_formation);
    public function getUrlAllAttestationParticipant($code_formation);
    public function downloadAllAttestationParticipant($code_formation);
    public function sendAllMailAttestationParticipant(Request $request): \Illuminate\Http\JsonResponse;
}
