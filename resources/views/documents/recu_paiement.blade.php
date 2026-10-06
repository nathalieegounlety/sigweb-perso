@extends('documents.layouts.document')

@section('titre_document', 'Reçu de paiement')
@section('qr_reference', $paiement->reference)

@section('contenu')
    <div class="titre">REÇU DE PAIEMENT</div>

    @include('documents.partials.entete_document', [
        'libelleNumero'       => 'N° RECU',
        'numero'              => $paiement->reference,
        'date'                => $paiement->updated_at,
        'libelleDestinataire' => 'DELIVRE A',
    ])

    <div class="bandeau"><b>Détails du paiement</b></div>

    @php
        $montant_paye = 0;
        $montant_restant = 0;
    @endphp

    <table class="details">
        <thead>
            <tr>
                <th class="c">#</th>
                <th class="l">Formation</th>
                <th class="c">Frais</th>
                <th class="c">Réf. transaction</th>
                <th class="c">Réduction</th>
                <th class="r">Montant</th>
                <th class="c">Etat</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($details_paiement as $item)
                @php
                    $pourcentage = $item->pourcentage != '' ? $item->pourcentage / 100 : 0;
                    $montant_net = $item->montant - ($item->montant * $pourcentage);

                    if ($item->statut_paiement == 'PAYE') {
                        $montant_paye += $montant_net;
                    } else {
                        $montant_restant += $montant_net;
                    }
                @endphp
                <tr>
                    <td class="c"><b>{{ $loop->iteration }}</b></td>
                    <td><b>{{ $item->code_formation }}</b> - {{ $item->theme }}</td>
                    <td class="c">{{ $item->libelle }}</td>
                    <td>
                        @if (($item->dt_paiement_partiel || $item->is_paiement_partiel) && !empty($item->reference_paiement_partiel))
                            {{ $item->reference_paiement_partiel }}
                        @else
                            {{ $item->num_transaction }}
                        @endif
                    </td>
                    <td class="c">{{ $item->pourcentage != '' ? $item->pourcentage : '-' }}</td>
                    <td class="r">{{ number_format($montant_net, 0, ',', '.') }}</td>
                    <td class="c">{{ $item->statut_paiement }}</td>
                </tr>
            @endforeach

            {{-- Totaux dans le tbody : en tfoot, DomPDF les répéterait sur chaque page --}}
            <tr class="total total-premier">
                <th colspan="5" class="l">Montant total hors taxe</th>
                <th class="r">{{ number_format($paiement->montant_total, 0, ',', '.') }}</th>
                <th class="vide">&nbsp;</th>
            </tr>
            <tr class="total">
                <th colspan="5" class="l">Montant payé</th>
                <th class="r">{{ number_format($montant_paye, 0, ',', '.') }}</th>
                <th class="vide">&nbsp;</th>
            </tr>
            <tr class="total">
                <th colspan="5" class="l">Montant restant</th>
                <th class="r">{{ number_format($montant_restant, 0, ',', '.') }}</th>
                <th class="vide">&nbsp;</th>
            </tr>
        </tbody>
    </table>

    @include('documents.partials.bloc_final', [
        'montant'        => $paiement->montant_total,
        'libelleMontant' => 'Montant total en lettres',
    ])
@endsection
