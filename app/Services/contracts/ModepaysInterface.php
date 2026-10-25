<?php

namespace App\Services\contracts;

use App\Models\Modepays;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface ModepaysInterface
{
    // déclaration des interfaces à utiliser
    public function saveModepays(Request $data): \Illuminate\Http\JsonResponse;

    public function getOneModepays(int $id);

    public function getModepays(Request $request): LengthAwarePaginator;

    public function update(Request $data): \Illuminate\Http\JsonResponse;

    public function remove(int $id): \Illuminate\Http\JsonResponse;

    public function getModepaysObj ():  \Illuminate\Http\JsonResponse;

    public function getModepaysObjPays (string $code_pays):  \Illuminate\Http\JsonResponse;

}
