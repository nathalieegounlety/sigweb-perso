@extends('documents.layouts.document')

@section('titre_document', 'Reçu de paiement')
@section('qr_reference', $paiement->reference)
@section('marge_laterale', '1cm')

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
        // Montant total : pris sur la première ligne (fonctionne même sans détail)
        $montant_total = (float) (collect($details_paiement)->first()->montant_total ?? 0);

        // Uniquement les tranches validées et réellement payées
        $lignes = collect($details_paiement)
            ->filter(fn ($item) => $item->statut_tranche == 'VALIDER' && $item->montant_paye > 0)
            ->values();

        $montant_total_paye = $lignes->sum(fn ($item) => (float) $item->montant_paye);
    @endphp

    <table class="details">
        <thead>
            <tr>
                <th class="c">#</th>
                <th class="l">Formation</th>
                <th class="c">Frais</th>
                <th class="c">Ref. transaction</th>
                <th class="c">Etat</th>
                <th class="c">Réduction</th>
                <th class="r">Montant</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lignes as $item)
                <tr>
                    <td class="c"><b>{{ $loop->iteration }}</b></td>
                    <td><b>{{ $item->code_formation }}</b><br><small>{{ $item->theme }}</small></td>
                    <td class="c">{{ $item->type_tarif }}</td>
                    <td class="c">{{ $item->num_transaction }}</td>
                    <td class="c">PAYE</td>
                    <td class="c">{{ $item->have_reduction_dsc ? '5%' : '-' }}</td>
                    <td class="r">{{ number_format($item->montant_paye, 0, ',', '.') }}</td>
                </tr>
            @endforeach

            <tr class="total">
                <th colspan="6" class="l">Montant total hors taxe</th>
                <th class="r">{{ number_format($montant_total, 0, ',', '.') }}</th>
            </tr>
            <tr class="total">
                <th colspan="6" class="l">Montant payé</th>
                <th class="r">{{ number_format($montant_total_paye, 0, ',', '.') }}</th>
            </tr>
            <tr class="total">
                <th colspan="6" class="l">Montant restant</th>
                <th class="r">{{ number_format($montant_total - $montant_total_paye, 0, ',', '.') }}</th>
            </tr>
        </tbody>
    </table>

    @include('documents.partials.bloc_final', ['montant' => $montant_total_paye])
@endsection
