<?php

namespace App\Http\Controllers;

use App\Exports\ExportEtatRecapitulatif;
use App\Helpers\ResponseHandler;
use App\Models\Detailpaiementouvrages;
use App\Models\Dureeformations;
use App\Models\Formations;
use App\Models\Inscriptions;
use App\Models\Ouvrages;
use App\Models\Rapports;
use Illuminate\Http\Request;
use App\Services\contracts\DocumentsInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use NumberFormatter;
use PDF;

class DocumentsController extends Controller
{
    private DocumentsInterface $documentService;

    public function __construct(DocumentsInterface $documentService){
        $this->documentService = $documentService;
        $this->middleware('auth:api',['except' => ['recuPaiement','proforma','consultationAttestationParticipant',
            'telechargerAttestationParticipant','listeParticipantVisio','listeParticipantPresentiel','proformaDsc','listeFormateurs',
            'exportAllAttestationParticipant','telechargerAttestationFormateur','consulterAttestationFormateur','proformaOuvrage',
            'exportAllAttestationFormateur','recuPaiementOuvrage','exportEtatRecapitulatif','exportListeGlobaleToPDF','exportListeParticipantAttestationPDF']]);
    }

    public function showVisuelFormation(Request $request)
    {
        try {
            // validation des données du formulaire

            $validated = $request->validate([
                'idFormation' => 'required|integer',
            ]);

            return $visuel = $this->documentService->getFormationVisuel($request->idFormation);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }

    public function recuPaiement(Request $request, $reference)
    {
        try {

            return $data = $this->documentService->downloadRecuPaiement($reference);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }

    public function recuPaiementOuvrage(Request $request, $reference)
    {
        try {

            return $data = $this->documentService->downloadRecuPaiementOuvrage($reference);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }

    public function urlRecuPaiement(Request $request)
    {
        try{
            $validated = $request->validate([
                'reference' => 'required|string',
            ]);

            return $this->documentService->getUrlSignedRecuPaiement($request->reference);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function urlRecuPaiementOuvrage(Request $request)
    {
        try{
            $validated = $request->validate([
                'reference' => 'required|string',
            ]);

            return $this->documentService->getUrlSignedRecuPaiementOuvrage($request->reference);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function proforma(Request $request, $reference)
    {
        try {

            return $data = $this->documentService->downloadProforma($reference);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }

    public function proformaOuvrage(Request $request, $reference)
    {
        try {

            return $data = $this->documentService->downloadProformaOuvrage($reference);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }

    public function proformaDsc(Request $request, $reference)
    {
        try {
            return $data = $this->documentService->downloadProformaDsc($reference);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }

    public function genererUrlProformaSigned(Request $request)
    {
        try{
            $validated = $request->validate([
                'reference' => 'required|string',
            ]);

        return $this->documentService->getUrlProformaSigned($request->reference);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function genererUrlProformaDscSigned(Request $request)
    {
        try{
            $validated = $request->validate([
                'reference' => 'required|string',
            ]);
            return $this->documentService->getUrlProformaDscSigned($request->reference);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }


    public function genererUrlProformaOuvrageSigned(Request $request)
    {
        try{
            $validated = $request->validate([
                'reference' => 'required|string',
            ]);
            return $this->documentService->getUrlProformaOuvrage($request->reference);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function listeAttestationParticipant(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $resp = $this->documentService->getListeAttestationParticipant($request, $request->code_formation);
            $data = $resp['pagination'];
            // Transformer pour enlever le bloc 'links'
            $response = [
                'current_page' => $data->currentPage(),
                'data'         => $data->items(),
                'first_page_url' => $data->url(1),
                'last_page_url'  => $data->url($data->lastPage()),
                'next_page_url'  => $data->nextPageUrl(),
                'prev_page_url'  => $data->previousPageUrl(),
                'per_page'       => $data->perPage(),
                'total'          => $data->total(),
                'nbr_duree_enregistrement' => $resp['nbr_duree_enregistrement'],
                'duree_formation' => $resp['duree_formation'],
                'nbrePresentiel' => $resp['nbrePresentiel'],
                'nbreVisio' => $resp['nbreVisio']
            ];
            return ResponseHandler::success('ok',$response);

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }

    public function consultationAttestationParticipant(Request $request,$rapport_id)
    {
        try{
            return $this->documentService->showAttestationParticipant($rapport_id);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function urlConsultationAttestation(Request $request){

        try{
            $validated = $request->validate([
                'rapport_id' => 'required',
            ]);

            return $this->documentService->getUrlConsultationAttestation($request->rapport_id);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function sendAttestationParticipant(Request $request){

        try{
            $validated = $request->validate([
                'rapport_id' => 'required',
            ]);

            return $this->documentService->sendMailAttestationParticipant($request->rapport_id);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }


    public function telechargerAttestationParticipant($rapport_id)
    {
        try {

            return $data = $this->documentService->downloadAttestationParticipant($rapport_id);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }

    public function listeParticipantVisio($code_formation)
    {
        try {
            Log::info($code_formation);
            return $data = $this->documentService->downloadListeParticipantVisio($code_formation);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }

    public function listeParticipantPresentiel($code_formation)
    {
        try {
            return $data = $this->documentService->downloadListeParticipantPresentiel($code_formation);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }

    public function urlListeParticipantPresentiel(Request $request){

        try{
            $validated = $request->validate([
                'code_formation' => 'required',
            ]);

            return $this->documentService->getUrlListeParticipantPresentiel($request->code_formation);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function urlListeParticipantVisio(Request $request){

        try{
            $validated = $request->validate([
                'code_formation' => 'required',
            ]);

            return $this->documentService->getUrlListeParticipantVisio($request->code_formation);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function urlAllAttestationParticipant(Request $request){

        try{
            $validated = $request->validate([
                'code_formation' => 'required',
            ]);

            return $this->documentService->getUrlAllAttestationParticipant($request->code_formation);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function exportAllAttestationParticipant($code_formation)
    {
        try {
            return $data = $this->documentService->downloadAllAttestationParticipant($code_formation);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }

    public function sendAllAttestationParticipant(Request $request){

        try{
            $validated = $request->validate([
                'listeAuditeurs' => 'required',
                'code_formation' => 'required'
            ]);

            return $this->documentService->sendAllMailAttestationParticipant($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function listeAttestationFormateurs(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $resp = $this->documentService->getListeAttestationFormateur($request, $request->code_formation);
            $data = $resp['pagination'];
            // Transformer pour enlever le bloc 'links'
            $response = [
                'current_page' => $data->currentPage(),
                'data'         => $data->items(),
                'first_page_url' => $data->url(1),
                'last_page_url'  => $data->url($data->lastPage()),
                'next_page_url'  => $data->nextPageUrl(),
                'prev_page_url'  => $data->previousPageUrl(),
                'per_page'       => $data->perPage(),
                'total'          => $data->total(),
                'nbreFormateur' => $resp['nbreFormateur']
            ];
            return ResponseHandler::success('ok',$response);

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }


    public function telechargerAttestationFormateur($id)
    {
        try {

            return $data = $this->documentService->downloadAttestationFormateur($id);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }


    public function consulterAttestationFormateur($id)
    {
        try {

            return $data = $this->documentService->downloadConsultationAttestationFormateur($id);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }

    public function urlAttestationFormateur(Request $request){

        try{
            $validated = $request->validate([
                'id' => 'required',
            ]);

            return $this->documentService->getUrlAttestationFormateur($request->id);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function urlConsultationAttestationFormateur(Request $request){

        try{
            $validated = $request->validate([
                'id' => 'required',
            ]);

            return $this->documentService->getUrlConsultationAttestationFormateur($request->id);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function sendAttestationFormateur(Request $request){

        try{
            $validated = $request->validate([
                'id' => 'required',
            ]);

            return $this->documentService->sendMailAttestationFormateur($request->id);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function sendAllAttestationFormateur(Request $request){

        try{
            $validated = $request->validate([
                'listeFormateurs' => 'required',
                'code_formation' => 'required'
            ]);

            return $this->documentService->sendAllMailAttestationFormateur($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function exportAllAttestationFormateur($code_formation)
    {
        try {
            return $data = $this->documentService->downloadAllAttestationFormateur($code_formation);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }

    public function urlAllAttestationFormateur(Request $request){

        try{
            $validated = $request->validate([
                'code_formation' => 'required',
            ]);

            return $this->documentService->getUrlAllAttestationFormateur($request->code_formation);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function listeFormateurs($code_formation)
    {
        try {
            return $data = $this->documentService->downloadListeFormateur($code_formation);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }

    public function urlListeFormateur(Request $request){

        try{
            $validated = $request->validate([
                'code_formation' => 'required',
            ]);

            return $this->documentService->getUrlListeFormateur($request->code_formation);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function exportEtatRecapitulatif(Request $request,$code_formation)
    { 
        return Excel::download(new ExportEtatRecapitulatif($code_formation),"SIGWEB_ERSUMA_Etat_recapitulatif_".$code_formation."_".date("Ymd").'_'.date("His").".xlsx");
    }

public function pdfEtatrecapitulatif(Request $request,$code_formation)
{
    set_time_limit(0);
    //etats récapitulatif des formations
      // information sur la formation en cours
    $formation = Formations::where("code_formation",$code_formation)->get()->first();
    // détails paiements formation
    $liste_participants = DB::table('inscriptions')
    ->leftjoin('auditeurs', 'inscriptions.email', '=', 'auditeurs.email')
    ->select('inscriptions.email','inscriptions.email', 'auditeurs.nom as nom', 'auditeurs.prenoms as prenoms','auditeurs.telephone as telephone','auditeurs.pays as pays','inscriptions.tarif_formation_id','inscriptions.type_formation')
    //->where('rapports.code_formation',$code_formation)
    ->where('inscriptions.code_formation',$code_formation)
    ->orderBy('auditeurs.nom', 'ASC')
    ->get();

    $total_participant = Rapports::where("code_formation",$code_formation)->count();
    // récupérer la liste des participants en présentielle dont le champ "presentiel" = 1
    $total_participant_presentiel =  DB::table('rapports')
    ->join('inscriptions', 'inscriptions.email', '=', 'rapports.email')
    ->leftjoin('auditeurs', 'inscriptions.email', '=', 'auditeurs.email')
    ->select('rapports.*', 'inscriptions.id as id_inscrit', 'auditeurs.nom as nom', 'auditeurs.prenoms as prenoms', 'auditeurs.telephone as telephone', 'auditeurs.pays as pays')
    ->where('rapports.code_formation', $code_formation)
    ->where('inscriptions.code_formation', $code_formation)
    ->where('rapports.temps_presence', '')
    ->count();

    $total_inscrit = Inscriptions::where("code_formation",$code_formation)->count();
    $total_inscrit_visio = Inscriptions::where("code_formation",$code_formation)->where("presentiel", 0)->count();
    $total_inscrit_presentiel = Inscriptions::where("code_formation",$code_formation)->where("presentiel", 1)->count();


     // Detailspaiement::where('reference_paiement','ERS-2021-FACT-000001');
    // return view('public.recu',compact("paiement","details_paiement"));
    $formatter = new NumberFormatter('fr', NumberFormatter::SPELLOUT);
    $formatter->setTextAttribute(NumberFormatter::DEFAULT_RULESET, "%spellout-ordinal");

    // share data to view
  view()->share([
        'formation' => $formation,
        'liste_participants' => $liste_participants,
        'total_inscrit' => $total_inscrit,
        'total_participant_presentiel' => $total_participant_presentiel,
        'total_participant' => $total_participant,
        'total_inscrit_visio' => $total_inscrit_visio,
        'total_inscrit_presentiel' => $total_inscrit_presentiel
]);

   $pdf = PDF::loadView('documents.pdf_etats_recapitulatif');
   $customPaper = array(0,0,720,1440);
   $pdf->setPaper($customPaper, 'landscape');

    $date = date('d_m_Y_His');
   // download PDF file with download method
  return $pdf->download('Etat_recapitulatif_wb_'.$code_formation.'_'.$date.'.pdf');


}

    /**
     * statsEtatRecapitulatifFormation
     *
     */
    public function exportEtatRecapitulatifOuvrage(Request $request,$id)
    {
        $ouvrage = Ouvrages::find($id);
        $details_paiements = Detailpaiementouvrages::with('paiement_valide')->where("ouvrage_id",$ouvrage->id)->get();

        $formatter = new NumberFormatter('fr', NumberFormatter::SPELLOUT);
        $formatter->setTextAttribute(NumberFormatter::DEFAULT_RULESET, "%spellout-ordinal");

        // share data to view
        view()->share(['ouvrage'=>$ouvrage,'details_paiements'=>$details_paiements]);
        $pdf = PDF::loadView('statistiques.pdf_etats_recapitulatif_ouvrage');
        $customPaper = array(0,0,720,1440);
        $pdf->setPaper($customPaper, 'landscape');

        $date = date('YmdHis');
        // download PDF file with download method
        return $pdf->download('Etat_recapitulatif_ouvrage_'.$date.'.pdf');


    }

    

    public function listeRapports(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $resp = $this->documentService->getListeRapports($request, $request->code_formation);
            $data = $resp['pagination'];
            // Transformer pour enlever le bloc 'links'
            $response = [
                'current_page' => $data->currentPage(),
                'data'         => $data->items(),
                'first_page_url' => $data->url(1),
                'last_page_url'  => $data->url($data->lastPage()),
                'next_page_url'  => $data->nextPageUrl(),
                'prev_page_url'  => $data->previousPageUrl(),
                'per_page'       => $data->perPage(),
                'total'          => $data->total(), 
            ];
            return ResponseHandler::success('ok',$response);

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }

    

  // Generate PDF pour la liste globale
  public function exportListeGlobaleToPDF($code_formation)
{
    set_time_limit(0);

    $liste_participants = DB::table('rapports')
        ->join('inscriptions', 'inscriptions.email', '=', 'rapports.email')
        ->leftjoin('auditeurs', 'inscriptions.email', '=', 'auditeurs.email')
        ->select('rapports.*', 'auditeurs.nom as nom', 'auditeurs.prenoms as prenoms', 'auditeurs.pays as pays')
        ->where('rapports.code_formation', $code_formation)
        ->where('inscriptions.code_formation', $code_formation)
        ->orderBy('auditeurs.nom', 'ASC')
        ->get();

    $formation = Formations::where('code_formation', $code_formation)->first();

    // Partager les données avec la vue
    view()->share([
        'liste_participants' => $liste_participants,
        'code_formation' => $code_formation,
        'formation' => $formation,
    ]);

    $pdf = PDF::loadView('documents.exportation_liste_globale_pdf');

    $pdf->setPaper('A4', 'landscape');
    $dom_pdf = $pdf->getDomPDF();
    $canvas = $dom_pdf->get_canvas();
    $footer = $canvas->open_object();
    $w = $canvas->get_width();
    $h = $canvas->get_height();
    $canvas->page_text($w - 100, $h - 30, "Page {PAGE_NUM} / {PAGE_COUNT}", null, 10, array(0, 0, 0));
    $canvas->close_object();
    $canvas->add_object($footer, "all");

    $date = date('d-m-Y_H-i-s');
    return $pdf->download($code_formation . '_LISTE_GLOBALE_PARTICIPANT_' . $date . '.pdf');
}

  

  // Generate PDF ok
 /* public function exportListeParticipantAttestationPDF($code_formation)
  {
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

    $pdf = PDF::loadView('documents.liste_participant_eligible_pdf');
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

    return $pdf->download($code_formation . '_RAPPORT_PARTICIPANT_' . $date . '.pdf');
  }*/

  public function exportListeParticipantAttestationPDF($code_formation)
{
    set_time_limit(0);

    $liste_participants = DB::table('rapports')
        ->join('inscriptions', 'inscriptions.email', '=', 'rapports.email')
        ->leftjoin('auditeurs', 'auditeurs.email', '=', 'inscriptions.email')
        ->select('rapports.*', 'auditeurs.nom as nom', 'auditeurs.prenoms as prenoms', 'auditeurs.pays as pays')
        ->where('rapports.code_formation', $code_formation)
        ->where('inscriptions.code_formation', $code_formation)
        ->orderBy('auditeurs.nom', 'ASC')
        ->get();

    // Variable temps de latence
    $temps_latence = 90; // par jour de formation

    
    $duree_formation = Dureeformations::where('code_formation', $code_formation)
        ->orderByDesc("id")
        ->get();

    $duree_session = DB::table('sessionformations')
        ->select(DB::raw('SUM(duree_reel - ' . (int)$temps_latence . ') as duree_session'))
        ->where('code_formation', $code_formation)
        ->get();

    view()->share([
        'liste_participants' => $liste_participants,
        'code_formation' => $code_formation,
        'durees' => $duree_formation,
        'duree_session' => $duree_session,
    ]);

    $pdf = PDF::loadView('documents.liste_participant_eligible_pdf');

    $pdf->setPaper('A4', 'landscape');
    $dom_pdf = $pdf->getDomPDF();
    $canvas = $dom_pdf->get_canvas();
    $footer = $canvas->open_object();
    $w = $canvas->get_width();
    $h = $canvas->get_height();
    $canvas->page_text($w - 100, $h - 30, "Page {PAGE_NUM} / {PAGE_COUNT}", null, 10, array(0, 0, 0));
    $canvas->close_object();
    $canvas->add_object($footer, "all");

    $date = date('d-m-Y_H:i:s');
    return $pdf->download($code_formation . '_RAPPORT_PARTICIPANT_' . $date . '.pdf');
}


}
