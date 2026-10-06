@extends('documents.layouts.document')

@section('titre_document', 'Facture Proforma')
@section('qr_reference', $inscrit->code_inscription)
@section('marge_laterale', '1.2cm')

@section('styles')
    /* Cellules empilées : inscription au-dessus, participation en dessous */
    .details td.empile { padding: 0; }
    .details td.empile div { padding: 5px; }
    .details td.empile div.haut { border-bottom: 1px solid #000; }
@endsection

@section('contenu')
    <div class="titre">FACTURE PROFORMA</div>

    @include('documents.partials.entete_document', [
        'libelleNumero'       => 'N° PROFORMA',
        'numero'              => getRefProforma($inscrit->code_inscription),
        'date'                => $inscrit->created_at,
        'libelleDestinataire' => 'ADRESSEE A',
    ])

    <div class="bandeau"><b>Détails du paiement</b></div>

    @php
        /**
         * Frais (inscription / participation) d'UNE formation.
         * Détails filtrés sur la formation ; sans code_formation (anciennes données),
         * on retombe sur l'ensemble des détails, comme avant.
         */
        $fraisFormation = function (string $codeFormation) use ($details) {
            $tous    = collect($details);
            $filtres = $tous->where('code_formation', $codeFormation);
            $lignes  = $filtres->isNotEmpty() ? $filtres : $tous;

            $vide  = ['libelle' => '', 'reste' => 0.0, 'solde' => 0.0, 'reduction' => false, 'code' => ''];
            $frais = ['inscription' => $vide, 'participation' => $vide];

            foreach ($lignes as $data) {
                $type = $data->getTarification->getGrilletarifaire->type_tarif;
                $cle  = $type === 'INSCRIPTION' ? 'inscription' : 'participation';

                $frais[$cle] = [
                    'libelle'   => $type,
                    'reste'     => (float) $data->montant_restant,
                    'solde'     => (float) $data->montant_paye,
                    'reduction' => (bool) $data->have_reduction_dsc,
                    'code'      => (string) $data->code_remise_dsc,
                ];
            }
            return $frais;
        };

        $libelleReduction = function (array $f) {
            if (!$f['reduction']) return '-';
            return $f['code'] === 'BOURSE_COMPLET' ? '100% (Bourse complète)' : '5%';
        };

        $fmt = fn ($n) => number_format($n, 0, ',', '.');

        // Inscription : mention (soldé / impayé) masquée dès qu'il y a une réduction
        $montantInscription = function (array $f) use ($fmt) {
            $mention = $f['reduction'] ? '' : ($f['reste'] == 0 ? ' (soldé)' : ' (impayé)');
            return ($f['reste'] == 0 ? $fmt($f['solde']) : $fmt($f['reste'])) . $mention;
        };

        // Participation : mention masquée pour une bourse complète ;
        // la part déjà payée est affichée au-dessus du reste dû
        $montantParticipation = function (array $f) use ($fmt) {
            $bourse = $f['reduction'] && $f['code'] === 'BOURSE_COMPLET';
            if ($f['reste'] == 0) {
                return $fmt($f['solde']) . ($bourse ? '' : ' (soldé)');
            }
            $texte = $f['solde'] > 0 ? $fmt($f['solde']) . ($bourse ? '' : ' (soldé)') . '<br>' : '';
            return $texte . $fmt($f['reste']) . ($bourse ? '' : ' (impayé)');
        };

        $i = 0;
        $total_du = 0;   // restant dû de TOUTES les formations
    @endphp

    <table class="details">
        <thead>
            <tr>
                <th class="c">#</th>
                <th class="l">Thème(s) de formation</th>
                <th class="c">Type de frais</th>
                <th class="c">Réduction</th>
                <th class="r">Montant</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($liste_formation as $formations)
                @foreach (getTarifFormationCustom($formations->code_formation, $formations->tarif_formation_id, $formations->email) as $formation)
                    @php
                        $i++;
                        $frais = $fraisFormation($formation->code_formation);
                        $total_du += $frais['inscription']['reste'] + $frais['participation']['reste'];
                    @endphp
                    {{-- Une ligne par formation (pas de rowspan : DomPDF le coupe mal entre deux pages) --}}
                    <tr>
                        <td class="c"><b>{{ $i }}</b></td>
                        <td><b>{{ $formation->code_formation }}</b> - {{ $formation->theme }}</td>
                        <td class="c empile">
                            <div class="haut">{{ $frais['inscription']['libelle'] ?: '-' }}</div>
                            <div>{{ $frais['participation']['libelle'] ?: '-' }}</div>
                        </td>
                        <td class="c empile">
                            <div class="haut">{{ $libelleReduction($frais['inscription']) }}</div>
                            <div>{{ $libelleReduction($frais['participation']) }}</div>
                        </td>
                        <td class="r empile">
                            <div class="haut">{!! $montantInscription($frais['inscription']) !!}</div>
                            <div>{!! $montantParticipation($frais['participation']) !!}</div>
                        </td>
                    </tr>
                @endforeach
            @endforeach

            <tr class="total">
                <th colspan="4" class="l">Montant total hors taxe</th>
                <th class="r">{{ $fmt($total_du) }}</th>
            </tr>
        </tbody>
    </table>

    @include('documents.partials.bloc_final', [
        'montant'          => $total_du,
        'signatureADroite' => true,
    ])
@endsection
