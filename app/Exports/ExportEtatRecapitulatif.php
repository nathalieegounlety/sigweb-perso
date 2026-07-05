<?php

namespace App\Exports;

use App\Models\Detailspaiements;
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
        $codeFormation = $this->code_formation; 
        $formation = Formations::where("code_formation", $codeFormation)->first();
        $totalInscGratuite = Detailspaiements::where("code_formation",$codeFormation)->where("id_reduction",3)->count();

        // Détails des participants
        $liste_participants = DB::table('inscriptions')
            ->leftJoin('auditeurs', 'inscriptions.email', '=', 'auditeurs.email')
            ->leftJoin('paiements', 'paiements.code_inscription', '=', 'inscriptions.code_inscription')
            ->leftJoin(
                DB::raw('
            (
                SELECT
                    dp.reference_paiement,
                    MAX(r.pourcentage) as reduction_pourcentage,
                    SUM(
                        CASE
                            WHEN gt.type_tarif = "INSCRIPTION"
                            THEN dp.montant_paye
                            ELSE 0
                        END
                    ) as frais_inscription_paye,
                    SUM(
                        CASE
                            WHEN gt.type_tarif = "INSCRIPTION"
                            THEN dp.montant_restant
                            ELSE 0
                        END
                    ) as frais_inscription_restant,
                    SUM(
                        CASE
                            WHEN gt.type_tarif <> "INSCRIPTION"
                            THEN dp.montant_paye
                            ELSE 0
                        END
                    ) as frais_scolarite_paye,

                    SUM(
                        CASE
                            WHEN gt.type_tarif <> "INSCRIPTION"
                            THEN dp.montant_restant
                            ELSE 0
                        END
                    ) as frais_scolarite_restant

                FROM detailspaiements dp
                INNER JOIN tarificationsformations tt
                    ON tt.id = dp.id_tarification_formation
                INNER JOIN grilletarifaires gt
                    ON gt.id = tt.id_grilletarifaire
                LEFT JOIN reductions r
                    ON r.id = dp.id_reduction

                GROUP BY dp.reference_paiement
            ) as dp_calc
        '),
                function ($join) {
                    $join->on('dp_calc.reference_paiement', '=', 'paiements.reference')
                        ->where('paiements.type', '=', 'DSC');
                }
            )

            /*
            |--------------------------------------------------------------------------
            | 2️⃣ Flag toujours retourné (is_paiement_dsc)
            |--------------------------------------------------------------------------
            */
            ->leftJoin(
                DB::raw('
            (
                SELECT
                    reference_paiement,
                    MAX(have_reduction_dsc) as have_reduction_dsc
                FROM detailspaiements
                GROUP BY reference_paiement
            ) as dp_flag
        '),
                'dp_flag.reference_paiement',
                '=',
                'paiements.reference'
            )

            ->select(
                'inscriptions.email',
                'auditeurs.nom as nom',
                'auditeurs.prenoms as prenoms',
                'auditeurs.telephone as telephone',
                'auditeurs.pays as pays',
                'inscriptions.tarif_formation_id',
                'inscriptions.type_formation',
                'inscriptions.presentiel',
                'inscriptions.observations',

                'paiements.montant_total',
                'paiements.montant_restant',
                'paiements.montant_paye',
                'paiements.statut_paiement',
                
                // Frais calculés via grille
                DB::raw('COALESCE(dp_calc.frais_inscription_paye, 0) as frais_inscription_paye'),
                DB::raw('COALESCE(dp_calc.frais_inscription_restant, 0) as frais_inscription_restant'),
                DB::raw('COALESCE(dp_calc.frais_scolarite_paye, 0) as frais_scolarite_paye'),
                DB::raw('COALESCE(dp_calc.frais_scolarite_restant, 0) as frais_scolarite_restant'),

                // Réduction
                DB::raw('COALESCE(dp_calc.reduction_pourcentage, 0) as reduction_pourcentage'),

                // Toujours retourné
                DB::raw('COALESCE(dp_flag.have_reduction_dsc, 0) as have_reduction_dsc')
            )
            ->selectRaw(
                '(EXISTS (
                    SELECT 1 FROM rapports
                    WHERE rapports.email = inscriptions.email 
                    AND rapports.code_formation = ?
                )) as a_participe',
                [$codeFormation]
            )
            ->where('inscriptions.code_formation', $codeFormation)
            ->orderBy('auditeurs.nom', 'ASC')
            ->get(); 

        // Total des participants
        $total_participant = Rapports::where("code_formation", $codeFormation)->count();

        // Total des participants en présentiel
        $total_participant_presentiel = DB::table('rapports')
            ->join('inscriptions', 'inscriptions.email', '=', 'rapports.email')
            ->leftJoin('auditeurs', 'inscriptions.email', '=', 'auditeurs.email')
            ->where('rapports.code_formation', $codeFormation)
            ->where('inscriptions.code_formation', $codeFormation)
            ->where('rapports.temps_presence', 0)
            ->count();

        $totalPaiementAttestation = DB::table('tarificationsformations')
            ->join('detailspaiements', 'detailspaiements.id_tarification_formation', '=', 'tarificationsformations.id')
            ->join('grilletarifaires', 'grilletarifaires.id', '=', 'tarificationsformations.id_grilletarifaire')
            ->join('paiements', 'paiements.reference', '=', 'detailspaiements.reference_paiement')
            ->join('modepaiements', 'modepaiements.id', '=', 'paiements.id_modepaiement')
            ->select('detailspaiements.*','paiements.email as email_paiement','grilletarifaires.type_tarif','grilletarifaires.montant','modepaiements.libelle as modepaiement')
            ->where('tarificationsformations.code_formation',$codeFormation)
            ->where('detailspaiements.statut_paiement',"PAYE")
            ->count();

        // Total des inscriptions
        $total_inscrit = Inscriptions::where("code_formation", $codeFormation)->count();

        // Total des inscriptions en visio
        $total_inscrit_visio = Inscriptions::where("code_formation", $codeFormation)->where("presentiel", 0)->count();

        // Total des inscriptions en présentiel
        $total_inscrit_presentiel = Inscriptions::where("code_formation", $codeFormation)->where("presentiel", 1)->count();

        $totalGratuitPresentiel = Detailspaiements::where("detailspaiements.code_formation",$codeFormation)
            ->where("id_reduction",3)
            ->join("inscriptions","inscriptions.email","detailspaiements.email")
            ->where("inscriptions.type_formation","PRESENTIEL")
            ->where("inscriptions.code_formation",$codeFormation)->count();

        $totalGratuitVisio = Detailspaiements::where("detailspaiements.code_formation",$codeFormation)
            ->where("id_reduction",3)
            ->join("inscriptions","inscriptions.email","detailspaiements.email")
            ->where("inscriptions.type_formation","VISIO")
            ->where("inscriptions.code_formation",$codeFormation)->count();

                      $fm_gratuite = false;
                  foreach($formation->getTarification($formation->code_formation) as $tarification){
                      if ($tarification->get_grille_libelle == "GRATUIT"){
                          $fm_gratuite = true;
                      }
                  }

        // Calcul des statistiques
        $stats = (object)[
            'inscrits_presentiel' => $total_inscrit_presentiel,
            'gratuits_presentiel' => $totalGratuitPresentiel,
            'payants_presentiel' => $total_inscrit_presentiel, // À ajuster selon votre logique
            'attestations_presentiel' => $totalPaiementAttestation, // À calculer selon votre logique
            'participants_presentiel' => $total_participant_presentiel,

            'inscrits_visio' => $total_inscrit_visio,
            'gratuits_visio' => $totalGratuitVisio, // À calculer selon votre logique
            'payants_visio' => $total_inscrit_visio, // À ajuster selon votre logique
            'attestations_visio' => 0, // À calculer selon votre logique
            'participants_visio' => $total_participant - $total_participant_presentiel,

            'total_inscrits' => $total_inscrit,
            'total_gratuits' => 0, // À calculer selon votre logique
            'total_payants' => $total_inscrit, // À ajuster selon votre logique
            'total_attestations' => 0, // À calculer selon votre logique
            'total_participants' => $total_participant,
            'total_insc_gratuite' => $totalInscGratuite
        ]; 
        

        return view('statistiques.export_etat_recapitulatif', [ 
            'formation' => $formation,
            'stats' => $stats,
            'participants' => $liste_participants,
            'fm_gratuite' => $fm_gratuite
        ]);
        
        /*
        
            'formation' => $formation,
            'liste_participants' => $liste_participants,
            'total_inscrit' => $total_inscrit,
            'total_participant_presentiel' => $total_participant_presentiel,
            'total_participant' => $total_participant,
            'total_inscrit_visio' => $total_inscrit_visio,
            'total_inscrit_presentiel' => $total_inscrit_presentiel
            */

    }
}