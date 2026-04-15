<?php

namespace App\Services\contracts;

use App\Models\FormationsDsc;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface FormationsdscInterface
{
    // déclaration des interfaces à utiliser
    public function saveFormationdsc(Request $data): \Illuminate\Http\JsonResponse;

    public function getFormationDsc(int $id): FormationsDsc;

    public function getFormationsDsc(Request $request): LengthAwarePaginator;

    public function update(Request $data): \Illuminate\Http\JsonResponse;

    public function remove(int $id): \Illuminate\Http\JsonResponse;
    public function objFormationDsc(): \Illuminate\Http\JsonResponse;
    public function getListeDossiersAttente(Request $request, string $code_formation);
    public function validerDossiersCandidature(Request $request): \Illuminate\Http\JsonResponse;
    public function getListeDossiersValides(Request $request, string $code_formation);

}
