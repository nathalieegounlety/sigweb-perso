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
    Route::get('obj',[\App\Http\Controllers\ModepaiementsController::class,'getModepaiementsObj']);
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
    Route::get('recentes','recenteFormation');
    Route::post('details','detailFormation');
    Route::get('for/attestation','listeFormationAttestation');
});

// gestion des documents
Route::prefix('v1/documents')->controller(\App\Http\Controllers\DocumentsController::class)->group(function(){
    Route::post('visuel/formation','showVisuelFormation');
    // reçu de paiement
    Route::get('paiements/recu/{reference}', 'recuPaiement')->name('paiements.recu')->middleware('signed');
    Route::get('proforma/{reference}', 'proforma')->name('pro-forma')->middleware('signed');
    Route::get('formationsdiplomantes/proforma/{reference}/', 'proformaDsc')->name('pro-forma-dsc')->middleware('signed');
    Route::get('proforma/pm/{reference}', 'proformaPm')->name('proforma-pm')->middleware('signed');
    Route::get('facture/normalisee/{reference}', 'genereFactureNormalise')->name('emcef.proforma-pm')->middleware('signed');
    Route::get('paiements/recu/ouvrage/{reference}', 'recuPaiementOuvrage')->name('paiements.recu-ouvrage')->middleware('signed');
    Route::get('proforma/ouvrage/{reference}', 'proformaOuvrage')->name('proforma-ouvrage')->middleware('signed');
    Route::post('url/signed/recu/paiement','urlRecuPaiement');
    Route::post('url/signed/recu/paiement/ouvrage','urlRecuPaiementOuvrage');
    Route::post('generer/url/proforma/signed','genererUrlProformaSigned');
    Route::post('generer/url/proforma/dsc/signed','genererUrlProformaDscSigned');
    Route::get('attestations','listeAttestationParticipant');
    Route::get('consultation/attestation/participant/{rapport_id}','consultationAttestationParticipant')->name('consultation.attestation.participant')->middleware('signed');
    Route::post('get/url/consultation/attestation','urlConsultationAttestation');
    Route::post('send/attestation/participant','sendAttestationParticipant');
    Route::get('telecharger/attestation/participant/{rapport_id}', 'telechargerAttestationParticipant')->name('telecharger.attestation_participant')->middleware('signed');
    Route::get('export/liste/participant/presentiel/{code_formation}','listeParticipantPresentiel')->name('liste.participant.presentiel')->middleware('signed');
    Route::get('export/liste/participant/visio/{code_formation}','listeParticipantVisio')->name('liste.participant.visio')->middleware('signed');
    Route::post('get/url/liste/participant/presentiel','urlListeParticipantPresentiel');
    Route::post('get/url/liste/participant/visio','urlListeParticipantVisio');
    Route::get('export/all/attestation/participant/{code_formation}','exportAllAttestationParticipant')->name('export.all.attestattion.participant')->middleware('signed');
    Route::post('get/url/all/attestation/participant','urlAllAttestationParticipant');
    Route::post('send/all/attestation/participant','sendAllAttestationParticipant');
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
    Route::post('inscription/formation','inscriptionFormation');
    Route::post('formations','getListeFormations');
    Route::post('by/email','getAuditeurByEmail');
    Route::post('inscription/store/paiement','inscriptionPaiement');
    Route::get('liste/pays','listePays');
    Route::get('last/three/formations','threeFormation');
    Route::get('indicateurs','indicateurs');
    Route::post('liste/inscriptions','listeInscriptions');
    Route::post('mespaiements','mesPaiements');
    Route::post('update/inscription','updateInscriptionInfo');

});

// gestion des api du frontoffice

// Récupérer les 6 dernières formations (homepage)
// Route::get('v1/formations/recentes', [HomeController::class, 'FormationsRecentes']);

// Récupérer toutes les formations (catalogue, avec pagination)
Route::get('v1/formations/catalogue', [HomeController::class, 'catalogue']);

// Récupérer toutes les details des formations
Route::get('v1/detailsformations/{id}', [HomeController::class, 'detailsformations']);

// Récupérer les moyens de paiements
Route::get('v1/pointsdepaiements',[HomeController::class, 'getPointsPaiements']);


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
    Route::get('obj','getReductionObj');
});

// gestion des paiements
Route::prefix('v1/paiements')->controller(\App\Http\Controllers\PaiementsController::class)->group(function(){
    Route::post('infos','infosPaiement');
    Route::post('info/partiel','infoPaiementPartiel');
    Route::post('partiel/store','savePaiementPartiel');

});

// gestion des paiements via fedapay
Route::prefix('v1/fedapay')->controller(\App\Http\Controllers\FedapayController::class)->group(function(){
    Route::post('store/paiement','savePaiement');
    Route::post('store/paiement/partiel','savePaiementPartiel');
});

// gestion des paiements via kkiapay
Route::prefix('v1/kkiapay')->controller(\App\Http\Controllers\KkiapayController::class)->group(function(){
    Route::post('store/paiement','savePaiement');
    Route::post('store/paiement/partiel','savePaiementPartiel');
});

// gestion de la comptabilité
Route::prefix('v1/comptabilites')->controller(\App\Http\Controllers\ComptabilitesController::class)->group(function(){
    Route::get('commandes','listeCommandes');
    Route::post('details/commande/formation','detailCommandeFormation');
    Route::post('details/commande/ouvrage','detailCommandeOuvrage');
    Route::post('details/commande/dsc','detailCommandeDsc');
    Route::post('details/paiement/ouvrage','detailPaiementOuvrage');
    Route::post('details/paiement/dsc','detailPaiementDsc');
    Route::post('deplacer/commande','deplacerCommandeFormation');
    Route::post('deplacer/commande/dsc','deplacerCommandeFormationDsc');
    Route::post('declasser/commande','declasserCommande');
    Route::get('paiements/attente','listePaiementsEnAttente');
    Route::post('traitement/paiement/partiel','traitementPaiementPartiel');
    Route::post('traitement/paiement/dsc','traitementPaiementDsc');
    Route::post('traitement/paiement','traitementPaiement');
    Route::get('paiements/valide','listePaiementsValide');
    Route::get('liste/paiements','listeDesPaiements');
});

// gestion des dashboard
Route::prefix('v1/dashboard')->controller(\App\Http\Controllers\DashboardController::class)->group(function(){
    Route::get('admin/indicateurs','adminIndicateurs');
});

// gestion api zoom
//
Route::get('/zoom/reunion/auth/callback', [\App\Http\Controllers\ZoomController::class, 'indexReunion'])->name('zoom.reunion-auth');
//
Route::get('/zoom/auth/callback', [\App\Http\Controllers\ZoomController::class, 'index'])->name('zoom.auth');
// refreshToken
Route::get('/zoom/refresh/token', [\App\Http\Controllers\ZoomController::class, 'refreshToken'])->name('zoom.refresh_token');
// AUTHORISATION
Route::get('/zoom/authorisation', [\App\Http\Controllers\ZoomController::class, 'authorisation'])->name('zoom.authorisation');
// zoom inscription
Route::get('/zoom/inscription', [\App\Http\Controllers\ZoomController::class, 'inscription'])->name('zoom.inscription')->middleware('auth');
//
Route::post('/zoom/inscription/store', [\App\Http\Controllers\ZoomController::class, 'inscriptionStore'])->name('zoom.sync-inscrit');





