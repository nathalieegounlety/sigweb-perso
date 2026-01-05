<?php

namespace App\Services\contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface ComptabilitesInterface
{

    public function getCommandes(Request $request) ;//: LengthAwarePaginator;
    public function getPaiementsattente(Request $request): LengthAwarePaginator;
    public function getPaiementsvalides(Request $request): LengthAwarePaginator;
}
