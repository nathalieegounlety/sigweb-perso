<?php

namespace App\Services\Impl;

use App\Services\contracts\ComptabilitesInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComptabilitesService implements ComptabilitesInterface
{

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
            ->select(
                'auditeurs.nom',
                'auditeurs.prenoms',
                'paiements.telephone',
                'paiements.email',
                'paiements.reference',
                'paiements.montant_restant', // pas d'alias ici pour simplifier
                'paiements.num_transaction',
                'paiements.updated_at',
                'paiements.type',
                'paiements.id' // important pour le tri et la pagination
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

    public function getPaiementsattente(Request $request): LengthAwarePaginator
    {
        // TODO: Implement getPaiementsattente() method.
    }

    public function getPaiementsvalides(Request $request): LengthAwarePaginator
    {
        // TODO: Implement getPaiementsvalides() method.
    }
}
