<?php

namespace App\Services\contracts;

use Illuminate\Http\Request;

interface DashboardInterface
{
    public function getAdminIndicateurs(): array;
    public function getAdminIndicateurStats();
}
