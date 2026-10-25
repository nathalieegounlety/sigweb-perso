<?php

namespace App\Services\contracts;

use App\Models\Historiqueactions; 
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface HistoriqueActionServiceInterface
{
    public function getHistoriques(int $perPage): LengthAwarePaginator;
    
    public function getHistorique(int $id);
    
    public function saveHistorique(Request $data): \Illuminate\Http\JsonResponse;
    
    public function update(Request $data): \Illuminate\Http\JsonResponse;
    
    public function remove(int $id): \Illuminate\Http\JsonResponse;
    
    public function updateDateComptable(Request $request);
     
}
