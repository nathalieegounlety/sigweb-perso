<?php

namespace App\Services\contracts;

use App\Models\Formateurs;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface FormateursInterface
{
    // déclaration des interfaces à utiliser
    public function saveFormateur(Request $data): \Illuminate\Http\JsonResponse; 

    public function getFormateurs(Request $request): LengthAwarePaginator; 

    public function update(Request $data): \Illuminate\Http\JsonResponse;

    public function remove(int $id): \Illuminate\Http\JsonResponse;    

    public function getFormateur(int $id); 

    public function getFormateurFormations(int $id);

}
