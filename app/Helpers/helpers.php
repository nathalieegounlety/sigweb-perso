<?php
use Illuminate\Support\Facades\Log;
use App\Models\Auditeurs;
use App\Models\Detailspaiements;
use App\Models\Dureeformations;
use App\Models\Enregistrementszooms;
use App\Models\Formateurs;
use App\Models\Formations;
use App\Models\Formationsformateurs;
use App\Models\FormationsTranches;
use App\Models\Inscriptions;
use App\Models\InscriptionsDsc;
use App\Models\InscritsTypePieces;
use App\Models\Modepaiements;
use App\Models\Paiements;
use App\Models\Rapports;
use App\Models\Reductions;
use App\Models\Signataires;
use App\Models\Moodlemoyennes;
use App\Models\Tarificationsformation;
use App\Models\Tmpmoodlecandidats;
use Illuminate\Support\Facades\DB;

if (!function_exists('testCode')) {
# code...
    function testCode(){
        return true;
    }
}

if (!function_exists('getPaiement')) {
    # code...
    function getPaiement($code_formation,$email,$tarif_formation_id){

        return $liste_participants = DB::table('tarificationsformations')
            ->join('detailspaiements', 'detailspaiements.id_tarification_formation', '=', 'tarificationsformations.id')
            ->join('grilletarifaires', 'grilletarifaires.id', '=', 'tarificationsformations.id_grilletarifaire')
            ->join('paiements', 'paiements.reference', '=', 'detailspaiements.reference_paiement')
            ->join('modepaiements', 'modepaiements.id', '=', 'paiements.id_modepaiement')
            ->leftjoin('reductions', 'reductions.id', '=', 'detailspaiements.id_reduction')
            ->select('detailspaiements.*','paiements.montant_total','paiements.email as email_paiement','grilletarifaires.type_tarif','grilletarifaires.montant','modepaiements.libelle as modepaiement','reductions.pourcentage')
            ->where('tarificationsformations.code_formation',$code_formation)
            ->where('detailspaiements.statut_paiement',"PAYE")
            ->where('detailspaiements.id_tarification_formation',$tarif_formation_id)
            ->where('paiements.email',$email)
            // ->orderBy('inscriptions.nom', 'ASC')
            ->get();

    }
}

if (!function_exists('getFraisInscription')) {
    # code...
    function getFraisInscription($code_formation,$email,$reference_paiement){

        return $liste_participants = DB::table('tarificationsformations')
            ->join('detailspaiements', 'detailspaiements.id_tarification_formation', '=', 'tarificationsformations.id')
            ->join('grilletarifaires', 'grilletarifaires.id', '=', 'tarificationsformations.id_grilletarifaire')
            ->join('paiements', 'paiements.reference', '=', 'detailspaiements.reference_paiement')
            ->join('modepaiements', 'modepaiements.id', '=', 'paiements.id_modepaiement')
            ->select('detailspaiements.montant_paye')
            ->where('tarificationsformations.code_formation',$code_formation)
            ->where('grilletarifaires.type_tarif',"INSCRIPTION")
            ->where('detailspaiements.statut_paiement',"PAYE")
            ->where('detailspaiements.reference_paiement',$reference_paiement)
            ->where('paiements.email',$email)
            // ->orderBy('inscriptions.nom', 'ASC')
            ->get(["detailspaiements.montant_paye"])
            ->first();

    }
}

if (!function_exists('getPaiementratuit')) {
    # code...
    function getPaiementratuit($code_formation,$email,$tarif_formation_id){

        return $liste_participants = DB::table('tarificationsformations')
            ->join('detailspaiements', 'detailspaiements.id_tarification_formation', '=', 'tarificationsformations.id')
            ->join('grilletarifaires', 'grilletarifaires.id', '=', 'tarificationsformations.id_grilletarifaire')
            ->join('paiements', 'paiements.reference', '=', 'detailspaiements.reference_paiement')
            ->join('modepaiements', 'modepaiements.id', '=', 'paiements.id_modepaiement')
            ->select('detailspaiements.*','paiements.email as email_paiement','grilletarifaires.type_tarif','grilletarifaires.montant','modepaiements.libelle as modepaiement')
            ->where('tarificationsformations.code_formation',$code_formation)
            ->where('detailspaiements.statut_paiement',"IMPAYE")
            ->where('detailspaiements.id_reduction',3)
            ->where('detailspaiements.id_tarification_formation',$tarif_formation_id)
            ->where('paiements.email',$email)
            // ->orderBy('inscriptions.nom', 'ASC')
            ->get();

    }
}


