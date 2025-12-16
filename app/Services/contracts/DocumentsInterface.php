<?php

namespace App\Services\contracts;

use App\Models\Formations;

interface DocumentsInterface
{
    public function getFormationVisuel(int $id);
}
