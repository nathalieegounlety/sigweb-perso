<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidaturesController;
use App\Http\Controllers\ExcelPresentielController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\StatistiquesController;
use App\Http\Controllers\ZoomRecordsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Demande de réinitialisation
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
    Route::post('/reset-password/info', [ResetPasswordController::class, 'infoResetPassword']);
    // Réinitialisation effective du mot de passe
    Route::post('/reset-password', [ResetPasswordController::class, 'reset']);
    // check email
    Route::post('/check-email', [AuthController::class, 'checkEmail']);

});

Route::middleware('auth:api')->prefix('v1')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    // Gestion des roles
    Route::post('roles/store', [\App\Http\Controllers\RolesController::class, 'store']);
});

// gestion des rôles
Route::prefix('v1/roles')->group(function () {
    Route::post('store', [\App\Http\Controllers\RolesController::class, 'store']);
    Route::get('/', [\App\Http\Controllers\RolesController::class, 'index']);
    Route::post('unique', [\App\Http\Controllers\RolesController::class, 'getRole']);
    Route::post('update', [\App\Http\Controllers\RolesController::class, 'update']);
    Route::post('remove', [\App\Http\Controllers\RolesController::class, 'remove']);
});

// gestion des permissions
Route::prefix('v1/permissions')->group(function () {
    Route::post('store', [\App\Http\Controllers\PermissionsController::class, 'store']);
    Route::get('/', [\App\Http\Controllers\PermissionsController::class, 'index']);
    Route::post('unique', [\App\Http\Controllers\PermissionsController::class, 'getPermission']);
    Route::post('update', [\App\Http\Controllers\PermissionsController::class, 'update']);
    Route::post('remove', [\App\Http\Controllers\PermissionsController::class, 'remove']);
});

// gestion des historiquesactions
Route::prefix('v1/historiquesactions')->group(function () {
    Route::post('store', [\App\Http\Controllers\HistoriqueActionController::class, 'store']);
    Route::get('/', [\App\Http\Controllers\HistoriqueActionController::class, 'index']);
    Route::post('unique', [\App\Http\Controllers\HistoriqueActionController::class, 'getHistoriques']);
    Route::post('update', [\App\Http\Controllers\HistoriqueActionController::class, 'update']);
    Route::post('remove', [\App\Http\Controllers\HistoriqueActionController::class, 'remove']);
    Route::post('update/dc/formation', [\App\Http\Controllers\HistoriqueActionController::class, 'updDateComptable']);
});

// gestion des modepaiements
Route::prefix('v1/modepaiements')->group(function () {
    Route::post('store', [\App\Http\Controllers\ModepaiementsController::class, 'store']);
    Route::get('/', [\App\Http\Controllers\ModepaiementsController::class, 'index']);
    Route::post('unique', [\App\Http\Controllers\ModepaiementsController::class, 'getModepaiement']);
    Route::post('update', [\App\Http\Controllers\ModepaiementsController::class, 'update']);
    Route::post('remove', [\App\Http\Controllers\ModepaiementsController::class, 'remove']);
    Route::get('obj', [\App\Http\Controllers\ModepaiementsController::class, 'getModepaiementsObj']);
});

// gestion des type pièces
Route::prefix('v1/inscritstypepieces')->group(function () {
    Route::post('store', [\App\Http\Controllers\InscritsTypePiecesController::class, 'store']);
    Route::get('/', [\App\Http\Controllers\InscritsTypePiecesController::class, 'index']);
    Route::post('unique', [\App\Http\Controllers\InscritsTypePiecesController::class, 'getInscritstypepiece']);
    Route::post('update', [\App\Http\Controllers\InscritsTypePiecesController::class, 'update']);
    Route::post('remove', [\App\Http\Controllers\InscritsTypePiecesController::class, 'remove']);
});