if (!function_exists('getPaiementAll')) {
    # code...
    function getPaiementAll($code_formation){

        return $liste_participants = DB::table('tarificationsformations')
            ->join('detailspaiements', 'detailspaiements.id_tarification_formation', '=', 'tarificationsformations.id')
            ->join('grilletarifaires', 'grilletarifaires.id', '=', 'tarificationsformations.id_grilletarifaire')
            ->join('paiements', 'paiements.reference', '=', 'detailspaiements.reference_paiement')
            ->join('modepaiements', 'modepaiements.id', '=', 'paiements.id_modepaiement')
            ->select('detailspaiements.*','paiements.email as email_paiement','grilletarifaires.type_tarif','grilletarifaires.montant','modepaiements.libelle as modepaiement')
            ->where('tarificationsformations.code_formation',$code_formation)
            ->where('detailspaiements.statut_paiement',"PAYE")
            // ->orderBy('inscriptions.nom', 'ASC')
            ->get();

    }
}


if (!function_exists('getTarifFormation')) {
    # code...
    function getTarifFormation($code_formation){

        // liste des formations disponibles
        return $liste_formation = DB::table('tarificationsformations')
            ->join('formations', 'formations.code_formation', '=', 'tarificationsformations.code_formation')
            ->join('grilletarifaires', 'grilletarifaires.id', '=', 'tarificationsformations.id_grilletarifaire')
            ->select('tarificationsformations.id', 'formations.code_formation', 'formations.date_debut','formations.theme','grilletarifaires.type_tarif as libelle','grilletarifaires.montant')
            ->where('tarificationsformations.id_grilletarifaire','!=',3)
            //->where('tarificationsformations.code_formation',$code_formation)
            ->where('formations.code_formation',$code_formation)
            ->orderBy('tarificationsformations.id', 'DESC')
            ->get();

    }
}


if (!function_exists('getTarifFormationCustom')) {
    # code...
    function getTarifFormationCustom($code_formation,$tarif_formation_id,$email){

        if (!empty($tarif_formation_id)) {
            # code...
            $choix_tarif =  Tarificationsformation::find($tarif_formation_id);
            // liste des formations disponibles
            return $liste_formation = DB::table('tarificationsformations')
                ->join('detailspaiements', 'detailspaiements.id_tarification_formation', '=', 'tarificationsformations.id')
                ->leftjoin('reductions', 'reductions.id', '=', 'detailspaiements.id_reduction')
                ->join('formations', 'formations.code_formation', '=', 'tarificationsformations.code_formation')
                ->join('grilletarifaires', 'grilletarifaires.id', '=', 'tarificationsformations.id_grilletarifaire')
                ->select('tarificationsformations.id', 'formations.code_formation', 'formations.date_debut','formations.theme','grilletarifaires.type_tarif as libelle','grilletarifaires.montant','reductions.pourcentage','detailspaiements.preuve_path')
                ->where('tarificationsformations.id_grilletarifaire',$choix_tarif->id_grilletarifaire)
                ->where('formations.code_formation',$code_formation)
                ->where('detailspaiements.id_tarification_formation',$choix_tarif->id)
                ->where('detailspaiements.email',$email)
                ->orderBy('tarificationsformations.id', 'DESC')
                ->get();

        } else {
            # code...
            // liste des formations disponibles
            return $liste_formation = DB::table('tarificationsformations')
                ->join('formations', 'formations.code_formation', '=', 'tarificationsformations.code_formation')
                ->join('grilletarifaires', 'grilletarifaires.id', '=', 'tarificationsformations.id_grilletarifaire')
                ->select('tarificationsformations.id', 'formations.code_formation', 'formations.date_debut','formations.theme','grilletarifaires.type_tarif as libelle','grilletarifaires.montant')
                //->where('tarificationsformations.id_grilletarifaire','!=',3)
                //->where('tarificationsformations.code_formation',$code_formation)
                ->where('formations.code_formation',$code_formation)
                ->orderBy('tarificationsformations.id', 'DESC')
                ->get();
        }



    }
}


