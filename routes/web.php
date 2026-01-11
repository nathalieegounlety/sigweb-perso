<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/page-new-refonte',[\App\Http\Controllers\RolesController::class,'index']);