// gestion des points de paiements
Route::prefix('v1/pointdepaiements')->group(function () {
    Route::post('store', [\App\Http\Controllers\PointDePaiementsController::class, 'store']);
    Route::get('/', [\App\Http\Controllers\PointDePaiementsController::class, 'index']);
    Route::post('unique', [\App\Http\Controllers\PointDePaiementsController::class, 'getPointdepaiement']);
    Route::post('update', [\App\Http\Controllers\PointDePaiementsController::class, 'update']);
    Route::post('remove', [\App\Http\Controllers\PointDePaiementsController::class, 'remove']);
});
Route::controller(\App\Http\Controllers\MaintenanceController::class)->prefix('v1/maintenance')->group(function () {
    Route::post('update/composant', 'update');
});

// gestion des formations
Route::prefix('v1/formations')->controller(\App\Http\Controllers\FormationsController::class)->group(function () {
    Route::post('store', 'store');
    Route::get('/', 'index');
    Route::post('unique', 'getFormation');
    Route::post('update', 'update');
    Route::post('remove', 'remove');
    Route::get('recentes', 'recenteFormation');
    Route::get('liste/reinscription/dsc','inscriptionDscFm');
    Route::post('details', 'detailFormation');
    Route::get('for/attestation', 'listeFormationAttestation');
    Route::get('dsc', 'formationsDsc');
    Route::post('details/dsc', 'detailFormationDsc');
    Route::get('obj', 'formationsobj');
    Route::get('liste/inscrits', 'listeIncrits');
    Route::post('liste/reductions', 'listeReductions');
    Route::post('add/reductions', 'addReductions');
    Route::post('delete/reduction', 'deleteReduction');
    Route::post('obj/liste/reductions', 'objReductionFormation');
    Route::get('dsc/obj', 'listeFormationDscObj');
    // 
    Route::post('liste/logos', 'listeLogos');
    Route::post('add/logos', 'addLogos');
    Route::post('delete/logo', 'deleteLogo');
    Route::post('edit/logos', 'editLogos');

});