if (!function_exists('getFormations')) {
    # code...
    function getFormations($formation_tarif_id){
        // get formation objet
        $tarif_formation = Tarificationsformation::find($formation_tarif_id);
        return $formation = Formations::where("code_formation",$tarif_formation->code_formation)->get();

    }
}



if (!function_exists('getFileSize')) {
    # code...
    function getFileSize($size,$unit=""){

        if( (!$unit && $size >= 1<<30) || $unit == "GB")
            return number_format($size/(1<<30),2)."GB";
        if( (!$unit && $size >= 1<<20) || $unit == "MB")
            return number_format($size/(1<<20),2)."MB";
        if( (!$unit && $size >= 1<<10) || $unit == "KB")
            return number_format($size/(1<<10),2)."KB";
        return number_format($size)." bytes";

    }
}



if (!function_exists('checkInscription')) {
    # code...
    function checkInscription($code_formation){

        $formations = Inscriptions::where("code_formation",$code_formation)->count();

        if($formations > 0){
            return true;
        }else{
            return false;
        }

    }
}


if (!function_exists('checkFormationRealisee')) {
    # code...
    function checkFormationRealisee($code_formation){

        $formations = Rapports::where("code_formation",$code_formation)->count();

        if($formations > 0){

            return true;

        }else{
            return false;
        }

    }
}


if (!function_exists('getFormateur')) {
    # code...
    function getFormateur(){

        return  $formateurs = Formateurs::orderBy("id","desc")->get();

    }
}



if (!function_exists('getFormateurformation')) {
    # code...
    function getFormateurformation($formation_id){

        return  $formateurs = Formationsformateurs::where('formation_id',$formation_id)->get();

    }
}

if (!function_exists('checkFormateur')) {
    # code...
    function checkFormateur($formateur_id,$formation_id){

        return  $check_formateur = Formationsformateurs::where('formation_id',$formation_id)->where('formateur_id',$formateur_id)->exists();


    }
}


if (!function_exists('getFormationByFormateur')) {
    # code...
    function getFormationByFormateur($formateur_id){

        return  $formateurs = Formationsformateurs::where('formateur_id',$formateur_id)->get();

    }
}

if (!function_exists('checkFormation')) {
    # code...
    function checkFormation($code_formation,$tarif_formation_id){
        $user = Auth()->user();
        return  $check_formation = Detailspaiements::where("id_tarification_formation",$tarif_formation_id)->where('email',$user->email)->exists();
    }
}


if (!function_exists('getTarifFormationById')) {
    # code...
    function getTarifFormationById($tarif_formation_id){
        return  $check_formation = Tarificationsformation::find($tarif_formation_id);
    }
}

if (!function_exists('getDetailspaiement')) {
    # code...
    function getDetailspaiement($reference){
        return  $data = Detailspaiements::where("reference_paiement",$reference)->get();
    }
}

if (!function_exists('getDetailspaiementImpaye')) {
    # code...
    function getDetailspaiementImpaye($reference){
        return  $data = Detailspaiements::where("reference_paiement",$reference)
            ->where('statut_paiement','!=','PAYE')->get();
    }
}

if (!function_exists('checkPaiementAuditeur')) {
    # code...
    function checkPaiementAuditeur($email,$tarif_formation_id){
        $data = Detailspaiements::where("email",$email)->where("id_tarification_formation",$tarif_formation_id)->where("statut_paiement","IMPAYE")->first();
        if ($data) {
            # code...
            // vérifier si le paiement est déjà effectué
            return    $paiement_en_attente = DB::table('paiements')
                ->where("email",$email)
                ->where("reference",$data->reference_paiement)
                ->whereNull('num_transaction')
                ->orWhere('statut_paiement','EN ATTENTE')
                ->exists();

        } else {
            # code...
            return false;
        }

    }
}


