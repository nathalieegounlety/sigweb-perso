@extends('documents.layouts.document')

@section('titre_document', 'Facture Proforma')
@section('qr_reference', $inscrit->code_inscription)
@section('marge_laterale', '1.2cm')

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
        $i = 0;
        $montant_total = 0;
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

                        // Sans tarif : pas de réduction (affichée "0"), comme avant.
                        // Avec tarif : réduction de la grille si elle existe, sinon "-".
                        $sansTarif   = empty($formations->tarif_formation_id);
                        $pourcentage = (!$sansTarif && $formation->pourcentage != '') ? $formation->pourcentage / 100 : 0;
                        $reduction   = $sansTarif ? '0' : ($formation->pourcentage != '' ? $formation->pourcentage : '-');

                        $montant_net    = $formation->montant - ($formation->montant * $pourcentage);
                        $montant_total += $montant_net;
                    @endphp
                    <tr>
                        <td class="c"><b>{{ $i }}</b></td>
                        <td><b>{{ $formation->code_formation }}</b> - {{ $formation->theme }}</td>
                        <td class="c">{{ $formation->libelle }}</td>
                        <td class="c">{{ $reduction }}</td>
                        <td class="r">{{ number_format($montant_net, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endforeach

            {{-- Total dans le tbody : en tfoot, DomPDF le répéterait sur chaque page --}}
            <tr class="total">
                <th colspan="4" class="l">Montant total hors taxe</th>
                <th class="r">{{ number_format($montant_total, 0, ',', '.') }}</th>
            </tr>
        </tbody>
    </table>

    @include('documents.partials.bloc_final', [
        'montant'          => $montant_total,
        'signatureADroite' => true,
    ])
@endsection