<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Mail\DscPaiementAttente;
use App\Mail\NotificationComptable;
use App\Mail\PaiementValideDsc;
use App\Mail\Paiementvalider;
use App\Models\Auditeurs;
use App\Models\Detailpaiementouvrages;
use App\Models\Detailspaiements;
use App\Models\Formations;
use App\Models\FormationsDsc;
use App\Models\FormationsTranches;
use App\Models\Inscriptions;
use App\Models\Modepaiements;
use App\Models\Paiements;
use App\Models\PaiementsTranche;
use App\Models\Reductions;
use App\Models\Tarificationsformation;
use App\Models\Tranches;
use App\Models\User;
use App\Services\contracts\ComptabilitesInterface;
use App\Services\util\GenarateurCodeService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ComptabilitesService implements ComptabilitesInterface
{
    private GenarateurCodeService $genarateurCodeService;

    public function __construct(GenarateurCodeService $genarateurCodeService)
    {
        $this->genarateurCodeService = $genarateurCodeService;
    }

    public function getCommandes(Request $request) //: LengthAwarePaginator
    {
        $date_now = now()->subDays(90)->format('Y-m-d'); // Plus propre que date() + strtotime
        $get_date = now()->format('Y-m-d');

        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        $filterUpdatedAt = $request->input('filter_updated_at');

        // Construire la requête avec le Query Builder (pas de get() trop tôt)
        $query = DB::table('paiements')
            ->leftJoin('auditeurs', 'auditeurs.email', '=', 'paiements.email')
            ->leftJoin('modepaiements', 'modepaiements.id', '=', 'paiements.id_modepaiement')
            ->select(
                'auditeurs.nom',
                'auditeurs.prenoms',
                'paiements.telephone',
                'paiements.email',
                'paiements.reference',
                'paiements.montant_restant',
                'paiements.num_transaction',
                'paiements.updated_at',
                'paiements.type',
                'paiements.id',
                'modepaiements.libelle',
                'paiements.id_modepaiement'
            )
            ->whereNull('paiements.num_transaction')
            ->whereDate('paiements.created_at', '>=', $date_now)
            ->whereDate('paiements.created_at', '<=', $get_date)
            ->where('paiements.declasse', false)
            ->where('paiements.is_paiement_partiel', false)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('paiements_tranches')
                    ->whereRaw('paiements_tranches.reference LIKE CONCAT("%", paiements.reference, "%")');
            });

        // Recherche globale
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('auditeurs.nom', 'like', "%{$search}%")
                    ->orWhere('auditeurs.prenoms', 'like', "%{$search}%")
                    ->orWhere('paiements.telephone', 'like', "%{$search}%")
                    ->orWhere('paiements.reference', 'like', "%{$search}%")
                    ->orWhere('paiements.email', 'like', "%{$search}%")
                    ->orWhere('paiements.type', 'like', "%{$search}%");

                    if (is_numeric(str_replace([' ', ','], '', $search))) {
                        $numericValue = (float)str_replace([' ', ','], '', $search);
                        $q->orWhere('paiements.montant_restant', '=', $numericValue);
                    }
            });
        }

        // Filtre par date de mise à jour
        if ($filterUpdatedAt) {
            $query->whereDate('paiements.updated_at', $filterUpdatedAt);
        }

        // Tri dynamique (sécurise $sortField pour éviter injection SQL !)
        $allowedSortFields = ['id', 'reference', 'montant_restant', 'updated_at', 'type'];
        $sortField = in_array($sortField, $allowedSortFields) ? $sortField : 'id';
        $sortOrder = $sortOrder === 'asc' ? 'asc' : 'desc';

        $query->orderBy("paiements.{$sortField}", $sortOrder);

        // Pagination correcte
        $paginated = $query->paginate($perPage);

        // Calcul de la somme totale sur les éléments paginés (ou sur toute la requête filtrée)
        $somme_totale = $query->sum('paiements.montant_restant');

        // Ajouter la somme dans les meta de la pagination
        //$paginated->additional(['montant_total' => $somme_totale]);
        return [
            'pagination' => $paginated,
            'montant_total' => $somme_totale
        ];
        // return $paginated;
    }

    public function getCommandesOuvrages(Request $request) //: LengthAwarePaginator
    {
        $date_now = now()->subDays(90)->format('Y-m-d'); // Plus propre que date() + strtotime
        $get_date = now()->format('Y-m-d');

        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        $filterUpdatedAt = $request->input('filter_updated_at');

        // Construire la requête avec le Query Builder (pas de get() trop tôt)
        $query = DB::table('paiements')
            ->leftJoin('auditeurs', 'auditeurs.email', '=', 'paiements.email')
            ->leftJoin('modepaiements', 'modepaiements.id', '=', 'paiements.id_modepaiement')
            ->select(
                'auditeurs.nom',
                'auditeurs.prenoms',
                'paiements.telephone',
                'paiements.email',
                'paiements.reference',
                'paiements.montant_restant',
                'paiements.num_transaction',
                'paiements.updated_at',
                'paiements.type',
                'paiements.id',
                'modepaiements.libelle',
                'paiements.id_modepaiement'
            )
            ->whereNull('paiements.num_transaction')
            ->whereDate('paiements.created_at', '>=', $date_now)
            ->whereDate('paiements.created_at', '<=', $get_date)
            ->where('paiements.declasse', false)
            ->where('paiements.is_paiement_partiel', false)
            ->where('paiements.type', 'OUVRAGE')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('paiements_tranches')
                    ->whereRaw('paiements_tranches.reference LIKE CONCAT("%", paiements.reference, "%")');
            });

        // Recherche globale
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('auditeurs.nom', 'like', "%{$search}%")
                    ->orWhere('auditeurs.prenoms', 'like', "%{$search}%")
                    ->orWhere('paiements.telephone', 'like', "%{$search}%")
                    ->orWhere('paiements.reference', 'like', "%{$search}%")
                    ->orWhere('paiements.email', 'like', "%{$search}%")
                    ->orWhere('paiements.type', 'like', "%{$search}%");

                    if (is_numeric(str_replace([' ', ','], '', $search))) {
                        $numericValue = (float)str_replace([' ', ','], '', $search);
                        $q->orWhere('paiements.montant_restant', '=', $numericValue);
                    }
            });
        }

        // Filtre par date de mise à jour
        if ($filterUpdatedAt) {
            $query->whereDate('paiements.updated_at', $filterUpdatedAt);
        }

        // Tri dynamique (sécurise $sortField pour éviter injection SQL !)
        $allowedSortFields = ['id', 'reference', 'montant_restant', 'updated_at', 'type'];
        $sortField = in_array($sortField, $allowedSortFields) ? $sortField : 'id';
        $sortOrder = $sortOrder === 'asc' ? 'asc' : 'desc';

        $query->orderBy("paiements.{$sortField}", $sortOrder);

        // Pagination correcte
        $paginated = $query->paginate($perPage);

        // Calcul de la somme totale sur les éléments paginés (ou sur toute la requête filtrée)
        $somme_totale = $query->sum('paiements.montant_restant');

        // Ajouter la somme dans les meta de la pagination
        //$paginated->additional(['montant_total' => $somme_totale]);
        return [
            'pagination' => $paginated,
            'montant_total' => $somme_totale
        ];
        // return $paginated;
    }

    public function getPaiementsattente(Request $request) //: LengthAwarePaginator
    {
        // TODO: Implement getPaiementsattente() method.
        $perPage   = $request->input('per_page', 50);
        $search    = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');
        $filterDate = $request->input('filter_date');

        /** PAIEMENTS CLASSIQUES */
        /*$classiques = DB::table('paiements')
            ->leftJoin('auditeurs', 'auditeurs.email', '=', 'paiements.email')
            ->leftJoin('modepaiements', 'modepaiements.id', '=', 'paiements.id_modepaiement')
            ->leftJoin('detailspaiements', 'detailspaiements.reference_paiement', '=', 'paiements.reference')
            ->leftJoin('reductions','reductions.id','=','detailspaiements.id_reduction')
            ->where('paiements.statut_paiement', 'EN ATTENTE') 
            ->where('paiements.type', '!=', 'dsc')
            ->where('paiements.is_paiement_partiel', false)
            ->whereNotNull('paiements.num_transaction')
            ->selectRaw("
            paiements.id,
            auditeurs.nom,
            auditeurs.prenoms,
            auditeurs.email,
            paiements.reference, 
            detailspaiements.montant_restant as montant,
            paiements.num_transaction,
            paiements.type,
            paiements.updated_at AS date,
            paiements.reference AS action_ref,
            'classique' AS route_type,
            modepaiements.id AS id_modepaiement,
            modepaiements.libelle AS libelle_modepaiement,
            reductions.id AS id_reduction,
            reductions.pourcentage AS pourcentage,
            0 AS have_reduction
            ")
            ->selectRaw("
                (
                    SELECT i.code_formation
                    FROM inscriptions i
                    WHERE i.code_inscription = paiements.code_inscription
                    LIMIT 1
                ) AS code_formation
            ")
            ->selectRaw("
                (
                    SELECT f.theme
                    FROM formations f
                    WHERE f.code_formation = (
                        SELECT i2.code_formation
                        FROM inscriptions i2
                        WHERE i2.code_inscription = paiements.code_inscription
                        LIMIT 1
                    )
                    LIMIT 1
                ) AS theme
            ");*/

            
        $classiques = DB::table('detailspaiements')
            ->leftJoin('paiements', 'paiements.reference', '=', 'detailspaiements.reference_paiement')
            ->leftJoin('auditeurs', 'auditeurs.email', '=', 'detailspaiements.email')
            ->leftJoin('reductions','reductions.id','=','detailspaiements.id_reduction')
            ->leftJoin('modepaiements', 'modepaiements.id', '=', 'paiements.id_modepaiement')
            ->leftJoin('formations', 'formations.code_formation', '=', 'detailspaiements.code_formation')
            ->where('paiements.statut_paiement', 'EN ATTENTE')
            ->where('detailspaiements.statut_traitement', 'ATTENTE')
            ->where('detailspaiements.montant_restant', '>',0)
            ->where('detailspaiements.is_paiement_partiel', false)
            ->where('paiements.type', '!=', 'dsc')
            ->selectRaw("
            detailspaiements.id,
            auditeurs.nom,
            auditeurs.prenoms,
            auditeurs.email,
            detailspaiements.reference_paiement as reference,
            detailspaiements.montant_restant as montant,
            paiements.num_transaction,
            paiements.type as type,
            detailspaiements.updated_at as date,
            detailspaiements.id as action_ref,
            'classique' as route_type,
            modepaiements.id as id_modepaiement,
            modepaiements.libelle as libelle_modepaiement,
            reductions.id as id_reduction,
            reductions.pourcentage,
            0 as have_reduction,
            formations.code_formation,
            formations.theme
        ");

        /** PAIEMENTS PARTIELS */
        $partiels = DB::table('detailspaiements')
            ->leftJoin('auditeurs', 'auditeurs.email', '=', 'detailspaiements.email')
            ->leftJoin('reductions','reductions.id','=','detailspaiements.id_reduction')
            ->leftJoin('modepaiements', 'modepaiements.id', '=', 'detailspaiements.modepaiement_id')
            ->leftJoin('formations', 'formations.code_formation', '=', 'detailspaiements.code_formation')
            ->where('detailspaiements.statut_paiement', 'PAYE')
            ->where('detailspaiements.statut_traitement', 'ATTENTE')
            ->where('detailspaiements.is_paiement_partiel', true)
            ->selectRaw("
            detailspaiements.id,
            auditeurs.nom,
            auditeurs.prenoms,
            auditeurs.email,
            detailspaiements.reference_paiement as reference,
            detailspaiements.montant_restant as montant,
            detailspaiements.reference_paiement_partiel as num_transaction,
            'FORMATION PARTIEL' as type,
            detailspaiements.updated_at as date,
            detailspaiements.id as action_ref,
            'partiel' as route_type,
            modepaiements.id as id_modepaiement,
            modepaiements.libelle as libelle_modepaiement,
            reductions.id as id_reduction,
            reductions.pourcentage,
            0 as have_reduction,
            formations.code_formation,
            formations.theme
        ");

        /** PAIEMENTS DSC (TRANCHES) */
        $dsc = DB::table('paiements_tranches')
            ->leftJoin('modepaiements', 'modepaiements.id', '=', 'paiements_tranches.modepaiement_id')
            ->leftJoin('detailspaiements', 'detailspaiements.id', '=', 'paiements_tranches.details_paiements_id')
            ->leftJoin('reductions','reductions.id','=','detailspaiements.id_reduction')
            ->leftJoin('formations', 'formations.code_formation', '=', 'detailspaiements.code_formation')
            ->leftJoin('auditeurs', 'auditeurs.email', '=', 'detailspaiements.email')
            ->where('paiements_tranches.statut_traitement', 'ATTENTE')
            ->whereNotNull('paiements_tranches.num_transaction')
            ->groupBy('paiements_tranches.reference')
            ->selectRaw("
            MIN(paiements_tranches.id) as id,
            MAX(auditeurs.nom) as nom,
            MAX(auditeurs.prenoms) as prenoms,
            MAX(auditeurs.email) as email,
            paiements_tranches.reference,
            SUM(paiements_tranches.montant_paye) as montant,
            MAX(paiements_tranches.num_transaction) as num_transaction,
            'DSC' as type,
            MAX(paiements_tranches.updated_at) as date,
            paiements_tranches.reference as action_ref,
            'dsc' as route_type,
            MAX(modepaiements.id) as id_modepaiement,
            MAX(modepaiements.libelle) as libelle_modepaiement,
            MAX(reductions.id) as id_reduction,
            MAX(reductions.pourcentage) as pourcentage,
            MAX(detailspaiements.have_reduction_dsc) as have_reduction,
            MAX(formations.code_formation) as code_formation,
            MAX(formations.theme) as theme
            ");

        /** UNION GLOBAL */
        $unionQuery = $classiques
            ->unionAll($partiels)
            ->unionAll($dsc);

        /** PAGINATION */
        $query = DB::query()->fromSub($unionQuery, 't');
            //->orderBy('date', 'desc')
            //->paginate($perPage);

        /* ==============================
     |  SEARCH GLOBAL
     ============================== */
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('t.nom', 'like', "%{$search}%")
                    ->orWhere('t.prenoms', 'like', "%{$search}%")
                    ->orWhere('t.reference', 'like', "%{$search}%")
                    ->orWhere('t.num_transaction', 'like', "%{$search}%")
                    ->orWhere('t.type', 'like', "%{$search}%")
                    ->orWhere('t.libelle_modepaiement', 'like', "%{$search}%");

                $clean = str_replace([' ', ','], '', $search);
                if (is_numeric($clean)) {
                    $q->orWhere('t.montant', (float) $clean);
                }
            });
        }

        if ($filterDate) {
            $query->whereDate('t.date', $filterDate);
        }


        $allowedSortFields = [
            'id',
            'nom',
            'prenoms',
            'reference',
            'email',
            'montant',
            'num_transaction',
            'type',
            'date',
            'id_modepaiement',
            'libelle_modepaiement',
            'pourcentage',
            'have_reduction'
        ];

        $sortField = in_array($sortField, $allowedSortFields) ? $sortField : 'date';
        $sortOrder = $sortOrder === 'asc' ? 'asc' : 'desc';

        $query->orderBy("t.$sortField", $sortOrder);


        /* ==============================
         |  PAGINATION
         ============================== */
        $paiements = $query->paginate($perPage);

        /** TOTAL GLOBAL */
        $total = (clone $query)->sum('montant'); // collect($paiements->items())->sum('montant');

        return [
            'pagination' => $paiements,
            'montant_total' => $total
        ];


    }

    public function getPaiementsvalides(Request $request) //: LengthAwarePaginator
    {
        // TODO: Implement getPaiementsvalides() method.
        $perPage   = $request->input('per_page', 50);
        $search    = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');
        $filterDate = $request->input('filter_date');

        /** PAIEMENTS CLASSIQUES */
        $classiques = DB::table('paiements')
            ->leftJoin('auditeurs', 'auditeurs.email', '=', 'paiements.email')
            ->leftJoin('modepaiements', 'modepaiements.id', '=', 'paiements.id_modepaiement')
            ->where('statut_traitement', 'VALIDER')
            ->where('paiements.type', '!=', 'dsc')
            ->where('is_paiement_partiel', false)
            ->whereNotNull('num_transaction')
            ->selectRaw("
            paiements.id,
            auditeurs.nom,
            auditeurs.prenoms,
            auditeurs.email,
            paiements.reference,
            paiements.montant_paye as montant,
            paiements.num_transaction,
            paiements.type,
            paiements.updated_at as date,
            paiements.reference as action_ref,
            'classique' as route_type,
            modepaiements.id as id_modepaiement,
            modepaiements.libelle as libelle_modepaiement,
            0 as id_reduction,
            0 as pourcentage,
            0 as have_reduction,
            '' as code_formation,
            '' as theme
        ");

        /** PAIEMENTS PARTIELS */
        $partiels = DB::table('detailspaiements')
            ->leftJoin('auditeurs', 'auditeurs.email', '=', 'detailspaiements.email')
            ->leftJoin('reductions','reductions.id','=','detailspaiements.id_reduction')
            ->leftJoin('modepaiements', 'modepaiements.id', '=', 'detailspaiements.modepaiement_id')
            ->leftJoin('formations', 'formations.code_formation', '=', 'detailspaiements.code_formation')
            ->where('detailspaiements.statut_paiement', 'PAYE')
            ->where('detailspaiements.statut_traitement', 'VALIDER')
            ->where('detailspaiements.is_paiement_partiel', true)
            ->selectRaw("
            detailspaiements.id,
            auditeurs.nom,
            auditeurs.prenoms,
            auditeurs.email,
            detailspaiements.reference_paiement as reference,
            detailspaiements.montant_paye as montant,
            detailspaiements.reference_paiement_partiel as num_transaction,
            'FORMATION PARTIEL' as type,
            detailspaiements.updated_at as date,
            detailspaiements.id as action_ref,
            'partiel' as route_type,
            modepaiements.id as id_modepaiement,
            modepaiements.libelle as libelle_modepaiement,
            reductions.id as id_reduction,
            reductions.pourcentage,
            0 as have_reduction,
            formations.code_formation,
            formations.theme
        ");

        /** PAIEMENTS DSC (TRANCHES) */
        $dsc = DB::table('paiements_tranches')
            ->leftJoin('modepaiements', 'modepaiements.id', '=', 'paiements_tranches.modepaiement_id')
            ->leftJoin('detailspaiements', 'detailspaiements.id', '=', 'paiements_tranches.details_paiements_id')
            ->leftJoin('reductions','reductions.id','=','detailspaiements.id_reduction')
            ->leftJoin('formations', 'formations.code_formation', '=', 'detailspaiements.code_formation')
            ->leftJoin('auditeurs', 'auditeurs.email', '=', 'detailspaiements.email')
            ->where('paiements_tranches.statut_traitement', 'VALIDER')
            ->whereNotNull('paiements_tranches.num_transaction')
            ->groupBy('paiements_tranches.reference')
            ->selectRaw("
            MIN(paiements_tranches.id) as id,
            MAX(auditeurs.nom) as nom,
            MAX(auditeurs.prenoms) as prenoms,
            MAX(auditeurs.email) as email,
            paiements_tranches.reference,
            SUM(paiements_tranches.montant_paye) as montant,
            MAX(paiements_tranches.num_transaction) as num_transaction,
            'DSC' as type,
            MAX(paiements_tranches.updated_at) as date,
            paiements_tranches.reference as action_ref,
            'dsc' as route_type,
            MAX(modepaiements.id) as id_modepaiement,
            MAX(modepaiements.libelle) as libelle_modepaiement,
            MAX(reductions.id) as id_reduction,
            MAX(reductions.pourcentage) as pourcentage,
            MAX(detailspaiements.have_reduction_dsc) as have_reduction,
            MAX(formations.code_formation) as code_formation,
            MAX(formations.theme) as theme
            ");

        /** UNION GLOBAL */
        $unionQuery = $classiques
            ->unionAll($partiels)
            ->unionAll($dsc);

        /** PAGINATION */
        $query = DB::query()->fromSub($unionQuery, 't');
        //->orderBy('date', 'desc')
        //->paginate($perPage);

        /* ==============================
     |  SEARCH GLOBAL
     ============================== */
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('t.nom', 'like', "%{$search}%")
                    ->orWhere('t.prenoms', 'like', "%{$search}%")
                    ->orWhere('t.reference', 'like', "%{$search}%")
                    ->orWhere('t.num_transaction', 'like', "%{$search}%")
                    ->orWhere('t.type', 'like', "%{$search}%")
                    ->orWhere('t.libelle_modepaiement', 'like', "%{$search}%");

                $clean = str_replace([' ', ','], '', $search);
                if (is_numeric($clean)) {
                    $q->orWhere('t.montant', (float) $clean);
                }
            });
        }

        if ($filterDate) {
            $query->whereDate('t.date', $filterDate);
        }


        $allowedSortFields = [
            'id',
            'nom',
            'prenoms',
            'reference',
            'email',
            'montant',
            'num_transaction',
            'type',
            'date',
            'id_modepaiement',
            'libelle_modepaiement',
            'pourcentage',
            'have_reduction'
        ];

        $sortField = in_array($sortField, $allowedSortFields) ? $sortField : 'date';
        $sortOrder = $sortOrder === 'asc' ? 'asc' : 'desc';

        $query->orderBy("t.$sortField", $sortOrder);


        /* ==============================
         |  PAGINATION
         ============================== */
        $paiements = $query->paginate($perPage);

        /** TOTAL GLOBAL */
        $total = (clone $query)->sum('montant'); // collect($paiements->items())->sum('montant');

        return [
            'pagination' => $paiements,
            'montant_total' => $total
        ];

    }

    public function getPaiementsOuvragesvalides(Request $request) //: LengthAwarePaginator
    {
        // TODO: Implement getPaiementsvalides() method.
        $perPage   = $request->input('per_page', 50);
        $search    = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');
        $filterDate = $request->input('filter_date');

        /** PAIEMENTS CLASSIQUES */
        $classiques = DB::table('paiements')
            ->leftJoin('auditeurs', 'auditeurs.email', '=', 'paiements.email')
            ->leftJoin('modepaiements', 'modepaiements.id', '=', 'paiements.id_modepaiement')
            ->where('statut_traitement', 'VALIDER')
            ->where('paiements.type', '=', 'OUVRAGE')
            ->where('is_paiement_partiel', false)
            ->whereNotNull('num_transaction')
            ->selectRaw("
            paiements.id,
            auditeurs.nom,
            auditeurs.prenoms,
            auditeurs.email,
            paiements.reference,
            paiements.montant_paye as montant,
            paiements.num_transaction,
            paiements.type,
            paiements.updated_at as date,
            paiements.reference as action_ref,
            'classique' as route_type,
            modepaiements.id as id_modepaiement,
            modepaiements.libelle as libelle_modepaiement,
            0 as id_reduction,
            0 as pourcentage,
            0 as have_reduction,
            '' as code_formation,
            '' as theme
        "); 

        /** UNION GLOBAL */
        $unionQuery = $classiques;

        /** PAGINATION */
        $query = DB::query()->fromSub($unionQuery, 't');
        //->orderBy('date', 'desc')
        //->paginate($perPage);

        /* ==============================
     |  SEARCH GLOBAL
     ============================== */
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('t.nom', 'like', "%{$search}%")
                    ->orWhere('t.prenoms', 'like', "%{$search}%")
                    ->orWhere('t.reference', 'like', "%{$search}%")
                    ->orWhere('t.num_transaction', 'like', "%{$search}%")
                    ->orWhere('t.type', 'like', "%{$search}%")
                    ->orWhere('t.libelle_modepaiement', 'like', "%{$search}%");

                $clean = str_replace([' ', ','], '', $search);
                if (is_numeric($clean)) {
                    $q->orWhere('t.montant', (float) $clean);
                }
            });
        }

        if ($filterDate) {
            $query->whereDate('t.date', $filterDate);
        }


        $allowedSortFields = [
            'id',
            'nom',
            'prenoms',
            'reference',
            'email',
            'montant',
            'num_transaction',
            'type',
            'date',
            'id_modepaiement',
            'libelle_modepaiement',
            'pourcentage',
            'have_reduction'
        ];

        $sortField = in_array($sortField, $allowedSortFields) ? $sortField : 'date';
        $sortOrder = $sortOrder === 'asc' ? 'asc' : 'desc';

        $query->orderBy("t.$sortField", $sortOrder);


        /* ==============================
         |  PAGINATION
         ============================== */
        $paiements = $query->paginate($perPage);

        /** TOTAL GLOBAL */
        $total = (clone $query)->sum('montant'); // collect($paiements->items())->sum('montant');

        return [
            'pagination' => $paiements,
            'montant_total' => $total
        ];

    }

    public function getDetailsCommandeFormation(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getDetailsCommandeFormation() method.
        $details_commande = DB::table('detailspaiements')
            ->join('paiements', 'paiements.reference', '=', 'detailspaiements.reference_paiement')
            ->leftjoin('reductions', 'reductions.id', '=', 'detailspaiements.id_reduction')
            ->join('tarificationsformations', 'tarificationsformations.id', '=', 'detailspaiements.id_tarification_formation')
            ->join('grilletarifaires', 'tarificationsformations.id_grilletarifaire', '=', 'grilletarifaires.id')
            ->join('formations', 'formations.code_formation', '=', 'tarificationsformations.code_formation')
            ->select('detailspaiements.have_reduction_dsc','detailspaiements.montant_restant','detailspaiements.montant_paye','formations.code_formation', 'formations.theme', 'formations.date_debut','grilletarifaires.montant','reductions.pourcentage','detailspaiements.preuve_path')
            ->where('detailspaiements.reference_paiement',$request->get('reference_paiement'))
            ->orderBy('detailspaiements.id', 'DESC')
            ->get();

        return  ResponseHandler::success("ok",$details_commande);
    }

    public function getDetailsCommandeOuvrage(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getDetailsCommandeOuvrage() method.
        $paiement = Paiements::whereReference($request->get('reference_paiement'))->first();
        $details_commande = Detailpaiementouvrages::join('ouvrages','ouvrages.id','=','detailpaiementouvrages.ouvrage_id')
        ->where("paiementouvrage_id",$paiement->id)
            ->get(['detailpaiementouvrages.id','detailpaiementouvrages.reference','detailpaiementouvrages.montant','ouvrages.libelle']);

        return  ResponseHandler::success("ok",$details_commande);
    }

    public function getDetailsCommandeDsc(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getDetailsCommandeDsc() method.
        $details_commande = DB::table('detailspaiements')
            ->join('paiements', 'paiements.reference', '=', 'detailspaiements.reference_paiement')
            ->leftjoin('reductions', 'reductions.id', '=', 'detailspaiements.id_reduction')
            ->join('tarificationsformations', 'tarificationsformations.id', '=', 'detailspaiements.id_tarification_formation')
            ->join('grilletarifaires', 'tarificationsformations.id_grilletarifaire', '=', 'grilletarifaires.id')
            ->join('formations', 'formations.code_formation', '=', 'tarificationsformations.code_formation')
            ->select('formations.code_formation', 'formations.theme', 'formations.date_debut','grilletarifaires.type_tarif','grilletarifaires.montant','detailspaiements.montant_restant','detailspaiements.have_reduction_dsc','reductions.pourcentage','detailspaiements.preuve_path')
            ->where('detailspaiements.reference_paiement',$request->get('reference_paiement'))
            ->orderBy('detailspaiements.id', 'DESC')
            ->get();

        return  ResponseHandler::success("ok",$details_commande);
    }

    public function storeDeplacerCommandeFormation(Request $request): \Illuminate\Http\JsonResponse
    {
        // update statut paiement
        $getPaiement = Paiements::where("reference",$request->reference)->get()->first();
        $paiement = Paiements::find($getPaiement->id);
        $user = auth('api')->user();

        if ($paiement->montant_total == 0) {
            # code...
            if ($request->statut_traitement == "VALIDER") {
                # code...
                $montant_paye = $paiement->montant_paye + $paiement->montant_restant;
                $montant_restant = $paiement->montant_total - $montant_paye;

                $paiement->setAttribute("statut_paiement","PAYE");
                $paiement->setAttribute("commentaire",$request->commentaire);
                $paiement->setAttribute("statut_traitement",trim($request->statut_traitement));
                $paiement->setAttribute('agent_traiteur', $user->id);
                $paiement->setAttribute('date_traitement', new \DateTime());
                $paiement->setAttribute("montant_paye",$montant_paye);
                $paiement->setAttribute("montant_restant",$montant_restant);
                $paiement->update();

                // send mail paiement validé
                // update table details paiement
                $details_paiement = Detailspaiements::where('reference_paiement',$request->reference)->get();

                foreach($details_paiement as $detail){
                    $upDetail = Detailspaiements::find($detail->id);
                    $upDetail->setAttribute('statut_traitement',trim($request->statut_traitement));
                    $upDetail->setAttribute('statut_paiement', "PAYE");
                    $upDetail->setAttribute("montant_paye",$upDetail->montant_restant);
                    $upDetail->setAttribute("montant_restant",0);
                    $upDetail->update();
                }

            } else {
                # code...
                $paiement->setAttribute("statut_paiement","EN ATTENTE");
                $paiement->setAttribute("statut_traitement","ATTENTE");
                $paiement->setAttribute("commentaire",$request->commentaire);
                $paiement->update();
            }

            return  ResponseHandler::success("Commande déplacée avec succès");

        }
        //######################################## montant supérieur à 0 ##############################################################################

        if ($request->statut_traitement == "VALIDER") {

            // vérifier si les champs mode_paiement sont disponible
            if(!empty($request->modepaiement_id)){

                $montant_paye = $paiement->montant_paye + $paiement->montant_restant;
                $montant_restant = $paiement->montant_total - $montant_paye;

                # code...
                $paiement->setAttribute("num_transaction",trim($request->num_transaction));
                $paiement->setAttribute("statut_transaction",trim($request->num_transaction));
                $paiement->setAttribute("id_modepaiement",$request->modepaiement_id);
                $paiement->setAttribute("statut_paiement","PAYE");
                $paiement->setAttribute("statut_traitement",trim($request->statut_traitement));
                $paiement->setAttribute('agent_traiteur', $user->id);
                $paiement->setAttribute('date_traitement', new \DateTime());
                $paiement->setAttribute("montant_paye",$montant_paye);
                $paiement->setAttribute("montant_restant",$montant_restant);
                $paiement->update();

                // send mail paiement validé
                // update table details paiement
                $details_paiement = Detailspaiements::where('reference_paiement',$request->reference)->get();

                foreach($details_paiement as $detail){
                    $upDetail = Detailspaiements::find($detail->id);
                    $upDetail->setAttribute('statut_traitement',trim($request->statut_traitement));
                    $upDetail->setAttribute('statut_paiement', "PAYE");
                    $upDetail->setAttribute("montant_paye",$upDetail->montant_restant);
                    $upDetail->setAttribute("montant_restant",0);
                    $upDetail->update();
                }
                $auditeur = Auditeurs::where("email",$paiement->email)->first();
                // envoie de mail pour la reférence de la facture
                $modepaiement = Modepaiements::find($request->modepaiement_id);
                $contenu = [
                    'nom' => $auditeur->nom ,
                    'prenoms' => $auditeur->prenoms,
                    'reference_paiement' => $request->reference,
                    'subject' => 'TRAITEMENT DE VOTRE PAIEMENT',
                    'commentaire' => "Paiement validé",
                    'email'=>$paiement->email,
                    'mode_paiement'=>$modepaiement->libelle,
                    'montant'=>$paiement->montant_total,
                    'type'=>$paiement->type,
                    'url_recu' => $this->genarateurCodeService->generateUrlSigned('paiements.recu',['reference' => $request->reference])
                ];

                try {
                    # code...
                    Mail::to(trim($paiement->email))->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new Paiementvalider($contenu));

                } catch (\Exception $e) {
                    //throw $th;
                }
            }else{
                // le champ mode de paiement est obligatoire
                return  ResponseHandler::error("Erreur : le champ mode de paiement est obligatoire.");
            }



        } else {

            if(!empty($request->reduction_id)){

            }else{

                $paiement->setAttribute("num_transaction",$request->num_transaction);
                $paiement->setAttribute("statut_transaction",$request->num_transaction);
            }
            $paiement->setAttribute("id_modepaiement",$request->modepaiement_id);
            $paiement->setAttribute("statut_paiement","EN ATTENTE");
            $paiement->setAttribute("statut_traitement","ATTENTE");
            $paiement->update();

            // application réduction

            //################# mise à jour reduction #################
            // mise à jour tarif formation
            if(!empty($request->reduction_id)){

                if($paiement->montant_total > 0){
                    // check details paiements

                    $chec_details_paiements = Detailspaiements::where("reference_paiement",$paiement->reference)
                        ->exists();

                    if($chec_details_paiements){
                        $detail_paiement = Detailspaiements::where("reference_paiement",$paiement->reference)
                            ->get();

                        # code...
                        $update_montant_paiement = $paiement->montant_total ;
                        $reduction = Reductions::find($request->reduction_id);
                        // application reduction au montant existant
                        $montant_paiement_reduction = $update_montant_paiement - ($update_montant_paiement*($reduction->pourcentage/100));

                        // update table details paiement
                        foreach ($detail_paiement as $details) {
                            # code...
                            $get_detail = Detailspaiements::find($details->id);
                            $get_detail->setAttribute("id_reduction",$request->reduction_id);
                            $get_detail->update();
                        }
                        // update table paiement
                        $paiement->setAttribute("montant_total",$montant_paiement_reduction);
                        $paiement->update();

                    }
                }
            }
            // ################# end mise à jour ####################
        }

        return  ResponseHandler::success("Commande déplacée avec succès");
    }

    public function storeDeplacerCommandeDsc(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement storeDeplacerCommandeDsc() method.
        // initialisé la vraiable permettant de savoir si le montant payé couvre une partie des frais de scolarité ou bien entierement
        $user = auth('api')->user();
        $have_frais_scolarite = false;
        $solde_frais_scolarite = false;
        $frais_scolarite_paye = $request->montant_total - $request->get_frais_inscription;
        $frais_inscription_paye = $request->get_frais_inscription;
        $montant_attendu = $request->get_frais_inscription + $request->get_frais_scolarite;
        $reference_paiement_partiel = "";
        // vérifier si le montant payé est supérieur ou égale au frais d'inscription
        if ($request->montant_total < $request->get_frais_inscription) {
            # code... alert
            return  ResponseHandler::error("Le montant payé ".$request->montant_total." ne couvre pas les frais d'inscription qui s'élève à ".$request->get_frais_inscription);

        }
        //################# vérification des conditions côté serveur ###########
        if ($request->montant_total != "" && $request->montant_total > 0) {
            $montant_paye = $request->montant_total;
            if ($montant_paye > $request->get_frais_inscription) {
                $diff_montant = $montant_paye - $request->get_frais_inscription;
                if ($request->get_reduction == 1) { // tranche unique
                    if ($diff_montant > $request->get_frais_scolarite) {
                        // alert attention le montant payé est supérieur au montant attendu
                        return  ResponseHandler::error("Attention le montant payé $montant_paye est supérieur au montant attendu $montant_attendu");

                    } else if($diff_montant == $request->get_frais_scolarite) {
                        // ok pour paiement
                        $have_frais_scolarite = true;
                        $solde_frais_scolarite = true;
                    }else{
                        // alert vous devez solder les frais de scolarité
                        return  ResponseHandler::error("Vous devez solder les frais de scolarité");

                    }
                } else { // double tranche
                    if ($diff_montant > $request->get_frais_scolarite) {
                        // alert attention le montant payé est supérieur au montant attendu
                        // disable btn enregistrer
                        return  ResponseHandler::error("Attention le montant payé $montant_paye est supérieur au montant attendu $montant_attendu");

                    } else if($diff_montant == $request->get_frais_scolarite) {
                        // ok pour paiement

                        $have_frais_scolarite = true;
                        $solde_frais_scolarite = true;

                    }else if($diff_montant == ($request->get_frais_scolarite/2)){ // 50%
                        // ok pour paiement

                        $have_frais_scolarite = true;
                        $solde_frais_scolarite = false;
                    }else { // < 50%
                        // alert vous devez solder les frais de scolarité
                        return  ResponseHandler::error("Vous devez solder 50% des frais de scolarité.");

                    }
                }
            } else if($montant_paye == $request->get_frais_inscription) {
                // ok pour paiement des frais d'inscription
            }else{
                return  ResponseHandler::error("Vous devez solder les frais d\'inscription.");

            }
        } else {

            return  ResponseHandler::error("Vous devez solder les frais d\'inscription.");
        }

        //################# fin vérification des conditions côté serveur #########



        // update statut paiement
        $getPaiement = Paiements::where("reference",$request->reference)->get()->first();
        $paiement = Paiements::find($getPaiement->id);
        $reference_paiement_partiel = $paiement->reference;
        //######################################## montant supérieur à 0 ################

        if ($request->statut_traitement == "VALIDER") {
            # code...
            // 1- vérifier s'il s'agit de tranche unique ou double tranche
            if ($request->get_reduction == 1) { // tranche unique
                # code...

            } else { // double tranche
                # code...
            }


            if ($have_frais_scolarite == true && $solde_frais_scolarite == true) {
                # code...
                $paiement->setAttribute("num_transaction",trim($request->num_transaction));
                $paiement->setAttribute("statut_paiement","PAYE");
                $paiement->setAttribute("statut_transaction",trim($request->num_transaction));
                $paiement->setAttribute("statut_traitement",trim($request->statut_traitement));
            }
            // update montant payé dans la table paiement, detailspaiement et paiementstranchez
            $update_montant_restant = $paiement->montant_restant - $request->montant_total;
            $update_montant_paye = $paiement->montant_paye + $request->montant_total;
            //
            $paiement->setAttribute("montant_paye",$update_montant_paye);
            $paiement->setAttribute("montant_restant",$update_montant_restant);
            $paiement->setAttribute("id_modepaiement",$request->modepaiement_id);
            $paiement->setAttribute('agent_traiteur', $user->id);
            $paiement->setAttribute('date_traitement', new \DateTime());
            $paiement->update();

            // update table details paiements
            if($request->montant_paye == $montant_attendu){ // solder
                $detailsPaiements = Detailspaiements::where('code_inscription',$paiement->code_inscription)->get();
                foreach($detailsPaiements as $item){
                    $detailPaiement = Detailspaiements::find($item->id);
                    $detailPaiement->setAttribute('montant_paye',$item->montant_restant);
                    $detailPaiement->setAttribute('montant_restant',$item->montant_restant-$detailPaiement->montant_restant);
                    $detailPaiement->setAttribute('statut_traitement',trim($request->statut_traitement));
                    $detailPaiement->setAttribute('statut_paiement', "PAYE");
                    $detailPaiement->update();

                    // enregistrement de la tranche
                    if($item->getTarification->getGrilletarifaire->type_tarif != 'INSCRIPTION'){
                        //get tranche
                        $tranche = Tranches::where('code_inscription',$paiement->code_inscription)->first();
                        $tranche_paiement = new PaiementsTranche();
                        $tranche_paiement->setAttribute('details_paiements_id',$item->id);
                        $tranche_paiement->setAttribute('montant_paye',$item->montant_restant);
                        $tranche_paiement->setAttribute('montant_restant',$item->montant_restant-$detailPaiement->montant_restant);
                        $tranche_paiement->setAttribute('reference',$paiement->reference);
                        //
                        $tranche_paiement->setAttribute("num_transaction",trim($request->num_transaction));
                        $tranche_paiement->setAttribute("statut_transaction",trim($request->num_transaction));
                        $tranche_paiement->setAttribute("statut_traitement",trim($request->statut_traitement));
                        $tranche_paiement->setAttribute("modepaiement_id",trim($paiement->id_modepaiement));
                        //
                        $tranche_paiement->setAttribute('tranche_id',$tranche->id);
                        $tranche_paiement->setAttribute('created_by',$user->id);
                        $tranche_paiement->setAttribute('updated_by',$user->id);
                        $tranche_paiement->save();
                    }else{
                        //enregistrement tranche pour inscription
                        $tranche_paiement = new PaiementsTranche();
                        $tranche_paiement->setAttribute('details_paiements_id',$item->id);
                        $tranche_paiement->setAttribute('montant_paye',$item->montant_restant);
                        $tranche_paiement->setAttribute('montant_restant',$item->montant_restant-$detailPaiement->montant_restant);
                        $tranche_paiement->setAttribute('reference',$paiement->reference);
                        //
                        $tranche_paiement->setAttribute("num_transaction",trim($request->num_transaction));
                        $tranche_paiement->setAttribute("statut_transaction",trim($request->num_transaction));
                        $tranche_paiement->setAttribute("statut_traitement",trim($request->statut_traitement));
                        $tranche_paiement->setAttribute("modepaiement_id",trim($paiement->id_modepaiement));
                        //
                        $tranche_paiement->setAttribute('is_inscription',true);
                        $tranche_paiement->setAttribute('created_by',$user->id);
                        $tranche_paiement->setAttribute('updated_by',$user->id);
                        $tranche_paiement->save();
                    }
                }
            }

            // cas de montant payé < montant attendu
            if($request->montant_total < $montant_attendu){ // non solder & paiement partiel
                $detailsPaiements = Detailspaiements::where('code_inscription',$paiement->code_inscription)->get();

                // update frais inscription
                foreach($detailsPaiements as $item){
                    if($item->getTarification->getGrilletarifaire->type_tarif == 'INSCRIPTION' && $frais_inscription_paye >= $item->montant_restant){
                        $detailPaiement = Detailspaiements::find($item->id);
                        $detailPaiement->setAttribute('montant_paye',$item->montant_restant);
                        $detailPaiement->setAttribute('montant_restant',$item->montant_restant-$detailPaiement->montant_restant);
                        $detailPaiement->setAttribute('statut_traitement',trim($request->statut_traitement));
                        $detailPaiement->setAttribute('statut_paiement', "PAYE");

                        // enregistrement de la preuve si ça existe
                        $get_preuve = $request->preuve_paiement;
                        if (!empty($get_preuve)) {
                            $extension = $get_preuve->getClientOriginalExtension();
                            $preuve_name = date('Ymd-His') . '.' . $extension;
                            $get_preuve->move(public_path('preuve'), $preuve_name);
                            $detailPaiement->setAttribute('preuve_path', $preuve_name);
                        }

                        $detailPaiement->update();
                        $frais_inscription = $item->montant_restant;

                        // enregistrement de la tranche pour inscription
                        $tranche_paiement = new PaiementsTranche();
                        $tranche_paiement->setAttribute('details_paiements_id',$detailPaiement->id);
                        $tranche_paiement->setAttribute('montant_paye',$item->montant_restant);
                        $tranche_paiement->setAttribute('montant_restant',$item->montant_restant-$detailPaiement->montant_restant);
                        $tranche_paiement->setAttribute('reference',$paiement->reference.'_1');
                        //
                        $tranche_paiement->setAttribute("num_transaction",trim($request->num_transaction));
                        $tranche_paiement->setAttribute("statut_transaction",trim($request->num_transaction));
                        $tranche_paiement->setAttribute("statut_traitement",trim($request->statut_traitement));
                        $tranche_paiement->setAttribute("modepaiement_id",trim($paiement->id_modepaiement));
                        $tranche_paiement->setAttribute('is_inscription',true);
                        //
                        $tranche_paiement->setAttribute('created_by',$user->id);
                        $tranche_paiement->setAttribute('updated_by',$user->id);
                        $tranche_paiement->save();
                        $reference_paiement_partiel = $paiement->reference.'_1';

                    }
                }

                // update frais scolarite
                foreach($detailsPaiements as $data){
                    if($data->getTarification->getGrilletarifaire->type_tarif != 'INSCRIPTION' && $frais_scolarite_paye > 0){
                        // verifier si le montant restant apres avoir soustrait les frais d'inscription, couvre le montant de la scolarite
                        $montant_scolarite_restant = $data->montant_restant - $frais_scolarite_paye;
                        $detailPaiement = Detailspaiements::find($data->id);
                        $detailPaiement->setAttribute('montant_paye',$data->montant_paye + $frais_scolarite_paye);
                        $detailPaiement->setAttribute('montant_restant',$montant_scolarite_restant);
                        $detailPaiement->setAttribute('statut_traitement',trim($request->statut_traitement));
                        $detailPaiement->setAttribute('statut_paiement', "PAYE");

                        // enregistrement de la preuve si ça existe
                        $get_preuve = $request->preuve_paiement;
                        if (!empty($get_preuve)) {
                            $extension = $get_preuve->getClientOriginalExtension();
                            $preuve_name = date('Ymd-His') . '.' . $extension;
                            $get_preuve->move(public_path('preuve'), $preuve_name);
                            $detailPaiement->setAttribute('preuve_path', $preuve_name);
                        }
                        $detailPaiement->update();
                        //get tranche
                        $tranche = Tranches::where('code_inscription',$paiement->code_inscription)->first();

                        $tranche_paiement = new PaiementsTranche();
                        $tranche_paiement->setAttribute('details_paiements_id',$data->id);
                        $tranche_paiement->setAttribute('montant_paye',$frais_scolarite_paye);
                        $tranche_paiement->setAttribute('montant_restant',$montant_scolarite_restant);
                        $tranche_paiement->setAttribute('reference',$paiement->reference.'_1');
                        //
                        $tranche_paiement->setAttribute("num_transaction",trim($request->num_transaction));
                        $tranche_paiement->setAttribute("statut_transaction",trim($request->num_transaction));
                        $tranche_paiement->setAttribute("statut_traitement",trim($request->statut_traitement));
                        $tranche_paiement->setAttribute("modepaiement_id",trim($paiement->id_modepaiement));
                        //
                        $tranche_paiement->setAttribute('tranche_id',$tranche->id);
                        $tranche_paiement->setAttribute('created_by',$user->id);
                        $tranche_paiement->setAttribute('updated_by',$user->id);
                        $tranche_paiement->save();
                        $reference_paiement_partiel = $paiement->reference.'_1';

                    }
                }
            }


            //####################### fin traitement pour statut valider #######################


            $auditeur = Auditeurs::where("email",$paiement->email)->first();
            // envoie de mail pour la reférence de la facture
            $modepaiement = Modepaiements::find($request->modepaiement_id);
            $contenu = [
                'nom' => $auditeur->nom ,
                'prenoms' => $auditeur->prenoms,
                'reference_paiement' => $reference_paiement_partiel,
                'subject' => 'TRAITEMENT DE VOTRE PAIEMENT',
                'commentaire' => "Paiement validé",
                'email'=>$paiement->email,
                'mode_paiement'=>$modepaiement->libelle,
                'montant'=>$request->montant_total,
                'type'=>$paiement->type,
                'url_recu'=> $this->genarateurCodeService->generateUrlSigned('paiements.recu',['reference'=>$reference_paiement_partiel])
            ];

            try {
                # code...
                Mail::to(trim($paiement->email))->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new PaiementValideDsc($contenu));
            } catch (\Exception $e) {
                //throw $th;
            }

        } else {
            # code...
            // update montant payé dans la table paiement, detailspaiement et paiementstranchez
            $update_montant_restant = $paiement->montant_restant - $request->montant_total;
            $update_montant_paye = $paiement->montant_paye + $request->montant_total;
            //
            $paiement->setAttribute("montant_paye",$update_montant_paye);
            $paiement->setAttribute("montant_restant",$update_montant_restant);
            $paiement->setAttribute("id_modepaiement",$request->modepaiement_id);
            $paiement->setAttribute("statut_paiement","EN ATTENTE");
            $paiement->setAttribute("statut_traitement","ATTENTE");
            $paiement->update();

            // update table details paiements
            if($request->montant_paye == $montant_attendu){ // solder
                $detailsPaiements = Detailspaiements::where('code_inscription',$paiement->code_inscription)->get();
                foreach($detailsPaiements as $item){
                    $detailPaiement = Detailspaiements::find($item->id);
                    $detailPaiement->setAttribute('montant_paye',$item->montant_restant);
                    $detailPaiement->setAttribute('montant_restant',$item->montant_restant-$detailPaiement->montant_restant);
                    $detailPaiement->setAttribute('statut_traitement',trim($request->statut_traitement));
                    $detailPaiement->setAttribute('statut_paiement', "PAYE");
                    // si preuve de paiement, on sauvegarde

                    $detailPaiement->update();

                    // enregistrement de la tranche
                    if($item->getTarification->getGrilletarifaire->type_tarif !== 'INSCRIPTION'){
                        //get tranche
                        $tranche = Tranches::where('code_inscription',$paiement->code_inscription)->first();
                        $tranche_paiement = new PaiementsTranche();
                        $tranche_paiement->setAttribute('details_paiements_id',$item->id);
                        $tranche_paiement->setAttribute('montant_paye',$item->montant_restant);
                        $tranche_paiement->setAttribute('montant_restant',$item->montant_restant-$detailPaiement->montant_restant);
                        $tranche_paiement->setAttribute('reference',$paiement->reference);
                        //
                        $tranche_paiement->setAttribute("num_transaction",trim($request->num_transaction));
                        $tranche_paiement->setAttribute("statut_transaction",trim($request->num_transaction));
                        $tranche_paiement->setAttribute("statut_traitement",trim($request->statut_traitement));
                        //
                        $tranche_paiement->setAttribute('tranche_id',$tranche->id);
                        $tranche_paiement->setAttribute('created_by',$user->id);
                        $tranche_paiement->setAttribute('updated_by',$user->id);
                        $tranche_paiement->save();
                    }
                }
            }
            // cas de montant payé < montant attendu
            if($request->montant_paye < $montant_attendu){ // non solder & paiement partiel
                $detailsPaiements = Detailspaiements::where('code_inscription',$paiement->code_inscription)->get();

                // update frais inscription
                foreach($detailsPaiements as $item){
                    if($item->getTarification->getGrilletarifaire->type_tarif == 'INSCRIPTION' && $frais_inscription_paye >= $item->montant_restant){
                        $detailPaiement = Detailspaiements::find($item->id);
                        $detailPaiement->setAttribute('montant_paye',$item->montant_restant);
                        $detailPaiement->setAttribute('montant_restant',$item->montant_restant-$detailPaiement->montant_restant);
                        $detailPaiement->update();
                        $frais_inscription = $item->montant_restant;
                    }
                }

                // update frais scolarite
                foreach($detailsPaiements as $data){
                    if($data->getTarification->getGrilletarifaire->type_tarif !== 'INSCRIPTION' && $frais_scolarite_paye > 0){
                        // verifier si le montant restant apres avoir soustrait les frais d'inscription, couvre le montant de la scolarite
                        $montant_scolarite_restant = $data->montant_restant - $frais_scolarite_paye;
                        $detailPaiement = Detailspaiements::find($data->id);
                        $detailPaiement->setAttribute('montant_paye',$data->montant_paye + $frais_scolarite_paye);
                        $detailPaiement->setAttribute('montant_restant',$montant_scolarite_restant);
                        $detailPaiement->update();
                        //get tranche
                        $tranche = Tranches::where('code_inscription',$paiement->code_inscription)->first();

                        $tranche_paiement = new PaiementsTranche();
                        $tranche_paiement->setAttribute('details_paiements_id',$data->id);
                        $tranche_paiement->setAttribute('montant_paye',$frais_scolarite_paye);
                        $tranche_paiement->setAttribute('montant_restant',$montant_scolarite_restant);
                        $tranche_paiement->setAttribute('reference',$paiement->reference.'_1');
                        //
                        $tranche_paiement->setAttribute("num_transaction",trim($request->num_transaction));
                        $tranche_paiement->setAttribute("statut_transaction",trim($request->num_transaction));
                        $tranche_paiement->setAttribute("statut_traitement",trim($request->statut_traitement));
                        //
                        $tranche_paiement->setAttribute('tranche_id',$tranche->id);
                        $tranche_paiement->setAttribute('created_by',$user->id);
                        $tranche_paiement->setAttribute('updated_by',$user->id);
                        $tranche_paiement->save();
                        $reference_paiement_partiel = $paiement->reference.'_1';

                    }
                }
            }
        }

        return  ResponseHandler::success("Commande déplacée avec succès");
    }

    public function saveDeclassercommande(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement saveDeclassercommande() method.
        $paiement = Paiements::whereReference($request->reference)->first();
        $updPaiement = Paiements::find($paiement->id);
        $updPaiement->setAttribute('declasse',1);
        $updPaiement->update();

        return  ResponseHandler::success("ok");
    }

    public function validerPaiement(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement validerPaiement() method.
        // update statut paiement
        $getPaiement = Paiements::where("reference",$request->reference)->get()->first();
        $paiement = Paiements::find($getPaiement->id);
        $user = auth('api')->user();

        if ($paiement->montant_total == 0) {
            # code...
            if ($request->statut_traitement == "VALIDER") {
                # code...
                $paiement->setAttribute("statut_paiement","PAYE");
                $paiement->setAttribute("montant_paye",$paiement->montant_restant);
                $paiement->setAttribute("montant_restant",0);
                $paiement->setAttribute('modepaiement_id', $request->modepaiement_id); 
                $paiement->setAttribute("commentaire",$request->commentaire);
                $paiement->setAttribute("statut_traitement",trim($request->statut_traitement));
                $paiement->setAttribute('agent_traiteur', $user->id);
                $paiement->setAttribute('date_traitement', new \DateTime());
                $paiement->update();

                // send mail paiement validé

                if ($paiement->type !== "OUVRAGE") {
                    # code...
                    // update table details paiement
                    $details_paiement = Detailspaiements::where('reference_paiement',$request->reference)->get();

                    foreach($details_paiement as $detail){
                        $detail->setAttribute('statut_traitement',trim($request->statut_traitement));
                        $detail->setAttribute('statut_paiement', "PAYE");
                        $detail->setAttribute("montant_paye",$detail->montant_restant);
                        $detail->setAttribute("montant_restant",0);
                        $detail->setAttribute('modepaiement_id', $request->modepaiement_id);
                        $detail->setAttribute('commentaire', $request->commentaire);
                        $detail->update();
                    }

                }

            } else {
                # code...
                $paiement->setAttribute("statut_paiement","EN ATTENTE");
                $paiement->setAttribute("statut_traitement","ATTENTE");
                $paiement->setAttribute("commentaire",$request->commentaire);
                $paiement->update();
            }

            return  ResponseHandler::success("Opération effectuée avec succès");
        }
        //######################################## montant supérieur à 0 ################

        if ($request->statut_traitement == "VALIDER") { // si statut VALIDER et pas de réduction
            $request->validate([
                'modepaiement_id'=>'required',
                'num_transaction'=>'required',
                'statut_traitement'=>'required',
            ]);
            # code...
            $paiement->setAttribute("num_transaction",trim($request->num_transaction));
            $paiement->setAttribute("statut_transaction",trim($request->num_transaction));
            $paiement->setAttribute("id_modepaiement",$request->modepaiement_id); 
            $paiement->setAttribute('commentaire', $request->commentaire);
            $paiement->setAttribute("statut_paiement","PAYE");
            $paiement->setAttribute("statut_traitement",trim($request->statut_traitement));
            $paiement->setAttribute('agent_traiteur', $user->id);
            $paiement->setAttribute('date_traitement', new \DateTime());
            // vérifier l'application de la réduction
            if($request->has('reduction_id') && !empty($request->input('reduction_id'))){

                    if($paiement->montant_total > 0){
                        // check details paiements

                        $chec_details_paiements = Detailspaiements::where("reference_paiement",$paiement->reference)
                            ->exists();

                        if($chec_details_paiements){
                            
                            $detail_paiement = Detailspaiements::where("reference_paiement",$paiement->reference)
                                ->get();

                            # code...
                            $update_montant_paiement = $paiement->montant_total ;
                            $reduction = Reductions::find($request->reduction_id);
                            // application reduction au montant existant
                            $montant_paiement_reduction = $update_montant_paiement - ($update_montant_paiement*($reduction->pourcentage/100));

                            // update table details paiement
                            foreach ($detail_paiement as $details) {
                                # code...
                                $get_detail = Detailspaiements::find($details->id);
                                $get_detail->setAttribute("id_reduction",$request->reduction_id);
                                $tarif_formation = Tarificationsformation::find($get_detail->id_tarification_formation);
                                $montant = $tarif_formation->getGrilletarifaire->montant;
                                $reduction = Reductions::find($request->reduction_id);
                                $montant_reduction = ($montant*$reduction->pourcentage)/100; 
                                    # code...
                                $get_detail->setAttribute("montant_paye",$montant-$montant_reduction);
                                $get_detail->setAttribute("montant_restant",0);  
                                $get_detail->setAttribute('modepaiement_id', $request->modepaiement_id);
                                $get_detail->setAttribute('commentaire', $request->commentaire);
                                $get_detail->setAttribute('statut_traitement',trim($request->statut_traitement));
                                $get_detail->setAttribute('statut_paiement', "PAYE");
                                $get_detail->update();
                            }
                            // update table paiement
                            $paiement->setAttribute("montant_total",$montant_paiement_reduction); 
                                # code...
                            $paiement->setAttribute("montant_paye",$montant_paiement_reduction);
                            $paiement->setAttribute("montant_restant",0); 

                            $paiement->update();

                        }
                    }
                
            } else {
                # code...
                
            $paiement->setAttribute("montant_paye",$paiement->montant_restant);
            $paiement->setAttribute("montant_restant",0);
            $paiement->update();

             if ($paiement->type !== "OUVRAGE") {
                # code...
                // update table details paiement
                $details_paiement = Detailspaiements::where('reference_paiement',$request->reference)->get();

                foreach($details_paiement as $detail){
                    $detail->setAttribute('statut_traitement',trim($request->statut_traitement));
                    $detail->setAttribute('statut_paiement', "PAYE");
                    $detail->setAttribute("montant_paye",$detail->montant_restant);
                    $detail->setAttribute("montant_restant",0);
                    $detail->setAttribute('modepaiement_id', $request->modepaiement_id);
                    $detail->setAttribute('commentaire', $request->commentaire);
                    $detail->update();
                }

            }

            }
            

            // send mail paiement validé            
            $auditeur = Auditeurs::where("email",$paiement->email)->first();
            // envoie de mail pour la reférence de la facture
            $modepaiement = Modepaiements::find($request->modepaiement_id);
            $nom = $auditeur->nom ?? '';
            $prenoms = $auditeur->prenoms ?? '';

            if($paiement->type !== "OUVRAGE"){
                $url = $this->genarateurCodeService->generateUrlSigned('paiements.recu',['reference'=>$request->reference]);
            }else{
                $url = $this->genarateurCodeService->generateUrlSigned('paiements.recu-ouvrage',['reference'=>$request->reference]);
            }

            $contenu = [
                'nom' => $nom,
                'prenoms' => $prenoms,
                'reference_paiement' => $request->reference,
                'subject' => 'TRAITEMENT DE VOTRE PAIEMENT',
                'commentaire' => "Paiement validé",
                'email'=>$paiement->email,
                'mode_paiement'=>$modepaiement->libelle,
                'montant'=>$paiement->montant_total,
                'type'=>$paiement->type,
                'url_recu' => $url
            ];

            try {
                # code...
                Mail::to(trim($paiement->email))->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new Paiementvalider($contenu));

            } catch (\Exception $e) {
                //throw $th;
            }

        } else {
            # code...
            if($request->has('reduction_id') && !empty($request->input('reduction_id'))){

            }else{
                $paiement->setAttribute("num_transaction",$request->num_transaction);
                $paiement->setAttribute("statut_transaction",$request->num_transaction);
            }
            $paiement->setAttribute("id_modepaiement",$request->modepaiement_id);
            $paiement->setAttribute("statut_paiement","EN ATTENTE");
            $paiement->setAttribute("statut_traitement","ATTENTE");
            $paiement->update();

            // application réduction

            if ($paiement->type !== "OUVRAGE") {
                # code...
                //################# mise à jour reduction #################
                // mise à jour tarif formation
                if($request->has('reduction_id') && !empty($request->input('reduction_id'))){

                    if($paiement->montant_total > 0){
                        // check details paiements

                        $chec_details_paiements = Detailspaiements::where("reference_paiement",$paiement->reference)
                            ->exists();

                        if($chec_details_paiements){
                            
                            $detail_paiement = Detailspaiements::where("reference_paiement",$paiement->reference)
                                ->get();

                            # code...
                            $update_montant_paiement = $paiement->montant_total ;
                            $reduction = Reductions::find($request->reduction_id);
                            // application reduction au montant existant
                            $montant_paiement_reduction = $update_montant_paiement - ($update_montant_paiement*($reduction->pourcentage/100));

                            // update table details paiement
                            foreach ($detail_paiement as $details) {
                                # code...
                                $get_detail = Detailspaiements::find($details->id);
                                $get_detail->setAttribute("id_reduction",$request->reduction_id);
                                $tarif_formation = Tarificationsformation::find($get_detail->id_tarification_formation);
                                $montant = $tarif_formation->getGrilletarifaire->montant;
                                $reduction = Reductions::find($request->reduction_id);
                                $montant_reduction = ($montant*$reduction->pourcentage)/100;
                                if ($get_detail->statut_traitement == "VALIDER") {
                                    # code...
                                    $get_detail->setAttribute("montant_paye",$montant-$montant_reduction);
                                    $get_detail->setAttribute("montant_restant",0);

                                } else {
                                    # code...
                                    $get_detail->setAttribute("montant_paye",0);
                                    $get_detail->setAttribute("montant_restant",$montant-$montant_reduction);
                                }

                                $get_detail->update();
                            }
                            // update table paiement
                            $paiement->setAttribute("montant_total",$montant_paiement_reduction); 
                                # code...
                                $paiement->setAttribute("montant_paye",0);
                                $paiement->setAttribute("montant_restant",$montant_paiement_reduction); 

                            $paiement->update();

                        }
                    }
                }

            }
            // ################# end mise à jour ####################
        }

        return  ResponseHandler::success("Opération effectuée avec succès");
    }

    public function validerPaiementPartiel(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement validerPaiementPartiel() method.
        // update statut paiement
        $user = auth('api')->user();
        $paiement = Paiements::where("reference",$request->reference)->get()->first();
        $getDetailPaiement = Detailspaiements::find($request->identifiant);

        // ! (vérifier si tous les autres occurences disponibles dans la table détailspaiements sont validées)
        $checkPaiementNonSolde = Detailspaiements::where("id","!=",$request->identifiant)
            ->where("reference_paiement",$request->reference)
            ->where("statut_traitement","ATTENTE")
            ->exists();

        $montant_paye = $request->montant_total; // getMontantFormation($request->identifiant);

        if ($montant_paye == 0) { // facture soldée
            # code...
            if ($request->statut_traitement == "VALIDER") {

                // update detailspaiements
                $getDetailPaiement->setAttribute('statut_traitement',trim($request->statut_traitement));
                $getDetailPaiement->setAttribute('statut_paiement', "PAYE");
                $getDetailPaiement->setAttribute("montant_paye",$getDetailPaiement->montant_restant);
                $getDetailPaiement->setAttribute("montant_restant",0);
                $getDetailPaiement->setAttribute("commentaire",$request->commentaire);
                // si preuve de paiement on sauvegarde
                if (!empty($request->preuve)) {
                    $preuve_paiement = $request->preuve;
                    $extension = $preuve_paiement->getClientOriginalExtension();
                    $preuve_name = date('Ymd-His') . '.' . $extension;
                    $preuve_paiement->move(public_path('preuve'), $preuve_name);
                    $getDetailPaiement->setAttribute('preuve_path', $preuve_name);
                }
                $getDetailPaiement->update();

                // si facture soldée update paiements
                if (!$checkPaiementNonSolde) {
                    # code...
                    if ($paiement->montant_restant == 0) {
                        $paiement->setAttribute("statut_paiement","PAYE");
                        $paiement->setAttribute("commentaire",$request->commentaire);
                        $paiement->setAttribute("statut_traitement",trim($request->statut_traitement));
                        $paiement->setAttribute('agent_traiteur', $user->id);
                        $paiement->setAttribute('date_traitement', new \DateTime());
                        $paiement->update();
                    }
                }

            }

            return  ResponseHandler::success("Opération effectuée avec succès.");
        }
        //######################################## montant supérieur à 0 ################

        if ($request->statut_traitement == "VALIDER") {
            # code...
            // update detailspaiements
            $getDetailPaiement->setAttribute('statut_traitement',trim($request->statut_traitement));
            $getDetailPaiement->setAttribute('statut_paiement', "PAYE");
            $getDetailPaiement->setAttribute("montant_paye",$getDetailPaiement->montant_restant);
            $getDetailPaiement->setAttribute("montant_restant",0);
            $getDetailPaiement->setAttribute("commentaire",$request->commentaire);
            // si preuve de paiement on sauvegarde
            if (!empty($request->preuve)) {
                $preuve_paiement = $request->preuve;
                $extension = $preuve_paiement->getClientOriginalExtension();
                $preuve_name = date('Ymd-His') . '.' . $extension;
                $preuve_paiement->move(public_path('preuve'), $preuve_name);
                $getDetailPaiement->setAttribute('preuve_path', $preuve_name);
            }
            $getDetailPaiement->update();

            // si facture soldée update paiements
            if (!$checkPaiementNonSolde) {
                # code...
                if ($paiement->montant_restant == 0) {
                    $paiement->setAttribute("num_transaction",trim($request->num_transaction));
                    $paiement->setAttribute("statut_transaction",trim($request->num_transaction));
                    $paiement->setAttribute("id_modepaiement",$request->modepaiement_id);
                    $paiement->setAttribute("statut_paiement","PAYE");
                    $paiement->setAttribute("statut_traitement",trim($request->statut_traitement));
                    $paiement->setAttribute('agent_traiteur', $user->id);
                    $paiement->setAttribute('date_traitement', new \DateTime());
                    $paiement->update();
                }
            }



            // send mail paiement validé
            $auditeur = Auditeurs::where("email",$paiement->email)->first();
            // envoie de mail pour la reférence de la facture
            $modepaiement = Modepaiements::find($request->modepaiement_id);
            $contenu = [
                'nom' => $auditeur->nom ,
                'prenoms' => $auditeur->prenoms,
                'reference_paiement' => $request->reference,
                'subject' => 'TRAITEMENT DE VOTRE PAIEMENT',
                'commentaire' => "Paiement validé",
                'email'=>$paiement->email,
                'mode_paiement'=>$modepaiement->libelle,
                'montant'=>$montant_paye,
                'type'=>$paiement->type,
                'url_recu'=>$this->genarateurCodeService->generateUrlSigned('paiements.recu',['reference'=>$request->reference])
            ];

            try {
                # code...
                Mail::to(trim($paiement->email))->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new Paiementvalider($contenu));

            } catch (\Exception $e) {
                //throw $th;
            }

        }

        return  ResponseHandler::success("Opération effectuée avec succès");

    }

    public function validerPaiementDsc(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement validerPaiementDsc() method.
        $user = auth('api')->user();
        // récupérer la référence de la table paiement
        $nbrePaiementAttente = PaiementsTranche::where('reference','like','%'.$request->reference.'%')
            ->where('statut_traitement','ATTENTE')
            ->count();
        $getPaiement = Paiements::where('reference',$request->reference)->first();

        // update table paiement tranche pour le choix des paiements concernés
        $listePTranches = collect($request->listeTranches)->map(fn($item) => (object) $item);
        $choixNbrePaiement = count($listePTranches);

        if($nbrePaiementAttente > $choixNbrePaiement){
            $paiementTotal = false;
        }else{
            $paiementTotal = true;
        }

        $paiement = Paiements::find($getPaiement->id);

        //######################################## montant supérieur à 0 ################
        //
        if ($request->statut_traitement == "VALIDER") {
            // update statut paiement si montant restant = 0
            if ($paiement->montant_restant == 0 && $paiementTotal) {
                # code...
                $paiement->setAttribute("num_transaction",trim($request->num_transaction));
                $paiement->setAttribute("statut_transaction",trim($request->num_transaction));
                $paiement->setAttribute("id_modepaiement",$request->modepaiement_id);
                $paiement->setAttribute("statut_paiement","PAYE");
                $paiement->setAttribute("statut_traitement",trim($request->statut_traitement));
                $paiement->setAttribute('agent_traiteur', $user->id);
                $paiement->setAttribute('date_traitement', new \DateTime());
                $paiement->update();
            }
            # code...

            foreach ($listePTranches as $key => $data) {
                # code...
                $paiementTranche = PaiementsTranche::find($data->tranche_id);
                $detailPaiementTranche = Detailspaiements::find($paiementTranche->details_paiements_id);
                $paiementTranche->setAttribute("num_transaction",trim($request->num_transaction));
                $paiementTranche->setAttribute("statut_transaction",trim($request->num_transaction));
                $paiementTranche->setAttribute("modepaiement_id",$request->modepaiement_id);
                $paiementTranche->setAttribute("statut_traitement",trim($request->statut_traitement));
                $paiementTranche->setAttribute('traiter_par', $user->id);
                $paiementTranche->update();
                // update details paiements
                // vérifier si le montant restant = 0
                if($detailPaiementTranche->montant_restant == 0){
                    $detailPaiementTranche->setAttribute('statut_traitement',trim($request->statut_traitement));
                    $detailPaiementTranche->setAttribute('statut_paiement', "PAYE");
                }
                // si preuve de paiement on sauvegarde
                if (!empty($request->preuve)) {
                    $preuve_paiement = $request->preuve;
                    $extension = $preuve_paiement->getClientOriginalExtension();
                    $preuve_name = date('Ymd-His') . '.' . $extension;
                    $preuve_paiement->move(public_path('preuve'), $preuve_name);
                    $paiementTranche->setAttribute('preuve_path', $preuve_name);
                    $detailPaiementTranche->setAttribute('preuve_path', $preuve_name);
                    $paiementTranche->update();
                }
                $detailPaiementTranche->update();
            }

            // send mail paiement validé
            $auditeur = Auditeurs::where("email",$paiement->email)->first();
            // envoie de mail pour la reférence de la facture
            $modepaiement = Modepaiements::find($request->modepaiement_id);

            $contenu = [
                'nom' => $auditeur->nom ,
                'prenoms' => $auditeur->prenoms,
                'reference_paiement' => $request->reference,
                'subject' => 'TRAITEMENT DE VOTRE PAIEMENT',
                'commentaire' => "Paiement validé",
                'email'=>$paiement->email,
                'mode_paiement'=>$modepaiement->libelle,
                'montant'=>$request->montant_total,
                'montant_restant'=>$paiement->montant_restant,
                'type'=>$paiement->type,
                'url_recu'=>$this->genarateurCodeService->generateUrlSigned('paiements.recu',['reference'=>$request->reference])
            ];

            try {
                # code...
                Mail::to(trim($paiement->email))->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new PaiementValideDsc($contenu));
            } catch (\Exception $e) {
                //throw $th;
            }

        } else {
            # code...
            if ($paiement->montant_restant == 0 && $paiementTotal) {
                # code...
                $paiement->setAttribute("num_transaction",$request->num_transaction);
                $paiement->setAttribute("statut_transaction",$request->num_transaction);
            }
            $paiement->setAttribute("id_modepaiement",$request->modepaiement_id);
            $paiement->setAttribute("statut_paiement","EN ATTENTE");
            $paiement->setAttribute("statut_traitement","ATTENTE");
            $paiement->update();
            // update table paiement tranche
            foreach ($listePTranches as $key => $data) {
                # code...
                $paiementTranche = PaiementsTranche::find($data->tranche_id);
                // update each table
                $paiementTranche->setAttribute("modepaiement_id",$request->modepaiement_id);
                $paiementTranche->setAttribute("statut_traitement","ATTENTE");
                $paiementTranche->setAttribute('traiter_par', $user->id);
                $paiementTranche->update();
            }
        }
        return  ResponseHandler::success("Opération effectuée avec succès");
    }

    public function getDetailsPaiementOuvrage(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getDetailsPaiementOuvrage() method.
        $paiement = Paiements::whereReference($request->get('reference_paiement'))->first();
        $details_paiement = Detailpaiementouvrages::join('ouvrages','ouvrages.id','=','detailpaiementouvrages.ouvrage_id')
            ->where("paiementouvrage_id",$paiement->id)
            ->get(['detailpaiementouvrages.id','detailpaiementouvrages.reference','detailpaiementouvrages.montant','ouvrages.libelle']);

        return  ResponseHandler::success("ok",$details_paiement);
    }

    public function getDetailsPaiementDsc(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getDetailsPaiementDsc() method.
        $details_paiement = DB::table('paiements_tranches')
            ->join('detailspaiements', 'paiements_tranches.details_paiements_id', '=', 'detailspaiements.id')
            ->join('tarificationsformations', 'tarificationsformations.id', '=', 'detailspaiements.id_tarification_formation')
            ->join('grilletarifaires', 'tarificationsformations.id_grilletarifaire', '=', 'grilletarifaires.id')
            ->join('formations', 'formations.code_formation', '=', 'tarificationsformations.code_formation')
            ->select('paiements_tranches.id','formations.code_formation', 'formations.theme', 'formations.date_debut','paiements_tranches.num_transaction','paiements_tranches.montant_paye','detailspaiements.have_reduction_dsc','detailspaiements.preuve_path','grilletarifaires.type_tarif','grilletarifaires.montant')
            ->where('paiements_tranches.reference','like','%'.$request->reference_paiement.'%')
            ->where('paiements_tranches.statut_traitement','ATTENTE')
            ->orderBy('paiements_tranches.id', 'DESC')
            ->get();

        return  ResponseHandler::success("ok",$details_paiement);
    }

    public function getDetailsPaiementValiderDsc(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getDetailsPaiementDsc() method.
        $details_paiement = DB::table('paiements_tranches')
            ->join('detailspaiements', 'paiements_tranches.details_paiements_id', '=', 'detailspaiements.id')
            ->join('tarificationsformations', 'tarificationsformations.id', '=', 'detailspaiements.id_tarification_formation')
            ->join('grilletarifaires', 'tarificationsformations.id_grilletarifaire', '=', 'grilletarifaires.id')
            ->join('formations', 'formations.code_formation', '=', 'tarificationsformations.code_formation')
            ->select('paiements_tranches.id','formations.code_formation', 'formations.theme', 'formations.date_debut','paiements_tranches.num_transaction','paiements_tranches.montant_paye','detailspaiements.have_reduction_dsc','detailspaiements.preuve_path','grilletarifaires.type_tarif','grilletarifaires.montant')
            ->where('paiements_tranches.reference','like','%'.$request->reference_paiement.'%')
            ->where('paiements_tranches.statut_traitement','VALIDER')
            ->orderBy('paiements_tranches.id', 'DESC')
            ->get();

        return  ResponseHandler::success("ok",$details_paiement);
    }

    public function getListePaiements(Request $request)
    {
        // TODO: Implement getListePaiements() method.
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Filtres spécifiques
        $filterActif = $request->input('filter_actif');
        $filterUpdatedAt = $request->input('filter_updated_at');

        $queryData = Inscriptions::leftJoin('auditeurs', 'auditeurs.email', '=', 'inscriptions.email')
            ->leftJoin('detailspaiements as dp', function ($join) {
                $join->on('dp.id_tarification_formation', '=', 'inscriptions.tarif_formation_id')
                    ->where('dp.email', '=', DB::raw('inscriptions.email'))
                    ->whereNotNull('dp.email');
            })
            ->leftJoin('paiements as p', function ($join) {
                $join->on('p.reference', '=', 'dp.reference_paiement');
            })
            ->leftJoin('modepaiements as mp', function ($join) {
                $join->on('mp.id', '=', 'p.id_modepaiement');
            })
            ->leftJoin('reductions', function ($join) {
                $join->on('reductions.id', '=', 'dp.id_reduction');
            })
            ->leftJoin('formations', function ($join) {
                $join->on('inscriptions.code_formation', '=', 'formations.code_formation');
            })
            ->selectRaw("
                auditeurs.email,
                inscriptions.tarif_formation_id,
                inscriptions.code_formation,
                CONCAT(auditeurs.prenoms, ' ', auditeurs.nom) as auditeur,
                CASE
                    WHEN p.type = 'DSC' THEN (
                        SELECT
                            SUM(
                                COALESCE(dp2.montant_paye, 0) + COALESCE(dp2.montant_restant, 0)
                            )
                        FROM detailspaiements dp2
                        WHERE dp2.reference_paiement = dp.reference_paiement
                    )
                    ELSE (
                        COALESCE(dp.montant_paye, 0) + COALESCE(dp.montant_restant, 0)
                    )
                END as montant,
                inscriptions.updated_at,
                inscriptions.id,
                inscriptions.code_inscription,
                dp.id as detailpaiement_id,
                dp.reference_paiement,
                dp.id_tarification_formation,
                mp.libelle as modepaiement,
                mp.id as modepaiement_id,
                dp.statut_paiement,
                dp.id_reduction,
                p.type,
                p.id as paiement_id,
                reductions.id as id_reduction,
                reductions.pourcentage,
                dp.have_reduction_dsc,
                dp.reference_paiement_partiel,
                p.num_transaction,
                dp.montant_restant,
                formations.is_formation_dsc,
                formations.theme
            ")
            ->selectRaw(
                'EXISTS (
                    SELECT 1
                    FROM paiements_tranches pt
                    WHERE pt.details_paiements_id = dp.id
                    AND pt.statut_traitement = "ATTENTE"
                ) as check_statut_ptranche'
            )
            ->selectRaw(
                'EXISTS (
                    SELECT 1
                    FROM paiements_tranches pt
                    WHERE pt.details_paiements_id = dp.id
                    AND pt.statut_traitement = "VALIDER"
                ) as check_recu_ptranche'
            )
        ;


        // Recherche globale sur plusieurs champs
        if ($search) {
            $queryData->where(function ($q) use ($search) {
                $q->where('auditeurs.nom', 'like', "%{$search}%")
                    ->orWhere('auditeurs.prenoms', 'like', "%{$search}%")
                    ->orWhere('auditeurs.email', 'like', "%{$search}%")
                    ->orWhere('dp.reference_paiement', 'like', "%{$search}%")
                    ->orWhere('dp.statut_paiement', 'like', "%{$search}%")
                    ->orWhere('inscriptions.code_formation', 'like', "%{$search}%")
                    ->orWhere('mp.libelle', 'like', "%{$search}%")
                ;
            });
        }

        // Filtres par colonne
        if ($filterUpdatedAt) {
            $queryData->whereDate('inscriptions.updated_at', $filterUpdatedAt);
        }
        // Tri
        $queryData->orderBy("inscriptions.{$sortField}", $sortOrder);

        return $queryData->paginate($perPage);
    }

    public function getDetailsProcessPaiementDsc(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getDetailsPaiementDsc() method.
        // reference_paiement peut prendre la valeur de code_inscription

        $reference_paiement = $request->reference_paiement;
        // check if reference exist
        $getReferencePaiement = explode("_",$reference_paiement)[0];
        $paiement = Paiements::where('reference',$getReferencePaiement)->first();
        if($paiement){

        }else{
            $paiement = Paiements::where('code_inscription',$getReferencePaiement)->first();
        }
        $refP = $paiement->reference;
        $checkPaiementTranche = PaiementsTranche::where("reference","like","%$paiement->reference%")->exists();
        $date_limite_inscription = "";
        $inscription = Inscriptions::where('code_inscription',$paiement->code_inscription)->first();
        $formation = Formations::where('code_formation',$inscription->code_formation)->first();
        $getDateInsc = FormationsDsc::where('formation_id',$formation->id)->first();
        if($getDateInsc){
            $date_limite_inscription = $getDateInsc->date_limite_paiement_inscription;
        }

        if($paiement){
            $details_paiement = Detailspaiements::join('tarificationsformations', 'tarificationsformations.id', '=', 'detailspaiements.id_tarification_formation')
                ->join('grilletarifaires', 'tarificationsformations.id_grilletarifaire', '=', 'grilletarifaires.id')
                ->leftJoin('formations', 'formations.code_formation', '=', 'detailspaiements.code_formation')
                ->where("detailspaiements.code_inscription", $paiement->code_inscription)
                ->get(['detailspaiements.id as detailsPaiementId','detailspaiements.reference_paiement','detailspaiements.code_formation','detailspaiements.code_inscription',
                    'detailspaiements.montant_paye','detailspaiements.montant_restant','detailspaiements.id_tarification_formation','formations.theme',
                    'detailspaiements.have_reduction_dsc','grilletarifaires.type_tarif','grilletarifaires.id as grilleId','grilletarifaires.montant as grille_montant']);
            $montant_total = 0;
            $frais_inscription = 0;
            $frais_scolarite = 0;
            foreach($details_paiement as $detail){
                $montant_total = $montant_total + $detail->montant_restant;
                if ($detail->type_tarif == 'INSCRIPTION'){
                    $frais_inscription = $frais_inscription + $detail->montant_restant;
                }else{
                    $frais_scolarite = $frais_scolarite + $detail->montant_restant;
                }
            }
            $have_tranche = Tranches::where('code_inscription',$paiement->code_inscription)->exists();
            $uniqueTranche = true; // default

            if($have_tranche){
                $getTranche = Tranches::where('code_inscription',$paiement->code_inscription)->first();
                if($getTranche->tranche_2 > 0){
                    $uniqueTranche = false;
                }
            }
            $data = [
                'detailsPaiement' => $details_paiement,
                'haveTranche' => $have_tranche,
                'uniqueTranche' => $uniqueTranche,
                'referencePaiement' => $request->reference_paiement,
                'date_limite_inscription' => $date_limite_inscription,
                'montant_total' => $montant_total,
                'frais_scolarite' => $frais_scolarite,
                'frais_inscription' => $frais_inscription,
                'refP' => $refP,
                'checkPaiementTranche' => $checkPaiementTranche
            ];
            return  ResponseHandler::success("ok",$data);
        }else{
            // error le paiement n'existe pas
            return  ResponseHandler::error("Le paiement n'existe pas");
        }

    }

    public function validerProcessPaiementDsc(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement validerPaiementDsc() method.
        $user = auth('api')->user();
        // récupérer la référence de la table paiement
        $getPaiement = Paiements::where('reference',$request->reference)->first();

        // update table paiement tranche pour le choix des paiements concernés
        $listePTranches = collect($request->listeTranches)->map(fn($item) => (object) $item);
        $paiement = Paiements::find($getPaiement->id);
        $montant_total_du = $request->montant_total;
        $montant_total_recu = $request->montant_total_recu;
        $codePaiement = $request->code_paiement;
        $getUser = User::where("email",$paiement->email)->first();
        $userInfo = User::find($getUser->id);
        // vérifier le dernier enregistrement portant la référence de paiement pour incrémenter
        $getPTranche = PaiementsTranche::where("reference","like","%$paiement->reference%")->latest()->first();
        //dd($getPTranche);
        if ($getPTranche) {
            # code...
            $ref = explode("_",$getPTranche->reference)[1]+1;
            $update_reference = $paiement->reference."_".$ref;
        } else {
            # code...
            $update_reference = $paiement->reference."_1";
        }

        // vérifier si le montant reçu est <= montant_total_du
        if($montant_total_recu > $montant_total_du){
            // montant invalide
            return ResponseHandler::error('Montant invalide');
        }else{
            $checkInscription = false;
            // vérifier si le montant payé >= frais inscription
            foreach ($listePTranches as $key => $data) {
                # code...
                if($data->montant_restant > 0 && $data->type_tarif == 'INSCRIPTION'){
                    $checkInscription = true;
                    if($montant_total_recu < $data->montant_restant){
                        return  ResponseHandler::error("Le montant reçu est inférieur au frais d'inscription");
                    }
                }
            }
            // vérifier si le montant payé >= frais scolarité
            if(!$checkInscription){
                foreach ($listePTranches as $key => $data) {
                    # code...
                    if($data->montant_restant > 0 && $data->type_tarif != 'INSCRIPTION'){
                        if($data->have_reduction_dsc == '1' || $data->have_reduction_dsc == 1){
                            // 100%
                            if($montant_total_recu < $data->montant_restant){
                                return  ResponseHandler::error("Le montant reçu est inférieur au frais de scolarité restant");
                            }
                        }
                        if($montant_total_recu < ($data->grille_montant/2)){
                            return  ResponseHandler::error("Le montant reçu est inférieur au frais de scolarité restant");
                        }
                    }
                }
            }
            // begin process
            // MAJ table paiements
            $paiement->setAttribute('montant_paye',$paiement->montant_paye + $montant_total_recu);
            $paiement->setAttribute('montant_restant',$paiement->montant_restant - $montant_total_recu);
            // si paiement soldé
            if(($montant_total_du-$montant_total_recu) == 0){ // facture soldée
                $paiement->setAttribute('num_transaction',$codePaiement);
                $paiement->setAttribute('statut_transaction',$codePaiement);
                $paiement->setAttribute('statut_paiement','PAYE');
            }
            $paiement->update();

            // MAJ information details paiements
            $montantScolariteRecu = $montant_total_recu;

            // Priorité => frais inscription
            $preuveNameUrl = null;
            foreach ($listePTranches as $key => $data) {
                # code...
                $detailPaiement = Detailspaiements::find($data->detailsPaiementId);
                if($detailPaiement->montant_restant > 0 && $data->type_tarif == 'INSCRIPTION'){
                    $detailPaiement->setAttribute("montant_paye",$detailPaiement->montant_paye + ($data->montant_restant));
                    $detailPaiement->setAttribute("montant_restant",0);
                    $detailPaiement->setAttribute("modepaiement_id",$request->modepaiement_id);
                    $detailPaiement->setAttribute("statut_traitement",'VALIDER');
                    $detailPaiement->setAttribute('statut_paiement', 'PAYE');
                    // si preuve de paiement on sauvegarde
                    if (!empty($request->preuve)) {
                        $preuve_paiement = $request->preuve;
                        $extension = $preuve_paiement->getClientOriginalExtension();
                        $preuve_name = date('Ymd-His') . '.' . $extension;
                        $preuve_paiement->move(public_path('preuve'), $preuve_name);
                        $detailPaiement->setAttribute('preuve_path', $preuve_name);
                        $preuveNameUrl = $preuve_name;
                    }
                    $detailPaiement->update();
                    // update frais scolarite
                    $montantScolariteRecu = ($montantScolariteRecu - $data->montant_restant);

                    // ajout des tranches et paiement tranche

                    //enregistrement tranche pour inscription
                    $tranche_paiement = new  PaiementsTranche();
                    $tranche_paiement->setAttribute('details_paiements_id',$detailPaiement->id);
                    $tranche_paiement->setAttribute('montant_paye',$data->montant_restant);
                    $tranche_paiement->setAttribute('montant_restant',$montant_total_du - $data->montant_restant);
                    $tranche_paiement->setAttribute('reference',$update_reference);
                    $tranche_paiement->setAttribute("num_transaction",trim($codePaiement));
                    $tranche_paiement->setAttribute("statut_transaction",trim($codePaiement));
                    // save preuve if exists
                    if (!empty($preuveNameUrl) && $preuveNameUrl != null) {
                        # code...
                        $tranche_paiement->setAttribute('preuve_path', $preuveNameUrl);
                    }
                    $tranche_paiement->setAttribute("statut_traitement","VALIDER");
                    $tranche_paiement->setAttribute("modepaiement_id",$request->modepaiement_id);
                    $tranche_paiement->setAttribute('is_inscription',true);
                    $tranche_paiement->setAttribute('traiter_par',$user->id);
                    $tranche_paiement->setAttribute('created_by',$userInfo->id);
                    $tranche_paiement->setAttribute('updated_by',$userInfo->id);
                    $tranche_paiement->save();

                }
            }

            // cas de Scolarité
            if($montantScolariteRecu > 0){
                foreach ($listePTranches as $key => $data) {
                    # code...

                    $detailPaiement = Detailspaiements::find($data->detailsPaiementId);
                    if($detailPaiement->montant_restant > 0 && $data->type_tarif != 'INSCRIPTION'){

                        $montantRestantScolarite = $detailPaiement->montant_restant - $montantScolariteRecu;
                        // update info in details paiements table
                        $detailPaiement->setAttribute("montant_paye",$detailPaiement->montant_paye + $montantScolariteRecu);
                        $detailPaiement->setAttribute("montant_restant",$montantRestantScolarite);
                        $detailPaiement->setAttribute("modepaiement_id",$request->modepaiement_id);
                        if(($data->montant_restant - $montantScolariteRecu) == 0){ // scolarité soldée
                            $detailPaiement->setAttribute("statut_traitement",'VALIDER');
                            $detailPaiement->setAttribute('statut_paiement', 'PAYE');
                        }
                        // save preuve if exists
                        if (!empty($preuveNameUrl) && $preuveNameUrl != null) {
                            # code...
                            $detailPaiement->setAttribute('preuve_path', $preuveNameUrl);
                        }else{
                            if (!empty($request->preuve)) {
                                $preuve_paiement = $request->preuve;
                                $extension = $preuve_paiement->getClientOriginalExtension();
                                $preuve_name = date('Ymd-His') . '.' . $extension;
                                $preuve_paiement->move(public_path('preuve'), $preuve_name);
                                $detailPaiement->setAttribute('preuve_path', $preuve_name);
                                $preuveNameUrl = $preuve_name;
                            }
                        }

                        $detailPaiement->update();

                        // ajout des tranches et paiement tranche
                        if ($request->haveTranche == '1' || $request->haveTranche == 1){
                            // tranche existe
                            $tranche = Tranches::where('code_inscription',$paiement->code_inscription)->first();
                        }else{
                            // pas de tranche
                            // sauvegarde de la tranche
                            $formationTranche = FormationsTranches::where('tranche','tranche 1')->where('grilletarifaire_id',$data->grilleId)->first();

                            $tranche = new Tranches();
                            $tranche->setAttribute('code_inscription',$paiement->code_inscription);
                            $tranche->setAttribute('email',$paiement->email);
                            $tranche->setAttribute('tranche_1',$data->grille_montant);
                            $tranche->setAttribute('tranche_2',0);
                            $tranche->setAttribute('echeance_1',$formationTranche->echeance_1);
                            $tranche->setAttribute('echeance_2',$formationTranche->echeance_1);
                            $tranche->setAttribute('statut','ATTENTE');
                            $tranche->setAttribute('created_by',$userInfo->id);
                            $tranche->setAttribute('updated_by',$userInfo->id);
                            $tranche->save();
                        }

                        //enregistrement tranche pour scolarité
                        $tranche_paiement = new  PaiementsTranche();
                        $tranche_paiement->setAttribute('details_paiements_id',$detailPaiement->id);
                        $tranche_paiement->setAttribute('montant_paye',$montantScolariteRecu);
                        $tranche_paiement->setAttribute('montant_restant',$montantRestantScolarite);
                        $tranche_paiement->setAttribute('reference',$update_reference);
                        $tranche_paiement->setAttribute('tranche_id',$tranche->id);
                        $tranche_paiement->setAttribute("num_transaction",trim($codePaiement));
                        $tranche_paiement->setAttribute("statut_transaction",trim($codePaiement));
                        // save preuve if exists
                        if (!empty($preuveNameUrl) && $preuveNameUrl != null) {
                            # code...
                            $tranche_paiement->setAttribute('preuve_path', $preuveNameUrl);
                        }
                        $tranche_paiement->setAttribute("statut_traitement","VALIDER");
                        $tranche_paiement->setAttribute("modepaiement_id",$request->modepaiement_id);
                        $tranche_paiement->setAttribute('is_inscription',false);
                        $tranche_paiement->setAttribute('traiter_par',$user->id);
                        $tranche_paiement->setAttribute('created_by',$userInfo->id);
                        $tranche_paiement->setAttribute('updated_by',$userInfo->id);
                        $tranche_paiement->save();

                    }
                }
            }

        }

        // envoie de mail pour notification paiement en attente
        // envoie de mail pour notifier l'enregistrement du paiement
        $mode_paiement = Modepaiements::find($request->modepaiement_id);
        $auditeur = Auditeurs::where("email",$paiement->email)->first();
        $contenu = [
            'nom' => $auditeur->nom,
            'prenoms' => $auditeur->prenoms,
            'reference_paiement' => $update_reference,
            'subject' => 'INFORMATION SUR VOTRE PAIEMENT '.$update_reference,
            'email' => $paiement->email,
            'montant' => $montant_total_recu,
            'montant_restant' => $paiement->montant_restant,
            'mode_paiement' => $mode_paiement->libelle
        ];

        try {
            # code...
            Mail::to($paiement->email)->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new DscPaiementAttente($contenu));

        } catch(\Exception $e){
            // Get error here

        }
        // Notification au comptable
        // vérifier si la variable send notication au comptable est sur true

        if (env('PAIEMENT_NOTIFIER_COMPTABLE')) {
            // send mail comptable
            $liste_comptable = User::whereHas('roles', function ($q) {
                $q->where('libelle', 'COMPTABLE');
            })->pluck('email');

                $contenu = [
                    'name' => 'COMPTABLE',
                    'reference_paiement' => $update_reference,
                    'subject' => 'NOUVEAU PAIEMENT '.date('d-m-Y H:i:s'),
                    'email'=>'',
                    'montant'=>$montant_total_recu,
                    'mode_paiement'=>$mode_paiement->libelle
                ];


                try {
                    # code...
                    Mail::to(env('MAIL_CC'))->cc($liste_comptable)->bcc(env('MAIL_BCC'))->queue(new NotificationComptable($contenu));
                } catch(\Exception $e){
                    // Get error here

                }

        }

        // Send mail to auditeur for validation
        // send mail paiement validé
        $auditeur = Auditeurs::where("email",$paiement->email)->first();
        // envoie de mail pour la reférence de la facture
        $contenu = [
            'nom' => $auditeur->nom ,
            'prenoms' => $auditeur->prenoms,
            'reference_paiement' => $update_reference,
            'subject' => 'TRAITEMENT DE VOTRE PAIEMENT',
            'commentaire' => "Paiement validé",
            'email'=>$paiement->email,
            'mode_paiement'=>$mode_paiement->libelle,
            'montant'=>$montant_total_recu,
            'montant_restant'=>$paiement->montant_restant,
            'type'=>$paiement->type,
            'url_recu'=>$this->genarateurCodeService->generateUrlSigned('paiements.recu',['reference'=>$paiement->reference])
        ];

        try {
            # code...
             Mail::to(trim($paiement->email))->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new PaiementValideDsc($contenu));

        } catch (\Exception $e) {
            //throw $th;
        }

        return  ResponseHandler::success("Opération effectuée avec succès",['success'=>true]);
    }
}
