<?php

namespace App\Services\contracts;

use App\Models\Moodlemodules;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface MoodlesmodulesInterface
{
    public function saveMoodlesmodule(Request $data): \Illuminate\Http\JsonResponse;

    public function getMoodlesmodule(int $id): Moodlemodules;

    public function getMoodlesmodules(Request $request): LengthAwarePaginator;

    public function update(Request $data): \Illuminate\Http\JsonResponse;

    public function remove(int $id): \Illuminate\Http\JsonResponse;

}
