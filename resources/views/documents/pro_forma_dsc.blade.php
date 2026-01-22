<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>Facture Proforma</title>
    <style type="text/css">
        @page {
            size: 21cm 29.7cm;
            margin: 1.2cm;
        }

        body {
            font-family: Tahoma, sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.4;
            color: #333;
        }

        p {
            margin-bottom: 0.25cm;
            direction: ltr;
            line-height: 115%;
            text-align: justify;
            font-family: Tahoma, serif;
            orphans: 2;
            widows: 2;
            background: transparent;
        }

        div, table, span, p {
            font-family: Tahoma, sans-serif;
        }

        a:link {
            color: #1571c6;
            text-decoration: underline;
        }

        header {
            height: auto;
            margin-bottom: 1cm;
        }

        footer {
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 5cm;
            z-index: 2;
            background: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        td, th {
            padding: 4px;
            text-align: left;
        }

        th {
            background-color: #abd;
            color: rgb(0, 0, 0);
            font-weight: bold;
            border: 1px solid #abd;
        }

        .title {
            text-align: center;
            font-size: 22pt;
            color: #006fc0;
            font-weight: bold;
            margin: 30px 0;
        }

        .subtitle {
            text-align: center;
            font-size: 14pt;
            color: #1571c6;
            font-weight: bold;
            margin: 10px 0;
            text-transform: uppercase;
            background-color: #1571c6;
            color: white;
            padding: 4px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 10px;
        }

        .info-table td {
            padding: 4px;
            border: none;
        }

        .info-table .header-cell {
            background-color: #1571c6;
            color: white;
            font-weight: bold;
            text-align: center;
        }

        .signature-section {
            margin-top: 40px;
            text-align: right;
            margin-bottom: 20px;
        }

        .signature-section img {
            height: 80px;
            width: auto;
            margin-bottom: 5px;
        }

        .signature-section .name {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 2px;
            font-size: 11pt;
        }

        .signature-section .title {
            font-size: 10pt;
            margin: 0;
            line-height: 1.2;
        }

        .footer-content {
            border-top: 8px solid #9cc2e5;
            border-bottom: 8px solid #1571c6;
            padding: 10px 0;
            text-align: center;
            font-size: 9pt;
            margin-top: 10px;
        }

        .qr-code {
            width: 90px;
            height: 90px;
            float: left;
            margin-right: 10px;
        }

        .footer-text {
            text-align: left;
            margin-left: 100px;
        }

        .background-image {
            background: url('img/logo/logo-ohada-new500.png') no-repeat center;
            opacity: 0.1;
            background-attachment: fixed;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .main-content {
            position: relative;
            z-index: 1;
            margin-top: 0.5cm;
        }
    </style>
</head>

<body lang="fr-BJ" link="#0563c1" vlink="#800000" dir="ltr" style="font-family: Tahoma, sans-serif;">
    <header>
        <div style="z-index:30;">
            <div style="float:left;width:86%;">
                <div style="border-bottom:5px double #2681ca; max-width:75%; padding: 0 0 10px 0;">
                    <div style="float:left; width: 90px; margin:0px; height:90px;">
                        <img src="img/logo/logo-ohada.png" alt="Logo OHADA" height="75px" style="height: 75px;">
                    </div>
                    <div style="font-size:65%; line-height:1.5; margin: -25px 0 0 95px; padding:0px; top:0; font-family: Tahoma, serif; font-weight:bold; text-align:justify;">
                        Organisation &nbsp;pour &nbsp;l’Harmonisation &nbsp;en &nbsp;Afrique &nbsp;du &nbsp;Droit &nbsp;des &nbsp;Affaires <br>
                        Organization &nbsp;&nbsp; for&nbsp; &nbsp;the&nbsp; &nbsp;Harmonization &nbsp;&nbsp; of &nbsp;&nbsp;Business &nbsp;&nbsp; Law &nbsp;in &nbsp;&nbsp; Africa <br>
                        Organización para la Armonización en África del Derecho de los Negocios <br>
                        Organização &nbsp;para &nbsp;a &nbsp;Harmonização &nbsp;em &nbsp;África &nbsp;do &nbsp;Direito dos Negócios
                    </div>
                </div>
            </div>
            <div style="float:right;width:14%;position:relative; text-align:right;">
                <img src="img/logo/attestation_flyers2.png" alt="Logo Attestation" height="80px">
            </div>
        </div>
    </header>

    <div class="main-content">
        <div class="background-image"></div>

        <div class="title">FACTURE PROFORMA</div>

        <div style="clear:both;">
            <table style="width: 100%; padding: 5px 0 0 0; margin: 0;">
                <tbody>
                    <tr>
                        <td style="width: 45%; padding:4px; background:#1571c6; color:#fff; text-align:center;">
                            N° PROFORMA
                        </td>
                        <td style="width: 20%;">
                            &nbsp;
                        </td>
                        <td style="width: 35%; padding:4px; background:#1571c6; color:#fff; text-align:center;">
                            DATE
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:center;">
                            <b>ERS-{{ date('Y') }}-PF-{{ \Carbon\Carbon::parse($inscrit->created_at)->format('mdhs') }}</b>
                        </td>
                        <td style="">
                            &nbsp;
                        </td>
                        <td style="text-align:center;">
                            <b>{{ \Carbon\Carbon::parse($inscrit->created_at)->format('d/m/Y') }}</b>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" style="padding:4px; margin-top:5px; background:#1571c6; color:#fff; text-align:center;">
                            <b>ADRESSEE A</b>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" style="line-height: 1.5;">
                            <b>{{ $auditeur->prenoms }} <span style="text-transform:uppercase;">{{ $auditeur->nom }}</span></b><br>
                            @if ($auditeur->telephone)
                                Tél : {{ $auditeur->telephone }} -
                            @endif
                            E-mail : {{ $auditeur->email }}
                            @if ($auditeur->ville)
                                <br>
                                {{ $auditeur->ville }},
                            @endif
                            @if ($auditeur->pays)
                                {{ $auditeur->pays }}
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="line-height: 1.3; margin:10px 0; padding:4px; text-align:center; text-transform:uppercase; background:#1571c6; color:#fff;">
            <b>Détails du paiement</b>
        </div>

        <div style="clear:both;">
            <table cellspacing="0" cellpadding="0" style="spacing: 0; cellpadding:0; border:0; width: 100%; padding: 5px 0 0 0; margin: 0; font-size:10.5pt;">
                <thead>
                    <tr style="background-color: #abd">
                        <th style="padding:5px; border: 1px solid black;">#</th>
                        <th style="padding:5px; border: 1px solid black; text-align:left;">Thème(s) de formation</th>
                        <th style="padding:5px; border: 1px solid black;">Type de frais</th>
                        <th style="padding:5px; border: 1px solid black;">Réduction</th>
                        <th style="padding:5px; border: 1px solid black;">Montant</th>
                    </tr>
                </thead>
                <tbody>
                @php
                    $i = 0;
                    $montant_total = 0;
                @endphp
                @foreach ($liste_formation as $formations)
                    @foreach (getTarifFormationCustom($formations->code_formation, $formations->tarif_formation_id, $formations->email) as $formation)
                        {{-- @if ($formation->libelle == 'PRESENTIEL') --}}
                        @php  $i = $i+1;  @endphp
                        @php
                            $pourcentage = 0;
                            $frais_inscription = 0;
                            $frais_scolarite = 0;
                            $frais_inscription_solde = 0;
                            $frais_scolarite_solde = 0;
                            $libelle_inscription = ''; $libelle_scolarite = '';
                            $have_reduction = false;
                        @endphp
                        @foreach ($details as $data)
                            @if ($data->getTarification->getGrilletarifaire->type_tarif != 'INSCRIPTION')
                                @php
                                    $frais_scolarite = $data->montant_restant;
                                    $frais_scolarite_solde = $data->montant_paye;
                                    $libelle_scolarite = $data->getTarification->getGrilletarifaire->type_tarif;
                                @endphp
                                {{--@if ($frais_scolarite == 0)
                                    @php
                                        $frais_scolarite = $data->getTarification->getGrilletarifaire->montant;
                                    @endphp
                                @endif--}}
                                @if ($data->have_reduction_dsc)
                                    @php
                                        $have_reduction = true;
                                    @endphp
                                @endif
                            @else
                                @php
                                    $frais_inscription = $data->montant_restant;
                                    $frais_inscription_solde = $data->montant_paye;
                                    $libelle_inscription = $data->getTarification->getGrilletarifaire->type_tarif;
                                @endphp
                                {{--@if ($frais_inscription == 0)
                                    @php
                                        $frais_inscription = $data->getTarification->getGrilletarifaire->montant;
                                    @endphp
                                @endif--}}
                            @endif
                        @endforeach


                        <tr style="margin:0;">
                            <td rowspan="2" style="margin:0; padding:5px; border: 1px solid black; text-align:center;">
                                <b>{{ $i }}</b>
                            </td>
                            <td rowspan="2" style="margin:0; padding:5px; border: 1px solid black;">
                                <b>{{ $formation->code_formation }}</b> - {{ $formation->theme }}
                            </td>

                            <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">
                                {{ $libelle_inscription }} </td>

                            <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">

                                -
                            </td>
                            <td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">
                                @if($frais_inscription == 0)
                                    {{ number_format($frais_inscription_solde, 0, ',', '.') }} (soldé)
                                @else
                                    {{ number_format($frais_inscription, 0, ',', '.') }} (impayé)
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">
                                {{ $libelle_scolarite }} </td>

                            <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">
                                @if ($have_reduction)
                                    5%
                                @else
                                    -
                                @endif
                            </td>
                            <td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">
                                @if($frais_scolarite == 0)
                                    {{ number_format($frais_scolarite_solde, 0, ',', '.') }} (soldé)
                                @else
                                    @if($frais_scolarite_solde > 0)
                                        {{ number_format($frais_scolarite_solde, 0, ',', '.') }} (soldé) <br>
                                    @endif
                                    {{ number_format($frais_scolarite, 0, ',', '.') }} (impayé)
                                @endif
                            </td>
                        </tr>

                        @php
                            $montant_total =
                                $montant_total +
                                ($formation->montant - $formation->montant * $pourcentage);
                        @endphp



                        {{-- @endif --}}
                    @endforeach
                @endforeach
                <tr style="margin:0;background-color: #abd">
                    <th  colspan="4" style="padding:5px; border: 1px solid black;">Montant total hors taxe
                    </th>
                    <th style="padding:5px; border: 1px solid black; text-align:right;">
                        <b>{{ number_format($frais_inscription+$frais_scolarite, 0, ',', '.') }}</b>
                    </th>
                </tr>
                </tbody>
            </table>
        </div>

        <p><span style="text-decoration: underline;">Montant en lettres</span> : <b>{{ $formatter->format($montant_total) }} francs CFA</b> (XOF).</p>

        <div class="signature-section">
            @php $signataire = getSignataireByDate(\Carbon\Carbon::parse(date('Y-m-d'))->format('Y-m-d')); @endphp
            <div style="text-align:right; margin:0; padding:0; z-index: 10;">
                <img src="storage/signature/{{ $signataire->signature }}" alt="Signature" style="margin: -15px 0 15px; padding:0; height: 80px; text-align:right;">
            </div>
            <div style="padding:0; margin:-20px 0 0; text-align:right; z-index: 100;">
                <b><u>{{ $signataire->titre }} {{ $signataire->nom }} {{ $signataire->prenoms }}</u></b><br>
                <small>{{ $signataire->fonction }}</small>
            </div>
        </div>
    </div>

    <footer>
        <div style="clear:both; margin-top:10px;">
            <table style="width: 100%; padding: 5px 0 0 0; margin: 0;">
                <tbody>
                    <tr>
                        <td style="width: 105px; margin:0; border: 0px solid black;">
                            <img width="90" height="90" src="data:image/png;base64, {!! base64_encode(QrCode::encoding('UTF-8')->format('svg')->size(95)->generate('REFERENCE : '.$inscrit->code_inscription.' Prénoms & nom : '.$auditeur->prenoms.' '.$auditeur->nom)) !!}">
                        </td>
                        <td style="margin:0; font-size:9pt; border: 0px solid black;">
                            <b>NB :</b> Traité relatif à l'Harmonisation du Droit des Affaires en Afrique <br>
                            <span><b>Article 51: </b></span><br>
                            <span>L'OHADA, ses avoirs, ses biens et ses revenus ainsi que les opérations autorisées par le présent Traité sont exonérés de tous impôts, taxes et droits de douane.</span> <br>
                            <span>L'OHADA est également exempte de toute obligation relative au recouvrement ou au paiement d'impôts, de taxes ou de droits de douane.</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="footer-content">
            ERSUMA, Ouando, Carrefour Cinquantenaire - Route de Pobè, 02 BP 353 Porto-Novo, BENIN <br>
            Tél. : +229 01 97 97 05 37 - E-mail : ersuma@ohada.org -
            Web : <font color="#0563c1"><u><a href="https://ersuma.ohada.org">https://ersuma.ohada.org</a></u></font>
        </div>
    </footer>
</body>
</html>
