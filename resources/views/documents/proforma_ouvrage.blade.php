@extends('documents.layouts.document')

@section('titre_document', 'Facture Proforma')
@section('qr_reference', $paiement->reference)

@section('contenu')
    <div class="titre">FACTURE PROFORMA</div>

    @include('documents.partials.entete_document', [
        'libelleNumero'       => 'N° PROFORMA',
        'numero'              => getRefProformaOuvrage($inscrit->code_inscription),
        'date'                => $paiement->created_at,
        'libelleDestinataire' => 'ADRESSEE A',
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
                    <td class="c"><b>{{ $item->reference }}</b></td> 
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