if (!function_exists('getDetailPaiementByAuditeur')) {
    # code...
    function getDetailPaiementByAuditeur($email,$tarif_formation_id){
        $data = Detailspaiements::where("email",$email)->where("id_tarification_formation",$tarif_formation_id)->where("statut_paiement","IMPAYE")->first();
        # code...
        // vérifier si le paiement est déjà effectué
        return    $paiement_en_attente = DB::table('paiements')
            ->where("email",$email)
            ->where("reference",$data->reference_paiement)
            ->where(function ($query) {
                $query->where('statut_paiement', 'EN ATTENTE')
                    ->orWhereNull('num_transaction');
            })
            ->first();

    }
}
//
if (!function_exists('getCodeInscription')) {
    # code...
    function getCodeInscription($reference){
        $detailspaiement = Detailspaiements::where("reference_paiement",$reference)->first();
        return $inscrit = Inscriptions::where("inscriptions.email",$detailspaiement->email)->where("inscriptions.tarif_formation_id",$detailspaiement->id_tarification_formation)->firstOrFail();
    }
}

// return signataire
if (!function_exists('getSignataire')) {
    # code...
    function getSignataire($code_formation){
        $date_fin = date("Ymd");
        $liste_signataires = Signataires::orderBy("ordre","asc")->get();
        foreach ($liste_signataires as $key => $signataire) {
            # code...
            if ($signataire->date_fin != "") {
                # code...
                if ($date_fin <= date("Ymd",strtotime($signataire->date_fin)) ) {
                    # code...
                    return $signataire;
                }
            } else {
                # code...
                if ($date_fin >= date("Ymd",strtotime($signataire->date_debut)) ) {
                    # code...
                    return $signataire;
                }
            }

        }
        return false;

    }
}
// getSignataireByDate
if (!function_exists('getSignataireByDate')) {
    # code...
    function getSignataireByDate($date){
        $date_fin = date("Ymd", strtotime($date));
        $liste_signataires = Signataires::orderBy("ordre","asc")->get();
        foreach ($liste_signataires as $key => $signataire) {
            # code...
            if ($signataire->date_fin != "") {
                # code...
                if ($date_fin <= date("Ymd",strtotime($signataire->date_fin)) ) {
                    # code...
                    return $signataire;
                }
            } else {
                # code...
                if ($date_fin >= date("Ymd",strtotime($signataire->date_debut)) ) {
                    # code...
                    return $signataire;
                }
            }

        }
        return false;
    }
}

// getSignataire

if (!function_exists('getNewSignataire')) {
    # code...
    function getNewSignataire(){
        return Signataires::whereNull("date_fin")->first();
    }
}

//

if (!function_exists('getDureeEnregistrement')) {
    # code...
    function getDureeEnregistrement($code_formation){
        $check_duree_formation = Dureeformations::where("code_formation",$code_formation)->first();
        if ($check_duree_formation) {
            return $check_duree_formation->duree_enregistrement;
        } else {
            # code... create durée
            return 0;
        }

    }
}

//

if (!function_exists('getInscriptionGratuite')) {
    # code...
    function getInscriptionGratuite($code_formation){
        return Detailspaiements::where("code_formation",$code_formation)->where("id_reduction",3)->count();

    }
}
if (!function_exists('getInscriptionGratuitePresentiel')) {
    # code...
    function getInscriptionGratuitePresentiel($code_formation){
        return Detailspaiements::where("detailspaiements.code_formation",$code_formation)
            ->where("id_reduction",3)
            ->join("inscriptions","inscriptions.email","detailspaiements.email")
            ->where("inscriptions.type_formation","PRESENTIEL")
            ->where("inscriptions.code_formation",$code_formation)->count();

    }
}
if (!function_exists('getInscriptionGratuiteVisio')) {
    # code...
    function getInscriptionGratuiteVisio($code_formation){
        return Detailspaiements::where("detailspaiements.code_formation",$code_formation)
            ->where("id_reduction",3)
            ->join("inscriptions","inscriptions.email","detailspaiements.email")
            ->where("inscriptions.type_formation","VISIO")
            ->where("inscriptions.code_formation",$code_formation)->count();

    }
}
if (!function_exists('getInscriptionPayante')) {
    # code...
    function getInscriptionPayante($code_formation){
        return Detailspaiements::where("code_formation",$code_formation)->count();

    }
}

