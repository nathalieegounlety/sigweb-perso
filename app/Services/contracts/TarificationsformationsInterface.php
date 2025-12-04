<?php

namespace App\Services\contracts;

use App\Models\Tarificationsformation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface TarificationsformationsInterface
{
    // déclaration des interfaces à utiliser
    public function saveTarificationsformation(Request $data): \Illuminate\Http\JsonResponse;

    public function getTarificationsformation(int $id): Tarificationsformation;

    public function getTarificationsformations(int $perPage): LengthAwarePaginator;

    public function update(Request $data): \Illuminate\Http\JsonResponse;

    public function remove(int $idRole): \Illuminate\Http\JsonResponse;

}
