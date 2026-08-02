<?php

namespace App\Services\contracts;

use Illuminate\Http\Request; 

interface ZoomRecordInterface
{
    public function getDetails(Request $request);
    public function store(Request $request);
    public function update(Request $request);
    public function remove(Request $request);
    public function getSessions(string $code_formation);
}
