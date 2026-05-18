<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Mail\Paiementattente;
use App\Mail\SendAttestation;
use App\Mail\SendAttestationFormateur;
use App\Models\Auditeurs;
use App\Models\Detailpaiementouvrages;
use App\Models\Detailspaiements;
use App\Models\Dureeformations;
use App\Models\Formations;
use App\Models\Formationsformateurs;
use App\Models\Inscriptions;
use App\Models\Logopartenaires;
use App\Models\Modepaiements;
use App\Models\Paiements;
use App\Models\PaiementsTranche;
use App\Models\Rapports;
use App\Models\Reductions;
use App\Models\Tmpmoodlecandidats;
use App\Services\contracts\DocumentsInterface;
use App\Services\util\GenarateurCodeService;
use Arius\NumberFormatter;
use Barryvdh\DomPDF\Facade\Pdf;
use http\Url;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentsService implements DocumentsInterface
{
    private GenarateurCodeService $genarateurCodeService;

    public function __construct(GenarateurCodeService $genarateurCodeService)
    {
        $this->genarateurCodeService = $genarateurCodeService;
    }

    public function getFormationVisuel(int $id)
    {
        // TODO: Implement getFormationVisuel() method.
        // Récupérer le visuel de la formation par son ID
        $doc = Formations::find($id);

        if (!$doc) {
            return ResponseHandler::error('Document non trouvé', 404);
        }

        $path = 'visuel_formation/' . $doc->visuel;
        $default_path = 'visuel_formation/visuel_not_found.png';
        try {
            $checkImg = true;
            if (!Storage::disk('public')->exists($path)) {
                //return ResponseHandler::error('Fichier non trouvé : ' . $doc->visuel, 404);
                $checkImg = false;
            }
            if ($checkImg) {
                return Storage::disk('public')->response($path);
            } else {
                return Storage::disk('public')->response($default_path);
            }
        } catch (\Exception $exception) {
            return Storage::disk('public')->response($default_path);
        }
    }

    public function downloadRecuPaiement(string $reference)
    {
        // TODO: Implement downloadRecuPaiement() method.
        // vérifier si la référence existe dans la table paiement tranche
        // récupérer la référence de la table paiement
        $getOneTranchePaiement = PaiementsTranche::where('reference', 'like', '%' . $reference . '%')->first();

        if ($getOneTranchePaiement) {
            # code... it is dsc
            $getOneDtPaiement = Detailspaiements::find($getOneTranchePaiement->details_paiements_id);
            $getPaiement = Paiements::where('reference', $getOneDtPaiement->reference_paiement)->first();

            $paiement = Paiements::find($getPaiement->id);

            $auditeur = Auditeurs::where("email", $paiement->email)->first();
            # code...
            $details_paiement = DB::table('detailspaiements')
                ->leftjoin('paiements_tranches', 'paiements_tranches.details_paiements_id', '=', 'detailspaiements.id')
                ->join('paiements', 'detailspaiements.reference_paiement', '=', 'paiements.reference')
                ->join('tarificationsformations', 'tarificationsformations.id', '=', 'detailspaiements.id_tarification_formation')
                ->join('grilletarifaires', 'tarificationsformations.id_grilletarifaire', '=', 'grilletarifaires.id')
                ->join('formations', 'formations.code_formation', '=', 'tarificationsformations.code_formation')
                ->select(
                    'formations.code_formation',
                    'formations.theme',
                    'formations.date_debut',
                    'paiements_tranches.montant_paye',
                    'detailspaiements.montant_restant',
                    'paiements.montant_total',
                    'paiements.montant_restant as recap_montant_restant',
                    'detailspaiements.have_reduction_dsc',
                    'detailspaiements.statut_paiement',
                    'detailspaiements.preuve_path',
                    'grilletarifaires.type_tarif',
                    'paiements_tranches.num_transaction',
                    'paiements_tranches.statut_traitement as statut_tranche'
                )
                ->where('detailspaiements.reference_paiement', $paiement->reference)
                ->orderBy('detailspaiements.id', 'DESC')
                ->get();

            // Detailspaiement::where('reference_paiement','ERS-2021-FACT-000001');
            // return view('public.recu',compact("paiement","details_paiement"));
            $formatter = new NumberFormatter('fr', NumberFormatter::SPELLOUT);
            $formatter->setTextAttribute(NumberFormatter::DEFAULT_RULESET, "%spellout-ordinal");

            // share data to view
            view()->share(['formatter' => $formatter, 'paiement' => $paiement, 'auditeur' => $auditeur, 'details_paiement' => $details_paiement]);
            $pdf = Pdf::loadView('documents.recu_paiement_dsc');
            $customPaper = array(0, 0, 720, 1440);
            $pdf->setPaper($customPaper, 'landscape');

            $date = date('d_m_Y_His');
            // download PDF file with download method
            return $pdf->download('recu_' . $reference . '_' . $date . '.pdf');
        } else {
            # code...

            $paiement = Paiements::where('reference', $reference)->get()->first();
            // vérifier si l'objet paiement n'est pas null
            if ($paiement) {
                # code...

                $auditeur = Auditeurs::where("email", $paiement->email)->first();
                $details_paiement = DB::table('detailspaiements')
                    ->join('paiements', 'paiements.reference', '=', 'detailspaiements.reference_paiement')
                    ->leftjoin('reductions', 'reductions.id', '=', 'detailspaiements.id_reduction')
                    ->join('tarificationsformations', 'tarificationsformations.id', '=', 'detailspaiements.id_tarification_formation')
                    ->join('grilletarifaires', 'tarificationsformations.id_grilletarifaire', '=', 'grilletarifaires.id')
                    ->join('formations', 'formations.code_formation', '=', 'tarificationsformations.code_formation')
                    ->select('formations.code_formation', 'formations.theme', 'formations.date_debut', 'grilletarifaires.montant', 'grilletarifaires.libelle', 'reductions.pourcentage', 'detailspaiements.statut_paiement', 'detailspaiements.preuve_path', 'paiements.num_transaction')
                    ->where('detailspaiements.reference_paiement', $reference)
                    ->orderBy('detailspaiements.id', 'DESC')
                    ->get();
                // Detailspaiement::where('reference_paiement','ERS-2021-FACT-000001');
                // return view('public.recu',compact("paiement","details_paiement"));
                $formatter = new NumberFormatter('fr', NumberFormatter::SPELLOUT);
                $formatter->setTextAttribute(NumberFormatter::DEFAULT_RULESET, "%spellout-ordinal");

                // share data to view
                view()->share(['formatter' => $formatter, 'paiement' => $paiement, 'auditeur' => $auditeur, 'details_paiement' => $details_paiement]);
                $pdf = Pdf::loadView('documents.recu_paiement');
                $customPaper = array(0, 0, 720, 1440);
                $pdf->setPaper($customPaper, 'landscape');

                $date = date('d_m_Y_His');
                // download PDF file with download method
                return $pdf->download('recu_' . $reference . '_' . $date . '.pdf');
            } else {
                # code...
                return  ResponseHandler::success("ok");
            }
        }
    }

    public function getUrlSignedRecuPaiement(string $reference): \Illuminate\Http\JsonResponse
    {
        $url = $this->genarateurCodeService->generateUrlSigned('paiements.recu', ['reference' => $reference]);
        $data = ['url' => $url];
        return  ResponseHandler::success("ok", $data, 200);
    }

    public function getUrlSignedRecuPaiementDsc(string $reference): \Illuminate\Http\JsonResponse
    {
        $url = $this->genarateurCodeService->generateUrlSigned('paiements.recu', ['reference' => $reference]);
        $data = ['url' => $url];
        return  ResponseHandler::success("ok", $data, 200);
    }

    public function getUrlSignedRecuPaiementOuvrage(string $reference): \Illuminate\Http\JsonResponse
    {
        $url = $this->genarateurCodeService->generateUrlSigned('paiements.recu-ouvrage', ['reference' => $reference]);
        $data = ['url' => $url];
        return  ResponseHandler::success("ok", $data, 200);
    }

    public function downloadProforma(string $reference)
    {
        // TODO: Implement downloadProforma() method.
        $inscrit = Inscriptions::where("code_inscription", $reference)->orderBy("id", "DESC")->get()->first();
        $auditeur = Auditeurs::where("email", $inscrit->email)->first();
        $liste_formation = Inscriptions::where("code_inscription", $reference)->orderBy("id", "DESC")->get();

        $formatter = new NumberFormatter('fr', NumberFormatter::SPELLOUT);
        $formatter->setTextAttribute(NumberFormatter::DEFAULT_RULESET, "%spellout-ordinal");

        // share data to view
        view()->share(['formatter' => $formatter, 'inscrit' => $inscrit, 'auditeur' => $auditeur, 'liste_formation' => $liste_formation]);
        $pdf = PDF::loadView('documents.pro_forma');
        $customPaper = array(0, 0, 720, 1440);
        $pdf->setPaper($customPaper, 'landscape');

        $date = date('d_m_Y_His');
        // download PDF file with download method
        return $pdf->download('proforma_' . $reference . '_' . $date . '.pdf');
    }

    public function getUrlProformaSigned(string $reference): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getUrlProformaSigned() method.
        $url = $this->genarateurCodeService->generateUrlSigned('pro-forma', ['reference' => $reference]);
        $data = ['url' => $url];
        return  ResponseHandler::success("ok", $data, 200);
    }

    public function getUrlProformaDscSigned(string $reference): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getUrlProformaDscSigned() method.
        $url = $this->genarateurCodeService->generateUrlSigned('pro-forma-dsc', ['reference' => $reference]);
        $data = ['url' => $url];
        return  ResponseHandler::success("ok", $data, 200);
    }

    public function downloadProformaDsc(string $reference)
    {
        // TODO: Implement downloadProformaDsc() method.
        $inscrit = Inscriptions::where("code_inscription", $reference)->orderBy("id", "DESC")->get()->first();
        $auditeur = Auditeurs::where("email", $inscrit->email)->first();
        $liste_formation = Inscriptions::where("code_inscription", $reference)->orderBy("id", "DESC")->get();
        $details = Detailspaiements::where("code_inscription", $reference)->orderBy("id", "DESC")->get();

        $formatter = new NumberFormatter('fr', NumberFormatter::SPELLOUT);
        $formatter->setTextAttribute(NumberFormatter::DEFAULT_RULESET, "%spellout-ordinal");

        // share data to view
        view()->share(['formatter' => $formatter, 'inscrit' => $inscrit, 'auditeur' => $auditeur, 'liste_formation' => $liste_formation, 'details' => $details]);
        $pdf = PDF::loadView('documents.pro_forma_dsc');
        $customPaper = array(0, 0, 720, 1440);
        $pdf->setPaper($customPaper, 'landscape');

        $date = date('d_m_Y_His');
        // download PDF file with download method
        return $pdf->download('proforma_' . $reference . '_' . $date . '.pdf');
    }

    public function getListeAttestationParticipant(Request $request, string $code_formation) //: LengthAwarePaginator
    {
        // TODO: Implement getListeAttestationParticipant() method.
        // ##################### calcul scoring ###########################################
        $getDuree = 0;
        $check_duree_formation = Dureeformations::where("code_formation", $code_formation)->first();

        if ($check_duree_formation) {
            $nbr_duree_enregistrement = $check_duree_formation->duree_enregistrement;
        } else {
            # code... create durée
            $nbr_duree_enregistrement = 0;
        }
        //variable temps de latence
        $temps_latence = 90; // par jour de formation ||
        // récupérer le nbre de jour de formation pour la formation en cours
        //$nbre_session_formation = Sessionformation::where('code_formation',$code_formation)->count();
        //$total_temps_latence = $temps_latence * $nbre_session_formation;

        $duree_formation = DB::table('sessionformations')
            ->select(DB::raw('sum(duree_reel - ' . $temps_latence . ') as duree_session'))
            ->where('code_formation', $code_formation)
            ->get();

        if ($nbr_duree_enregistrement > 0) {
            $getDuree = $nbr_duree_enregistrement;
        } else {
            $getDuree = $duree_formation[0]->duree_session;
        }

        $scoring = intval(($getDuree * 2) / 3);
        //####################### end calcul scoring ####################################################


        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        $nbrePresentiel = DB::table('rapports')
            ->join('inscriptions', 'inscriptions.email', '=', 'rapports.email')
            ->where('rapports.code_formation', $code_formation)
            ->where('inscriptions.code_formation', $code_formation)
            ->where('rapports.temps_presence', 0)
            ->count();

        $nbreVisio = DB::table('rapports')
            ->join('inscriptions', 'inscriptions.email', '=', 'rapports.email')
            ->where('rapports.code_formation', $code_formation)
            ->where('inscriptions.code_formation', $code_formation)
            ->where('rapports.temps_presence', '>=', $scoring)
            ->count();

        $query = DB::table('rapports')
            ->join('inscriptions', 'inscriptions.email', '=', 'rapports.email')
            ->leftJoin('auditeurs', 'inscriptions.email', '=', 'auditeurs.email')
            ->leftJoin('tarificationsformations', 'inscriptions.tarif_formation_id', '=', 'tarificationsformations.id')
            ->leftJoin('grilletarifaires', 'tarificationsformations.id_grilletarifaire', '=', 'grilletarifaires.id')
            ->leftJoin('detailspaiements', function ($join) {
                $join->on('detailspaiements.email', '=', 'inscriptions.email');
                $join->on('detailspaiements.id_tarification_formation', '=', 'inscriptions.tarif_formation_id');
            })
            ->select(
                'rapports.id',
                'rapports.email',
                'rapports.attestation_send',
                'rapports.code_formation',
                'rapports.temps_presence',
                'inscriptions.id as id_inscrit',
                'auditeurs.nom',
                'auditeurs.prenoms',
                'auditeurs.pays',
                'auditeurs.civilite',
                'auditeurs.genre',
                'detailspaiements.statut_paiement'
            )
            ->where('rapports.code_formation', $code_formation)
            ->where('inscriptions.code_formation', $code_formation)
            ->where(function ($q) use ($scoring) {
                $q->where('rapports.temps_presence', 0)
                    ->orWhere('rapports.temps_presence', '>=', $scoring);
            });
        // Recherche globale sur plusieurs champs
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('auditeurs.nom', 'like', "%{$search}%")
                    ->orWhere('auditeurs.prenoms', 'like', "%{$search}%")
                    ->orWhere('rapports.email', 'like', "%{$search}%")
                    ->orWhere('rapports.temps_presence', 'like', "%{$search}%")
                    ->orWhere('auditeurs.pays', 'like', "%{$search}%")
                    ->orWhere('detailspaiements.statut_paiement', 'like', "%{$search}%");
            });
        }

        switch ($sortField) {
            case 'nom':
                # code...
                $query->orderBy("auditeurs.{$sortField}", $sortOrder);
                break;
            case 'prenoms':
                # code...
                $query->orderBy("auditeurs.{$sortField}", $sortOrder);
                break;
            case 'email':
                # code...
                $query->orderBy("rapports.{$sortField}", $sortOrder);
                break;
            case 'temps_presence':
                # code...
                $query->orderBy("rapports.{$sortField}", $sortOrder);
                break;
            case 'statut_paiement':
                # code...
                $query->orderBy("detailspaiements.{$sortField}", $sortOrder);
                break;
            case 'attestation_send':
                # code...
                $query->orderBy("rapports.{$sortField}", $sortOrder);
                break;

            default:
                # code...
                $query->orderBy("rapports.{$sortField}", $sortOrder);
                break;
        }

        $data = $query->paginate($perPage);

        return [
            'pagination' => $data,
            'nbr_duree_enregistrement' => $nbr_duree_enregistrement,
            'duree_formation' => $duree_formation,
            'nbreVisio' => $nbreVisio,
            'nbrePresentiel' => $nbrePresentiel,
        ];
    }

    public function showAttestationParticipant($rapport_id)
    {
        // TODO: Implement showAttestationParticipantVisio() method.
        set_time_limit(0);
        $fichier = "";
        // ******************************************* //

        $rapport = DB::table('rapports')
            ->select('rapports.code_formation', 'temps_presence')
            ->where('rapports.id', $rapport_id)
            ->get()
            ->first();

        $code_formation = $rapport->code_formation;


        $date = date('d-m-YH:i:s');
        $date_jour = date('d-m-Y');
        $date_jour = Carbon::now()->locale('fr_FR');
        $date_jour = $date_jour->isoFormat('DD MMM YYYY');
        if ($rapport->temps_presence > 0) {
            // il s'agit de visio
            $liste_participants = DB::table('rapports')
                ->join('inscriptions', 'inscriptions.email', '=', 'rapports.email')
                ->join('auditeurs', 'inscriptions.email', '=', 'auditeurs.email')
                ->join('formations', 'formations.code_formation', '=', 'rapports.code_formation')
                ->select('rapports.*', 'auditeurs.nom as nom', 'auditeurs.prenoms as prenoms', 'auditeurs.pays as pays', 'formations.lieu', 'formations.theme', 'formations.date_debut', 'formations.date_fin', 'inscriptions.presentiel', 'formations.code_formation')
                ->where('rapports.id', $rapport_id)
                ->where('rapports.code_formation', $code_formation)
                ->where('inscriptions.code_formation', $code_formation)
                ->orderBy('auditeurs.nom', 'ASC')
                ->get();
        } else {
            // il s'agit de presentiel
            $liste_participants = DB::table('rapports')
                ->join('inscriptions', 'inscriptions.email', '=', 'rapports.email')
                ->join('auditeurs', 'inscriptions.email', '=', 'auditeurs.email')
                ->join('formations', 'formations.code_formation', '=', 'rapports.code_formation')
                ->select('rapports.*', 'auditeurs.nom as nom', 'auditeurs.prenoms as prenoms', 'auditeurs.pays as pays', 'formations.lieu', 'formations.theme', 'formations.date_debut', 'formations.date_fin', 'inscriptions.presentiel', 'formations.code_formation')
                ->where('rapports.id', $rapport_id)
                ->where('rapports.code_formation', $code_formation)
                ->where('inscriptions.code_formation', $code_formation)
                ->where('rapports.temps_presence', 0)
                ->orderBy('auditeurs.nom', 'ASC')
                ->get();
        }

        $duree_formation = Dureeformations::all()->where('code_formation', $code_formation)->sortByDesc("id");
        $temps_latence = 90;
        $duree_session = DB::table('sessionformations')
            ->select(DB::raw('sum(duree_reel - ' . $temps_latence . ') as duree_session'))
            ->where('code_formation', $code_formation)
            ->get();
        // formation
        $formation = Formations::where("code_formation", $code_formation)->first();
        $logo_partenaires = Logopartenaires::where('code_formation', $code_formation)->get();

        //dd($formation->typeformation_id);
        if ($formation->typeformation_id != null) {
            # code...
            $typeformation_id = $formation->typeformation_id;
        } else {
            # code...
            $typeformation_id = 1;
        }

        // share data to view
        view()->share(['liste_participants' => $liste_participants, 'date' => $date_jour, 'code_formation' => $code_formation, 'durees' => $duree_formation, 'duree_session' => $duree_session, "typeformation_id" => $typeformation_id, 'logo_partenaires' => $logo_partenaires, 'formation' => $formation]);

        // service permettant de retourner la vue sur condition
        $pdf = $this->genarateurCodeService->getBladeAttestationParticipant($formation->is_event, $formation);

        $pdf->setPaper('A4', 'landscape');


        return $pdf->stream();
    }

    public function getUrlConsultationAttestation($rapport_id)
    {
        // TODO: Implement getUrlConsultationAttestationVisio() method.
        $url = $this->genarateurCodeService->generateUrlSigned('consultation.attestation.participant', ['rapport_id' => $rapport_id]);
        $data = ['url' => $url];
        return  ResponseHandler::success("ok", $data, 200);
    }

    public function sendMailAttestationParticipant($rapport_id): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement sendMailAttestationParticipant() method.
        $rapport = DB::table('rapports')
            ->select('rapports.*')
            ->where('rapports.id', $rapport_id)
            ->get()
            ->first();

        $code_formation = $rapport->code_formation;

        // récupérer les information du participant

        $date = date('d-m-YH:i:s');
        $date_jour = date('d-m-Y');
        $date_jour = Carbon::now()->locale('fr_FR');
        $date_jour = $date_jour->isoFormat('DD MMM YYYY');
        if ($rapport->temps_presence > 0) {
            $checkvisio = 'oui';
            $liste_participant = DB::table('rapports')
                ->join('inscriptions', 'inscriptions.email', '=', 'rapports.email')
                ->leftjoin('auditeurs', 'inscriptions.email', '=', 'auditeurs.email')
                ->join('formations', 'formations.code_formation', '=', 'rapports.code_formation')
                ->select('rapports.*', 'auditeurs.nom as nom', 'auditeurs.prenoms as prenoms', 'auditeurs.pays as pays', 'formations.lieu', 'formations.theme', 'formations.date_debut', 'formations.date_fin', 'formations.code_formation')
                ->where('rapports.id', $rapport_id)
                ->where('rapports.code_formation', $code_formation)
                ->where('inscriptions.code_formation', $code_formation)
                ->orderBy('auditeurs.nom', 'ASC')
                ->get()->first();
        } else {
            $checkvisio = 'non';
            $liste_participant = DB::table('rapports')
                ->join('inscriptions', 'inscriptions.email', '=', 'rapports.email')
                ->leftjoin('auditeurs', 'inscriptions.email', '=', 'auditeurs.email')
                ->join('formations', 'formations.code_formation', '=', 'rapports.code_formation')
                ->select('rapports.*', 'auditeurs.nom as nom', 'auditeurs.prenoms as prenoms', 'auditeurs.pays as pays', 'formations.lieu', 'formations.theme', 'formations.date_debut', 'formations.date_fin', 'formations.code_formation')
                ->where('rapports.id', $rapport_id)
                ->where('rapports.code_formation', $code_formation)
                ->where('inscriptions.code_formation', $code_formation)
                ->where('rapports.temps_presence', 0)
                ->orderBy('auditeurs.nom', 'ASC')
                ->get()->first();
        }


        // $fichier_name = Crypt::encrypt($rapport_id);
        $signedUrl = $this->genarateurCodeService->generateUrlSigned('telecharger.attestation_participant', ['rapport_id' => $rapport_id]);

        // enregistrer les infos dans une table pour passer la views
        $contenu = [
            'nom_prenoms' => $liste_participant->prenoms . ' ' . $liste_participant->nom,
            'email' => $liste_participant->email,
            //'fichier_name' => $fichier_name,
            'signedUrl' => $signedUrl,
            'code_formation' => $code_formation,
            'theme' => $liste_participant->theme,
            'password' => substr(md5($code_formation . '' . $rapport->email), 0, 8),
            'subject' => 'ATTESTATION ' . $code_formation . ' ' . $liste_participant->nom,
            'visio' => $checkvisio
        ];

        // send mail
        try {
            # code...
            Mail::to(trim($rapport->email))->cc([env('MAIL_CC')])->bcc(env('MAIL_BCC'))->queue(new SendAttestation($contenu));
            $up_rapport = Rapports::find($rapport->id);
            $up_rapport->setAttribute('attestation_send', true);
            $up_rapport->update();
            return ResponseHandler::success('Mail envoyé avec succès', ['success' => true, 'code_formation' => $rapport->code_formation]);
        } catch (\Exception $e) {
            // Get error here
            Log::info('echec de l\'envoi du mail');
            return ResponseHandler::success('Mail non envoyé ', ['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function downloadAttestationParticipant($rapport_id)
    {
        // TODO: Implement downloadAttestationParticipant() method.
        set_time_limit(0);

        $rapport = DB::table('rapports')
            ->select('rapports.*')
            ->where('rapports.id', $rapport_id)
            ->get()
            ->first();

        $code_formation = $rapport->code_formation;

        $date = date('d-m-YH:i:s');
        $date_jour = date('d-m-Y');
        $date_jour = Carbon::now()->locale('fr_FR');
        $date_jour = $date_jour->isoFormat('DD MMM YYYY');
        if ($rapport->temps_presence > 0) {
            $liste_participants = DB::table('rapports')
                ->join('inscriptions', 'inscriptions.email', '=', 'rapports.email')
                ->leftjoin('auditeurs', 'inscriptions.email', '=', 'auditeurs.email')
                ->join('formations', 'formations.code_formation', '=', 'rapports.code_formation')
                ->select('rapports.*', 'auditeurs.nom as nom', 'auditeurs.prenoms as prenoms', 'auditeurs.pays as pays', 'formations.theme', 'formations.date_debut', 'formations.date_fin', 'formations.code_formation', 'formations.lieu', 'inscriptions.presentiel')
                ->where('rapports.id', $rapport_id)
                ->where('rapports.code_formation', $code_formation)
                ->where('inscriptions.code_formation', $code_formation)
                ->orderBy('auditeurs.nom', 'ASC')
                ->get();
        } else {
            $liste_participants = DB::table('rapports')
                ->join('inscriptions', 'inscriptions.email', '=', 'rapports.email')
                ->leftjoin('auditeurs', 'inscriptions.email', '=', 'auditeurs.email')
                ->join('formations', 'formations.code_formation', '=', 'rapports.code_formation')
                ->select('rapports.*', 'auditeurs.nom as nom', 'auditeurs.prenoms as prenoms', 'auditeurs.pays as pays', 'formations.theme', 'formations.date_debut', 'formations.date_fin', 'formations.code_formation', 'formations.lieu', 'inscriptions.presentiel')
                ->where('rapports.id', $rapport_id)
                ->where('rapports.code_formation', $code_formation)
                ->where('inscriptions.code_formation', $code_formation)
                ->where('rapports.temps_presence', 0)
                ->orderBy('auditeurs.nom', 'ASC')
                ->get();
        }


        //duree
        $duree_formation = Dureeformations::all()->where('code_formation', $code_formation)->sortByDesc("id");
        $temps_latence = 90;
        $duree_session = DB::table('sessionformations')
            ->select(DB::raw('sum(duree_reel - ' . $temps_latence . ') as duree_session'))
            ->where('code_formation', $code_formation)
            ->get();

        // formation
        $formation = Formations::where("code_formation", $code_formation)->first();
        $logo_partenaires = Logopartenaires::where('code_formation', $code_formation)->get();
        //dd($formation->typeformation_id);
        if ($formation->typeformation_id != null) {
            # code...
            $typeformation_id = $formation->typeformation_id;
        } else {
            # code...
            $typeformation_id = 1;
        }


        // share data to view
        view()->share(['liste_participants' => $liste_participants, 'date' => $date_jour, 'code_formation' => $code_formation, 'typeformation_id' => $typeformation_id, 'duree_session' => $duree_session, 'durees' => $duree_formation, 'logo_partenaires' => $logo_partenaires, 'formation' => $formation]);
        // service permettant de retourner la vue sur condition
        $pdf = $this->genarateurCodeService->getBladeAttestationParticipant($formation->is_event, $formation);

        $password = substr(md5($code_formation . '' . $rapport->email), 0, 8);
        $pdf->setPaper('A4', 'landscape');
        $pdf->setEncryption($password);
        $date = date('d_m_Y_His');
        $auditeur = Auditeurs::whereEmail($rapport->email)->first();
        $participant_name = $auditeur->nom . ' ' . $auditeur->prenoms;
        $fichier_name = Str::slug($participant_name);
        $file_name = $formation->code_formation . '_' . $fichier_name . '_' . $date;

        //$file_name = md5($file);
        // download PDF file with download method
        return $pdf->download($file_name . '.pdf');
    }

    public function downloadListeParticipantVisio($code_formation)
    {
        // TODO: Implement downloadListeParticipantVisio() method.

        set_time_limit(0);
        // récupérer la liste des rapports dont le code de formation est passé en url
        $liste_participants = DB::table('rapports')
            ->join('inscriptions', 'inscriptions.email', '=', 'rapports.email')
            ->leftjoin('auditeurs', 'auditeurs.email', '=', 'inscriptions.email')
            ->select('rapports.*', 'auditeurs.nom as nom', 'auditeurs.prenoms as prenoms', 'auditeurs.pays as pays')
            ->where('rapports.code_formation', $code_formation)
            ->where('inscriptions.code_formation', $code_formation)
            ->orderBy('auditeurs.nom', 'ASC')
            ->get();
        $duree_formation = Dureeformations::all()->where('code_formation', $code_formation)->sortByDesc("id");
        //variable temps de latence
        $temps_latence = 90; // par jour de formation ||
        // récupérer le nbre de jour de formation pour la formation en cours
        // $nbre_session_formation = Sessionformation::where('code_formation',$code_formation)->count();
        //$total_temps_latence = $temps_latence * $nbre_session_formation;

        $duree_session = DB::table('sessionformations')
            ->select(DB::raw('sum(duree_reel - ' . $temps_latence . ') as duree_session'))
            ->where('code_formation', $code_formation)
            ->get();

        $date = date('d-m-YH:i:s');
        // share data to view
        view()->share(['liste_participants' => $liste_participants, 'code_formation' => $code_formation, 'durees' => $duree_formation, 'duree_session' => $duree_session]);
        // ############ personalisation de l'exportation des données ######################
        // ########### end personalisationd de l'exportation des données ##################
        $pdf = PDF::loadView('documents.liste_participant_attestation_pdf');
        $pdf->setPaper('A4', 'landscape');

        //  $pdf->output() 

        // 
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $footer = $canvas->open_object();
        $w = $canvas->get_width();
        $h = $canvas->get_height();
        $canvas->close_object();
        $canvas->add_object($footer, "all");

        return $pdf->download($code_formation . '_RAPPORT_PARTICIPANT_' . $date . '.pdf');
    }

    public function downloadListeParticipantPresentiel($code_formation)
    {
        // TODO: Implement downloadListeParticipantPresentiel() method.

        set_time_limit(0);
        // récupérer la liste des rapports dont le code de formation est passé en url
        $liste_participants = DB::table('rapports')
            ->join('inscriptions', 'inscriptions.email', '=', 'rapports.email')
            ->leftjoin('auditeurs', 'inscriptions.email', '=', 'auditeurs.email')
            ->select('auditeurs.email', 'auditeurs.nom as nom', 'auditeurs.prenoms as prenoms', 'auditeurs.pays as pays')
            ->where('rapports.code_formation', $code_formation)
            ->where('inscriptions.code_formation', $code_formation)
            ->where('rapports.temps_presence', 0)
            ->orderBy('auditeurs.nom', 'ASC')
            ->get();
        $nbre_participant = count($liste_participants);
        // récupérer l'objet de la formation
        $formation =  Formations::where('code_formation', $code_formation)->get()->first();

        $date = date('d-m-YH:i:s');
        // share data to view
        view()->share(['liste_participants' => $liste_participants, 'code_formation' => $code_formation, 'formation' => $formation, 'nbre_participant' => $nbre_participant]);
        // ############ personalisation de l'exportation des données ######################

        // ########### end personalisation de l'exportation des données ##################

        $pdf = PDF::loadView('documents.liste_participant_presentiel_attestation_pdf');
        $pdf->setPaper('A4', 'landscape');

        //$pdf->output()

        // Optionnel : Ajoute un pied de page
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $footer = $canvas->open_object();
        $w = $canvas->get_width();
        $h = $canvas->get_height();
        $canvas->close_object();
        $canvas->add_object($footer, "all");

        return $pdf->download($code_formation . '_LISTE_PRESENTIELLE_' . $date . '.pdf');
    }

    public function getUrlListeParticipantPresentiel($code_formation)
    {
        // TODO: Implement getUrlListeParticipantPresentiel() method.
        $url = $this->genarateurCodeService->generateUrlSigned('liste.participant.presentiel', ['code_formation' => $code_formation]);
        $data = ['url' => $url];
        return  ResponseHandler::success("ok", $data, 200);
    }

    public function getUrlListeParticipantVisio($code_formation)
    {
        // TODO: Implement getUrlListeParticipantVisio() method.
        $url = $this->genarateurCodeService->generateUrlSigned('liste.participant.visio', ['code_formation' => $code_formation]);
        $data = ['url' => $url];
        return  ResponseHandler::success("ok", $data, 200);
    }

    public function getUrlAllAttestationParticipant($code_formation)
    {
        // TODO: Implement getUrlAllAttestationParticipant() method.
        $url = $this->genarateurCodeService->generateUrlSigned('export.all.attestattion.participant', ['code_formation' => $code_formation]);
        $data = ['url' => $url];
        return  ResponseHandler::success("ok", $data, 200);
    }

    public function getUrlAllAttestationPresentielParticipant($code_formation)
    {
        $url = $this->genarateurCodeService->generateUrlSigned('export.all.attestation.presentiel.participant', ['code_formation' => $code_formation]);
        $data = ['url' => $url];
        return  ResponseHandler::success("ok", $data, 200);
    }

    public function downloadAllAttestationParticipant($code_formation)
    {
        // TODO: Implement downloadAllAttestationParticipant() method.

        set_time_limit(0);
        $fichier = "";
        // ******************************************* //

        $date = date('d-m-YH:i:s');
        $date_jour = date('d-m-Y');
        $date_jour = Carbon::now()->locale('fr_FR');
        $date_jour = $date_jour->isoFormat('DD MMM YYYY');
        $liste_participants = DB::table('rapports')
            ->join('inscriptions', 'inscriptions.email', '=', 'rapports.email')
            ->leftjoin('auditeurs', 'inscriptions.email', '=', 'auditeurs.email')
            ->join('formations', 'formations.code_formation', '=', 'rapports.code_formation')
            ->select('rapports.*', 'auditeurs.nom as nom', 'auditeurs.prenoms as prenoms', 'auditeurs.pays as pays', 'formations.lieu', 'formations.theme', 'formations.date_debut', 'formations.date_fin', 'formations.code_formation', 'inscriptions.presentiel')
            ->where('rapports.code_formation', $code_formation)
            ->where('inscriptions.code_formation', $code_formation)
            ->where('rapports.temps_presence', '>', 0)
            ->orderBy('auditeurs.nom', 'ASC')
            ->get();

        $duree_formation = Dureeformations::all()->where('code_formation', $code_formation)->sortByDesc("id");

        $temps_latence = 90;
        $duree_session = DB::table('sessionformations')
            ->select(DB::raw('sum(duree_reel - ' . $temps_latence . ') as duree_session'))
            ->where('code_formation', $code_formation)
            ->get();

        // formation
        $formation = Formations::where("code_formation", $code_formation)->first();
        $logo_partenaires = Logopartenaires::where('code_formation', $code_formation)->get();
        //dd($formation->typeformation_id);
        if ($formation->typeformation_id != null) {
            # code...
            $typeformation_id = $formation->typeformation_id;
        } else {
            # code...
            $typeformation_id = 1;
        }


        // share data to view
        view()->share(['liste_participants' => $liste_participants, 'date' => $date_jour, 'code_formation' => $code_formation, 'durees' => $duree_formation, 'duree_session' => $duree_session, 'typeformation_id' => $typeformation_id, 'logo_partenaires' => $logo_partenaires, 'formation' => $formation]);

        // service permettant de retourner la vue sur condition
        $pdf = $this->genarateurCodeService->getBladeAttestationParticipant($formation->is_event, $formation);

        $pdf->setPaper('A4', 'landscape');


        // download PDF file with download method
        return $pdf->download($code_formation . '_ATTESTATION_' . $date . '.pdf');
    }

    public function downloadAllAttestationPresentielParticipant($code_formation)
    {
        // TODO: Implement downloadAllAttestationParticipant() method.

        set_time_limit(0);
        $fichier = "";
        // ******************************************* //

        $date = date('d-m-YH:i:s');
        $date_jour = date('d-m-Y');
        $date_jour = Carbon::now()->locale('fr_FR');
        $date_jour = $date_jour->isoFormat('DD MMM YYYY');
        $liste_participants = DB::table('rapports')
            ->join('inscriptions', 'inscriptions.email', '=', 'rapports.email')
            ->leftjoin('auditeurs', 'inscriptions.email', '=', 'auditeurs.email')
            ->join('formations', 'formations.code_formation', '=', 'rapports.code_formation')
            ->select('rapports.*', 'auditeurs.nom as nom', 'auditeurs.prenoms as prenoms', 'auditeurs.pays as pays', 'formations.lieu', 'formations.theme', 'formations.date_debut', 'formations.date_fin', 'formations.code_formation', 'inscriptions.presentiel')
            ->where('rapports.code_formation', $code_formation)
            ->where('inscriptions.code_formation', $code_formation)
            ->where('rapports.temps_presence', 0)
            ->orderBy('auditeurs.nom', 'ASC')
            ->get();

        $duree_formation = Dureeformations::all()->where('code_formation', $code_formation)->sortByDesc("id");

        $temps_latence = 90;
        $duree_session = DB::table('sessionformations')
            ->select(DB::raw('sum(duree_reel - ' . $temps_latence . ') as duree_session'))
            ->where('code_formation', $code_formation)
            ->get();

        // formation
        $formation = Formations::where("code_formation", $code_formation)->first();
        $logo_partenaires = Logopartenaires::where('code_formation', $code_formation)->get();
        //dd($formation->typeformation_id);
        if ($formation->typeformation_id != null) {
            # code...
            $typeformation_id = $formation->typeformation_id;
        } else {
            # code...
            $typeformation_id = 1;
        }


        // share data to view
        view()->share(['liste_participants' => $liste_participants, 'date' => $date_jour, 'code_formation' => $code_formation, 'durees' => $duree_formation, 'duree_session' => $duree_session, 'typeformation_id' => $typeformation_id, 'logo_partenaires' => $logo_partenaires, 'formation' => $formation]);

        // service permettant de retourner la vue sur condition
        $pdf = $this->genarateurCodeService->getBladeAttestationParticipant($formation->is_event, $formation);

        $pdf->setPaper('A4', 'landscape');


        // download PDF file with download method
        return $pdf->download($code_formation . '_ATTESTATION_' . $date . '.pdf');
    }

    public function sendAllMailAttestationParticipant(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement sendAllMailAttestationParticipant() method.

        $listeAuditeurs = json_decode($request->listeAuditeurs, true);
        //Log::info($listeAuditeurs);
        if (!empty($listeAuditeurs)) {
            foreach ($listeAuditeurs as $key => $id) {
                $rapport = Rapports::find($id);
                if ($rapport) {

                    $email = $rapport->email;

                    $code_formation = $rapport->code_formation;

                    $inscrit = Inscriptions::select('inscriptions.*')
                        ->where('email', $email)
                        ->where('code_formation', $code_formation)
                        ->first();
                    $auditeur = Auditeurs::where("email", $inscrit->email)->first();

                    $formation = Formations::select('formations.*')
                        ->where('code_formation', $code_formation)
                        ->first();


                    if ($inscrit->presentiel == "1") {

                        $signedUrl = $this->genarateurCodeService->generateUrlSigned('telecharger.attestation_participant', ['rapport_id' => $rapport->id]);

                        // enregistrer les infos dans une table pour passer la views
                        $contenu = [
                            'nom_prenoms' => $auditeur->prenoms . ' ' . $auditeur->nom,
                            'email' => $inscrit->email,
                            //'fichier_name' => $fichier_name,
                            'code_formation' => $code_formation,
                            'signedUrl' => $signedUrl,
                            'theme' => $formation->theme,
                            'password' => substr(md5($code_formation . '' . $inscrit->email), 0, 8),
                            'subject' => 'ATTESTATION ' . $code_formation . ' ' . $auditeur->nom,
                            'visio' => 'non'
                        ];
                        // send mail

                        Mail::to(trim($inscrit->email))->cc([env('MAIL_CC')])->bcc(env('MAIL_BCC'))->queue(new SendAttestation($contenu));
                        // update column attestattion_send in rapport table
                        $up_rapport = Rapports::find($rapport->id);
                        $up_rapport->setAttribute('attestation_send', true);
                        $up_rapport->update();
                    } else {


                        //$fichier_name = Crypt::encrypt($rapport->id);
                        $signedUrl = $this->genarateurCodeService->generateUrlSigned('telecharger.attestation_participant', ['rapport_id' => $rapport->id]);

                        // enregistrer les infos dans une table pour passer la views
                        $contenu = [
                            'nom_prenoms' => $auditeur->prenoms . ' ' . $auditeur->nom,
                            'email' => $inscrit->email,
                            //'fichier_name' => $fichier_name,
                            'signedUrl' => $signedUrl,
                            'code_formation' => $code_formation,
                            'theme' => $formation->theme,
                            'password' => substr(md5($code_formation . '' . $inscrit->email), 0, 8),
                            'subject' => 'ATTESTATION ' . $code_formation . ' ' . $auditeur->nom,
                            'visio' => 'oui'
                        ];
                        // send mail

                        Mail::to(trim($inscrit->email))->cc([env('MAIL_CC')])->bcc(env('MAIL_BCC'))->queue(new SendAttestation($contenu));
                        $rapport->setAttribute('attestation_send', true);
                        $rapport->update();
                    }
                }
            }
        }
        return  ResponseHandler::success('E-mail envoyé avec succès', ['success' => true, 'code_formation' => $request->code_formation]);
    }

    public function getListeAttestationFormateur(Request $request, string $code_formation)
    {
        // TODO: Implement getListeAttestationFormateur() method.


        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'nom');
        $sortOrder = $request->input('sort_order', 'asc');

        $nbreFormateur =  DB::table('formationsformateurs')
            ->join('formations', 'formations.id', '=', 'formationsformateurs.formation_id')
            ->join('formateurs', 'formateurs.id', '=', 'formationsformateurs.formateur_id')
            ->where('formations.code_formation', $code_formation)
            ->count();

        // récupérer la liste des panélistes
        $query =  DB::table('formationsformateurs')
            ->join('formations', 'formations.id', '=', 'formationsformateurs.formation_id')
            ->join('formateurs', 'formateurs.id', '=', 'formationsformateurs.formateur_id')
            ->leftjoin('pays', 'pays.id', '=', 'formateurs.nationalite')
            ->leftjoin('roleformateurs', 'formationsformateurs.roleformateur_id', '=', 'roleformateurs.id')
            ->select(
                'formationsformateurs.id',
                'formateurs.nom',
                'formateurs.prenoms',
                'formateurs.telephone',
                'formateurs.email',
                'formationsformateurs.role',
                'pays.nationalite',
                'roleformateurs.libelle as role_formateur'
            )
            ->where('formations.code_formation', $code_formation);

        // Recherche globale sur plusieurs champs
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('formateurs.nom', 'like', "%{$search}%")
                    ->orWhere('formateurs.prenoms', 'like', "%{$search}%")
                    ->orWhere('formateurs.telephone', 'like', "%{$search}%")
                    ->orWhere('formationsformateurs.role', 'like', "%{$search}%")
                    ->orWhere('roleformateurs.libelle', 'like', "%{$search}%")
                    ->orWhere('pays.nationalite', 'like', "%{$search}%"); // Recherche sur auteur
            });
        }
        switch ($sortField) {
            case 'nom':
                # code...
                $query->orderBy("formateurs.{$sortField}", $sortOrder);
                break;
            case 'prenoms':
                # code...
                $query->orderBy("formateurs.{$sortField}", $sortOrder);
                break;
            case 'telephone':
                # code...
                $query->orderBy("formateurs.{$sortField}", $sortOrder);
                break;
            case 'role':
                # code...
                $query->orderBy("formationsformateurs.{$sortField}", $sortOrder);
                break;
            case 'role_formateur':
                # code...
                $query->orderBy("roleformateurs.libelle", $sortOrder);
                break;
            case 'nationalite':
                # code...
                $query->orderBy("pays.{$sortField}", $sortOrder);
                break;

            default:
                # code...
                $query->orderBy("formateurs.{$sortField}", $sortOrder);
                break;
        }

        $data = $query->paginate($perPage);

        return [
            'pagination' => $data,
            'nbreFormateur' => $nbreFormateur
        ];
    }

    public function downloadAttestationFormateur($id)
    {
        // TODO: Implement downloadAttestationFormateur() method.

        $formateur = Formationsformateurs::where('id', $id)->get();

        $getFormateur = Formationsformateurs::find($id);
        //
        $date = date('d-m-YH:i:s');
        $date_jour = date('d-m-Y');
        $date_jour = Carbon::now()->locale('fr_FR');
        $date_jour = $date_jour->isoFormat('DD MMM YYYY');
        $formation = Formations::find($getFormateur->formation_id);
        $logo_partenaires = Logopartenaires::where('code_formation', $formation->code_formation)->get();
        // share data to view
        view()->share(['formateur' => $formateur, 'date' => $date_jour, 'logo_partenaires' => $logo_partenaires]);

        // service permettant de retourner la vue sur condition
        $pdf = $this->genarateurCodeService->getBladeAttestationFormateur($formation->is_event, $formation->typeformation_id);

        $password = substr(md5($getFormateur->getFormation->code_formation . '' . $getFormateur->getFormateur->email), 0, 8);
        $pdf->setPaper('A4', 'landscape');
        $pdf->setEncryption($password);
        $date = date('d_m_Y_His');
        $formateur_name = $getFormateur->getFormateur->nom . ' ' . $getFormateur->getFormateur->prenoms;
        $fichier_name = Str::slug($formateur_name);
        $file_name = $formation->code_formation . '_' . $fichier_name . '_' . $date;
        //$file_name = md5($file);
        // download PDF file with download method
        return $pdf->download($file_name . '.pdf');
    }

    public function getUrlAttestationFormateur($id)
    {
        // TODO: Implement getUrlAttestationFormateur() method.
        $url = $this->genarateurCodeService->generateUrlSigned('telecharger.attestation_formateur', ['id' => $id]);
        $data = ['url' => $url];
        return  ResponseHandler::success("ok", $data, 200);
    }

    public function downloadConsultationAttestationFormateur($id)
    {
        // TODO: Implement downloadConsultationAttestationFormateur() method.

        $formateur = Formationsformateurs::where('id', $id)->get();
        $getOneformateur = Formationsformateurs::find($id);
        $nom_formateur = Str::slug($getOneformateur->getFormateur->nom . '_' . $getOneformateur->getFormateur->prenoms);

        $date_pdf = date('Y-m-d');
        $date_jour = Carbon::now()->locale('fr_FR');
        $date_jour = $date_jour->isoFormat('DD MMM YYYY');
        $formation = Formations::find($getOneformateur->formation_id);
        $logo_partenaires = Logopartenaires::where('code_formation', $formation->code_formation)->get();
        // share data to view
        view()->share(['formateur' => $formateur, 'date' => $date_jour, 'logo_partenaires' => $logo_partenaires]);

        // service permettant de retourner la vue sur condition
        $pdf = $this->genarateurCodeService->getBladeAttestationFormateur($formation->is_event, $formation->typeformation_id);
        $pdf->setPaper('A4', 'landscape');

        // download PDF file with download method
        return $pdf->download($formation->code_formation . '_ATTESTATION_FORMATEUR_' . $nom_formateur . '_' . $date_pdf . '.pdf');
    }

    public function getUrlConsultationAttestationFormateur($id)
    {
        // TODO: Implement getUrlConsultationAttestationFormateur() method.
        $url = $this->genarateurCodeService->generateUrlSigned('consultation.attestation_formateur', ['id' => $id]);
        $data = ['url' => $url];
        return  ResponseHandler::success("ok", $data, 200);
    }

    public function sendMailAttestationFormateur($id): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement sendMailAttestationFormateur() method.

        $formateur = Formationsformateurs::find($id);

        // récupérer les information du participant
        $date_jour = Carbon::now()->locale('fr_FR');
        $date_jour = $date_jour->isoFormat('DD MMM YYYY');

        $signedUrl = $this->genarateurCodeService->generateUrlSigned('telecharger.attestation_formateur', ['id' => $id]);

        // enregistrer les infos dans une table pour passer la views
        $contenu = [
            'nom_prenoms' => $formateur->getFormateur->prenoms . ' ' . $formateur->getFormateur->nom,
            'email' => $formateur->getFormateur->email,
            'signedUrl' => $signedUrl,
            'code_formation' => $formateur->getFormation->code_formation,
            'theme' => $formateur->getFormation->theme,
            'password' => substr(md5($formateur->getFormation->code_formation . '' . $formateur->getFormateur->email), 0, 8),
            'subject' => 'ATTESTATION ' . $formateur->getFormation->code_formation . ' ' . $formateur->getFormateur->nom
        ];
        // send mail
        try {
            # code...
            Mail::to(trim($formateur->getFormateur->email))->cc([env('MAIL_CC')])->bcc(env('MAIL_BCC'))->queue(new SendAttestationFormateur($contenu));

            return ResponseHandler::success('Mail envoyé avec succès', ['success' => true, 'code_formation' => $formateur->getFormation->code_formation]);
        } catch (\Exception $e) {
            // Get error here
            Log::info('echec de l\'envoi du mail');
            return ResponseHandler::success('Mail non envoyé ', ['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function sendAllMailAttestationFormateur(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement sendAllMailAttestationFormateur() method.
        $listeFormateurs = json_decode($request->listeFormateurs, true);
        //Log::info($listeFormateurs);
        if (!empty($listeFormateurs)) {
            foreach ($listeFormateurs as $key => $id) {
                $formateurs = Formationsformateurs::join("formations", "formationsformateurs.formation_id", "=", "formations.id")
                    ->join("formateurs", "formationsformateurs.formateur_id", "=", "formateurs.id")
                    ->where("formationsformateurs.id", $id)
                    ->get(["formateurs.email", "formateurs.nom", "formateurs.prenoms", "formations.code_formation", "formations.theme", "formationsformateurs.id"])
                    ->first();
                // récupérer les information du participant
                if ($formateurs) {
                    # code...
                    $date = date('d-m-YH:i:s');
                    $date_jour = date('d-m-Y');
                    $date_jour = Carbon::now()->locale('fr_FR');
                    $date_jour = $date_jour->isoFormat('DD MMM YYYY');

                    $signedUrl = $this->genarateurCodeService->generateUrlSigned('telecharger.attestation_formateur', ['id' => $formateurs->id]);

                    // enregistrer les infos dans une table pour passer la views
                    $contenu = [
                        'nom_prenoms' => $formateurs->prenoms . ' ' . $formateurs->nom,
                        'email' => $formateurs->email,
                        //'fichier_name' => $fichier_name,
                        'signedUrl' => $signedUrl,
                        'code_formation' => $formateurs->code_formation,
                        'theme' => $formateurs->theme,
                        'password' => substr(md5($formateurs->code_formation . '' . $formateurs->email), 0, 8),
                        'subject' => 'ATTESTATION ' . $formateurs->code_formation . ' ' . $formateurs->nom
                    ];
                    // send mail
                    try {
                        # code...
                        Mail::to(trim($formateurs->email))->cc([env('MAIL_CC')])->bcc(env('MAIL_BCC'))->queue(new SendAttestationFormateur($contenu));
                    } catch (\Exception $e) {
                        // Get error here
                        Log::info('echec de l\'envoi du mail');
                        return ResponseHandler::success('Mail non envoyé ', ['success' => false, 'error' => $e->getMessage()]);
                    }
                }
            }
            return ResponseHandler::success('Mail envoyé avec succès', ['success' => true, 'code_formation' => $request->code_formation]);
        }
    }

    public function downloadAllAttestationFormateur($code_formation)
    {
        // TODO: Implement downloadAllAttestationFormateur() method.

        set_time_limit(0);
        $fichier = "";
        // ******************************************* //
        $formation = Formations::where('code_formation', $code_formation)->first();
        $formateurs = Formationsformateurs::where('formation_id', $formation->id)->get();
        $logo_partenaires = Logopartenaires::where('code_formation', $formation->code_formation)->get();

        $date = date('d-m-YH:i:s');
        $date_jour = date('d-m-Y');
        $date_pdf = date('Y-m-d');
        $date_jour = Carbon::now()->locale('fr_FR');
        $date_jour = $date_jour->isoFormat('DD MMM YYYY');
        // share data to view
        view()->share(['formateur' => $formateurs, 'date' => $date_jour, 'logo_partenaires' => $logo_partenaires]);

        // service permettant de retourner la vue sur condition
        $pdf = $this->genarateurCodeService->getBladeAttestationFormateur($formation->is_event, $formation->typeformation_id);
        // $pdf = PDF::loadView('attestations.pdf_attestation_by_formateur');
        $pdf->setPaper('A4', 'landscape');
        // download PDF file with download method
        return $pdf->download($formation->code_formation . '_ATTESTATION_FORMATEURS_' . $date_pdf . '.pdf');
    }

    public function getUrlAllAttestationFormateur($code_formation)
    {
        // TODO: Implement getUrlAllAttestationFormateur() method.
        $url = $this->genarateurCodeService->generateUrlSigned('export.all.attestattion.formateur', ['code_formation' => $code_formation]);
        $data = ['url' => $url];
        return  ResponseHandler::success("ok", $data, 200);
    }

    public function downloadListeFormateur($code_formation)
    {
        // TODO: Implement downloadListeFormateur() method.
        set_time_limit(0);
        // récupérer la liste des formateurs
        $liste_formateurs =  DB::table('formationsformateurs')
            ->join('formations', 'formations.id', '=', 'formationsformateurs.formation_id')
            ->join('formateurs', 'formateurs.id', '=', 'formationsformateurs.formateur_id')
            ->leftjoin('pays', 'pays.id', '=', 'formateurs.nationalite')
            ->select('formateurs.id as id_formateur', 'formateurs.nom', 'formateurs.prenoms', 'formateurs.telephone', 'formateurs.email', 'pays.nationalite')
            ->where('formations.code_formation', $code_formation)
            //->where('inscriptions.code_formation',$code_formation)
            ->orderBy('formateurs.nom', 'ASC')
            ->get();
        $nbre_formateur = count($liste_formateurs);
        // récupérer l'objet de la formation
        $formation =  Formations::where('code_formation', $code_formation)->get()->first();

        $date = date('d-m-YH:i:s');
        // share data to view
        view()->share(['liste_formateurs' => $liste_formateurs, 'code_formation' => $code_formation, 'formation' => $formation, 'nbre_formateur' => $nbre_formateur]);

        $pdf = PDF::loadView('attestations.liste_formateur_pdf');
        $pdf->output();
        $pdf->setPaper('A4', 'landscape');
        // ##### a considerer pour pagination ###############
        // $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $footer = $canvas->open_object();
        $w = $canvas->get_width();
        $h = $canvas->get_height();
        $canvas->close_object();
        $canvas->add_object($footer, "all");

        return $pdf->download($code_formation . '_LISTE_FORMATEUR_' . $date . '.pdf');
    }

    public function getUrlListeFormateur($code_formation)
    {
        // TODO: Implement getUrlListeFormateur() method.
        $url = $this->genarateurCodeService->generateUrlSigned('liste.formateurs', ['code_formation' => $code_formation]);
        $data = ['url' => $url];
        return  ResponseHandler::success("ok", $data, 200);
    }

    public function downloadProformaOuvrage(string $reference)
    {
        // TODO: Implement downloadProformaOuvrage() method.

        $paiement = Paiements::where('reference', $reference)->get()->first();
        $auditeur = Auditeurs::where('email', $paiement->email)->first();
        $details_paiement = Detailpaiementouvrages::where("paiementouvrage_id", $paiement->id)->get();
        // Detailspaiement::where('reference_paiement','ERS-2021-FACT-000001');
        // return view('public.recu',compact("paiement","details_paiement"));
        $formatter = new NumberFormatter('fr', NumberFormatter::SPELLOUT);
        $formatter->setTextAttribute(NumberFormatter::DEFAULT_RULESET, "%spellout-ordinal");

        // share data to view
        view()->share(['formatter' => $formatter, 'paiement' => $paiement, 'auditeur' => $auditeur, 'details_paiement' => $details_paiement]);
        $pdf = PDF::loadView('documents.proforma_ouvrage');
        $customPaper = array(0, 0, 720, 1440);
        $pdf->setPaper($customPaper, 'landscape');

        $date = date('d_m_Y_His');
        // download PDF file with download method
        return $pdf->download('proforma_' . $reference . '_' . $date . '.pdf');
    }

    public function getUrlProformaOuvrageSigned(string $reference): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement
        $url = $this->genarateurCodeService->generateUrlSigned('proforma-ouvrage', ['reference' => $reference]);
        $data = ['url' => $url];
        return  ResponseHandler::success("ok", $data, 200);
    }

    public function downloadRecuPaiementOuvrage(string $reference)
    {
        // TODO: Implement downloadRecuPaiementOuvrage() method.
        $paiement = Paiements::where('reference', $reference)->get()->first();
        $auditeur = Auditeurs::where('email', $paiement->email)->first();
        $details_paiement = Detailpaiementouvrages::where("paiementouvrage_id", $paiement->id)->get();
        // Detailspaiement::where('reference_paiement','ERS-2021-FACT-000001');
        // return view('public.recu',compact("paiement","details_paiement"));
        $formatter = new NumberFormatter('fr', NumberFormatter::SPELLOUT);
        $formatter->setTextAttribute(NumberFormatter::DEFAULT_RULESET, "%spellout-ordinal");

        // share data to view
        view()->share(['formatter' => $formatter, 'paiement' => $paiement, 'auditeur' => $auditeur, 'details_paiement' => $details_paiement]);
        $pdf = PDF::loadView('documents.recu_paiement_ouvrage');
        $customPaper = array(0, 0, 720, 1440);
        $pdf->setPaper($customPaper, 'landscape');

        $date = date('d_m_Y_His');
        // download PDF file with download method
        return $pdf->download('recu_' . $reference . '_' . $date . '.pdf');
    }

    public function getListeRapports(Request $request, string $code_formation) //: LengthAwarePaginator
    {
        // ##################### calcul scoring ###########################################
        $getDuree = 0;
        $check_duree_formation = Dureeformations::where("code_formation", $code_formation)->first();

        if ($check_duree_formation) {
            $nbr_duree_enregistrement = $check_duree_formation->duree_enregistrement;
        } else {
            # code... create durée
            $nbr_duree_enregistrement = 0;
        }
        //variable temps de latence
        $temps_latence = 90; // par jour de formation ||
        // récupérer le nbre de jour de formation pour la formation en cours
        //$nbre_session_formation = Sessionformation::where('code_formation',$code_formation)->count();
        //$total_temps_latence = $temps_latence * $nbre_session_formation;

        $duree_formation = DB::table('sessionformations')
            ->select(DB::raw('sum(duree_reel - ' . $temps_latence . ') as duree_session'))
            ->where('code_formation', $code_formation)
            ->get();

        if ($nbr_duree_enregistrement > 0) {
            $getDuree = $nbr_duree_enregistrement;
        } else {
            $getDuree = $duree_formation[0]->duree_session;
        }

        $scoring = intval(($getDuree * 2) / 3);
        //####################### end calcul scoring ####################################################


        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        $query = DB::table('rapports')
            ->join('inscriptions', 'inscriptions.email', '=', 'rapports.email')
            ->leftJoin('auditeurs', 'inscriptions.email', '=', 'auditeurs.email')
            ->select(
                'rapports.id',
                'rapports.email',
                'rapports.attestation_send',
                'rapports.code_formation',
                'rapports.temps_presence',
                'inscriptions.id as id_inscrit',
                'auditeurs.nom',
                'auditeurs.prenoms',
                'auditeurs.pays',
                'auditeurs.civilite',
                'auditeurs.genre'
            )
            ->where('rapports.code_formation', $code_formation)
            ->where('inscriptions.code_formation', $code_formation);
        /*->where(function ($q) use ($scoring) {
                $q->where('rapports.temps_presence', 0)
                    ->orWhere('rapports.temps_presence', '>=', $scoring);
            });*/
        // Recherche globale sur plusieurs champs
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('auditeurs.nom', 'like', "%{$search}%")
                    ->orWhere('auditeurs.prenoms', 'like', "%{$search}%")
                    ->orWhere('rapports.email', 'like', "%{$search}%")
                    ->orWhere('rapports.temps_presence', 'like', "%{$search}%")
                    ->orWhere('auditeurs.pays', 'like', "%{$search}%"); // Recherche sur auteur
            });
        }
        $query->orderBy("rapports.{$sortField}", $sortOrder);

        $data = $query->paginate($perPage);

        return [
            'pagination' => $data,
            'nbr_duree_enregistrement' => $nbr_duree_enregistrement,
            'duree_formation' => $duree_formation
        ];
    }

    public function downloadAttestationMoodleCandidat($id, $code_formation)
    {
        // TODO: Implement downloadAttestationMoodleCandidat() method.

        set_time_limit(0);
        // récupérer la liste des rapports dont le code de formation est passé en url
    $candidat = Tmpmoodlecandidats::find($id);

    $participant = DB::table('moodlemoyennegenerales') 
        ->join('tmpmoodlecandidats', 'tmpmoodlecandidats.email', '=', 'moodlemoyennegenerales.reference_candidat') 
        ->select('moodlemoyennegenerales.annee_academique', 'moodlemoyennegenerales.code_formation', 'moodlemoyennegenerales.moyenne', 'tmpmoodlecandidats.prenoms', 'tmpmoodlecandidats.nom','tmpmoodlecandidats.email','tmpmoodlecandidats.ddn','tmpmoodlecandidats.lieu_naissance')
        ->where('moodlemoyennegenerales.code_formation', $code_formation) 
        ->where('moodlemoyennegenerales.reference_candidat', $candidat->email)
        ->orderBy('moodlemoyennegenerales.moyenne', 'DESC')
        ->first(); 
 
    // récupérer l'objet de la formation
    $formation =  Formations::where('code_formation', $code_formation)->get()->first();
    $date = date('d-m-YH:i:s');
    // share data to view
    //view()->share(['participant' => $participant, 'code_formation' => $code_formation, 'formation' => $formation]);
 
    //$pdf = PDF::loadView('attestations.attestation_dsc', $participant);
    $pdf = PDF::loadView('attestations.attestation_dsc', [
    'participant' => $participant,
    'code_formation' => $code_formation,
    'formation' => $formation,
]);
    $pdf->output();
    $pdf->setPaper('A4', 'landscape');
    // ##### a considerer pour pagination ###############
    // $pdf->output();
    $dom_pdf = $pdf->getDomPDF();
    $canvas = $dom_pdf->get_canvas();
    $footer = $canvas->open_object();
    $w = $canvas->get_width();
    $h = $canvas->get_height();
    //     $canvas->page_text($w-60,$h-28, "Page {PAGE_NUM} sur {PAGE_COUNT}", null, 10, array(0, 0, 0));
    $canvas->close_object();
    $canvas->add_object($footer, "all");
    //  $canvas->get_cpdf()->setEncryption('frogs', '', array('print'));
    // ##### end a considerer pour pagination ###############
    // download PDF file with download method

    return $pdf->download($code_formation . '_ATTESTATION_DSC_' . $date . '.pdf');

    }
 
    public function getUrlAttestationMoodleCandidat($id, $code_formation)
    {
        // TODO: Implement getUrlAttestationMoodleCandidat() method.
        $url = $this->genarateurCodeService->generateUrlSigned('attestationCandidat.moodle', ['id' => $id, 'code_formation' => $code_formation]);
        $data = ['url' => $url];
        return  ResponseHandler::success("ok", $data, 200);
    }

    

    public function downloadReleveNoteCandidat($id, $code_formation)
    {
        // TODO: Implement downloadReleveNoteCandidat() method.

        set_time_limit(0);
       $candidat = Tmpmoodlecandidats::find($id);
    
    // récupérer la liste des notes
     $notes = DB::table('moodlemoyennes') 
        ->join('moodlemodules', 'moodlemodules.id', '=', 'moodlemoyennes.moodlemodule_id') 
        ->select('moodlemoyennes.note_totale as note', 'moodlemoyennes.note_coef as note_credit', 'moodlemodules.credit', 'moodlemoyennes.reference_candidat','moodlemodules.code','moodlemodules.libelle')
        ->where('moodlemoyennes.code_formation', $code_formation) 
        ->where('moodlemoyennes.reference_candidat', $candidat->email)
        ->orderBy('moodlemoyennes.note_coef', 'DESC')
        ->get(); 

    // 
    $moyenne_generale = DB::table('moodlemoyennegenerales') 
        ->join('tmpmoodlecandidats', 'tmpmoodlecandidats.email', '=', 'moodlemoyennegenerales.reference_candidat') 
        ->select('moodlemoyennegenerales.annee_academique', 'moodlemoyennegenerales.code_formation', 'moodlemoyennegenerales.moyenne', 'tmpmoodlecandidats.prenoms', 'tmpmoodlecandidats.nom','tmpmoodlecandidats.email')
        ->where('moodlemoyennegenerales.code_formation', $code_formation) 
        ->where('moodlemoyennegenerales.reference_candidat', $candidat->email)
        ->orderBy('moodlemoyennegenerales.moyenne', 'DESC')
        ->first();  


    // récupérer l'objet de la formation
    $formation =  Formations::where('code_formation', $code_formation)->get()->first();
    $date = date('d-m-YH:i:s');
    // share data to view
    //view()->share(['notes' => $notes, 'code_formation' => $code_formation, 'formation' => $formation, 'candidat' => $candidat,'moyenne_generale' => $moyenne_generale]);
 
    //$pdf = PDF::loadView('moodlecandidats.pdf_releve_notes', $notes);
    $pdf = PDF::loadView('documents.pdf_releve_notes', [
      'notes' => $notes, 'code_formation' => $code_formation, 'formation' => $formation, 'candidat' => $candidat,'moyenne_generale' => $moyenne_generale
    ]);

    $pdf->output();
    $pdf->setPaper('A4', 'landscape');
    // ##### a considerer pour pagination ###############

    // $pdf->output();
    $dom_pdf = $pdf->getDomPDF();
    $canvas = $dom_pdf->get_canvas();
    $footer = $canvas->open_object();
    $w = $canvas->get_width();
    $h = $canvas->get_height();
    //     $canvas->page_text($w-60,$h-28, "Page {PAGE_NUM} sur {PAGE_COUNT}", null, 10, array(0, 0, 0));
    $canvas->close_object();
    $canvas->add_object($footer, "all");
    //  $canvas->get_cpdf()->setEncryption('frogs', '', array('print'));
    // ##### end a considerer pour pagination ###############
    // download PDF file with download method

    return $pdf->download($code_formation . '_RELEVE_DE_NOTES_' . $date . '.pdf');

    }

    

    public function getUrlReleveNoteCandidat($id, $code_formation)
    {
        // TODO: Implement getUrlReleveNoteCandidat() method.
        $url = $this->genarateurCodeService->generateUrlSigned('releveNoteCandidat.moodle', ['id' => $id, 'code_formation' => $code_formation]);
        $data = ['url' => $url];
        return  ResponseHandler::success("ok", $data, 200);
    }

    // impression des attestations moodle par lot
    

    public function downloadAllAttestationMoodleCandidat($code_formation)
    {
        // TODO: Implement downloadAllAttestationMoodleCandidat() method.

        set_time_limit(0); 
     $participants = DB::table('moodlemoyennegenerales') 
        ->join('tmpmoodlecandidats', 'tmpmoodlecandidats.email', '=', 'moodlemoyennegenerales.reference_candidat') 
        ->select('moodlemoyennegenerales.annee_academique', 'moodlemoyennegenerales.code_formation', 'moodlemoyennegenerales.moyenne', 'tmpmoodlecandidats.prenoms', 'tmpmoodlecandidats.nom','tmpmoodlecandidats.email','tmpmoodlecandidats.ddn','tmpmoodlecandidats.lieu_naissance')
        ->where('moodlemoyennegenerales.code_formation', $code_formation) 
        //->where('moodlemoyennegenerales.reference_candidat', $candidat->email)
        ->orderBy('moodlemoyennegenerales.moyenne', 'DESC')
        ->get(); 
 
    // récupérer l'objet de la formation
    $formation =  Formations::where('code_formation', $code_formation)->get()->first();
    $date = date('d-m-YH:i:s');
    // share data to view
    //view()->share(['participant' => $participant, 'code_formation' => $code_formation, 'formation' => $formation]);
 
    //$pdf = PDF::loadView('attestations.attestation_dsc', $participant);
    $pdf = PDF::loadView('attestations.attestation_dsc_impression_lot', [
    'participants' => $participants,
    'code_formation' => $code_formation,
    'formation' => $formation,
]);
    $pdf->output();
    $pdf->setPaper('A4', 'landscape');
    // ##### a considerer pour pagination ###############
    // $pdf->output();
    $dom_pdf = $pdf->getDomPDF();
    $canvas = $dom_pdf->get_canvas();
    $footer = $canvas->open_object();
    $w = $canvas->get_width();
    $h = $canvas->get_height();
    //     $canvas->page_text($w-60,$h-28, "Page {PAGE_NUM} sur {PAGE_COUNT}", null, 10, array(0, 0, 0));
    $canvas->close_object();
    $canvas->add_object($footer, "all");
    //  $canvas->get_cpdf()->setEncryption('frogs', '', array('print'));
    // ##### end a considerer pour pagination ###############
    // download PDF file with download method

    return $pdf->download($code_formation . '_ATTESTATION_DSC_' . $date . '.pdf');

    }
 
    public function getUrlAllAttestationMoodleCandidat($code_formation)
    {
        // TODO: Implement getUrlAllAttestationMoodleCandidat() method.
        $url = $this->genarateurCodeService->generateUrlSigned('allAttestationCandidat.moodle', ['code_formation' => $code_formation]);
        $data = ['url' => $url];
        return  ResponseHandler::success("ok", $data, 200);
    }

    

    public function downloadAllReleveNoteCandidat($code_formation)
    {
        // TODO: Implement downloadAllReleveNoteCandidat() method.

        set_time_limit(0);
        $participants = DB::table('moodlemoyennegenerales') 
        ->join('tmpmoodlecandidats', 'tmpmoodlecandidats.email', '=', 'moodlemoyennegenerales.reference_candidat') 
        ->select('moodlemoyennegenerales.annee_academique', 'moodlemoyennegenerales.code_formation', 'moodlemoyennegenerales.moyenne', 'tmpmoodlecandidats.prenoms', 'tmpmoodlecandidats.nom','tmpmoodlecandidats.email')
        ->where('moodlemoyennegenerales.code_formation', $code_formation) 
        //->where('moodlemoyennegenerales.reference_candidat', $candidat->email)
        ->orderBy('moodlemoyennegenerales.moyenne', 'DESC')
        ->get();  


    // récupérer l'objet de la formation
    $formation =  Formations::where('code_formation', $code_formation)->get()->first();
    $date = date('d-m-YH:i:s');
    // share data to view
    //view()->share(['notes' => $notes, 'code_formation' => $code_formation, 'formation' => $formation, 'candidat' => $candidat,'moyenne_generale' => $moyenne_generale]);
 
    //$pdf = PDF::loadView('moodlecandidats.pdf_releve_notes', $notes);
    $pdf = PDF::loadView('documents.pdf_impression_lot_releve_notes', [
      'code_formation' => $code_formation, 'formation' => $formation, 'participants' => $participants
    ]);

    $pdf->output();
    $pdf->setPaper('A4', 'landscape');
    // ##### a considerer pour pagination ###############

    // $pdf->output();
    $dom_pdf = $pdf->getDomPDF();
    $canvas = $dom_pdf->get_canvas();
    $footer = $canvas->open_object();
    $w = $canvas->get_width();
    $h = $canvas->get_height();
    //     $canvas->page_text($w-60,$h-28, "Page {PAGE_NUM} sur {PAGE_COUNT}", null, 10, array(0, 0, 0));
    $canvas->close_object();
    $canvas->add_object($footer, "all");
    //  $canvas->get_cpdf()->setEncryption('frogs', '', array('print'));
    // ##### end a considerer pour pagination ###############
    // download PDF file with download method

    return $pdf->download($code_formation . '_RELEVE_DE_NOTES_' . $date . '.pdf');

    }

    

    public function getUrlAllReleveNoteCandidat($code_formation)
    {
        // TODO: Implement getUrlAllReleveNoteCandidat() method.
        $url = $this->genarateurCodeService->generateUrlSigned('allReleveNoteCandidat.moodle', ['code_formation' => $code_formation]);
        $data = ['url' => $url];
        return  ResponseHandler::success("ok", $data, 200);
    }

    public function getUrlAllRegistreNoteCandidat($code_formation)
    {
        // TODO: Implement getUrlAllRegistreNoteCandidat() method.
        $url = $this->genarateurCodeService->generateUrlSigned('allRegistreNoteCandidat.moodle', ['code_formation' => $code_formation]);
        $data = ['url' => $url];
        return  ResponseHandler::success("ok", $data, 200);
    }

    public function getUrlAllRegistreNoteCandidatSearch($code_formation)
    {
        // TODO: Implement getUrlAllRegistreNoteCandidatSearch() method.
        $url = $this->genarateurCodeService->generateUrlSigned('allRegistreNoteCandidatSearch.moodle', ['code_formation' => $code_formation]);
        $data = ['url' => $url];
        return  ResponseHandler::success("ok", $data, 200);
    }

    
    public function downloadAllRegistreNoteCandidat($code_formation)
    {
        // TODO: Implement downloadAllRegistreNoteCandidat() method.

        set_time_limit(0); 
        
    $candidat = DB::table('moodlemoyennegenerales') 
        ->join('tmpmoodlecandidats', 'tmpmoodlecandidats.email', '=', 'moodlemoyennegenerales.reference_candidat') 
        ->select('moodlemoyennegenerales.annee_academique', 'moodlemoyennegenerales.code_formation', 'moodlemoyennegenerales.moyenne', 'tmpmoodlecandidats.prenoms', 'tmpmoodlecandidats.nom','tmpmoodlecandidats.email','tmpmoodlecandidats.ddn','tmpmoodlecandidats.lieu_naissance')
        ->where('moodlemoyennegenerales.code_formation', $code_formation)  
        ->orderBy('moodlemoyennegenerales.moyenne', 'DESC')
        ->first(); 

    $participants = DB::table('moodlemoyennegenerales') 
        ->join('tmpmoodlecandidats', 'tmpmoodlecandidats.email', '=', 'moodlemoyennegenerales.reference_candidat') 
        ->select('moodlemoyennegenerales.annee_academique', 'moodlemoyennegenerales.code_formation', 'moodlemoyennegenerales.moyenne', 'tmpmoodlecandidats.prenoms', 'tmpmoodlecandidats.nom','tmpmoodlecandidats.email','tmpmoodlecandidats.ddn','tmpmoodlecandidats.lieu_naissance')
        ->where('moodlemoyennegenerales.code_formation', $code_formation)  
        ->orderBy('moodlemoyennegenerales.moyenne', 'DESC')
        ->get(); 
    
    // récupérer l'objet de la formation
    $formation =  Formations::where('code_formation', $code_formation)->get()->first();
    $date = date('d-m-YH:i:s'); 
    //$pdf = PDF::loadView('attestations.attestation_dsc', $participant);
    $pdf = PDF::loadView('documents.pdf_liste_notes', [
    'participants' => $participants,
    'code_formation' => $code_formation,
    'formation' => $formation,
    'candidat' => $candidat
]);
    $pdf->output();
    $pdf->setPaper('A4', 'landscape');
    // ##### a considerer pour pagination ###############
    // $pdf->output();
    $dom_pdf = $pdf->getDomPDF();
    $canvas = $dom_pdf->get_canvas();
    $footer = $canvas->open_object();
    $w = $canvas->get_width();
    $h = $canvas->get_height();
    //     $canvas->page_text($w-60,$h-28, "Page {PAGE_NUM} sur {PAGE_COUNT}", null, 10, array(0, 0, 0));
    $canvas->close_object();
    $canvas->add_object($footer, "all");
    //  $canvas->get_cpdf()->setEncryption('frogs', '', array('print'));
    // ##### end a considerer pour pagination ###############
    // download PDF file with download method

    return $pdf->download($code_formation . '_LISTE_DES_NOTES_DSC_' . $date . '.pdf');

    }

    
    public function downloadAllRegistreNoteCandidatSearch($code_formation)
    {
        // TODO: Implement downloadAllRegistreNoteCandidatSearch() method.

        set_time_limit(0);
         
    $candidat = DB::table('moodlemoyennegenerales') 
        ->join('tmpmoodlecandidats', 'tmpmoodlecandidats.email', '=', 'moodlemoyennegenerales.reference_candidat') 
        ->select('moodlemoyennegenerales.annee_academique', 'moodlemoyennegenerales.code_formation', 'moodlemoyennegenerales.moyenne', 'tmpmoodlecandidats.prenoms', 'tmpmoodlecandidats.nom','tmpmoodlecandidats.email','tmpmoodlecandidats.ddn','tmpmoodlecandidats.lieu_naissance')
        ->where('moodlemoyennegenerales.code_formation', $code_formation)  
        ->orderBy('moodlemoyennegenerales.moyenne', 'DESC')
        ->first(); 

    $participants = DB::table('moodlemoyennegenerales') 
        ->join('tmpmoodlecandidats', 'tmpmoodlecandidats.email', '=', 'moodlemoyennegenerales.reference_candidat') 
        ->select('moodlemoyennegenerales.annee_academique', 'moodlemoyennegenerales.code_formation', 'moodlemoyennegenerales.moyenne', 'tmpmoodlecandidats.prenoms', 'tmpmoodlecandidats.nom','tmpmoodlecandidats.email','tmpmoodlecandidats.ddn','tmpmoodlecandidats.lieu_naissance')
        ->where('moodlemoyennegenerales.code_formation', $code_formation)  
        ->orderBy('tmpmoodlecandidats.nom', 'ASC')
        ->get(); 
    
    // récupérer l'objet de la formation
    $formation =  Formations::where('code_formation', $code_formation)->get()->first();
    $date = date('d-m-YH:i:s'); 
    //$pdf = PDF::loadView('attestations.attestation_dsc', $participant);
    $pdf = PDF::loadView('documents.pdf_liste_notes', [
    'participants' => $participants,
    'code_formation' => $code_formation,
    'formation' => $formation,
    'candidat' => $candidat
]);
    $pdf->output();
    $pdf->setPaper('A4', 'landscape');
    // ##### a considerer pour pagination ###############
    // $pdf->output();
    $dom_pdf = $pdf->getDomPDF();
    $canvas = $dom_pdf->get_canvas();
    $footer = $canvas->open_object();
    $w = $canvas->get_width();
    $h = $canvas->get_height();
    //     $canvas->page_text($w-60,$h-28, "Page {PAGE_NUM} sur {PAGE_COUNT}", null, 10, array(0, 0, 0));
    $canvas->close_object();
    $canvas->add_object($footer, "all");
    //  $canvas->get_cpdf()->setEncryption('frogs', '', array('print'));
    // ##### end a considerer pour pagination ###############
    // download PDF file with download method

    return $pdf->download($code_formation . '_LISTE_DES_NOTES_DSC_' . $date . '.pdf');

    }
}