// gestion des documents
Route::prefix('v1/documents')->controller(\App\Http\Controllers\DocumentsController::class)->group(function () {
    Route::post('visuel/formation', 'showVisuelFormation');
    // reçu de paiement
    Route::get('paiements/recu/{reference}', 'recuPaiement')->name('paiements.recu')->middleware('signed');
    Route::get('proforma/{reference}', 'proforma')->name('pro-forma')->middleware('signed');
    Route::get('formationsdiplomantes/proforma/{reference}/', 'proformaDsc')->name('pro-forma-dsc')->middleware('signed');
    Route::get('proforma/pm/{reference}', 'proformaPm')->name('proforma-pm')->middleware('signed');
    Route::get('facture/normalisee/{reference}', 'genereFactureNormalise')->name('emcef.proforma-pm')->middleware('signed');
    Route::get('paiements/recu/ouvrage/{reference}', 'recuPaiementOuvrage')->name('paiements.recu-ouvrage')->middleware('signed');
    Route::get('proforma/ouvrage/{reference}', 'proformaOuvrage')->name('proforma-ouvrage')->middleware('signed');
    Route::post('url/signed/recu/paiement', 'urlRecuPaiement');
    Route::post('url/signed/recu/paiement/ouvrage', 'urlRecuPaiementOuvrage');
    Route::post('generer/url/proforma/signed', 'genererUrlProformaSigned');
    Route::post('generer/url/proforma/dsc/signed', 'genererUrlProformaDscSigned');
    Route::post('generer/get/url/proforma/ouvrage/signed', 'genererUrlProformaOuvrageSigned');
    // ####### ATTESTATION POUR LES PARTICIPANTS #####################
    Route::get('attestations', 'listeAttestationParticipant');
    Route::get('consultation/attestation/participant/{rapport_id}', 'consultationAttestationParticipant')->name('consultation.attestation.participant')->middleware('signed');
    Route::post('get/url/consultation/attestation', 'urlConsultationAttestation');
    Route::post('send/attestation/participant', 'sendAttestationParticipant');
    Route::get('telecharger/attestation/participant/{rapport_id}', 'telechargerAttestationParticipant')->name('telecharger.attestation_participant')->middleware('signed');
    Route::get('export/liste/participant/presentiel/{code_formation}', 'listeParticipantPresentiel')->name('liste.participant.presentiel')->middleware('signed');
    Route::get('export/liste/participant/visio/{code_formation}', 'listeParticipantVisio')->name('liste.participant.visio')->middleware('signed');
    Route::post('get/url/liste/participant/presentiel', 'urlListeParticipantPresentiel');
    Route::post('get/url/liste/participant/visio', 'urlListeParticipantVisio');
    Route::get('export/all/attestation/participant/{code_formation}', 'exportAllAttestationParticipant')->name('export.all.attestattion.participant')->middleware('signed');
    Route::get('export/all/attestation/presentiel/participant/{code_formation}', 'exportAllAttestationPresentielParticipant')->name('export.all.attestation.presentiel.participant')->middleware('signed');
    Route::post('get/url/all/attestation/participant', 'urlAllAttestationParticipant');
    Route::post('get/url/all/attestation/presentiel/participant', 'urlAllAttestationPresentielParticipant');
    Route::post('send/all/attestation/participant', 'sendAllAttestationParticipant');
    // ####### ATTESTATION POUR LES FORMATEURS #####################
    Route::get('attestations/formateurs', 'listeAttestationFormateurs');
    Route::get('consultation/attestation/formateur/{id}', 'consulterAttestationFormateur')->name('consultation.attestation_formateur')->middleware('signed');
    Route::get('telecharger/attestation/formateur/{id}', 'telechargerAttestationFormateur')->name('telecharger.attestation_formateur')->middleware('signed');
    Route::post('get/url/attestation/formateur', 'urlAttestationFormateur');
    Route::post('get/url/consultation/attestation/formateur', 'urlConsultationAttestationFormateur');
    Route::post('send/attestation/formateur', 'sendAttestationFormateur');
    Route::post('send/all/attestation/formateur', 'sendAllAttestationFormateur');
    Route::get('export/all/attestation/formateur/{code_formation}', 'exportAllAttestationFormateur')->name('export.all.attestattion.formateur')->middleware('signed');
    Route::post('get/url/all/attestation/formateur', 'urlAllAttestationFormateur');
    Route::get('export/liste/formateurs/{code_formation}', 'listeFormateurs')->name('liste.formateurs')->middleware('signed');
    Route::post('get/url/liste/formateur', 'urlListeFormateur');
    Route::get('/export/etat-recapitulatif/pdf/{code_formation}', 'pdfEtatrecapitulatif');
    Route::get('/export/etat-recapitulatif/excel/{code_formation}', 'exportEtatRecapitulatif');
    Route::get('etats-recapitulatif/{id}/ouvrage', 'exportEtatRecapitulatifOuvrage');
    // liste rapports 
    Route::get('liste/rapports', 'listeRapports');
    Route::get('exportation/liste_globale_pdf/{code_formation}','exportListeGlobaleToPDF');
    Route::get('exportation_pdf/{code_formation}','exportListeParticipantAttestationPDF');

    // ATTESTATION MOODLE CANDIDAT 
    Route::post('get/url/attestation/moodle/candidat', 'urlAttestationMoodleCandidat');
    Route::get('export/attestation/moodle/candidat/{id}/{code_formation}', 'attestationCandidat')->name('attestationCandidat.moodle')->middleware('signed');
    
    Route::post('get/url/releve/note/candidat', 'urlReleveNoteCandidat');
    Route::get('export/releve/note/candidat/{id}/{code_formation}', 'releveNoteCandidat')->name('releveNoteCandidat.moodle')->middleware('signed');
    
    Route::post('get/url/all/attestation/moodle/candidat', 'urlAllAttestationMoodleCandidat');
    Route::get('export/all/attestation/moodle/candidat/{code_formation}', 'allAttestationCandidat')->name('allAttestationCandidat.moodle')->middleware('signed');
    
    Route::post('get/url/all/releve/note/candidat', 'urlAllReleveNoteCandidat');
    Route::get('export/all/releve/note/candidat/{code_formation}', 'allReleveNoteCandidat')->name('allReleveNoteCandidat.moodle')->middleware('signed');
    
    
    Route::post('get/url/all/registre/note/emag/candidat', 'urlAllRegistreNoteEmagCandidat');
    Route::get('export/all/registre/note/emag/candidat/{code_formation}', 'allRegistreNoteEmagCandidat')->name('emargement.moodle')->middleware('signed');


    Route::post('get/url/all/registre/note/candidat', 'urlAllRegistreNoteCandidat');
    Route::get('export/all/registre/note/candidat/{code_formation}', 'allRegistreNoteCandidat')->name('allRegistreNoteCandidat.moodle')->middleware('signed');

    Route::post('get/url/all/registre/note/candidat/search', 'urlAllRegistreNoteCandidatSearch');
    Route::get('export/all/registre/note/candidat/search/{code_formation}', 'allRegistreNoteCandidatSearch')->name('allRegistreNoteCandidatSearch.moodle')->middleware('signed');
    Route::post('get/url/piece/auditeur', 'urlPieceAuditeur')->name('api.documents.url_piece_auditeur');
    Route::get('pieces_auditeur/{auditeur_id}/{code_formation}/{path}','showPieceAuditeur')
    ->name('api.documents.piece_auditeur.show')
    ->where('path', '.*') 
    ->middleware('signed');
    });


