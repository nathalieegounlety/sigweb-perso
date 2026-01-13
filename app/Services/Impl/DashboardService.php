<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Auditeurs;
use App\Models\Formations;
use App\Models\Inscriptions;
use App\Models\Rapports;
use App\Services\contracts\DashboardInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService implements DashboardInterface
{

    public function getAdminIndicateurs(): array // \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getAdminIndicateurs() method.
        // 1️⃣ Dernières 5 formations inactives
        $liste_formations = Formations::where('statut', 'INACTIF')
            ->orderByDesc('id')
            ->take(5)
            ->get();

// 2️⃣ Comptes généraux
        $count_formations = Formations::where('statut', 'INACTIF')->count();
        $count_rapports = Rapports::count();
        $count_inscrits = Inscriptions::count();
        $count_auditeurs = Auditeurs::count();

// 3️⃣ Nombre total de panelistes par code formation
        $count_panelistes = DB::table('panelistes')
            ->selectRaw('COUNT(DISTINCT email) as total')
            ->groupBy('code_formation')
            ->get()
            ->sum('total');



// Date de départ : premier jour du mois en cours
        $startDate = Carbon::now()->startOfMonth();

// Générer les 12 derniers mois (format `m` ou `Y-m` si besoin)
        $table_mois = [];

        for ($i = 11; $i >= 0; $i--) {
            $table_mois[] = $startDate->copy()->subMonths($i)->format('m'); // pour juste le mois
            // $table_mois[] = $startDate->copy()->subMonths($i)->format('Y-m'); // si tu veux année-mois
        }

        // Dates de début et fin pour les 12 derniers mois
        $date_fin = Carbon::now()->format('Ym'); // format YYYYMM
        $date_debut = Carbon::now()->subMonths(11)->format('Y/m'); // format YYYY/MM

// Requête pour les paiements des 12 derniers mois
        $date_fin = Carbon::now()->format('Ym'); // YYYYMM
        $date_debut = Carbon::now()->subMonths(11)->format('Y/m'); // YYYY/MM

        $sql = "SELECT
    DATE_FORMAT(p.date_traitement, '%Y/%m') as dates,
    SUM(p.montant_paye) as montant_total,
    SUM(CASE WHEN p.id_modepaiement = 1 THEN p.montant_paye ELSE 0 END) as montant_total_kkiapay,
    SUM(CASE WHEN p.id_modepaiement = 3 THEN p.montant_paye ELSE 0 END) as montant_total_cinetpay,
    SUM(CASE WHEN p.id_modepaiement = 2 THEN p.montant_paye ELSE 0 END) as montant_total_fedapay
FROM (
    SELECT montant_paye, id_modepaiement, date_traitement
    FROM paiements
    WHERE type != 'DSC' AND statut_traitement = 'VALIDER'

    UNION ALL

    SELECT montant_paye, modepaiement_id as id_modepaiement, updated_at as date_traitement
    FROM paiements_tranches
    WHERE statut_traitement = 'VALIDER'

    UNION ALL

    SELECT montant_paye, modepaiement_id as id_modepaiement, updated_at as date_traitement
    FROM detailspaiements
    WHERE statut_traitement = 'VALIDER'
) p
WHERE DATE_FORMAT(p.date_traitement, '%Y/%m') BETWEEN '$date_debut' AND '$date_fin'
GROUP BY dates
ORDER BY dates
";

        $paiement_douze_dernier_mois = DB::select($sql);

        // 1️⃣ Récupérer les paiements rejetés
        $liste_paiement_rejete = DB::table('paiements')
            ->join('auditeurs', 'auditeurs.email', '=', 'paiements.email')
            ->join('modepaiements', 'modepaiements.id', '=', 'paiements.id_modepaiement')
            ->select(
                'paiements.commentaire',
                'auditeurs.nom',
                'auditeurs.prenoms',
                'paiements.telephone',
                'paiements.email',
                'paiements.reference',
                'paiements.montant_total',
                'paiements.num_transaction',
                'paiements.updated_at',
                'modepaiements.libelle as mode_paiement'
            )
            ->where('paiements.statut_traitement', 'REJETER')
            ->orderByDesc('paiements.id')
            ->get();

        $nbr_paiement_rejete = $liste_paiement_rejete->count();


// 2️⃣ Paiements en attente (non partiels)
        $nbre_liste_paiement = DB::table('paiements')
            ->leftJoin('auditeurs', 'auditeurs.email', '=', 'paiements.email')
            ->join('modepaiements', 'modepaiements.id', '=', 'paiements.id_modepaiement')
            ->where('paiements.statut_traitement', 'ATTENTE')
            ->where('paiements.montant_restant', '!=', 0)
            ->where('paiements.type', '!=', 'dsc')
            ->where('paiements.is_paiement_partiel', false)
            ->whereNotNull('paiements.num_transaction')
            ->count();

        $total_montant_liste_paiement = DB::table('paiements')
            ->where('statut_traitement', 'ATTENTE')
            ->where('montant_restant', '!=', 0)
            ->where('type', '!=', 'dsc')
            ->where('is_paiement_partiel', false)
            ->whereNotNull('num_transaction')
            ->sum('montant_restant');

        $nbr_paiement_attente = $nbre_liste_paiement;


// 3️⃣ Paiements partiels en cours
        $liste_paiement_partiel_encours = DB::table('detailspaiements')
            ->leftJoin('auditeurs', 'auditeurs.email', '=', 'detailspaiements.email')
            ->leftJoin('paiements', 'paiements.reference', '=', 'detailspaiements.reference_paiement')
            ->join('modepaiements', 'modepaiements.id', '=', 'detailspaiements.modepaiement_id')
            ->where('detailspaiements.statut_paiement', 'PAYE')
            ->where('detailspaiements.statut_traitement', 'ATTENTE')
            ->where('detailspaiements.is_paiement_partiel', true)
            ->get([
                'auditeurs.nom',
                'auditeurs.prenoms',
                'detailspaiements.email',
                'detailspaiements.id',
                'detailspaiements.reference_paiement',
                'modepaiements.libelle as mode_paiement',
                'detailspaiements.created_at',
                'detailspaiements.updated_at',
                'paiements.type',
                'detailspaiements.reference_paiement_partiel'
            ]);

        $nbr_paiement_attente += $liste_paiement_partiel_encours->count();

// Calcul du total des paiements partiels
        $totalPpartiel = $liste_paiement_partiel_encours->sum(function($item) {
            return getMontantFormation($item->id);
        });


// 4️⃣ Paiements partiels DSC
        $liste_paiement_partiel_dsc = DB::table('paiements_tranches')
            ->leftJoin('detailspaiements', 'detailspaiements.id', '=', 'paiements_tranches.details_paiements_id')
            ->leftJoin('auditeurs', 'auditeurs.email', '=', 'detailspaiements.email')
            ->join('modepaiements', 'modepaiements.id', '=', 'paiements_tranches.modepaiement_id')
            ->where('paiements_tranches.statut_traitement','ATTENTE')
            ->where('paiements_tranches.montant_paye', '!=', 0)
            ->whereNotNull('paiements_tranches.num_transaction')
            ->select(
                DB::raw("SUM(paiements_tranches.montant_paye) as montant_paye"),
                DB::raw("MAX(auditeurs.nom) as nom"),
                DB::raw("MAX(auditeurs.prenoms) as prenoms"),
                DB::raw("MAX(auditeurs.telephone) as telephone"),
                DB::raw("MAX(auditeurs.email) as email"),
                'paiements_tranches.reference',
                DB::raw("MAX(paiements_tranches.num_transaction) as num_transaction"),
                DB::raw("MAX(paiements_tranches.updated_at) as updated_at"),
                DB::raw("MAX(modepaiements.libelle) as mode_paiement"),
                DB::raw("MAX(paiements_tranches.id) as max_id")
            )
            ->groupBy('paiements_tranches.reference')
            ->orderByDesc('max_id')
            ->get();

        $nbr_paiement_attente += count($liste_paiement_partiel_dsc);

        $total_paiement_partiel_dsc = 0;
        foreach ($liste_paiement_partiel_dsc as $data) {
            $total_paiement_partiel_dsc += $data->montant_paye;
        }

        $mt_paiement_attente = $total_montant_liste_paiement + $totalPpartiel + $total_paiement_partiel_dsc;

        // 1️⃣ Définir les dates pour les 90 derniers jours
        $date_now = date('Y-m-d', strtotime('-90 days'));
        $date_today = date('Y-m-d');

// 2️⃣ Montant des commandes en attente (hors DSC)
        $liste_commandes = DB::table('paiements')
            ->where("type", "!=", "DSC")
            ->where("is_paiement_partiel", false)
            ->whereNull('num_transaction')
            ->whereBetween('created_at', [$date_now, $date_today])
            ->sum('montant_restant');

// 3️⃣ Montant des commandes en attente (DSC)
        $liste_commandes_dsc = DB::table('paiements')
            ->where("type", "=", "DSC")
            ->whereNull('num_transaction')
            ->whereBetween('created_at', [$date_now, $date_today])
            ->sum('montant_restant');

// Total commandes en attente
        $mt_commandes = $liste_commandes + $liste_commandes_dsc;

// 4️⃣ Paiements validés (hors DSC et paiements simples)
        $liste_paiement_valider_mt = DB::table('paiements')
            ->where('statut_traitement', 'VALIDER')
            ->where("is_paiement_partiel", false)
            ->where('type', '!=', 'DSC')
            ->sum('montant_paye');

// 5️⃣ Paiements partiels validés
        $liste_paiement_partiel_valider_mt = DB::table('detailspaiements')
            ->where('statut_traitement', 'VALIDER')
            ->where("is_paiement_partiel", true)
            ->sum('montant_paye');

// 6️⃣ Paiements DSC validés
        $liste_paiement_valider_mt_dsc = DB::table('paiements_tranches')
            ->where('statut_traitement', 'VALIDER')
            ->sum('montant_paye');

// Total paiements validés
        $mt_paiement_valider = $liste_paiement_valider_mt + $liste_paiement_partiel_valider_mt + $liste_paiement_valider_mt_dsc;

// 7️⃣ Paiements en ligne (hors DSC) - kkiapay et cinetpay
        $liste_paiement_enligne_mt = DB::table('paiements')
            ->join('modepaiements', 'modepaiements.id', '=', 'paiements.id_modepaiement')
            ->where("is_paiement_partiel", false)
            ->where('statut_traitement', 'VALIDER')
            ->where('type', '!=', 'DSC')
            ->whereIn('modepaiements.id', [1,2,3]) // kkiapay, fedapay & cinetpay
            ->sum('montant_paye');

// 8️⃣ Paiements partiels en ligne
        $liste_paiement_partiel_enligne_mt = DB::table('detailspaiements')
            ->join('modepaiements', 'modepaiements.id', '=', 'detailspaiements.modepaiement_id')
            ->where("is_paiement_partiel", true)
            ->where('statut_traitement', 'VALIDER')
            ->whereIn('modepaiements.id', [1,2,3])
            ->sum('montant_paye');

// 9️⃣ Paiements DSC en ligne
        $liste_paiement_enligne_mt_dsc = DB::table('paiements_tranches')
            ->join('modepaiements', 'modepaiements.id', '=', 'paiements_tranches.modepaiement_id')
            ->where('statut_traitement', 'VALIDER')
            ->whereIn('modepaiements.id', [1,2,3])
            ->sum('montant_paye');

// Total paiements en ligne
        $mt_paiement_enligne = $liste_paiement_enligne_mt + $liste_paiement_partiel_enligne_mt + $liste_paiement_enligne_mt_dsc;

// 10️⃣ Paiements rejetés (hors DSC)
        $mt_paiement_rejeter = DB::table('paiements')
            ->where('statut_traitement', 'REJETER')
            ->where('type', '!=', 'DSC')
            ->sum('montant_total');

        $montant_total_valider = 0;
        $date_fin_mt = date('Ym');

// Récupérer le montant total des paiements validés pour le mois en cours
        $encaissement_valider = DB::table('paiements')
            ->selectRaw('SUM(montant_paye) as montant_total')
            ->where('statut_traitement', 'VALIDER')
            ->where('type', '!=', 'DSC')
            ->where('is_paiement_partiel', 0)
            ->whereRaw("DATE_FORMAT(date_traitement,'%Y%m') = ?", [$date_fin_mt])
            ->unionAll(
                DB::table('paiements_tranches')
                    ->selectRaw('SUM(montant_paye) as montant_total')
                    ->where('statut_traitement', 'VALIDER')
                    ->whereRaw("DATE_FORMAT(updated_at,'%Y%m') = ?", [$date_fin_mt])
            )
            ->unionAll(
                DB::table('detailspaiements')
                    ->selectRaw('SUM(montant_paye) as montant_total')
                    ->where('statut_traitement', 'VALIDER')
                    ->where('is_paiement_partiel', 1)
                    ->whereRaw("DATE_FORMAT(updated_at,'%Y%m') = ?", [$date_fin_mt])
            )
            ->get();

// Somme totale
        foreach ($encaissement_valider as $valider) {
            $montant_total_valider += $valider->montant_total;
        }

        $montant_total_rejeter = 0;
        $montant_total_attente = 0;
        $montant_total_commandes = 0;
        $date_fin_mt = date('Ym');

// ------------------------
// Encaissements rejetés pour le mois en cours
// ------------------------
        $encaissement_rejeter = DB::table('paiements')
            ->selectRaw('SUM(montant_total) as montant_total')
            ->where('statut_traitement', 'REJETER')
            ->where('type', '!=', 'DSC')
            ->whereRaw("DATE_FORMAT(date_traitement,'%Y%m') = ?", [$date_fin_mt])
            ->unionAll(
                DB::table('paiements_tranches')
                    ->selectRaw('SUM(montant_paye) as montant_total')
                    ->where('statut_traitement', 'REJETER')
                    ->whereRaw("DATE_FORMAT(updated_at,'%Y%m') = ?", [$date_fin_mt])
            )
            ->get();

        foreach ($encaissement_rejeter as $rejeter) {
            $montant_total_rejeter += $rejeter->montant_total;
        }

// ------------------------
// Encaissements en attente pour le mois en cours
// ------------------------
        $encaissement_en_attente = DB::table('paiements')
            ->selectRaw('SUM(montant_paye) as montant_total')
            ->where('statut_traitement', 'ATTENTE')
            ->where('type', '!=', 'DSC')
            ->where('is_paiement_partiel', 0)
            ->whereRaw("DATE_FORMAT(date_traitement,'%Y%m') = ?", [$date_fin_mt])
            ->unionAll(
                DB::table('paiements_tranches')
                    ->selectRaw('SUM(montant_paye) as montant_total')
                    ->where('statut_traitement', 'ATTENTE')
                    ->whereRaw("DATE_FORMAT(updated_at,'%Y%m') = ?", [$date_fin_mt])
            )
            ->unionAll(
                DB::table('detailspaiements')
                    ->selectRaw('SUM(montant_paye) as montant_total')
                    ->where('statut_traitement', 'ATTENTE')
                    ->where('is_paiement_partiel', 1)
                    ->whereRaw("DATE_FORMAT(updated_at,'%Y%m') = ?", [$date_fin_mt])
            )
            ->get();

        foreach ($encaissement_en_attente as $attente) {
            $montant_total_attente += $attente->montant_total;
        }

// ------------------------
// Commandes en attente pour le mois en cours
// ------------------------
        $commandes_en_attente = DB::table('paiements')
            ->selectRaw('SUM(montant_restant) as montant_total')
            ->whereNull('num_transaction')
            ->whereRaw("DATE_FORMAT(created_at,'%Y%m') = ?", [$date_fin_mt])
            ->get();

        foreach ($commandes_en_attente as $attente) {
            $montant_total_commandes += $attente->montant_total;
        }

// ------------------------
// Nombre d'attestations délivrées
// ------------------------
        $nbre_attestations = 0; // à compléter selon ta logique

        // 4️⃣ Préparer le JSON
        return $data = [
            'liste_formations' => $liste_formations,
            'count_formations' => $count_formations,
            'count_rapports' => $count_rapports,
            'count_inscrits' => $count_inscrits,
            'count_auditeurs' => $count_auditeurs,
            'count_panelistes' => $count_panelistes,
            'table_mois' => $table_mois,
            'paiement_douze_dernier_mois' => $paiement_douze_dernier_mois,
            'nbr_paiement_attente' => $nbr_paiement_attente,
            'nbr_paiement_rejete' => $nbr_paiement_rejete,
            'montant_total_valider' => $montant_total_valider,
            'montant_total_rejeter' => $montant_total_rejeter,
            'montant_total_attente' => $montant_total_attente,
            'mt_paiement_valider' => $mt_paiement_valider,
            'mt_paiement_enligne' => $mt_paiement_enligne,
            'mt_paiement_attente' => $mt_paiement_attente,
            'mt_commandes' => $mt_commandes,
            'nbre_attestations' => $nbre_attestations,
            'montant_total_commandes' => $montant_total_commandes
        ];
/*





        $data = [
            'montant_total_commandes' => $montant_total_commandes,
            'liste_formations' => $liste_formations,
            'count_formations' => $count_formations,
            'count_rapports' => $count_rapports,
            'count_inscrits' => $count_inscrits,
            'count_auditeurs' => $count_auditeurs,
            'count_panelistes' => $count_panelistes,
            'table_mois' => $table_mois,
            'paiement_douze_dernier_mois' => $paiement_douze_dernier_mois,
            'nbr_paiement_attente' => $nbr_paiement_attente,
            'nbr_paiement_rejete' => $nbr_paiement_rejete,
            'montant_total_valider' => $montant_total_valider,
            'montant_total_rejeter' => $montant_total_rejeter,
            'montant_total_attente' => $montant_total_attente,
            'mt_paiement_valider' => $mt_paiement_valider,
            'mt_paiement_enligne' => $mt_paiement_enligne,
            'mt_paiement_attente' => $mt_paiement_attente,
            'mt_commandes' => $commandes_en_attente,
            'nbre_attestations' => $nbre_attestations
        ];

        return ResponseHandler::success('ok',$data);*/

    }
}
