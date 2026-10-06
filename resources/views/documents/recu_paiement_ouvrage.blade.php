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

    <table class="details">
        <thead>
            <tr>
                <th class="c">#</th>
                <th class="c">Référence</th>
                <th class="l">Ouvrage</th>
                <th class="r">Montant</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($details_paiement as $item)
                <tr>
                    <td class="c"><b>{{ $loop->iteration }}</b></td>
                    <td><b>{{ $item->reference }}</b></td>
                    {{-- ?-> : pas de plantage si l'ouvrage a été supprimé --}}
                    <td>{{ $item->getOuvrage?->libelle ?? '-' }}</td>
                    <td class="r">{{ number_format($item->montant, 0, ',', '.') }}</td>
                </tr>
            @endforeach

            <tr class="total">
                <th colspan="3" class="l">Montant total hors taxe</th>
                <th class="r">{{ number_format($paiement->montant_total, 0, ',', '.') }}</th>
            </tr>
        </tbody>
    </table>

    @include('documents.partials.bloc_final', ['montant' => $paiement->montant_total])
@endsection
