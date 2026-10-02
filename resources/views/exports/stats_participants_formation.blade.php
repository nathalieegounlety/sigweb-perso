@php
    $estDsc = (bool) $formation->is_formation_dsc;
    // Colonnes avant "Payé" dans le pied de tableau
    $colsAvantPaye = $estDsc ? 9 : 6;
    $nbColonnes    = $estDsc ? 13 : 10;

    $libelleReduction = function ($r) {
        if ($r->have_reduction_dsc) {
            return $r->code_remise_dsc === 'BOURSE_COMPLET' ? '100% (Bourse complète)' : '5%';
        }
        return ((float) $r->reduction_affichee) . '%';
    };

    $libellesReduction = ['BOURSE_COMPLET' => 'Bourse complète', 'REDUCTION_5' => 'Réduction 5%', 'SANS' => 'Sans réduction'];
@endphp
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #1f2937; }
    h2 { margin: 0 0 2px; font-size: 13px; }
    h3 { font-size: 10.5px; margin: 10px 0 4px; }
    .sous-titre { margin: 0 0 4px; font-size: 10px; }
    .note { color: #6b7280; font-size: 8px; margin-bottom: 6px; }
    .filtres { color: #555; margin-bottom: 8px; line-height: 1.5; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #9ca3af; padding: 3px 4px; vertical-align: middle; }
    th { background: #e5e7eb; text-align: left; }
    tr:nth-child(even) td { background: #f9fafb; }
    .c { text-align: center; }
    .r { text-align: right; white-space: nowrap; }
    .paye { color: #15803d; font-weight: bold; }
    .impaye { color: #b91c1c; font-weight: bold; }
    tfoot td { font-weight: bold; background: #f3f4f6; }

    /* Tableau récapitulatif */
    .recap { width: 60%; margin-bottom: 4px; font-size: 9.5px; }
    .recap th, .recap td { border: 1.5px solid #000; padding: 4px 6px; text-align: center; }
    .recap th, .recap tr.total td { background: #aabbdd; font-weight: bold; }
    .recap th.vide { background: transparent; border-color: transparent; }
    .recap td.libelle { font-weight: bold; text-align: left; }
</style>
</head>
<body>

    <h2>SIGWEB ETAT RECAPITULATIF DES PAIEMENTS DE LA FORMATION - {{ $formation->code_formation }}</h2>
    @if(!empty($formation->theme))
        <p class="sous-titre">{{ $formation->theme }}</p>
    @endif
    <p class="note">Édité le {{ now()->format('d/m/Y à H:i') }}</p>

    {{-- ============ RÉCAPITULATIF (global, non filtré) ============ --}}
    <h3>Récapitulatif</h3>
    <table class="recap">
        <thead>
            <tr>
                <th class="vide"></th>
                <th>Inscrits</th>
                <th>Gratuits</th>
                <th>Payants</th>
                @if($fmGratuite) <th>Attestations</th> @endif
                <th>Ont participé</th>
            </tr>
        </thead>
        <tbody>
        @foreach($lignesRecap as $l)
            <tr class="{{ $l['libelle'] === 'TOTAL' ? 'total' : '' }}">
                <td class="libelle">{{ $l['libelle'] }}</td>
                <td>{{ $l['inscrits'] }}</td>
                <td>{{ $l['gratuits'] }}</td>
                <td>{{ $l['payants'] }}</td>
                @if($fmGratuite) <td>{{ $l['attestations'] }}</td> @endif
                <td>{{ $l['participants'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <p class="note">Le récapitulatif porte sur l'ensemble de la formation ; les filtres ne s'appliquent qu'à l'état de paiement ci-dessous.</p>

    {{-- ============ ÉTAT DE PAIEMENT (filtré) ============ --}}
    <h3>Etat de paiement</h3>
    <div class="filtres">
        Critères :
        @if(empty(array_filter($filtres, fn ($v) => $v !== null && $v !== ''))) aucun (tous les participants) @endif
        @if(!empty($filtres['type_formation'])) — {{ $estDsc ? 'Catégorie' : 'Mode' }} : {{ $filtres['type_formation'] }} @endif
        @if(!empty($filtres['statut_paiement']))
            — Statut : {{ $filtres['statut_paiement'] === 'PAYE' ? 'Payé' : 'Non payé' }}
        @endif
        @if(!empty($filtres['type_reduction']))
            — Réduction : {{ $libellesReduction[$filtres['type_reduction']] ?? $filtres['type_reduction'] }}
        @endif
        @if(isset($filtres['a_participe']) && $filtres['a_participe'] !== '')
            — A participé : {{ $filtres['a_participe'] ? 'Oui' : 'Non' }}
        @endif
        @if(!empty($filtres['search'])) — Recherche : « {{ $filtres['search'] }} » @endif
    </div>

    <table>
        <thead>
            <tr>
                <th class="c">#</th>
                <th>Nom et Prénoms</th>
                <th>E-mail / Téléphone</th>
                <th class="c">{{ $estDsc ? 'Catégorie' : 'Mode' }}</th>
                @if($estDsc)
                    <th class="r">Frais d'inscription</th>
                    <th class="r">Frais de participation</th>
                    <th class="c">Réduction</th>
                @endif
                <th class="r">Montant total</th>
                <th class="c">Statut</th>
                <th class="r">Payé</th>
                <th class="r">Restant</th>
                <th class="c">Participé</th>
                <th>Observation</th>
            </tr>
        </thead>
        <tbody>
        @forelse($rows as $i => $r)
            <tr>
                <td class="c">{{ $i + 1 }}</td>
                <td>{{ $r->nom }} {{ $r->prenoms }}</td>
                <td>{{ $r->email }}<br>{{ $r->telephone }}</td>
                <td class="c">{{ $r->type_formation }}</td>
                @if($estDsc)
                    <td class="r">{{ number_format((float) $r->frais_inscription, 0, ',', ' ') }}</td>
                    <td class="r">{{ number_format((float) $r->frais_participation, 0, ',', ' ') }}</td>
                    <td class="c">{{ $libelleReduction($r) }}</td>
                @endif
                <td class="r">{{ number_format((float) $r->montant_total, 0, ',', ' ') }}</td>
                <td class="c {{ $r->statut_paiement === 'PAYE' ? 'paye' : 'impaye' }}">
                    {{ $r->statut_paiement ?: 'IMPAYE' }}
                </td>
                <td class="r">{{ number_format((float) $r->montant_paye, 0, ',', ' ') }}</td>
                <td class="r">{{ number_format((float) $r->montant_restant, 0, ',', ' ') }}</td>
                <td class="c">{{ $r->a_participe ? 'OUI' : 'NON' }}</td>
                <td>{{ $r->observations }}</td>
            </tr>
        @empty
            <tr><td colspan="{{ $nbColonnes }}" class="c">Aucun participant ne correspond aux critères.</td></tr>
        @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="{{ $colsAvantPaye }}">Total : {{ $rows->count() }} participant(s)</td>
                <td class="r">{{ number_format($totalPaye, 0, ',', ' ') }}</td>
                <td class="r">{{ number_format($totalRestant, 0, ',', ' ') }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

</body>
</html>