if (!function_exists('getMontantFormation')) {
    # code...
    function getMontantFormation($id){
        $detailPaiement = Detailspaiements::find($id);
        if ($detailPaiement->id_reduction) {
            # code...

            $getReduction = Reductions::find($detailPaiement->id_reduction);
            $valeurReduction = $getReduction->pourcentage;
        } else {
            # code...
            $valeurReduction = 0;
        }
        $getMontant = DB::table('tarificationsformations')
            ->join('grilletarifaires', 'grilletarifaires.id', '=', 'tarificationsformations.id_grilletarifaire')
            ->select('grilletarifaires.montant')
            ->where('tarificationsformations.id',$detailPaiement->id_tarification_formation)
            ->first();

        if($getMontant){
            $montant = $getMontant->montant - ($getMontant->montant*($valeurReduction/100));

        }else{
            $montant = 0;

        }

        return $montant;

    }
}



if (!function_exists('getMontantFormationCustom')) {
    # code...
    function getMontantFormationCustom($id){
        $detailPaiement = Detailspaiements::find($id);
        $paiement = Paiements::where("reference",$detailPaiement->reference_paiement)->first();

        // vérifier s'il s'agit d'une formation dsc
        if ($paiement->type == "DSC") {
            # code...
            $montant = 0;
            $listeDetails = Detailspaiements::where("reference_paiement",$paiement->reference)->get();
            foreach ($listeDetails as $key => $data) {
                # code...
                if ($data->have_reduction_dsc) {
                    # code...
                    $valeurReduction = 0.95; // 5% de reduction
                } else {
                    # code...
                    $valeurReduction = 1; // pas de reduction
                }
                $getMontant = DB::table('tarificationsformations')
                    ->join('grilletarifaires', 'grilletarifaires.id', '=', 'tarificationsformations.id_grilletarifaire')
                    ->select('grilletarifaires.montant')
                    ->where('tarificationsformations.id',$data->id_tarification_formation)
                    ->first();

                if($getMontant){
                    $montant = $montant + ($getMontant->montant*$valeurReduction);
                }else{
                    $montant = $montant +0;

                }

            }

            return $montant;


        } else {
            # code...

            if ($detailPaiement->id_reduction) {
                # code...

                $getReduction = Reductions::find($detailPaiement->id_reduction);
                $valeurReduction = $getReduction->pourcentage;
            } else {
                # code...
                $valeurReduction = 0;
            }
            $getMontant = DB::table('tarificationsformations')
                ->join('grilletarifaires', 'grilletarifaires.id', '=', 'tarificationsformations.id_grilletarifaire')
                ->select('grilletarifaires.montant')
                ->where('tarificationsformations.id',$detailPaiement->id_tarification_formation)
                ->first();

            if($getMontant){
                $montant = $getMontant->montant - ($getMontant->montant*($valeurReduction/100));

            }else{
                $montant = 0;

            }

            return $montant;
        }

    }
}


if (!function_exists('getReduction')) {
    # code...
    function getReduction($id){
        $getReduction = Reductions::find($id);
        return $getReduction->pourcentage;
    }
}


if (!function_exists('getMontantFormationByTarif')) {
    # code...
    function getMontantFormationByTarif($tarif){

        $getMontant = DB::table('tarificationsformations')
            ->join('grilletarifaires', 'grilletarifaires.id', '=', 'tarificationsformations.id_grilletarifaire')
            ->select('grilletarifaires.montant')
            ->where('tarificationsformations.id',$tarif)
            ->first();

        if($getMontant){
            $montant = $getMontant->montant ;

        }else{
            $montant = 0;
        }
        return $montant;

    }
}