// gestion des types de formation
Route::prefix('v1/typeformations')->controller(\App\Http\Controllers\TypeformationsController::class)->group(function () {
    Route::post('store', 'store');
    Route::get('/', 'index');
    Route::post('unique', 'getTypeFormation');
    Route::post('update', 'update');
    Route::post('remove', 'remove');
});

// gestion des moodlestypeexamens
Route::prefix('v1/moodletypeexamens')->controller(\App\Http\Controllers\MoodletypeexamensController::class)->group(function () {
    Route::post('store', 'store');
    Route::get('/', 'index');
    Route::post('unique', 'getMoodletypeexamen');
    Route::post('update', 'update');
    Route::post('remove', 'remove');
});

// gestion des moodlesmodules
Route::prefix('v1/moodlesmodules')->controller(\App\Http\Controllers\MoodlemodulesController::class)->group(function () {
    Route::post('store', 'store');
    Route::get('/', 'index');
    Route::post('unique', 'getMoodlemodules');
    Route::post('update', 'update');
    Route::post('remove', 'remove');
});



// gestion des grilles tarifaires
Route::prefix('v1/grilletarifaires')->controller(\App\Http\Controllers\GrilletarifairesController::class)->group(function () {
    Route::post('store', 'store');
    Route::get('/', 'index');
    Route::post('unique', 'getGrilleTarifaire');
    Route::post('update', 'update');
    Route::post('remove', 'remove');
    Route::get('obj', 'objGrilleTarifaire');
});

// gestion des utilisateurs
Route::prefix('v1/utilisateurs')->controller(\App\Http\Controllers\UsersController::class)->group(function () {
    Route::post('store', 'store');
    Route::get('/', 'index');
    Route::post('update', 'update');
    Route::post('remove', 'remove');
    Route::post('get_user', 'getUser'); 
    Route::get('/export', 'exportListeUsers'); 
});

// gestion des auditeurs
Route::prefix('v1/auditeurs')->controller(\App\Http\Controllers\AuditeursContoller::class)->group(function () {
    Route::post('store', 'store');
    Route::get('/', 'index');
    Route::post('update', 'update');
    Route::post('remove', 'remove');
    Route::post('get_user', 'getAuditeurById');
    Route::post('inscription/formation', 'inscriptionFormation');
    Route::post('inscription/formation/by/admin', 'inscriptionFormationByAdmin');
    Route::post('formations', 'getListeFormations');
    Route::post('by/email', 'getAuditeurByEmail');
    Route::post('inscription/store/paiement', 'inscriptionPaiement');
    Route::get('liste/pays', 'listePays');
    Route::get('last/three/formations', 'threeFormation');
    Route::get('indicateurs', 'indicateurs');
    Route::post('liste/inscriptions', 'listeInscriptions');
    Route::post('mespaiements', 'mesPaiements');
    Route::post('update/inscription', 'updateInscriptionInfo');
    Route::post('inscription/formation/dsc', 'inscriptionFormationDsc');
    Route::post('inscription/formation/dsc/by/admin', 'inscriptionFormationDscByAdmin');
    Route::post('check/piece/dsc', 'checkPieceDsc');
    Route::post('liste/pieces', 'listePieces');
    Route::post('add/pieces', 'addPiece');
    Route::post('update/temps/presence', 'updateTempsPresence');
    Route::post('info/indicateurs', 'indicateursAuditeur');
    Route::post('liste/modes', 'listeModes');
    Route::post('update/modes', 'updateMode');
    Route::post('update/email', 'updateEmailAuditeur');
    Route::post('delete/compte', 'deleteCompteAuditeur');
    Route::get('update/tarif/{code_formation}', 'updateTarifAuditeur');
    Route::post('mes/atestations', 'getAttestionsAudtieur');
    Route::post('remove/inscription', 'removeInscription');
});

