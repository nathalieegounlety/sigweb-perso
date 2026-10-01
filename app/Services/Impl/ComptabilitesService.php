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
use Illuminate\Support\Facades\Log;

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

    /**
     * Requête filtrée commune à la liste et à l'export (sans tri ni pagination).
     */
    private function paiementsValidesQuery(Request $request)
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
            '' as theme,
            paiements.code_inscription
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
            formations.theme,
            detailspaiements.code_inscription
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
            MAX(formations.theme) as theme,
            MAX(detailspaiements.code_inscription) as code_inscription
            ");


        $query = DB::query()->fromSub(
            $classiques->unionAll($partiels)->unionAll($dsc), 't'
        );

        // Recherche globale (inchangée)
        if ($search = $request->input('search')) {
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

        // Filtres multicritères
        $query
            ->when($request->filled('filter_date'),     fn ($q) => $q->whereDate('t.date', $request->filter_date))
            ->when($request->filled('date_debut'),      fn ($q) => $q->whereDate('t.date', '>=', $request->date_debut))
            ->when($request->filled('date_fin'),        fn ($q) => $q->whereDate('t.date', '<=', $request->date_fin))
            ->when($request->filled('type'),            fn ($q) => $q->where('t.type', $request->type))
            ->when($request->filled('modepaiement_id'), fn ($q) => $q->where('t.id_modepaiement', $request->modepaiement_id))
            ->when($request->filled('montant_min'),     fn ($q) => $q->where('t.montant', '>=', (float) $request->montant_min))
            ->when($request->filled('montant_max'),     fn ($q) => $q->where('t.montant', '<=', (float) $request->montant_max));

        return $query;
    }

    private function appliquerTriPV($query, Request $request)
    {
        $allowed = ['nom','prenoms','reference','email','montant','num_transaction',
                    'type','date','libelle_modepaiement','pourcentage','have_reduction'];

        $field = in_array($request->input('sort_field'), $allowed) ? $request->input('sort_field') : 'date';
        $order = $request->input('sort_order') === 'desc' ? 'desc' : 'asc';

        return $query->orderBy("t.$field", $order);
    }

    public function getPaiementsvalides(Request $request) //: LengthAwarePaginator
    { 
         $query = $this->paiementsValidesQuery($request);

            // Somme calculée AVANT tri et pagination, sinon LIMIT/OFFSET la fausse
            $total = (clone $query)->sum('t.montant');

            $paiements = $this->appliquerTriPV($query, $request)
                ->paginate($request->input('per_page', 10));

            return [
                'pagination'    => $paiements,
                'montant_total' => $total,
            ];
    }

    public function getPaiementsvalidesExport(Request $request)
    {
        return $this->appliquerTriPV($this->paiementsValidesQuery($request), $request)->get();
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

            
            $action = 'DEPLACEMENT COMMANDE';
            $description = "Déplacement comande : ".$request->reference." statut ".$request->statut_traitement." commentaire ".$request->commentaire;
            $this->genarateurCodeService->saveHistoriqueAction($action, $description, auth('api')->user()->id);

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

            $action = 'VALIDATION COMMANDE';
            $description = "Validation comande : ".$request->reference." statut ".$request->statut_traitement." commentaire ".$request->commentaire;
            $this->genarateurCodeService->saveHistoriqueAction($action, $description, auth('api')->user()->id);

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
    try {
        $user = auth('api')->user();
        if (!$user) {
            return ResponseHandler::error('Utilisateur non authentifié',302);
        }
 
        $paiement = Paiements::where('reference', $request->reference)->first();
        if (!$paiement) {
            return ResponseHandler::error('Paiement introuvable',302);
        }
 
        $statutDemande = $request->statut_traitement === 'VALIDER' ? 'VALIDER' : 'ATTENTE';
 
        // Dossier gratuit / exonéré intégralement — pas de logique de réduction.
        if ((int) $paiement->montant_total === 0) {
            DB::transaction(function () use ($paiement, $statutDemande, $request, $user) {
                if ($statutDemande === 'VALIDER') {
                    $paiement->statut_paiement = 'PAYE';
                    $paiement->montant_paye = $paiement->montant_restant;
                    $paiement->montant_restant = 0;
                    $paiement->modepaiement_id = $request->modepaiement_id;
                    $paiement->commentaire = $request->commentaire;
                    $paiement->statut_traitement = 'VALIDER';
                    $paiement->agent_traiteur = $user->id;
                    $paiement->date_traitement = now();
                    $paiement->save();
 
                    if ($paiement->type !== 'OUVRAGE') {
                        $details = Detailspaiements::where('reference_paiement', $paiement->reference)->get();
                        foreach ($details as $detail) {
                            $detail->statut_traitement = 'VALIDER';
                            $detail->statut_paiement = 'PAYE';
                            $detail->montant_paye = $detail->montant_restant;
                            $detail->montant_restant = 0;
                            $detail->modepaiement_id = $request->modepaiement_id;
                            $detail->commentaire = $request->commentaire;
                            $detail->save();
                        }
                    }
                } else {
                    $paiement->statut_paiement = 'EN ATTENTE';
                    $paiement->statut_traitement = 'ATTENTE';
                    $paiement->commentaire = $request->commentaire;
                    $paiement->save();
 
                    if ($paiement->type !== 'OUVRAGE') {
                        Detailspaiements::where('reference_paiement', $paiement->reference)
                            ->where('statut_traitement', '!=', 'VALIDER')
                            ->update(['statut_traitement' => 'ATTENTE']);
                    }
                }
            });
 
            return ResponseHandler::success('Opération effectuée avec succès');
        }
 
        // Cas général (montant_total > 0)
        $modePaiement = null;
        if ($statutDemande === 'VALIDER') {
            $request->validate([
                'modepaiement_id' => 'required',
                'num_transaction' => 'required',
            ]);
 
            $modePaiement = Modepaiements::find($request->modepaiement_id);
            if (!$modePaiement) {
                return ResponseHandler::error('Mode de paiement invalide',302);
            }
        }
 
        $reductionId = $request->input('reduction_id');
        $appliquerReduction = !empty($reductionId);
 
        if ($appliquerReduction) {
            if ((int) $paiement->montant_paye > 0) {
                return ResponseHandler::error('Impossible de changer la réduction : un montant a déjà été payé sur ce dossier',302);
            }
 
            if ($paiement->type === 'OUVRAGE') {
                return ResponseHandler::error("La réduction ne s'applique pas aux paiements de type OUVRAGE",302);
            }
 
            $reduction = Reductions::find($reductionId);
            if (!$reduction) {
                return ResponseHandler::error('Réduction introuvable',302);
            }
 
            $details = Detailspaiements::where('reference_paiement', $paiement->reference)->get();
            if ($details->isEmpty()) {
                return ResponseHandler::error('Aucune ligne de détail pour appliquer la réduction',302);
            }
 
            $lignesAvecMontant = [];
            $nouveauMontantTotal = 0;
 
            foreach ($details as $detail) {
                $tarifFormation = Tarificationsformation::find($detail->id_tarification_formation);
                $grille = $tarifFormation->getGrilletarifaire ?? null;
                if (!$tarifFormation || !$grille) {
                    return ResponseHandler::error("Grille tarifaire introuvable pour la ligne {$detail->id}",302);
                }
 
                $montantGrille = (int) $grille->montant;
                $montantReduction = (int) round($montantGrille * $reduction->pourcentage / 100);
                $montantApresReduction = max(0, $montantGrille - $montantReduction);
 
                $nouveauMontantTotal += $montantApresReduction;
                $lignesAvecMontant[] = (object) ['detail' => $detail, 'montant' => $montantApresReduction];
            }
 
            DB::transaction(function () use (
                $paiement, $lignesAvecMontant, $nouveauMontantTotal, $reductionId,
                $statutDemande, $request, $user
            ) {
                foreach ($lignesAvecMontant as $item) {
                    $detail = $item->detail;
                    $detail->id_reduction = $reductionId;
                    $detail->modepaiement_id = $request->modepaiement_id;
                    $detail->commentaire = $request->commentaire;
 
                    if ($statutDemande === 'VALIDER') {
                        $detail->montant_paye = $item->montant;
                        $detail->montant_restant = 0;
                        $detail->statut_traitement = 'VALIDER';
                        $detail->statut_paiement = 'PAYE';
                    } else {
                        $detail->montant_paye = 0;
                        $detail->montant_restant = $item->montant;
                        $detail->statut_traitement = 'ATTENTE';
                    }
                    $detail->save();
                }
 
                $paiement->montant_total = $nouveauMontantTotal;
                $paiement->commentaire = $request->commentaire;
                $paiement->id_modepaiement = $request->modepaiement_id;
 
                if ($statutDemande === 'VALIDER') {
                    $paiement->montant_paye = $nouveauMontantTotal;
                    $paiement->montant_restant = 0;
                    $paiement->num_transaction = trim($request->num_transaction);
                    $paiement->statut_transaction = trim($request->num_transaction);
                    $paiement->statut_paiement = 'PAYE';
                    $paiement->statut_traitement = 'VALIDER';
                    $paiement->agent_traiteur = $user->id;
                    $paiement->date_traitement = now();
                } else {
                    $paiement->montant_paye = 0;
                    $paiement->montant_restant = $nouveauMontantTotal;
                    $paiement->statut_paiement = 'EN ATTENTE';
                    $paiement->statut_traitement = 'ATTENTE';
                }
                $paiement->save();
            });
        } else {
            // Pas de réduction — validation ou mise en attente classique
            DB::transaction(function () use ($paiement, $statutDemande, $request, $user) {
                if ($statutDemande === 'VALIDER') {
                    $paiement->num_transaction = trim($request->num_transaction);
                    $paiement->statut_transaction = trim($request->num_transaction);
                    $paiement->id_modepaiement = $request->modepaiement_id;
                    $paiement->commentaire = $request->commentaire;
                    $paiement->statut_paiement = 'PAYE';
                    $paiement->statut_traitement = 'VALIDER';
                    $paiement->agent_traiteur = $user->id;
                    $paiement->date_traitement = now();
                    $paiement->montant_paye = $paiement->montant_restant;
                    $paiement->montant_restant = 0;
                    $paiement->save();
 
                    if ($paiement->type !== 'OUVRAGE') {
                        $details = Detailspaiements::where('reference_paiement', $paiement->reference)->get();
                        foreach ($details as $detail) {
                            $detail->statut_traitement = 'VALIDER';
                            $detail->statut_paiement = 'PAYE';
                            $detail->montant_paye = $detail->montant_restant;
                            $detail->montant_restant = 0;
                            $detail->modepaiement_id = $request->modepaiement_id;
                            $detail->commentaire = $request->commentaire;
                            $detail->save();
                        }
                    }
                } else {
                    $paiement->id_modepaiement = $request->modepaiement_id;
                    $paiement->statut_paiement = 'EN ATTENTE';
                    $paiement->statut_traitement = 'ATTENTE';
                    $paiement->save();
                }
            });
        }
 
        if ($statutDemande === 'VALIDER') {
            $auditeur = Auditeurs::where('email', $paiement->email)->first();
            $modepaiement = Modepaiements::find($request->modepaiement_id);
            $url = $paiement->type !== 'OUVRAGE'
                ? $this->genarateurCodeService->generateUrlSigned('paiements.recu', ['reference' => $request->reference])
                : $this->genarateurCodeService->generateUrlSigned('paiements.recu-ouvrage', ['reference' => $request->reference]);
 
            $contenu = [
                'nom' => $auditeur->nom ?? '',
                'prenoms' => $auditeur->prenoms ?? '',
                'reference_paiement' => $request->reference,
                'subject' => 'TRAITEMENT DE VOTRE PAIEMENT',
                'commentaire' => 'Paiement validé',
                'email' => $paiement->email,
                'mode_paiement' => $modepaiement->libelle ?? '',
                'montant' => $paiement->montant_total,
                'type' => $paiement->type,
                'url_recu' => $url,
            ];
 
            try {
                Mail::to(trim($paiement->email))->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new Paiementvalider($contenu));
            } catch (\Exception $e) {
                Log::error('validerPaiement : échec envoi mail', ['error' => $e->getMessage(), 'reference' => $request->reference]);
            }
        }
 
        return ResponseHandler::success('Opération effectuée avec succès');
 
    } catch (\Exception $e) {
        Log::error('validerPaiement : exception', ['error' => $e->getMessage(), 'reference' => $request->reference ?? null]);
        return ResponseHandler::error('Une erreur est survenue lors de la validation du paiement',302);
    }
}

     public function validerPaiementPartiel(Request $request): \Illuminate\Http\JsonResponse
{
    try {
        $user = auth('api')->user();
        if (!$user) {
            return ResponseHandler::error('Utilisateur non authentifié',302);
        }
 
        $paiement = Paiements::where('reference', $request->reference)->first();
        if (!$paiement) {
            return ResponseHandler::error('Paiement introuvable',302);
        }
 
        $detail = Detailspaiements::find($request->identifiant);
        if (!$detail || $detail->reference_paiement !== $paiement->reference) {
            return ResponseHandler::error("Ligne de paiement introuvable ou n'appartenant pas à ce dossier",302);
        }
 
        if ($request->hasFile('preuve')) {
            $validation = validator($request->all(), [
                'preuve' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
            ]);
            if ($validation->fails()) {
                return ResponseHandler::error('Fichier de preuve invalide (jpg, jpeg, png ou pdf, 5 Mo max attendu)',302);
            }
        }
 
        // Idempotence : ligne déjà validée -> pas de retraitement.
        if ($detail->statut_traitement === 'VALIDER') {
            return ResponseHandler::success('Cette ligne a déjà été validée', ['success' => true]);
        }
 
        $statutDemande = $request->statut_traitement === 'VALIDER' ? 'VALIDER' : 'ATTENTE';
 
        if ($statutDemande !== 'VALIDER') {
            // "non VALIDER" = remettre/laisser en ATTENTE, cohérent avec le
            // comportement établi sur validerPaiementDsc. Pas de mouvement
            // financier dans ce cas.
            $detail->statut_traitement = 'ATTENTE';
            $detail->save();
            return ResponseHandler::success('Opération effectuée avec succès');
        }
 
        // Règle confirmée : montant déclaré == solde réel de la ligne, exactement.
        $montantDeclare = (int) $request->montant_total;
        $soldeLigne = (int) $detail->montant_restant;
 
        if ($soldeLigne <= 0) {
            return ResponseHandler::error('Cette ligne ne présente plus de solde à régler',302);
        }
 
        if ($montantDeclare !== $soldeLigne) {
            return ResponseHandler::error("Le montant déclaré ({$montantDeclare}) doit correspondre exactement au solde dû sur cette ligne ({$soldeLigne})",302);
        }
 
        $modePaiement = Modepaiements::find($request->modepaiement_id);
        if (!$modePaiement) {
            return ResponseHandler::error('Mode de paiement invalide',302);
        }
 
        $numTransaction = trim((string) ($request->num_transaction ?? ''));
 
        // Fichier de preuve — nom unique garanti, toujours dans public_path.
        $preuveNameUrl = null;
        if ($request->hasFile('preuve')) {
            $preuve = $request->file('preuve');
            $preuveNameUrl = date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $preuve->getClientOriginalExtension();
            $preuve->move(public_path('preuve'), $preuveNameUrl);
        }
 
        DB::transaction(function () use (
            $paiement, $detail, $montantDeclare, $numTransaction, $modePaiement,
            $preuveNameUrl, $user, $request
        ) {
            $detail->statut_traitement = 'VALIDER';
            $detail->statut_paiement = 'PAYE';
            $detail->montant_paye = $detail->montant_paye + $montantDeclare;
            $detail->montant_restant = 0;
            $detail->commentaire = $request->commentaire;
            $detail->modepaiement_id = $modePaiement->id;
            $detail->reference_paiement_partiel = $numTransaction;
            $detail->is_paiement_partiel = true;
            if ($preuveNameUrl) {
                $detail->preuve_path = $preuveNameUrl;
            }
            $detail->save();
 
            $nouveauMontantPaye = $paiement->montant_paye + $montantDeclare;
            $nouveauMontantRestant = $paiement->montant_restant - $montantDeclare;
 
            $paiement->montant_paye = $nouveauMontantPaye;
            $paiement->montant_restant = $nouveauMontantRestant;
            $paiement->agent_traiteur = $user->id;
            $paiement->date_traitement = now();
 
            if ($nouveauMontantRestant > 0) {
                $paiement->is_paiement_partiel = true;
            } else {
                $paiement->statut_paiement = 'PAYE';
                $paiement->statut_traitement = 'VALIDER';
                $paiement->commentaire = $request->commentaire;
                $paiement->num_transaction = $numTransaction;
                $paiement->id_modepaiement = $modePaiement->id;
            }
            $paiement->save();
        });
 
        $auditeur = Auditeurs::where('email', $paiement->email)->first();
 
        $contenu = [
            'nom' => $auditeur->nom ?? '',
            'prenoms' => $auditeur->prenoms ?? '',
            'reference_paiement' => $paiement->reference,
            'subject' => 'TRAITEMENT DE VOTRE PAIEMENT',
            'commentaire' => 'Paiement validé',
            'email' => $paiement->email,
            'mode_paiement' => $modePaiement->libelle ?? '',
            'montant' => $montantDeclare,
            'type' => $paiement->type,
            'url_recu' => $this->genarateurCodeService->generateUrlSigned('paiements.recu', ['reference' => $paiement->reference]),
        ];
 
        try {
            Mail::to(trim($paiement->email))->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new Paiementvalider($contenu));
        } catch (\Exception $e) {
            Log::error('validerPaiementPartiel : échec envoi mail', ['error' => $e->getMessage(), 'reference' => $paiement->reference]);
        }
 
        return ResponseHandler::success('Opération effectuée avec succès');
 
    } catch (\Exception $e) {
        Log::error('validerPaiementPartiel : exception', ['error' => $e->getMessage(), 'reference' => $request->reference ?? null]);
        return ResponseHandler::error('Une erreur est survenue lors de la validation du paiement',302);
    }
}

    public function validerPaiementDsc(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return ResponseHandler::error('Utilisateur non authentifié');
            }
    
            $reference = $request->reference;
            $paiement = Paiements::where('reference', $reference)->first();
            if (!$paiement) {
                return ResponseHandler::error('Paiement introuvable',302);
            }
    
            if ($request->hasFile('preuve')) {
                $validation = validator($request->all(), [
                    'preuve' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
                ]);
                if ($validation->fails()) {
                    return ResponseHandler::error('Fichier de preuve invalide (jpg, jpeg, png ou pdf, 5 Mo max attendu)',302);
                }
            }
    
            $statutDemande = $request->statut_traitement === 'VALIDER' ? 'VALIDER' : 'ATTENTE';
            $numTransactionSaisi = trim((string) ($request->num_transaction ?? ''));
    
            $listePTranches = collect($request->listeTranches ?? [])->map(fn ($item) => (object) $item);
            if ($listePTranches->isEmpty()) {
                return ResponseHandler::error('Aucune tranche sélectionnée',302);
            }
    
            // Fichier de preuve déplacé UNE SEULE FOIS (l'original le faisait dans
            // la boucle -> échec garanti dès la 2e tranche avec preuve jointe).
            $preuveNameUrl = null;
            if ($request->hasFile('preuve')) {
                $preuve = $request->file('preuve');
                $preuveNameUrl = date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $preuve->getClientOriginalExtension();
                $preuve->move(public_path('preuve'), $preuveNameUrl);
            }
    
            // Résoudre et vérifier l'appartenance de chaque tranche AVANT toute
            // écriture — jamais de confiance aveugle sur tranche_id.
            $tranchesResolues = [];
            foreach ($listePTranches as $data) {
                $trancheId = $data->tranche_id ?? null;
                if (!$trancheId) {
                    return ResponseHandler::error('tranche_id manquant sur une ligne soumise',302);
                }
    
                $paiementTranche = PaiementsTranche::find($trancheId);
                if (!$paiementTranche || strpos($paiementTranche->reference, $paiement->reference) !== 0) {
                    return ResponseHandler::error("La tranche {$trancheId} est introuvable ou n'appartient pas à ce dossier",302);
                }
    
                $detail = Detailspaiements::find($paiementTranche->details_paiements_id);
                if (!$detail) {
                    return ResponseHandler::error("Ligne de détail introuvable pour la tranche {$trancheId}",302);
                }
    
                $tranchesResolues[] = (object) ['tranche' => $paiementTranche, 'detail' => $detail];
            }
    
            $auMoinsUnChangement = false;
    
            DB::transaction(function () use (
                $tranchesResolues, $statutDemande, $numTransactionSaisi, $request,
                $user, $preuveNameUrl, &$auMoinsUnChangement
            ) {
                foreach ($tranchesResolues as $item) {
                    $tranche = $item->tranche;
                    $detail = $item->detail;
    
                    // déjà VALIDER : on ne rétrograde jamais par erreur, et on
                    // évite un retraitement inutile (mails en double, etc.)
                    if ($tranche->statut_traitement === 'VALIDER' && $statutDemande !== 'VALIDER') {
                        continue;
                    }
                    if ($tranche->statut_traitement === $statutDemande) {
                        continue; // rien à changer
                    }
    
                    $estOrigineKkiapay = (int) $tranche->modepaiement_id === 1;
    
                    //if (!$estOrigineKkiapay) {
                        // saisie manuelle : la référence/mode du comptable s'applique
                        $tranche->num_transaction = $numTransactionSaisi;
                        $tranche->statut_transaction = $numTransactionSaisi;
                        $tranche->modepaiement_id = $request->modepaiement_id;
                    //}
                    // sinon (origine KkiaPay) : num_transaction/statut_transaction/
                    // modepaiement_id restent la vraie référence KkiaPay, intouchés
    
                    $tranche->statut_traitement = $statutDemande;
                    $tranche->traiter_par = $user->id;
                    if ($preuveNameUrl) {
                        $tranche->preuve_path = $preuveNameUrl;
                    }
                    $tranche->save();
    
                    if ($statutDemande === 'VALIDER') {
                        if ((int) $detail->montant_restant === 0) {
                            $detail->statut_traitement = 'VALIDER';
                            $detail->statut_paiement = 'PAYE';
                        }
                        if ($preuveNameUrl) {
                            $detail->preuve_path = $preuveNameUrl;
                        }
                    } else {
                        // cohérence : la ligne redescend en ATTENTE avec sa tranche,
                        // sauf si elle était déjà validée par ailleurs (protection
                        // contre une rétrogradation accidentelle)
                        if ($detail->statut_traitement !== 'VALIDER') {
                            $detail->statut_traitement = 'ATTENTE';
                        }
                    }
                    $detail->save();
    
                    $auMoinsUnChangement = true;
                }
            });
    
            // Mise à jour du statut global du Paiements — en dehors du closure
            // précédent pour rester simple, mais toujours dans la même requête,
            // basée uniquement sur le solde réel.
            $paiement->refresh();
            if ($statutDemande === 'VALIDER') {
                if ((int) $paiement->montant_restant === 0 && $paiement->statut_traitement !== 'VALIDER') {
                    $paiement->num_transaction = $numTransactionSaisi ?: $paiement->num_transaction;
                    $paiement->statut_transaction = $numTransactionSaisi ?: $paiement->statut_transaction;
                    $paiement->id_modepaiement = $request->modepaiement_id ?: $paiement->id_modepaiement;
                    $paiement->statut_paiement = 'PAYE';
                    $paiement->statut_traitement = 'VALIDER';
                    $paiement->agent_traiteur = $user->id;
                    $paiement->date_traitement = now();
                    $paiement->save();
                }
            } else {
                if ($paiement->statut_traitement !== 'VALIDER') {
                    $paiement->id_modepaiement = $request->modepaiement_id ?: $paiement->id_modepaiement;
                    $paiement->statut_paiement = 'EN ATTENTE';
                    $paiement->statut_traitement = 'ATTENTE';
                    $paiement->save();
                }
            }
    
            if ($statutDemande === 'VALIDER' && $auMoinsUnChangement) {
                $auditeur = Auditeurs::where('email', $paiement->email)->first();
                $modepaiement = Modepaiements::find($paiement->id_modepaiement);
    
                $contenu = [
                    'nom' => $auditeur->nom ?? '',
                    'prenoms' => $auditeur->prenoms ?? '',
                    'reference_paiement' => $reference,
                    'subject' => 'TRAITEMENT DE VOTRE PAIEMENT',
                    'commentaire' => 'Paiement validé',
                    'email' => $paiement->email,
                    'mode_paiement' => $modepaiement->libelle ?? '',
                    'montant' => $request->montant_total,
                    'montant_restant' => $paiement->montant_restant,
                    'type' => $paiement->type,
                    'url_recu' => $this->genarateurCodeService->generateUrlSigned('paiements.recu', ['reference' => $reference]),
                ];
    
                try {
                    Mail::to(trim($paiement->email))->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new PaiementValideDsc($contenu));
                } catch (\Exception $e) {
                    Log::error('validerPaiementDsc : échec envoi mail', ['error' => $e->getMessage(), 'reference' => $reference]);
                }
            }
    
            return ResponseHandler::success('Opération effectuée avec succès');
    
        } catch (\Exception $e) {
            Log::error('validerPaiementDsc : exception', ['error' => $e->getMessage(), 'reference' => $request->reference ?? null]);
            return ResponseHandler::error('Une erreur est survenue lors de la validation du paiement',302);
        }
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
    
    /**
     * Requête filtrée commune à la liste et à l'export (sans tri ni pagination).
     */
    public function listePaiementsQuery(Request $request)
    {
        /* =========================================================
        |  1. Requête de base (jointures + colonnes)
        ========================================================= */
        $base = Inscriptions::query()
            ->leftJoin('auditeurs', 'auditeurs.email', '=', 'inscriptions.email')
            ->leftJoin('detailspaiements as dp', function ($join) {
                $join->on('dp.id_tarification_formation', '=', 'inscriptions.tarif_formation_id')
                    ->on('dp.email', '=', 'inscriptions.email');  
            })
            ->leftJoin('paiements as p', 'p.reference', '=', 'dp.reference_paiement')
            ->leftJoin('modepaiements as mp', 'mp.id', '=', 'p.id_modepaiement')
            ->leftJoin('reductions', 'reductions.id', '=', 'dp.id_reduction')
            ->leftJoin('formations', 'formations.code_formation', '=', 'inscriptions.code_formation')
            ->selectRaw("
                inscriptions.id,
                inscriptions.tarif_formation_id,
                inscriptions.code_formation,
                inscriptions.code_inscription,
                inscriptions.updated_at,
                auditeurs.email,
                auditeurs.nom,
                auditeurs.prenoms,
                CONCAT(auditeurs.prenoms, ' ', auditeurs.nom) as auditeur,
                CASE
                    WHEN p.type = 'DSC' THEN (
                        SELECT SUM(COALESCE(dp2.montant_paye, 0) + COALESCE(dp2.montant_restant, 0))
                        FROM detailspaiements dp2
                        WHERE dp2.reference_paiement = dp.reference_paiement
                    )
                    ELSE COALESCE(dp.montant_paye, 0) + COALESCE(dp.montant_restant, 0)
                END as montant,
                dp.id as detailpaiement_id,
                dp.reference_paiement,
                dp.id_tarification_formation,
                dp.preuve_path,
                dp.statut_paiement,
                dp.have_reduction_dsc,
                dp.reference_paiement_partiel,
                dp.montant_restant,
                mp.libelle as modepaiement,
                mp.id as modepaiement_id,
                p.type,
                p.id as paiement_id,
                p.num_transaction,
                reductions.id as id_reduction,
                reductions.pourcentage,
                formations.id as formation_id,
                formations.is_formation_dsc,
                formations.theme
            ")
            ->selectRaw("
                EXISTS (
                    SELECT 1 FROM paiements_tranches pt
                    WHERE pt.details_paiements_id = dp.id
                    AND pt.statut_traitement = 'ATTENTE'
                ) as check_statut_ptranche
            ")
            ->selectRaw("
                EXISTS (
                    SELECT 1 FROM paiements_tranches pt
                    WHERE pt.details_paiements_id = dp.id
                    AND pt.statut_traitement = 'VALIDER'
                ) as check_recu_ptranche
            ");
    
        /* =========================================================
        |  2. Filtres sur des colonnes réelles : appliqués sur la base 
        ========================================================= */
        if ($search = $request->input('search')) {
            $base->where(function ($q) use ($search) {
                $q->where('auditeurs.nom', 'like', "%{$search}%")
                ->orWhere('auditeurs.prenoms', 'like', "%{$search}%")
                ->orWhere('auditeurs.email', 'like', "%{$search}%")
                ->orWhere('dp.reference_paiement', 'like', "%{$search}%")
                ->orWhere('dp.statut_paiement', 'like', "%{$search}%")
                ->orWhere('inscriptions.code_formation', 'like', "%{$search}%")
                ->orWhere('mp.libelle', 'like', "%{$search}%");
            });
        }
    
        $base
            ->when($request->filled('filter_updated_at'), fn ($q) => $q->whereDate('inscriptions.updated_at', $request->filter_updated_at))
            ->when($request->filled('date_debut'),        fn ($q) => $q->whereDate('inscriptions.updated_at', '>=', $request->date_debut))
            ->when($request->filled('date_fin'),          fn ($q) => $q->whereDate('inscriptions.updated_at', '<=', $request->date_fin))
            ->when($request->filled('modepaiement_id'),   fn ($q) => $q->where('mp.id', $request->modepaiement_id))
            ->when($request->filled('formation_id'),      fn ($q) => $q->where('formations.id', $request->formation_id))
            ->when($request->filled('statut_paiement'),      fn ($q) => $q->where('dp.statut_paiement', $request->statut_paiement));
    
        /* =========================================================
        |  3. "montant" est un alias calculé : on enveloppe la requête
        |     pour pouvoir le filtrer (et le trier) comme une colonne
        ========================================================= */
        return DB::query()
            ->fromSub($base, 't')
            ->when($request->filled('montant_min'), fn ($q) => $q->where('t.montant', '>=', (float) $request->montant_min))
            ->when($request->filled('montant_max'), fn ($q) => $q->where('t.montant', '<=', (float) $request->montant_max));
    }
    
    
    /**
     * Tri : liste blanche des colonnes autorisées.
     */
    private function appliquerFiltreP($query, Request $request)
    {
        $allowed = [
            'nom', 'prenoms', 'auditeur', 'email', 'reference_paiement', 'code_formation',
            'statut_paiement', 'montant', 'modepaiement', 'type', 'updated_at',
        ];
    
        $field = in_array($request->input('sort_field'), $allowed, true) ? $request->input('sort_field') : 'updated_at';
        $order = $request->input('sort_order') === 'desc' ? 'desc' : 'asc';
    
        return $query
            ->orderBy("t.$field", $order)
            ->orderBy('t.id');   // départage les égalités : pagination stable
    }

    public function getListePaiements(Request $request)
    {
        // TODO: Implement getListePaiements() method.
        $perPage = $request->input('per_page', 50);
        return $this->appliquerFiltreP($this->listePaiementsQuery($request), $request)
        ->paginate($perPage);  
    }

    /**
     * Pour l'export : même requête, sans pagination.
     */
    public function getListePaiementsExport(Request $request)
    {
        return $this->appliquerFiltreP($this->listePaiementsQuery($request), $request)->get();
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

        $update_reference = $paiement->reference;

        if($checkPaiementTranche){
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
                'checkPaiementTranche' => $checkPaiementTranche,
                'update_reference' => $update_reference
            ];
            return  ResponseHandler::success("ok",$data);
        }else{
            // error le paiement n'existe pas
            return  ResponseHandler::error("Le paiement n'existe pas");
        }

    }

     public function validerProcessPaiementDsc(Request $request): \Illuminate\Http\JsonResponse
        {
        try {

            $user = auth('api')->user();
            if (!$user) {
                return ResponseHandler::error('Utilisateur non authentifié');
            }
            Log::info("begin validation paiement {$request->reference} montant reçu : {$request->montant_total_recu}");
    
            // Validation stricte du fichier de preuve
            if ($request->hasFile('preuve')) {
                $validation = validator($request->all(), [
                    'preuve' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
                ]);
                if ($validation->fails()) {
                    return ResponseHandler::error('Fichier de preuve invalide (jpg, jpeg, png ou pdf, 5 Mo max attendu)');
                }
            }
    
            $paiement = Paiements::where('reference', $request->reference)->first();
            if (!$paiement) {
                return ResponseHandler::error('Paiement introuvable');
            }
    
            $userInfo = User::where('email', $paiement->email)->first();
            if (!$userInfo) {
                return ResponseHandler::error('Auditeur introuvable pour ce paiement');
            }
    
            $modePaiement = Modepaiements::find($request->modepaiement_id);
            if (!$modePaiement) {
                return ResponseHandler::error('Mode de paiement invalide');
            }
    
            $montantTotalRecu = (int) $request->montant_total_recu;
            $codePaiement = trim((string) $request->code_paiement);
    
            if ($montantTotalRecu <= 0) {
                return ResponseHandler::error('Montant invalide');
            }
    
            // Idempotence : ce code_paiement a-t-il déjà été appliqué sur ce dossier ?
            if ($codePaiement !== '' && PaiementsTranche::where('reference', 'like', "%{$paiement->reference}%")
                ->where('num_transaction', $codePaiement)->exists()) {
                return ResponseHandler::error('Ce paiement a déjà été validé précédemment', 302);
            }
    
            // Jamais au-delà du solde réellement dû (source de vérité : la base,
            // pas montant_total envoyé par le client comme dans l'original).
            if ($montantTotalRecu > (int) $paiement->montant_restant) {
                return ResponseHandler::error('Le montant reçu dépasse le solde dû sur ce dossier',302);
            }
    
            // Résoudre chaque ligne annoncée depuis la base — jamais depuis le
            // payload — pour type_tarif / grille / montant_restant.
            $listePTranches = collect($request->listeTranches ?? []);
            $inscriptionDetail = null;
            $scolariteDetail = null;
    
            foreach ($listePTranches as $item) {
                $item = (object) $item;
                $detailId = $item->detailsPaiementId ?? null;
                if (!$detailId) {
                    return ResponseHandler::error('detailsPaiementId manquant sur une ligne soumise',302);
                }
                Log::info("id details paiement ok {$detailId}");

                $detail = Detailspaiements::find($detailId);
                if (!$detail || $detail->code_inscription !== $paiement->code_inscription) {
                    return ResponseHandler::error("La ligne {$detailId} est introuvable ou n'appartient pas à ce dossier", 302);
                }
                Log::info("id details paiement retrouvé {$detail->id}");
                if ((int) $detail->montant_restant <= 0) {
                    continue; // déjà soldée, on l'ignore silencieusement
                }
    
                $typeTarif = $detail->getTarification->getGrilletarifaire->type_tarif ?? null;
                if ($typeTarif === 'INSCRIPTION') {
                    $inscriptionDetail = $detail;
                } else {
                    $scolariteDetail = $detail;
                }
            }
    
            if (!$inscriptionDetail && !$scolariteDetail) {
                return ResponseHandler::error('Aucune ligne restant due sur ce dossier parmi celles soumises');
            }
    
            // Priorité aux frais d'inscription (obligatoires, intégraux), comme
            // dans l'original.
            $montantRestantAAllouer = $montantTotalRecu;
            $montantInscriptionApplique = 0;
            $montantScolariteApplique = 0;
            $baseRestantScolarite = 0;
            $reductionAppliquee = false;
            $checkTranche = $request->input('haveTranche');
            // vérifier si la tranche existe, sinon mettre double tranche
            if ($checkTranche) {
                # code...
                $getTranche = Tranches::whereCodeInscription($paiement->code_inscription)->first();
                if ($getTranche) {
                    # code...
                    if ($getTranche->tranche_2 > 0) {
                        # code...
                        $trancheDemandee = "tranche_double";
                    }else{
                        $trancheDemandee = "tranche_unique";
                    }
                } else {
                    # code...
                    $trancheDemandee = "tranche_double";
                }
                
            } else {
                # code...
                $trancheDemandee = "tranche_double";
            }
            
            //$trancheDemandee = $request->input('haveTranche');
            $ligneScolariteIntacte = false;
    
            if ($inscriptionDetail) {
                $soldeInscription = (int) $inscriptionDetail->montant_restant;
                if ($montantRestantAAllouer < $soldeInscription) {
                    return ResponseHandler::error("Le montant reçu est inférieur au solde des frais d'inscription ({$soldeInscription})", 302);
                }
                $montantInscriptionApplique = $soldeInscription;
                $montantRestantAAllouer -= $montantInscriptionApplique;
            }
    
            if ($scolariteDetail && $montantRestantAAllouer > 0) {
                $grille = $scolariteDetail->getTarification->getGrilletarifaire ?? null;
                if (!$grille) {
                    return ResponseHandler::error('Grille tarifaire introuvable pour la ligne scolarité', 302);
                }
    
                $ligneScolariteIntacte = (int) $scolariteDetail->montant_paye === 0;
                $baseRestantScolarite = (int) $scolariteDetail->montant_restant;
                $reductionAppliquee = $scolariteDetail->have_reduction_dsc;
                // CAS OU AUCUN PAIEMENT DES FRAIS DE SCOLARITE N'EST ENCORE EFFECTUE
                if ($ligneScolariteIntacte) {
                    if ($trancheDemandee === 'tranche_unique') {
                        $baseRestantScolarite = (int) round($baseRestantScolarite);
                        $reductionAppliquee = true;
                        if ($montantRestantAAllouer < $baseRestantScolarite) {
                            return ResponseHandler::error('Le montant reçu est inférieur au solde de scolarité (tranche unique, doit être réglée en totalité)',302);
                        }
                        Log::info("tranche unique et montant valide");
                    } else {
                        $reductionAppliquee = false;
                        $montantGrille = (int) $grille->montant;
                        if ($montantRestantAAllouer < ($montantGrille/2)) {
                            return ResponseHandler::error('Le montant reçu est inférieur à 50% des frais de scolarité (tranche double)'.($montantGrille/2),302);
                        }
                        Log::info("tranche double et montant valide");
                    }
                }else{
                    // sinon (ligne déjà entamée) : tranche ignorée, borne = montant_restant réel

                    // le montant à payer devrait soldé le montant restant
                    if ($montantRestantAAllouer < $baseRestantScolarite) {
                        return ResponseHandler::error('Le montant reçu est inférieur au solde de scolarité restante '.$baseRestantScolarite,302);
                    }

                }
                // vérifier si le reliquat dépasse le montant restant de la scolarité
                if ($montantRestantAAllouer > $baseRestantScolarite) {
                    return ResponseHandler::error('Le montant appliqué à la scolarité dépasse le solde réellement dû');
                }
    
                $montantScolariteApplique = $montantRestantAAllouer;
            } elseif ($montantRestantAAllouer > 0) {
                // reliquat non attribuable (pas de ligne scolarité soumise pour l'absorber)
                return ResponseHandler::error('Le montant reçu excède ce qui peut être appliqué aux lignes soumises');
            }
    
            // Fichier de preuve — nom garanti unique (pas seulement à la seconde
            // près comme l'original), toujours dans public_path comme demandé.
            $preuveNameUrl = null;
            if ($request->hasFile('preuve')) {
                $preuve = $request->file('preuve');
                $preuveName = date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $preuve->getClientOriginalExtension();
                $preuve->move(public_path('preuve'), $preuveName);
                $preuveNameUrl = $preuveName;
            }
    
            $getPTranche = PaiementsTranche::where('reference', 'like', "%{$paiement->reference}%")->latest()->first();
            $suffixe = $getPTranche
                ? ((int) (explode('_', $getPTranche->reference)[1] ?? 0)) + 1
                : 1;
            $updateReference = $paiement->reference . '_' . $suffixe;
    
            DB::transaction(function () use (
                $paiement, $inscriptionDetail, $scolariteDetail, $montantInscriptionApplique,
                $montantScolariteApplique, $baseRestantScolarite, $reductionAppliquee,
                $ligneScolariteIntacte, $trancheDemandee, $montantTotalRecu, $codePaiement,
                $modePaiement, $preuveNameUrl, $updateReference, $user, $userInfo
            ) {
                if ($inscriptionDetail) {
                    $inscriptionDetail->montant_paye = $inscriptionDetail->montant_paye + $montantInscriptionApplique;
                    $inscriptionDetail->montant_restant = 0;
                    $inscriptionDetail->modepaiement_id = $modePaiement->id;
                    $inscriptionDetail->statut_traitement = 'VALIDER';
                    $inscriptionDetail->statut_paiement = 'PAYE';
                    if ($preuveNameUrl) {
                        $inscriptionDetail->preuve_path = $preuveNameUrl;
                    }
                    $inscriptionDetail->save();
    
                    PaiementsTranche::forceCreate([
                        'details_paiements_id' => $inscriptionDetail->id,
                        'montant_paye' => $montantInscriptionApplique,
                        'montant_restant' => 0,
                        'reference' => $updateReference,
                        'num_transaction' => $codePaiement,
                        'statut_transaction' => $codePaiement,
                        'statut_traitement' => 'VALIDER',
                        'preuve_path' => $preuveNameUrl,
                        'modepaiement_id' => $modePaiement->id,
                        'is_inscription' => true,
                        'traiter_par' => $user->id,
                        'created_by' => $userInfo->id,
                        'updated_by' => $userInfo->id,
                    ]);
                }
    
                if ($scolariteDetail && $montantScolariteApplique > 0) {
                    $nouveauRestant = max(0, $baseRestantScolarite - $montantScolariteApplique);
    
                    $scolariteDetail->montant_paye = $scolariteDetail->montant_paye + $montantScolariteApplique;
                    $scolariteDetail->montant_restant = $nouveauRestant;
                    $scolariteDetail->modepaiement_id = $modePaiement->id;
                    $scolariteDetail->have_reduction_dsc = $reductionAppliquee;
                    if ($nouveauRestant <= 0) {
                        $scolariteDetail->statut_traitement = 'VALIDER';
                        $scolariteDetail->statut_paiement = 'PAYE';
                    }
                    if ($preuveNameUrl) {
                        $scolariteDetail->preuve_path = $preuveNameUrl;
                    }
                    $scolariteDetail->save();
    
                    // échéancier — recrée/actualise seulement si la ligne était
                    // intacte (nouveau plan) ; sinon celui existant reste valable.
                    $trancheId = null;
                    if ($ligneScolariteIntacte && $trancheDemandee) {
                        $trancheId = $this->genarateurCodeService->enregistrerEcheancierTranche($paiement, $scolariteDetail, $trancheDemandee, $userInfo);
                    } else {
                        $echeancierExistant = Tranches::where('code_inscription', $paiement->code_inscription)->first();
                        $trancheId = $echeancierExistant->id ?? null;
                    }
    
                    PaiementsTranche::forceCreate([
                        'details_paiements_id' => $scolariteDetail->id,
                        'montant_paye' => $montantScolariteApplique,
                        'montant_restant' => $nouveauRestant,
                        'reference' => $updateReference,
                        'tranche_id' => $trancheId,
                        'num_transaction' => $codePaiement,
                        'statut_transaction' => $codePaiement,
                        'statut_traitement' => 'VALIDER',
                        'preuve_path' => $preuveNameUrl,
                        'modepaiement_id' => $modePaiement->id,
                        'is_inscription' => false,
                        'traiter_par' => $user->id,
                        'created_by' => $userInfo->id,
                        'updated_by' => $userInfo->id,
                    ]);
                }
    
                $nouveauMontantPaye = $paiement->montant_paye + $montantTotalRecu;
                $nouveauMontantRestant = $paiement->montant_restant - $montantTotalRecu;
    
                $paiement->montant_paye = $nouveauMontantPaye;
                $paiement->montant_restant = $nouveauMontantRestant;
                if ($nouveauMontantRestant <= 0) {
                    $paiement->num_transaction = $codePaiement;
                    $paiement->statut_transaction = $codePaiement;
                    $paiement->statut_paiement = 'PAYE';
                }
                $paiement->save();
            });
    
            // Notifications — best-effort, hors transaction
            $auditeur = Auditeurs::where('email', $paiement->email)->first();
    
            $contenu = [
                'nom' => $auditeur->nom ?? '',
                'prenoms' => $auditeur->prenoms ?? '',
                'reference_paiement' => $updateReference,
                'subject' => 'INFORMATION SUR VOTRE PAIEMENT ' . $updateReference,
                'email' => $paiement->email,
                'montant' => $montantTotalRecu,
                'montant_restant' => $paiement->montant_restant,
                'mode_paiement' => $modePaiement->libelle ?? '',
            ];
    
            try {
                Mail::to($paiement->email)->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new DscPaiementAttente($contenu));
            } catch (\Exception $e) { 
                Log::error('validerProcessPaiementDsc : échec envoi mail auditeur', ['error' => $e->getMessage(), 'reference' => $updateReference]);
            }
    
            if (env('PAIEMENT_NOTIFIER_COMPTABLE')) {
                $listeComptable = User::whereHas('roles', fn ($q) => $q->where('libelle', 'COMPTABLE'))->pluck('email');
    
                try {
                    Mail::to(env('MAIL_CC'))->cc($listeComptable)->bcc(env('MAIL_BCC'))->queue(new NotificationComptable([
                        'name' => 'COMPTABLE',
                        'reference_paiement' => $updateReference,
                        'subject' => 'NOUVEAU PAIEMENT ' . now()->format('d-m-Y H:i:s'),
                        'email' => '',
                        'montant' => $montantTotalRecu,
                        'mode_paiement' => $modePaiement->libelle ?? '',
                    ]));
                } catch (\Exception $e) {
                    Log::error('validerProcessPaiementDsc : échec envoi mail comptable', ['error' => $e->getMessage(), 'reference' => $updateReference]);
                }
            }
    
            try {
                Mail::to(trim($paiement->email))->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new PaiementValideDsc([
                    'nom' => $auditeur->nom ?? '',
                    'prenoms' => $auditeur->prenoms ?? '',
                    'reference_paiement' => $updateReference,
                    'subject' => 'TRAITEMENT DE VOTRE PAIEMENT',
                    'commentaire' => 'Paiement validé',
                    'email' => $paiement->email,
                    'mode_paiement' => $modePaiement->libelle ?? '',
                    'montant' => $montantTotalRecu,
                    'montant_restant' => $paiement->montant_restant,
                    'type' => $paiement->type,
                    'url_recu' => $this->genarateurCodeService->generateUrlSigned('paiements.recu', ['reference' => $paiement->reference]),
                ]));
            } catch (\Exception $e) {
                Log::error('validerProcessPaiementDsc : échec envoi mail traitement', ['error' => $e->getMessage(), 'reference' => $updateReference]);
            }
    
            return ResponseHandler::success('Opération effectuée avec succès', ['success' => true]);
    
        } catch (\Exception $e) {
            Log::error('validerProcessPaiementDsc : exception', ['error' => $e->getMessage(), 'reference' => $request->reference ?? null]);
            return ResponseHandler::error("Une erreur est survenue lors de la validation du paiement");
        }
    }
}
