<?php

namespace App\Http\Controllers;

use App\Exports\AllAttestationsFormateursExport;
use App\Exports\AllAttestationsParticipantsExport;
use App\Exports\AllParticipantsExport;
use App\Exports\AllRapportsExport;
use App\Exports\AuditeursExport;
use App\Exports\ExportEtatRecapitulatifOuvrage;
use App\Exports\ExportListePresence;
use App\Exports\ExportsInscritsDsc;
use App\Exports\ExportsInscritsDscSR;
use App\Exports\ExportsInscritsExcel;
use App\Exports\ExportsInscritsPresentielVisioExcel;
use App\Exports\ExportsInscritsVisioExcel;
use App\Exports\ExportsListeDossiersRejetesDsc;
use App\Exports\ExportsListeDossiersValidesDsc;
use App\Exports\ListeFormateur;
use App\Helpers\ResponseHandler;
use App\Imports\ImportCandidatsMoodle;
use App\Imports\ImportNoteCandidatMoodle;
use App\Imports\InscriptionPresentielVisioImport;
use App\Imports\InscriptionReunionImport;
use App\Imports\ParticipantReunionImport;
use App\Imports\PresentielImport;
use App\Imports\WebinairezoomImportation;
use App\Mail\ImportationPresentiel;
use App\Mail\NotificationImportation;
use App\Models\Documentwebinaires;
use App\Models\Formations;
use App\Models\Moodlemoyennes;
use App\Models\Moodlenotes;
use App\Models\Rapports;
use App\Models\Tmpmoodlecandidats;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class ExportationExcelController extends Controller
{
    /**
     * Export stats mode paiement
     *
     */
    public function exportListeInscritsDsc($code_formation) // ExportInscritsExcel
    {
        return Excel::download(new ExportsInscritsDsc($code_formation), "SIGWEB_ERSUMA_liste_inscrits_dsc_" . $code_formation . '_' . date("Ymd") . '_' . date("His") . ".xlsx");
    }

    /**
     * Export stats mode paiement
     *
     */
    public function exportListeInscritsDscSR($code_formation) // ExportInscritsExcel
    {
        return Excel::download(new ExportsInscritsDscSR($code_formation), "SIGWEB_ERSUMA_liste_inscrits_dsc_sousreserve_" . $code_formation . '_' . date("Ymd") . '_' . date("His") . ".xlsx");
    }

    /**
     * Export stats mode paiement
     *
     */
    public function exportListeInscritPresentielleExcel($code_formation) // ExportInscritsExcel
    {
         return Excel::download(new ExportsInscritsExcel($code_formation) ,"SIGWEB_ERSUMA_liste_inscrits_presentiel_".$code_formation.'_'.date("Ymd").'_'.date("His").".xlsx");
    }
    /**
     * Export stats mode paiement
     *
     */
    public function exportListePresenceExcel($code_formation) // ExportInscritsExcel
    {
        //return Excel::download(new ExportAuditeurPays,"auditeur_pays_".date("His").".xlsx");
        return Excel::download(new ExportListePresence($code_formation) ,"SIGWEB_ERSUMA_liste_presence_".$code_formation.'_'.date("Ymd").'_'.date("His").".xlsx");
    }

    /**
     * Export stats mode paiement
     *
     */
    public function exportListeInscritVisioExcel($code_formation) // ExportInscritsExcel
    {
        return Excel::download(new ExportsInscritsVisioExcel($code_formation) ,"SIGWEB_ERSUMA_liste_inscrits_visio_".$code_formation.'_'.date("Ymd").'_'.date("His").".xlsx");
    }

    /**
     * Export stats mode paiement
     *
     */
    public function exportListeInscritPresentielleVisioExcel($code_formation) // ExportInscritsExcel
    {
        return Excel::download(new ExportsInscritsPresentielVisioExcel($code_formation) ,"SIGWEB_ERSUMA_liste_inscrits_".$code_formation.'_'.date("Ymd").'_'.date("His").".xlsx");
    }

    /**
     * Export etat récapitulatif
     *
     */
    public function exportEtatRecapOuvrage(Request $request,$id)
    {
        return Excel::download(new ExportEtatRecapitulatifOuvrage($id),"SIGWEB_ERSUMA_Etat_recapitulatif_".date("Ymd").'_'.date("His").".xlsx");
    }


    public function importationRapportsWebianire(Request $request)
    {


        // récupérer le code de la formation
        $code_formation = $request->code_formation;
        $numero_webinaire = 0; // initialiser numero webinaire

        foreach ($request->doc_participant as $key => $document) {
            Excel::import(new WebinairezoomImportation($code_formation), $document);
        }

        // ############### traitement des données importés pour sortir le nombre de temps de chaque participant "

        $liste_participant = DB::table('participants')
            ->select(DB::raw('sum(temps_presence) as duree'), 'email', 'id_webinaire')
            ->where('code_formation', $code_formation)
            ->where('a_participe', 'oui')
            ->groupBy('email', 'id_webinaire')
            ->get();

        foreach ($liste_participant as $value) {

            $numero_webinaire = $value->id_webinaire;

            Rapports::create([
                'email' => $value->email,
                'temps_presence' => $value->duree,
                'code_formation' => $code_formation,
                'id_webinaire' => $value->id_webinaire
            ]);
        }

        if (count($liste_participant) > 0) {
            // sauvegarde des fichiers
            $fichier_inscription = $request->doc_inscription;
            $fichier_participation = $request->doc_participant;
            if (!empty($fichier_inscription)) {
                $extension = $fichier_inscription->getClientOriginalExtension();
                $fichier_name = $numero_webinaire . "_rapport_sur_inscriptions_" . date('Ymd-His') . '.' . $extension;
               // echo $fichier_name . "<br>";
                // $fichier_inscription->move(public_path('documents_csv'), $fichier_name);
                $fichier_inscription->storeAs('documents_csv', $fichier_name, 'public');
                Documentwebinaires::create([
                    'id_webinaire' => $numero_webinaire,
                    'code_formation' => $code_formation,
                    'document_name' => $fichier_name
                ]);
            }
            $i = 0;
            foreach ($fichier_participation as $key => $document) {
                $i++;
                $extension = $document->getClientOriginalExtension();
                $document_name = $numero_webinaire . "_rapport_sur_participants_" . $i . '_' . date('Ymd-His') . '.' . $extension;
                //echo $document_name . "<br>";
                // Storage::put('avatars/1', $content) Storage::put('avatars/1', $content)
                // $document->move( public_path('documents_csv'), $document_name);
                $document->storeAs('documents_csv', $document_name, 'public');

                Documentwebinaires::create([
                    'id_webinaire' => $numero_webinaire,
                    'code_formation' => $code_formation,
                    'document_name' => $document_name
                ]);
            }

            // update statut formation

            $formation = Formations::where('code_formation', $code_formation)->first();
            $formation->setAttribute('statut', 'INACTIF');
            $formation->update();

            // send mail for notification team ersuma
            if ($formation) {

                //try {

                    // send identifiant to mail for user
                    //$email = "sigweb.ersuma@ohada.org";
                    $email = env("MAIL_CC");
                    $contenu = [
                        'code_formation' => $formation->code_formation,
                        'email' => $email,
                        'subject' => 'SIGWEB - Importation rapports Zoom ' . $formation->code_formation
                    ];

                    # code...
                    Mail::to($email)->queue(new NotificationImportation($contenu));
                /*} catch (\Exception $e) {
                    // Get error here
                }*/
            }

            // redirection avec message de succès
            return ResponseHandler::success("Rapports importés avec succès",['success'=>true]);
        } else {
            // redirection avec message d'erreur
            return ResponseHandler::success("Echec lors de l'importation des données",['success'=>false]);
        }
    }


    public function importRapportPresentiel(Request $request)
    {

        // procéder à l'extration des données relatives aux inscrits
        $importer =   Excel::import(new PresentielImport($request->code_formation),$request->file_presentiel);

        if ($importer) {
            # code...
            try {

                // send identifiant to mail for user
                $email = env("MAIL_CC");
                $contenu = [
                    'code_formation' => $request->code_formation,
                    'email' => $email,
                    'subject'=>'SIGWEB - Importation liste des participants à la formation : '.$request->code_formation
                ];

                # code...
                Mail::to($email)->cc([env('MAIL_SUPPORT_TWO'),env('MAIL_SUPPORT_TREE')])->queue(new ImportationPresentiel($contenu));

            } catch(\Exception $e){
                // Get error here

            }

            return ResponseHandler::success("Liste Présentiel importée avec succès",['success'=>true]);

        } else {
            # code...
            return ResponseHandler::success("Echec importation",['success'=>false]);
        }

    }

    public function importInscription(Request $request)
    {

        // procéder à l'extration des données relatives aux inscrits
        $importer =   Excel::import(new InscriptionPresentielVisioImport($request->code_formation,$request->cout_presentiel,$request->cout_visio),$request->file_presentiel);

        if ($importer) {
            # code...
            try {

                // send identifiant to mail for user
                $email = env("MAIL_CC");
                $contenu = [
                    'code_formation' => $request->code_formation,
                    'email' => $email,
                    'subject'=>'SIGWEB - Importation liste des participants à la formation : '.$request->code_formation
                ];

                # code...
                Mail::to($email)->cc([env('MAIL_SUPPORT_TWO'),env('MAIL_SUPPORT_TREE')])->queue(new ImportationPresentiel($contenu));

            } catch(\Exception $e){
                // Get error here

            }

            return ResponseHandler::success("Inscription importée avec succès",['success'=>true]);

        } else {
            # code...
            return ResponseHandler::success("Echec importation",['success'=>false]);
        }


    }

    public function importReunion(Request $request)
    {
        // récupérer le code de la formation
        $code_formation = $request->code_formation;
        $numero_webinaire = 0; // initialiser numero webinaire
        // procéder à l'extration des données relatives aux inscrits
        Excel::import(new InscriptionReunionImport($code_formation),$request->doc_inscription);

        foreach ($request->doc_participant as $key => $document) {
            Excel::import(new ParticipantReunionImport($code_formation),$document);

        }

        //  \Session::put('success', 'Your file is imported successfully in database.');

        // ############### traitement des données importés pour sortir le nombre de temps de chaque participant "

        $liste_participant = DB::table('participants')
            ->select(DB::raw('sum(temps_presence) as duree'), 'email','id_webinaire')
            ->where('code_formation',$code_formation)
            ->where('a_participe','oui')
            ->groupBy('email','id_webinaire')
            ->get();

        foreach ($liste_participant as $value){

            $numero_webinaire = $value->id_webinaire;

            Rapports::create([
                'email' => $value->email,
                'temps_presence' => $value->duree,
                'code_formation'=>$code_formation,
                'id_webinaire'=>$value->id_webinaire
            ]);
        }

        if(count($liste_participant) > 0){
            // sauvegarde des fichiers
            $fichier_inscription = $request->doc_inscription;
            $fichier_participation = $request->doc_participant;
            if(!empty($fichier_inscription)){
                $extension = $fichier_inscription->getClientOriginalExtension();
                $fichier_name = $numero_webinaire."_rapport_sur_inscriptions_".date('Ymd-His').'.'.$extension;
                echo $fichier_name."<br>";
                // $fichier_inscription->move(public_path('documents_csv'), $fichier_name);
                $fichier_inscription->storeAs('documents_csv', $fichier_name,'public');
                Documentwebinaires::create([
                    'id_webinaire' => $numero_webinaire,
                    'code_formation' => $code_formation,
                    'document_name'=>$fichier_name
                ]);
            }
            $i = 0;
            foreach ($fichier_participation as $key => $document) {
                $i++;
                $extension = $document->getClientOriginalExtension();
                $document_name = $code_formation."_rapport_sur_participants_" . $i . '_'.date('Ymd-His').'.'.$extension;
                //echo $document_name."<br>";
                // Storage::put('avatars/1', $content) Storage::put('avatars/1', $content)
                // $document->move( public_path('documents_csv'), $document_name);
                $document->storeAs('documents_csv', $document_name,'public');

                Documentwebinaires::create([
                    'id_webinaire' => $numero_webinaire,
                    'code_formation' => $code_formation,
                    'document_name'=>$document_name
                ]);

            }

            // update statut formation

            $formation = Formations::where('code_formation',$code_formation)->first();
            $formation->setAttribute('statut', 'INACTIF');
            $formation->update();

            // send mail for notification team ersuma
            if($formation){

                try {

                    // send identifiant to mail for user
                    $email = "sigweb.ersuma@ohada.org";
                    $contenu = [
                        'code_formation' => $formation->code_formation,
                        'email' => $email,
                        'subject'=>'SIGWEB - Importation rapports Zoom '.$formation->code_formation
                    ];


                    # code...
                    Mail::to($email)->cc([env('MAIL_SUPPORT_TWO'),env('MAIL_SUPPORT_TREE')])->queue(new NotificationImportation($contenu));

                } catch(\Exception $e){
                    // Get error here

                }
            }

            // redirection avec message de succès

            return ResponseHandler::success("Rapports importé avec succès",['success'=>true]);

        } else {
            # code...
            return ResponseHandler::success("Echec importation",['success'=>false]);

        }

    }

    public function importNotes(Request $request)
    {
        ini_set('max_execution_time', 0); // illimité

        // récupérer le code de la formation
        $code_formation = $request->code_formation;
        $objFormation = Formations::where("code_formation",$code_formation)->first();
        $annee_academique = date("Y",strtotime($objFormation->date_debut));

        // procéder à l'extration des données relatives aux notes dans le fichier envoyé
        Excel::import(new ImportNoteCandidatMoodle($code_formation, $annee_academique),$request->fichier_note);

        // vérifier si les notes sont importées
        $date = date('Y-m-d');
        $checkNote = Moodlenotes::where("created_at","like","%$date%")->count();
        $checkNoteMoyenne = Moodlemoyennes::where("created_at","like","%$date%")->count();

        if ($checkNote > 0 || $checkNoteMoyenne > 0) {
            # code...
            return ResponseHandler::success("Notes importées avec succès", ['success' => true]);

        } else {
            # code...
            return ResponseHandler::success("Echec importation", ['success' => false]);
        }

    }

    public function importCandidats(Request $request)
    {

        // procéder à l'extration des données relatives aux notes dans le fichier envoyé
        Excel::import(new ImportCandidatsMoodle(),$request->liste_candidats);

        // vérifier si les notes sont importées
        $date = date('Y-m-d');
        $checkData = Tmpmoodlecandidats::where("updated_at","like","%$date%")->count();

        if ($checkData > 0) {
            # code...
            return ResponseHandler::success("Importation effectuée avec succès",['success' => true]);

        } else {
            # code...
            return ResponseHandler::success("Echec lors de l'importation des données",['success' => false]);
        }
    }

    // export liste auditeur
    public function AuditeursExportListe() {
        return Excel::download(new AuditeursExport(),"SIGWEB_LISTE_DES_AUDITEURS_".date("Ymdhis").".xlsx");
    }

    // export list formateur
        public function FormateursExportListe() 
    {   
        return Excel::download(new ListeFormateur() ,"SIGWEB_Liste_Formateurs_".date("Ymd").'_'.date("His").".xlsx"); 
    }
    // export all participant by code formation
    public function ExportAllParticipantExcel(Request $request) {
        return Excel::download(new AllParticipantsExport($request->code_formation),"SIGWEB_LISTE_PARTICIPANTS_".date("Ymdhis").".xlsx");
    }
    // export all rapport by code formation
    public function ExportAllRapportExcel(Request $request) {
        return Excel::download(new AllRapportsExport($request->code_formation),"SIGWEB_LISTE_RAPPORTS_".date("Ymdhis").".xlsx");
    }
    // export all attestation participant by code formation
    public function ExportAllAttestationParticipantExcel(Request $request) {
        return Excel::download(new AllAttestationsParticipantsExport($request->code_formation),"SIGWEB_LISTE_PARTICIPANTS_ATTESTATIONS_".date("Ymdhis").".xlsx");
    }
    // export all attestation formateur by code formation
    public function ExportAllAttestationFormateurExcel(Request $request) {
        return Excel::download(new AllAttestationsFormateursExport($request->code_formation),"SIGWEB_LISTE_FORMATEURS_ATTESTATIONS_".date("Ymdhis").".xlsx");
    }

    
    public function exportListeDossiersValidesDsc($code_formation) 
    {
        return Excel::download(new ExportsListeDossiersValidesDsc($code_formation), "SIGWEB_ERSUMA_liste_dossiers_valides_dsc_" . $code_formation . '_' . date("Ymd") . '_' . date("His") . ".xlsx");
    }
    
    public function exportListeDossiersRejetesDsc($code_formation) 
    {
        return Excel::download(new ExportsListeDossiersRejetesDsc($code_formation), "SIGWEB_ERSUMA_liste_dossiers_rejetes_dsc_" . $code_formation . '_' . date("Ymd") . '_' . date("His") . ".xlsx");
    }
}