if (!function_exists('getMontantFormationByTarifCustom')) {
    # code...
    function getMontantFormationByTarifCustom($tarif){

        $getMontant = DB::table('tarificationsformations')
            ->join('grilletarifaires', 'grilletarifaires.id', '=', 'tarificationsformations.id_grilletarifaire')
            ->select('grilletarifaires.montant')
            ->where('tarificationsformations.id',$tarif)
            ->first();

        if($getMontant){
            $montant = $getMontant->montant ;

        }else{
            $montant = 0;
        }
        return $montant;

    }
}

if (!function_exists('checkAParticiper')) {
    # code...
    function checkAParticiper($code_formation,$email){
        $checkData = Rapports::where("code_formation",$code_formation)->where("email",$email)->exists();
        return $checkData;

    }
}

if (!function_exists('getMontantFm')) {
    # code...
    function getMontantFm($code_formation,$email){
        return Detailspaiements::where("code_formation",$code_formation)->where("email",$email)->get();


    }
}

if (!function_exists('getPaiementAuditeur')) {
    # code...
    function getPaiementAuditeur($email,$tarif){
        return $liste_paiements = Detailspaiements::where("id_tarification_formation",$tarif)
            ->where("email",$email)
            ->whereNotNull('detailspaiements.email')
            ->orderBy('detailspaiements.id', 'DESC')->get();
    }
}

if (!function_exists('getModePaiement')) {
    # code...
    function getModePaiement($reference){
        $paiement = Paiements::where("reference",$reference)->first();
        if($paiement){
            if($paiement->id_modepaiement){
                $mp = Modepaiements::find($paiement->id_modepaiement);
                return $mp->libelle;
            }else{
                return 'N/A';
            }
        }else{
            return 'N/A';
        }
    }
}

if (!function_exists('checkPaiementTranche')) {
    # code...
    function checkPaiementTranche($reference){
        return FormationsTranches::where("reference","like","%$reference%")->exists();
    }
}

if (!function_exists('checkTrancheUnique')) {
    # code...
    function checkTrancheUnique($reference){
        return Detailspaiements::where("reference_paiement", $reference)->where("have_reduction_dsc",true)->exists();
    }
}

if (!function_exists('getFormationByCodeInscription')) {
    # code...
    function getFormationByCodeInscription($code_inscription){
        $inscription = Inscriptions::where("code_inscription", $code_inscription)->first();
        return $inscription->code_formation;
    }
}

if (!function_exists('getAllFormationByCodeInscription')) {
    # code...
    function getAllFormationByCodeInscription($code_inscription){
        $inscription = Inscriptions::where("code_inscription", $code_inscription)->get();
        return $inscription;
    }
}

function encryptData($data)
{
    $key = substr(hash('sha256', env('APP_KEY')), 0, 32); // Génération d'une clé de 32 caractères
    $iv = substr(hash('sha256', Str::random(16)), 0, 16); // IV (vector initialization)

    $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
    return substr(base64_encode($iv . '::' . $encrypted), 0, 100); // Limite à 100 caractères
}

function decryptData($encryptedData)
{
    $key = substr(hash('sha256', env('APP_KEY')), 0, 32);

    $decoded = base64_decode($encryptedData);
    list($iv, $encryptedText) = explode('::', $decoded, 2);

    return openssl_decrypt($encryptedText, 'AES-256-CBC', $key, 0, $iv);
}

// chekDossierAuditeur
if (!function_exists('chekDossierAuditeur')) {
    # code...
    function chekDossierAuditeur($auditeur_id){
        return  $check = InscritsTypePieces::where("created_by", $auditeur_id)->exists();
    }
}

