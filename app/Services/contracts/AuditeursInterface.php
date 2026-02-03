<?php

namespace App\Services\contracts;

use App\Models\Auditeurs;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface AuditeursInterface
{
    // déclaration des interfaces à utiliser
    public function saveAuditeur(Request $data): \Illuminate\Http\JsonResponse;

    public function getAuditeur(int $id): Auditeurs;

    public function getAuditeurs(Request $request): LengthAwarePaginator;

    public function update(Request $data): \Illuminate\Http\JsonResponse;

    public function remove(int $id): \Illuminate\Http\JsonResponse;

    public function storeInscriptionFormation(Request $data): \Illuminate\Http\JsonResponse;

    public function storeInscriptionFormationDsc(Request $data): \Illuminate\Http\JsonResponse;

    public function getAuditeurByEmail(string $email): \Illuminate\Http\JsonResponse;

    public function getListeFormationsByCodeInscription(string $codeInscription): \Illuminate\Http\JsonResponse;

    public function storeInscriptionPaiement(Request $data): \Illuminate\Http\JsonResponse;
    public function getListePays(): \Illuminate\Http\JsonResponse;
    public function getLastThreeFormation(): \Illuminate\Http\JsonResponse;
    public function getIndicateurs(): \Illuminate\Http\JsonResponse;
    public function getListeInscriptions(Request $request): \Illuminate\Http\JsonResponse;
    public function updateAuditeur(Request $request): \Illuminate\Http\JsonResponse;
    public function getMesPaiements(Request $request): \Illuminate\Http\JsonResponse;
    public function updateInformationInscription(Request $request): \Illuminate\Http\JsonResponse;
    public function checkPiece(Request $request): \Illuminate\Http\JsonResponse;
    public function mesPieces(): \Illuminate\Http\JsonResponse;
    public function ajouterPiece(Request $request): \Illuminate\Http\JsonResponse;

}