// gestion des api du frontoffice

// Récupérer les 6 dernières formations (homepage)
// Route::get('v1/formations/recentes', [HomeController::class, 'FormationsRecentes']);

// Récupérer toutes les formations (catalogue, avec pagination)
Route::get('v1/formations/catalogue', [HomeController::class, 'catalogue']);

// Récupérer toutes les details des formations
Route::get('v1/detailsformations/{id}', [HomeController::class, 'detailsformations']);

// Récupérer toutes les details des formations

Route::get('v1/detailsformationsdsc/{code_formation}', [HomeController::class, 'detailsformationsdsc']);


// Récupérer les moyens de paiements
Route::get('v1/pointsdepaiements', [HomeController::class, 'getPointsPaiements']);


// Inscription d'un auditeur
//Route::post('/auditeurs', [AuditeurController::class, 'store'])->name('auditeurs.store');

// gestion des ouvrages
Route::prefix('v1/ouvrages')->controller(\App\Http\Controllers\OuvragesController::class)->group(function () {
    Route::post('store', 'store');
    Route::get('/', 'index');
    Route::post('unique', 'getOuvrage');
    Route::post('update', 'update');
    Route::post('remove', 'remove');
    Route::get('obj', 'getObjOuvrage');
    Route::post('store/achat', 'storeAchat');
    Route::get('stats/recapitulatif', 'statsRecapitulatif');
});

// gestion des formations dsc
Route::prefix('v1/formationsdsc')->controller(\App\Http\Controllers\FormationsDscController::class)->group(function () {
    Route::post('store', 'store');
    Route::get('/', 'index');
    Route::get('obj', 'getObFormationDsc');
    Route::post('unique', 'getFormationdsc');
    Route::post('update', 'update');
    Route::post('remove', 'remove');
    Route::get('dossiers/attente', 'listeDossiersAttente');
    Route::post('traitements/dossiers/candidature', 'traitementsDossier');
    Route::get('dossiers/valides', 'listeDossiersValides');
    Route::get('dossiers/sousreserve', 'listeDossierSousreserve');
    Route::get('dossiers/rejetes', 'listeDossiersRejetes');
});

// gestion des signataires
Route::prefix('v1/signataires')->controller(\App\Http\Controllers\SignatairesController::class)->group(function () {
    Route::post('store', 'store');
    Route::get('/', 'index');
    Route::post('unique', 'getSignataire');
    Route::post('update', 'update');
    Route::post('remove', 'remove');
});

// gestion des roles formateurs
Route::prefix('v1/roleformateurs')->controller(\App\Http\Controllers\RoleformateursController::class)->group(function () {
    Route::post('store', 'store');
    Route::get('/', 'index');
    Route::post('unique', 'getRoleformateur');
    Route::post('update', 'update');
    Route::post('remove', 'remove');
});

// gestion des reductions
Route::prefix('v1/reductions')->controller(\App\Http\Controllers\ReductionsController::class)->group(function () {
    Route::post('store', 'store');
    Route::get('/', 'index');
    Route::post('unique', 'getReduction');
    Route::post('update', 'update');
    Route::post('remove', 'remove');
    Route::get('obj', 'getReductionObj');
});

// gestion des paiements
Route::prefix('v1/paiements')->controller(\App\Http\Controllers\PaiementsController::class)->group(function () {
    Route::post('infos', 'infosPaiement');
    Route::post('info/partiel', 'infoPaiementPartiel');
    Route::post('partiel/store', 'savePaiementPartiel');
    Route::post('infos/ouvrage', 'infosPaiementOuvrage');
    Route::get('recapitulatif/{codeFormation}', 'getRecapitulatifPaiements');
    Route::get('recapitulatif/stats/{codeFormation}/participants', 'statsParticipantsFormation');
    Route::get('recapitulatif/stats/export/{codeFormation}/participants', 'statsExportParticipantsFormation');

});

