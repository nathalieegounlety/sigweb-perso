<?php

namespace App\Exports;

use App\Models\Formations;
use App\Models\Inscriptions;
use App\Models\Rapports;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class ExportEtatRecapitulatif implements FromView
{
    private $code_formation;

    public function __construct($code_formation)
    {
        $this->code_formation = $code_formation;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function view(): View
    {
        $code_formation = $this->code_formation;
        //etats récapitulatif des formations
        // information sur la formation en cours
        $formation = Formations::where("code_formation",$code_formation)->get()->first();
        // détails paiements formation
        $liste_participants = DB::table('inscriptions')
            ->leftjoin('auditeurs', 'inscriptions.email', '=', 'auditeurs.email')
            ->select('inscriptions.email','inscriptions.email', 'auditeurs.nom as nom', 'auditeurs.prenoms as prenoms','auditeurs.telephone as telephone','auditeurs.pays as pays','inscriptions.tarif_formation_id','inscriptions.type_formation')
            //->where('rapports.code_formation',$code_formation)
            ->where('inscriptions.code_formation',$code_formation)
            ->distinct()
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



        return view('statistiques.export_etat_recapitulatif', [
            'formation' => $formation,
            'liste_participants' => $liste_participants,
            'total_inscrit' => $total_inscrit,
            'total_participant_presentiel' => $total_participant_presentiel,
            'total_participant' => $total_participant,
            'total_inscrit_visio' => $total_inscrit_visio,
            'total_inscrit_presentiel' => $total_inscrit_presentiel
        ]);
    }
}
