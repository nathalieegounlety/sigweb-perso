<?php

namespace App\Services\contracts;

use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ZoomRecordInterface
{
    public function getDetails(Request $request);
    public function store(Request $request);
    public function update(Request $request);
    public function remove(Request $request);
    public function getSessions(Request $request);
}
