<?php

namespace App\Exports;

use App\Models\Dureeformations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AllAttestationsParticipantsExport implements FromQuery, WithHeadings, WithChunkReading
{
    private $code_formation;

    public function __construct($code_formation)
    {
        $this->code_formation = $code_formation;
    }

    public function query()
    {
        // ##################### calcul scoring ###########################################
        $getDuree = 0;
        $check_duree_formation = Dureeformations::where("code_formation",$this->code_formation)->first();

        if ($check_duree_formation) {
            $nbr_duree_enregistrement = $check_duree_formation->duree_enregistrement;
        } else {
            # code... create durée
            $nbr_duree_enregistrement = 0;
        }
        //variable temps de latence
        $temps_latence = 90; // par jour de formation ||
        // récupérer le nbre de jour de formation pour la formation en cours 

        $duree_formation = DB::table('sessionformations')
            ->select(DB::raw('sum(duree_reel - ' . $temps_latence . ') as duree_session'))
            ->where('code_formation', $this->code_formation)
            ->get();

        if($nbr_duree_enregistrement > 0){
            $getDuree = $nbr_duree_enregistrement;
        }else{
            $getDuree = $duree_formation[0]->duree_session;
        }

        $scoring = intval(($getDuree*2)/3);
        //####################### end calcul scoring ####################################################

        $data =  DB::table('rapports')
            ->join('inscriptions', 'inscriptions.email', '=', 'rapports.email')
            ->leftJoin('auditeurs', 'inscriptions.email', '=', 'auditeurs.email')
            ->leftJoin('tarificationsformations', 'inscriptions.tarif_formation_id', '=', 'tarificationsformations.id')
            ->leftJoin('grilletarifaires', 'tarificationsformations.id_grilletarifaire', '=', 'grilletarifaires.id')
            ->leftJoin('detailspaiements', function ($join) {
                $join->on('detailspaiements.email', '=', 'inscriptions.email');
                $join->on('detailspaiements.id_tarification_formation', '=', 'inscriptions.tarif_formation_id');
            })
            ->select(
                'rapports.code_formation',
                'auditeurs.nom',
                'auditeurs.prenoms',
                'rapports.email',
                'rapports.temps_presence', 
                'rapports.attestation_send', 
                'detailspaiements.statut_paiement'
            )
            ->where('rapports.code_formation', $this->code_formation)
            ->where('inscriptions.code_formation', $this->code_formation)
            ->where(function ($q) use ($scoring) {
                $q->where('rapports.temps_presence', 0)
                    ->orWhere('rapports.temps_presence', '>=', $scoring);
            })->orderBy('rapports.email');

            Log::info($this->code_formation);

            return $data;
    }

    public function headings(): array
    {
        return ['Code formation','Nom','Prénoms','Email','Temps présence','Attestation envoyée','Statut paiement'];
    }

    

    public function chunkSize(): int
    {
        return 1000;
    }
}
