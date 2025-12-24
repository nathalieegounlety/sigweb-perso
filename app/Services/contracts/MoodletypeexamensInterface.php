<?php

namespace App\Services\contracts;

use App\Models\Moodletypeexamens;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface MoodletypeexamensInterface
{
    // déclaration des interfaces à utiliser
    public function saveMoodletypeexamen(Request $data): \Illuminate\Http\JsonResponse;

    public function getMoodletypeexamen(int $id): Moodletypeexamens;

    public function getMoodletypeexamens(Request $request): LengthAwarePaginator;

    public function update(Request $data): \Illuminate\Http\JsonResponse;

    public function remove(int $id): \Illuminate\Http\JsonResponse;

}