// getNbrPieces
if (!function_exists('getNbrPieces')) {
    # code...
    function getNbrPieces(){
        $user = auth()->user();
        $countPiece = InscritsTypePieces::count();
        $countPieceAuditeur = InscritsTypePieces::where('created_by',$user->id)->count();

        return  $countPiece - $countPieceAuditeur;

    }
}
// code_inscription
if (!function_exists('getNbrPiecesMail')) {
    # code...
    function getNbrPiecesMail($code_inscription){
        $user = auth()->user();

        // Effectuer une jointure entre les tables auditeurs et inscrits
        $inscrit = \App\Models\Inscriptions::where("code_inscription", $code_inscription)->first();

        // Déterminer le type de formation
        $typeFormation = $inscrit->type_formation;

        // Récupérer les pièces déjà enregistrées
        $pieces = \App\Models\AuditeursPieces::where('created_by', $user->id)->pluck('inscrits_typespieces_id');

        // Exclure la pièce appropriée en fonction du type de formation
        if ($typeFormation == 'PROFESSIONNELS') {
            $typePieces = \App\Models\InscritsTypePieces::whereNotIn('id', $pieces)->where('is_professionnel', true)->orderBy("libelle", "asc")->get();
            $nbre_piece_require = \App\Models\InscritsTypePieces::where('is_professionnel', true)->count();

        } else {
            $typePieces = \App\Models\InscritsTypePieces::whereNotIn('id', $pieces)->where('is_etudiant', true)->orderBy("libelle", "asc")->get();
            $nbre_piece_require = \App\Models\InscritsTypePieces::where('is_etudiant', true)->count();
        }

        $nombrepieces = count($pieces);

        return  $nbre_piece_require - $nombrepieces;
    }
}

if (!function_exists('getAuditeurByRef')) {
    # code...
    function getAuditeurByRef($reference){
        $paiement = Paiements::where("reference", $reference)->first();
        $auditeur = Auditeurs::where("email",$paiement->email)->first();
        return $auditeur;
    }
}
//
if (!function_exists('checkConsultationZoom')) {
    # code...
    function checkConsultationZoom($code_formation){

        $formation = Formations::where("code_formation",$code_formation)->first();

        $check = Enregistrementszooms::where("formation_id", $formation->id)->exists();
        return $check;
    }
}
//
if (!function_exists('checkDossierDscValide')) {
    # code...
    function checkDossierDscValide($code_inscription){
        $check = InscriptionsDsc::where("code_inscription",$code_inscription)->exists();
        return $check;
    }
}

if (!function_exists('checkDsc2024')) {
    # code...
    function checkDsc2024($code_inscription){
        $inscrit = Inscriptions::where("code_inscription", $code_inscription)->orderBy("id", "DESC")->get()->first();
        $getFormation = Formations::where("code_formation",$inscrit->code_formation)->first();
        // vérifier s'il sagit d'une formation 2025
        $date = date("Y",strtotime($getFormation->date_debut));
        if ($date > 2024 ) {
            return false;
        }else{
            return true; // DSC 2024
        }
    }


    if (!function_exists('checkAjourne')) {
        # code...
        function checkAjourne($code_formation,$email){
            return  $checkData = Moodlemoyennes::where("code_formation", $code_formation)
                ->where('reference_candidat',$email)
                ->where('note_totale','<=',7)
                ->exists();
        }
    }

//

    if (!function_exists('getCandidat')) {
        # code...
        function getCandidat($email){
            return  Tmpmoodlecandidats::where("email", $email)
                ->first();
        }
    }

    if (!function_exists('getNoteCandidat')) {
        # code...
        function getNoteCandidat($code_formation,$email){

            $notes = DB::table('moodlemoyennes')
                ->join('moodlemodules', 'moodlemodules.id', '=', 'moodlemoyennes.moodlemodule_id')
                ->select('moodlemoyennes.note_totale as note', 'moodlemoyennes.note_coef as note_credit', 'moodlemodules.credit', 'moodlemoyennes.reference_candidat','moodlemodules.code','moodlemodules.libelle')
                ->where('moodlemoyennes.code_formation', $code_formation)
                ->where('moodlemoyennes.reference_candidat', $email)
                ->orderBy('moodlemoyennes.note_coef', 'DESC')
                ->get();

            return  $notes;
        }
    }

}
