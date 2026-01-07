<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Auditeurs;
use App\Models\Detailspaiements;
use App\Models\Formations;
use App\Models\Paiements;
use App\Models\PaiementsTranche;
use App\Services\contracts\DocumentsInterface;
use App\Services\util\GenarateurCodeService;
use Arius\NumberFormatter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
            if($checkImg){
                return Storage::disk('public')->response($path);
            }else{
                return Storage::disk('public')->response($default_path);

            }
        }catch (\Exception $exception){
            return Storage::disk('public')->response($default_path);
        }

    }

    public function downloadRecuPaiement(string $reference)
    {
        // TODO: Implement downloadRecuPaiement() method.
        // vérifier si la référence existe dans la table paiement tranche
        // récupérer la référence de la table paiement
        $getOneTranchePaiement = PaiementsTranche::where('reference',$reference)->first();

        if ($getOneTranchePaiement) {
            # code... it is dsc
            $getOneDtPaiement = Detailspaiements::find($getOneTranchePaiement->details_paiements_id);
            $getPaiement = Paiements::where('reference',$getOneDtPaiement->reference_paiement)->first();

            $paiement = Paiements::find($getPaiement->id);

            $auditeur = Auditeurs::where("email",$paiement->email)->first();
            # code...
            $details_paiement = DB::table('detailspaiements')
                ->leftjoin('paiements_tranches', 'paiements_tranches.details_paiements_id', '=', 'detailspaiements.id')
                ->join('paiements', 'detailspaiements.reference_paiement', '=', 'paiements.reference')
                ->join('tarificationsformations', 'tarificationsformations.id', '=', 'detailspaiements.id_tarification_formation')
                ->join('grilletarifaires', 'tarificationsformations.id_grilletarifaire', '=', 'grilletarifaires.id')
                ->join('formations', 'formations.code_formation', '=', 'tarificationsformations.code_formation')
                ->select('formations.code_formation', 'formations.theme', 'formations.date_debut','paiements_tranches.montant_paye',
                    'detailspaiements.montant_restant','paiements.montant_total','paiements.montant_restant as recap_montant_restant',
                    'detailspaiements.have_reduction_dsc','detailspaiements.statut_paiement','detailspaiements.preuve_path',
                    'grilletarifaires.type_tarif','paiements_tranches.num_transaction')
                ->where('detailspaiements.reference_paiement',$paiement->reference)
                ->orderBy('detailspaiements.id', 'DESC')
                ->get();

            // Detailspaiement::where('reference_paiement','ERS-2021-FACT-000001');
            // return view('public.recu',compact("paiement","details_paiement"));
            $formatter = new NumberFormatter('fr', NumberFormatter::SPELLOUT);
            $formatter->setTextAttribute(NumberFormatter::DEFAULT_RULESET, "%spellout-ordinal");

            // share data to view
            view()->share(['formatter'=>$formatter,'paiement'=>$paiement, 'auditeur'=>$auditeur, 'details_paiement'=>$details_paiement]);
            $pdf = Pdf::loadView('documents.recu_paiement_dsc');
            $customPaper = array(0,0,720,1440);
            $pdf->setPaper($customPaper, 'landscape');

            $date = date('d_m_Y_His');
            // download PDF file with download method
            return $pdf->download('recu_'.$reference.'_'.$date.'.pdf');

        } else {
            # code...

            $paiement = Paiements::where('reference',$reference)->get()->first();
            // vérifier si l'objet paiement n'est pas null
            if ( $paiement) {
                # code...

                $auditeur = Auditeurs::where("email",$paiement->email)->first();
                $details_paiement = DB::table('detailspaiements')
                    ->join('paiements', 'paiements.reference', '=', 'detailspaiements.reference_paiement')
                    ->leftjoin('reductions', 'reductions.id', '=', 'detailspaiements.id_reduction')
                    ->join('tarificationsformations', 'tarificationsformations.id', '=', 'detailspaiements.id_tarification_formation')
                    ->join('grilletarifaires', 'tarificationsformations.id_grilletarifaire', '=', 'grilletarifaires.id')
                    ->join('formations', 'formations.code_formation', '=', 'tarificationsformations.code_formation')
                    ->select('formations.code_formation', 'formations.theme', 'formations.date_debut','grilletarifaires.montant','grilletarifaires.libelle','reductions.pourcentage','detailspaiements.statut_paiement','detailspaiements.preuve_path','paiements.num_transaction')
                    ->where('detailspaiements.reference_paiement',$reference)
                    ->orderBy('detailspaiements.id', 'DESC')
                    ->get();
                // Detailspaiement::where('reference_paiement','ERS-2021-FACT-000001');
                // return view('public.recu',compact("paiement","details_paiement"));
                $formatter = new NumberFormatter('fr', NumberFormatter::SPELLOUT);
                $formatter->setTextAttribute(NumberFormatter::DEFAULT_RULESET, "%spellout-ordinal");

                // share data to view
                view()->share(['formatter'=>$formatter,'paiement'=>$paiement, 'auditeur'=>$auditeur, 'details_paiement'=>$details_paiement]);
                $pdf = Pdf::loadView('documents.recu_paiement');
                $customPaper = array(0,0,720,1440);
                $pdf->setPaper($customPaper, 'landscape');

                $date = date('d_m_Y_His');
                // download PDF file with download method
                return $pdf->download('recu_'.$reference.'_'.$date.'.pdf');

            } else {
                # code...
                return  ResponseHandler::success("ok");
            }


        }

    }



    public function getUrlSignedRecuPaiement(string $reference): \Illuminate\Http\JsonResponse
    {
        $url = $this->genarateurCodeService->generateUrlSigned('paiements.recu',['reference' => $reference]);
        $data = ['url'=>$url];
        return  ResponseHandler::success("ok",$data,200);
    }

    public function getUrlSignedRecuPaiementDsc(string $reference): \Illuminate\Http\JsonResponse
    {
        $url = $this->genarateurCodeService->generateUrlSigned('paiements.recu',['reference' => $reference]);
        $data = ['url'=>$url];
        return  ResponseHandler::success("ok",$data,200);
    }

    public function getUrlSignedRecuPaiementOuvrage(string $reference): \Illuminate\Http\JsonResponse
    {
        $url = $this->genarateurCodeService->generateUrlSigned('paiements.recu-ouvrage',['reference' => $reference]);
        $data = ['url'=>$url];
        return  ResponseHandler::success("ok",$data,200);
    }


}
