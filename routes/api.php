<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('register',[AuthController::class,'register']);
    Route::post('login',[AuthController::class,'login']);
    Route::post('logout',[AuthController::class,'logout']);
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

// gestion des documents
Route::prefix('v1/documents')->controller(\App\Http\Controllers\DocumentsController::class)->group(function(){
    Route::post('visuel/formation','showVisuelFormation');
});
// gestion des types de formation
Route::prefix('v1/typeformations')->controller(\App\Http\Controllers\TypeformationsController::class)->group(function(){
    Route::post('store','store');
    Route::get('/','index');
    Route::post('unique','getTypeFormation');
    Route::post('update','update');
    Route::post('remove','remove');
});

// gestion des grilles tarifaires
Route::prefix('v1/grilletarifaires')->controller(\App\Http\Controllers\GrilletarifairesController::class)->group(function(){
    Route::post('store','store');
    Route::get('/','index');
    Route::post('unique','getGrilleTarifaire');
    Route::post('update','update');
    Route::post('remove','remove');
    Route::get('obj','objGrilleTarifaire');
});

// gestion des utilisateurs
Route::prefix('v1/utilisateurs')->controller(\App\Http\Controllers\UsersController::class)->group(function(){
    Route::post('store','store');
    Route::get('/','index');
    Route::post('update','update');
    Route::post('remove','remove');
    Route::post('get_user','getUser');
});

// gestion des auditeurs
Route::prefix('v1/auditeurs')->controller(\App\Http\Controllers\AuditeursContoller::class)->group(function(){
    Route::post('store','store');
    Route::get('/','index');
    Route::post('update','update');
    Route::post('remove','remove');
    Route::post('get_user','getUser');
});

// gestion des api du frontoffice

// Récupérer les 6 dernières formations (homepage)
Route::get('v1/formations/recentes', [HomeController::class, 'FormationsRecentes']);

// Récupérer toutes les formations (catalogue, avec pagination)
Route::get('v1/formations/catalogue', [HomeController::class, 'catalogue']);

// Récupérer toutes les details des formations
Route::get('v1/detailsformations/{id}', [HomeController::class, 'detailsformations']);

// Inscription d'un auditeur
//Route::post('/auditeurs', [AuditeurController::class, 'store'])->name('auditeurs.store');

// gestion des ouvrages
Route::prefix('v1/ouvrages')->controller(\App\Http\Controllers\OuvragesController::class)->group(function(){
    Route::post('store','store');
    Route::get('/','index');
    Route::post('unique','getOuvrage');
    Route::post('update','update');
    Route::post('remove','remove');
});

// gestion des signataires
Route::prefix('v1/signataires')->controller(\App\Http\Controllers\SignatairesController::class)->group(function(){
    Route::post('store','store');
    Route::get('/','index');
    Route::post('unique','getSignataire');
    Route::post('update','update');
    Route::post('remove','remove');
});

// gestion des roles formateurs
Route::prefix('v1/roleformateurs')->controller(\App\Http\Controllers\RoleformateursController::class)->group(function(){
    Route::post('store','store');
    Route::get('/','index');
    Route::post('unique','getRoleformateur');
    Route::post('update','update');
    Route::post('remove','remove');
});

// gestion des reductions
Route::prefix('v1/reductions')->controller(\App\Http\Controllers\ReductionsController::class)->group(function(){
    Route::post('store','store');
    Route::get('/','index');
    Route::post('unique','getReduction');
    Route::post('update','update');
    Route::post('remove','remove');
});