// gestion des paiements via fedapay
Route::prefix('v1/fedapay')->controller(\App\Http\Controllers\FedapayController::class)->group(function () {
    Route::post('store/paiement', 'savePaiement');
    Route::post('store/paiement/partiel', 'savePaiementPartiel');
    Route::post('store/paiement/ouvrage', 'savePaiementOuvrage');
    Route::post('store/paiement/dsc', 'savePaiementDsc');
    Route::post('store/paiement/partiel/dsc', 'savePaiementPartielDsc');
    Route::post('webhook/paiements', 'webhook');
});

// gestion des paiements via kkiapay
Route::prefix('v1/kkiapay')->controller(\App\Http\Controllers\KkiapayController::class)->group(function () {
    Route::post('store/paiement', 'savePaiement');
    Route::post('store/paiement/partiel', 'savePaiementPartiel');
    Route::post('store/paiement/ouvrage', 'savePaiementOuvrage');
    Route::post('store/paiement/dsc', 'savePaiementDsc');
    Route::post('store/paiement/partiel/dsc', 'savePaiementPartielDsc');
    Route::post('webhook', 'webhook'); 
});

// gestion de la comptabilité
Route::prefix('v1/comptabilites')->controller(\App\Http\Controllers\ComptabilitesController::class)->group(function () {
    Route::get('commandes', 'listeCommandes');
    Route::get('commandes/ouvrages', 'listeCommandesOuvrages');
    Route::post('details/commande/formation', 'detailCommandeFormation');
    Route::post('details/commande/ouvrage', 'detailCommandeOuvrage');
    Route::post('details/commande/dsc', 'detailCommandeDsc');
    Route::post('details/paiement/ouvrage', 'detailPaiementOuvrage');
    Route::post('details/paiement/dsc', 'detailPaiementDsc');
    Route::post('details/paiement/valider/dsc', 'detailPaiementValiderDsc');
    Route::post('deplacer/commande', 'deplacerCommandeFormation');
    Route::post('deplacer/commande/dsc', 'deplacerCommandeFormationDsc');
    Route::post('declasser/commande', 'declasserCommande');
    Route::get('paiements/attente', 'listePaiementsEnAttente');
    Route::post('traitement/paiement/partiel', 'traitementPaiementPartiel');
    Route::post('traitement/paiement/dsc', 'traitementPaiementDsc');
    Route::post('traitement/paiement', 'traitementPaiement');
    Route::get('paiements/valide', 'listePaiementsValide');
    Route::get('paiements/ouvrages/valide', 'listePaiementsOuvragesValide');
    Route::get('liste/paiements', 'listeDesPaiements');
    Route::post('details/process/paiements/dsc', 'detailProcessPaiementDsc');
    Route::post('traitement/process/paiement/dsc', 'traitementProcessPaiementDsc');
    Route::get('paiements/valide/export', 'exportPaiementsValide');
    Route::get('paiements/liste/export', 'exportListePaiements');
});

// gestion des dashboard
Route::prefix('v1/dashboard')->controller(\App\Http\Controllers\DashboardController::class)->group(function () {
    Route::get('admin/indicateurs', 'adminIndicateurs');
    Route::get('admin/indicateurs/stats', 'adminIndicateurStats');
});

// gestion api zoom 
Route::prefix('v1/zoom')->controller(\App\Http\Controllers\ZoomController::class)->group(function () {
    Route::get('reunion/auth/callback', 'indexReunion')->name('zoom.reunion-auth');
    //
    Route::get('auth/callback', 'index')->name('zoom.auth');
    // refreshToken
    Route::get('refresh/token', 'refreshToken')->name('zoom.refresh_token');
    // AUTHORISATION
    Route::get('authorisation', 'authorisation')->name('zoom.authorisation');
    // zoom inscription
    Route::get('inscription', 'inscription')->name('zoom.inscription')->middleware('auth');
    //
    Route::post('inscription/store', 'inscriptionStore')->name('zoom.sync-inscrit');
}); 

