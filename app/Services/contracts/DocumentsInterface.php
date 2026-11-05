<?php

namespace App\Services\contracts;

use App\Models\Formations;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface DocumentsInterface
{
    public function getFormationVisuel(int $id);
    public function downloadRecuPaiement(string $reference);
    public function downloadRecuPaiementOuvrage(string $reference);
    public function getUrlSignedRecuPaiement(string $reference): \Illuminate\Http\JsonResponse;
    public function getUrlSignedRecuPaiementDsc(string $reference): \Illuminate\Http\JsonResponse;
    public function getUrlSignedRecuPaiementOuvrage(string $reference): \Illuminate\Http\JsonResponse;
    public function downloadProforma(string $reference);
    public function downloadProformaOuvrage(string $reference);
    public function getUrlProformaSigned(string $reference): \Illuminate\Http\JsonResponse;
    public function getUrlProformaDscSigned(string $reference): \Illuminate\Http\JsonResponse;
    public function getUrlProformaOuvrageSigned(string $reference): \Illuminate\Http\JsonResponse;
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
    public function getUrlAllAttestationPresentielParticipant($code_formation);
    public function downloadAllAttestationParticipant($code_formation);
    public function downloadAllAttestationPresentielParticipant($code_formation);
    public function sendAllMailAttestationParticipant(Request $request): \Illuminate\Http\JsonResponse;
    public function getListeAttestationFormateur(Request $request, string $code_formation);
    public function downloadAttestationFormateur($id);
    public function getUrlAttestationFormateur($id);
    public function downloadConsultationAttestationFormateur($id);
    public function getUrlConsultationAttestationFormateur($id);
    public function sendMailAttestationFormateur($id): \Illuminate\Http\JsonResponse;
    public function sendAllMailAttestationFormateur(Request $request): \Illuminate\Http\JsonResponse;
    public function downloadAllAttestationFormateur($code_formation);
    public function getUrlAllAttestationFormateur($code_formation);
    public function downloadListeFormateur($code_formation);
    public function getUrlListeFormateur($code_formation);    
    public function getListeRapports(Request $request, string $code_formation); //: LengthAwarePaginator;
    public function downloadAttestationMoodleCandidat($id, $code_formation);
    public function getUrlAttestationMoodleCandidat($id, $code_formation);
    public function downloadReleveNoteCandidat($id, $code_formation);
    public function getUrlReleveNoteCandidat($id, $code_formation);
    // impression  par lot des attestations moodle
    public function downloadAllAttestationMoodleCandidat($code_formation);
    public function getUrlAllAttestationMoodleCandidat($code_formation);
    public function downloadAllReleveNoteCandidat($code_formation);
    public function getUrlAllReleveNoteCandidat($code_formation);
    // registre de notes 
    public function downloadAllRegistreNoteCandidat($code_formation);
    public function getUrlAllRegistreNoteCandidat($code_formation);
    public function downloadAllRegistreNoteCandidatSearch($code_formation);
    public function getUrlAllRegistreNoteCandidatSearch($code_formation);
    public function getUrlAllRegistreNoteEmagCandidat($code_formation);
    public function downloadListeEmargement($code_formation);
}
