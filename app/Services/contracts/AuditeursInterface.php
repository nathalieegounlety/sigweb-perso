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

    public function getAuditeurs(int $perPage): LengthAwarePaginator;

    public function update(Request $data): \Illuminate\Http\JsonResponse;

    public function remove(int $idRole): \Illuminate\Http\JsonResponse;

}