Route::get('v1/test-error-mail', function () {
    throw new Exception('🔥 Test envoi mail erreur Laravel 12');
});

Route::prefix('v1/exportation')->controller(\App\Http\Controllers\ExportationExcelController::class)->group(function () {
    Route::get('formation/dsc/{code_formation}', 'exportListeInscritsDsc');
    Route::get('formation/dsc/sr/{code_formation}', 'exportListeInscritsDscSR');
    Route::get('formation/presentielle/{code_formation}', 'exportListeInscritPresentielleExcel');
    Route::get('formation/visio/{code_formation}', 'exportListeInscritVisioExcel');
    Route::get('formation/liste/presence/{code_formation}', 'exportListePresenceExcel');
    Route::get('formation/visio/presentiel/{code_formation}', 'exportListeInscritPresentielleVisioExcel');
    Route::get('export/{id}/etats-recapitulatif/ouvrage', 'exportEtatRecapOuvrage');
    // importation des rapports
    Route::post('rapports/webinaire', 'importationRapportsWebianire');
    Route::post('rapports/presentiel', 'importRapportPresentiel');
    Route::post('inscriptions', 'importInscription');
    Route::post('reunions', 'importReunion');
    Route::post('notes', 'importNotes');
    Route::post('candidats', 'importCandidats');
    Route::post('modules', 'importMoodleModules');

    // liste des auditeurs 
    Route::get('auditeurs', 'AuditeursExportListe');
    Route::get('formateurs', 'FormateursExportListe'); 
    // export brute excel
    Route::get('all/liste/attestation/formateurs/{code_formation}', 'ExportAllAttestationFormateurExcel'); 
    Route::get('all/liste/attestation/participants/{code_formation}', 'ExportAllAttestationParticipantExcel'); 
    Route::get('all/liste/rapports/{code_formation}', 'ExportAllRapportExcel'); 
    Route::get('all/liste/participants/{code_formation}', 'ExportAllParticipantExcel'); 
    Route::get('dossiers/valides/dsc/{code_formation}', 'exportListeDossiersValidesDsc');
    Route::get('dossiers/rejetes/dsc/{code_formation}', 'exportListeDossiersRejetesDsc');
});

// gestion des tarifs
Route::prefix('v1/tarifications')->controller(\App\Http\Controllers\TarificationsformationController::class)->group(function () {
    Route::post('get/tarif', 'getTarif'); 
});


// gestion des formateurs
Route::prefix('v1/formateurs')->controller(\App\Http\Controllers\FormateursController::class)->group(function () {
    Route::post('store', 'store');
    Route::get('/', 'index'); 
    Route::post('update', 'update');
    Route::post('remove', 'remove'); 
    Route::post('get/details', 'getDetails'); 
    Route::post('get/formations', 'getFormations'); 
    Route::post('get/by/formation', 'getFormateursFormation'); 
    Route::get('obj/roles', 'objRoles');
    Route::get('obj', 'objFormateurs');
    Route::post('store/for/formation','storeAddFormateur');
    Route::post('store/edit/role','storeEditRoleFormateur');
    Route::post('store/delete/formateur','storeDeleteFormateur');
    Route::post('importation/lot','importationFormateur');
});

// routes/api.php
Route::prefix('v1/zoom-records')->controller(ZoomRecordsController::class)->group(function () {
    Route::post('get/details', 'getDetails');         
    Route::get('get/sessions/{formationId}', 'getSessions'); 
    Route::post('store', 'store');
    Route::post('update', 'update');
    Route::post('remove', 'remove');
});


Route::prefix('v1/panelistes')->controller(\App\Http\Controllers\RapportsController::class)->group(function () {
    Route::get('/', 'panelistes');
    Route::post('/search', 'searchPanelistes');

});

Route::prefix('v1/participants')->controller(\App\Http\Controllers\RapportsController::class)->group(function () {
    Route::get('/', 'participants');
    Route::post('/search', 'searchParticipants');

});

Route::prefix('v1/autresparticipants')->controller(\App\Http\Controllers\RapportsController::class)->group(function () {
    Route::get('/', 'autresparticipants');
    Route::post('/search', 'searchAutresparticipants');

});

