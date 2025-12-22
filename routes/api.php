<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('register',[AuthController::class,'register']);
    Route::post('login',[AuthController::class,'login']);
});


Route::middleware('auth:api')->prefix('v1')->group(function(){
    Route::get('me',[AuthController::class,'me']);
    Route::post('logout',[AuthController::class,'logout']);
    Route::post('refresh',[AuthController::class,'refresh']);
    // Gestion des roles
    Route::post('roles/store',[\App\Http\Controllers\RolesController::class,'store']);
});

// gestion des rôles
Route::prefix('v1/roles')->group(function(){
    Route::post('store',[\App\Http\Controllers\RolesController::class,'store']);
    Route::get('/',[\App\Http\Controllers\RolesController::class,'index']);
    Route::post('unique',[\App\Http\Controllers\RolesController::class,'getRole']);
    Route::post('update',[\App\Http\Controllers\RolesController::class,'update']);
    Route::post('remove',[\App\Http\Controllers\RolesController::class,'remove']);
});

// gestion des permissions
Route::prefix('v1/permissions')->group(function(){
    Route::post('store',[\App\Http\Controllers\PermissionsController::class,'store']);
    Route::get('/',[\App\Http\Controllers\PermissionsController::class,'index']);
    Route::post('unique',[\App\Http\Controllers\PermissionsController::class,'getPermission']);
    Route::post('update',[\App\Http\Controllers\PermissionsController::class,'update']);
    Route::post('remove',[\App\Http\Controllers\PermissionsController::class,'remove']);
});

// gestion des modepaiements
Route::prefix('v1/modepaiements')->group(function(){
    Route::post('store',[\App\Http\Controllers\ModepaiementsController::class,'store']);
    Route::get('/',[\App\Http\Controllers\ModepaiementsController::class,'index']);
    Route::post('unique',[\App\Http\Controllers\ModepaiementsController::class,'getModepaiement']);
    Route::post('update',[\App\Http\Controllers\ModepaiementsController::class,'update']);
    Route::post('remove',[\App\Http\Controllers\ModepaiementsController::class,'remove']);
});

Route::controller(\App\Http\Controllers\MaintenanceController::class)->prefix('v1/maintenance')->group(function () {
    Route::post('update/composant', 'update');
});

// gestion des formations
Route::prefix('v1/formations')->controller(\App\Http\Controllers\FormationsController::class)->group(function(){
    Route::post('store','store');
    Route::get('/','index');
    Route::post('unique','getFormation');
    Route::post('update','update');
    Route::post('remove','remove');
});

// gestion des formations
Route::prefix('v1/documents')->controller(\App\Http\Controllers\DocumentsController::class)->group(function(){
    Route::post('visuel/formation','showVisuelFormation');
});



// gestion des api du frontoffice

// Récupérer les 6 dernières formations (homepage)
Route::get('v1/formations/recentes', [HomeController::class, 'FormationsRecentes']);

// Récupérer toutes les formations (catalogue, avec pagination)
Route::get('v1/formations/catalogue', [HomeController::class, 'catalogue']);