Route::prefix('v1/statistiques')->controller(StatistiquesController::class)->group(function () {
    Route::get('/inscrits', 'statsEtatInscrits')->name('statistiques.inscrits');
});

Route::get('v1/exportation/inscrits/formations', [ExcelPresentielController::class, 'exportListeInscritFormation'])
     ->name('exportation_inscrits_formations');

Route::get('/v1/files/{path}', [FileController::class, 'show'])
    ->where('path', '.*');
    
// updateCodeUser
Route::get('/v1/update/liste', [HomeController::class, 'updateCodeUser']);

// Gestion de la scolarité
Route::prefix('v1/scolarites')->controller(CandidaturesController::class)->group(function () {
    Route::get('moodle/liste/candidats', 'listeCandidats')->name('listeCandidat.moodle'); 
    Route::post('moodle/update/info/candidat', 'updateInfoCandidat');
    Route::post('moodle/liste/formations/candidat', 'listeFormationsCandidat')->name('listeformation.moodle'); 
    Route::get('moodle/liste/notesfinale/candidats', 'listeNoteFinaleCandidats')->name('listeNoteFinaleCandidats.moodle'); 
    Route::post('moodle/liste/notes/brutes', 'listeNotesBrutes')->name('listenotesbrutes.moodle'); 
    Route::post('moodle/calcul/mg', 'calculMg')->name('calculMg.moodle'); 
    Route::get('moodle/registre/notes/candidats', 'registreNotesCandidats')->name('registreNotesCandidats.moodle'); 
    Route::post('moodle/info/diplome/candidat', 'infoDiplomes')->name('info.diplome'); 
});



// gestion des paiements par AfribaPay
Route::prefix('v1/afribapay')->controller(\App\Http\Controllers\AfribapayController::class)->group(function () {
    Route::get('token', 'generateToken'); 
    Route::get('liste/variable', 'getListeVar'); 
    Route::post('webhook', 'webhook'); 
    Route::post('sendPayIn','sendPayIn');
    Route::post('checkPayIn/{id}','checkPayIn');
    Route::post('paiement/partiel/{id}','savePaiementPartiel');
});

// gestion des exportations periodiques
Route::prefix('v1/exportationperiodique')->controller(\App\Http\Controllers\ExportperiodiquesController::class)->group(function () {
    Route::post('liste/paiements', 'listePaiements'); 
    Route::get('liste/roles', 'listeRoles'); 
    Route::get('liste/historiques', 'listeHistoriques'); 
    Route::post('liste/historiques/periode', 'listeHistoriquesPeriode'); 
});

// gestion des modepays
Route::prefix('v1/modepays')->group(function () {
    Route::post('store', [\App\Http\Controllers\ModepaysController::class, 'store']);
    Route::get('/', [\App\Http\Controllers\ModepaysController::class, 'index']);
    Route::post('unique', [\App\Http\Controllers\ModepaysController::class, 'getModepays']);
    Route::post('update', [\App\Http\Controllers\ModepaysController::class, 'update']);
    Route::post('remove', [\App\Http\Controllers\ModepaysController::class, 'remove']);
    Route::get('obj', [\App\Http\Controllers\ModepaysController::class, 'getModepaysObj']);
    Route::post('obj/pays', [\App\Http\Controllers\ModepaysController::class, 'getModepaysObjPays']);
});

// gestion des operateurs
Route::prefix('v1/operateurs')->group(function () {
    Route::post('store', [\App\Http\Controllers\OperateursController::class, 'store']);
    Route::get('/', [\App\Http\Controllers\OperateursController::class, 'index']);
    Route::post('unique', [\App\Http\Controllers\OperateursController::class, 'getOperateur']);
    Route::post('update', [\App\Http\Controllers\OperateursController::class, 'update']);
    Route::post('remove', [\App\Http\Controllers\OperateursController::class, 'remove']);
    Route::get('obj', [\App\Http\Controllers\OperateursController::class, 'getOperateurObj']);
    Route::post('obj/mode/pays', [\App\Http\Controllers\OperateursController::class, 'getObjOperateurByMP']);
});

