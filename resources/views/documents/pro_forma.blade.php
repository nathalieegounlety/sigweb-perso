<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>

<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <title>Facture Proforma</title>
    <style type="text/css">
        @page {
            size: 21cm 29.7cm;
            margin: 1.27cm;
            font-family: Tahoma, serif;
        }

        body {
            font-family: Tahoma, sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.2;
            color: #333;
        }

        .content {
            margin-top: 0.5cm;
            margin-bottom: 1.5cm;
            position: relative;
        }

        .background-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('img/logo/logo-ohada-new500.png') no-repeat center 70px;
            opacity: 0.1;
            z-index: -1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #1571c6;
            color: white;
            font-weight: bold;
        }

        .title {
            text-align: center;
            font-size: 22pt;
            color: #006fc0;
            font-weight: bold;
            margin: 20px 0;
        }

        .subtitle {
            text-align: center;
            font-size: 16pt;
            color: #1571c6;
            font-weight: bold;
            margin: 15px 0;
            text-transform: uppercase;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 8px;
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
            text-align: center;
            margin-bottom: 20px;
        }

        .signature-section img {
            height: 60px;
            width: auto;
            max-width: 150px;
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


        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 5cm;
            z-index: 1000;
            background: white;
        }

        .footer-content {
            border-top: 8px solid #9cc2e5;
            border-bottom: 8px solid #1571c6;
            padding: 10px 0;
            text-align: center;
            font-size: 9pt;
            margin-top: 20px;
            padding-bottom: 6cm;
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
    </style>
</head>

<body lang="fr-BJ" link="#0563c1" vlink="#800000" dir="ltr" style="font-family: Tahoma, sans-serif;">

    <div class="content">
        <header>
            <div style="z-index:30;">
                <div style="float:left;width:86%;">
                    <div style="border-bottom:5px double #2681ca; max-width:75%; padding: 0 0 10px 0;">
                        <div style="float:left; width: 90px; margin:0px; height:90px;">
                            <img src="img/logo/logo-ohada.png" alt="Logo OHADA" height="75px" style="height: 75px;">
                        </div>
                        <div
                            style="font-size:65%; line-height:1.5; margin: -25px 0 0 95px; padding:0px; top:0; font-family: Tahoma, serif; font-weight:bold; text-align:justify;">
                            Organisation &nbsp;pour &nbsp;l’Harmonisation &nbsp;en &nbsp;Afrique &nbsp;du &nbsp;Droit
                            &nbsp;des &nbsp;Affaires <br>
                            Organization &nbsp;&nbsp; for&nbsp; &nbsp;the&nbsp; &nbsp;Harmonization &nbsp;&nbsp; of
                            &nbsp;&nbsp;Business &nbsp;&nbsp; Law &nbsp;in &nbsp;&nbsp; Africa <br>
                            Organización para la Armonización en África del Derecho de los Negocios <br>
                            Organização &nbsp;para &nbsp;a &nbsp;Harmonização &nbsp;em &nbsp;África &nbsp;do
                            &nbsp;Direito dos Negócios
                        </div>
                    </div>
                </div>
                <div style="float:right;width:15%;position:relative; text-align:right;">
                    <img src="img/logo/attestation_flyers2.png" alt="Logo Attestation" height="80px">
                </div>
            </div>
        </header>

        <div class="main-content" style="position: relative; z-index: 1;">
            <div class="background-image"></div>

            <div class="title">Facture Proforma</div>

            <table class="info-table">
                <tbody>
                    <tr>
                        <td class="header-cell" style="width: 45%;">N° Proforma</td>
                        <td style="width: 20%;">&nbsp;</td>
                        <td class="header-cell" style="width: 35%;">Date</td>
                    </tr>
                    <tr>
                        <td style="text-align:center;">
                            <b>ERS-{{ date('Y') }}-PF-{{ \Carbon\Carbon::parse($inscrit->created_at)->format('mdhs') }}</b>
                        </td>
                        <td style="">&nbsp;</td>
                        <td style="text-align:center;">
                            <b>{{ \Carbon\Carbon::parse($inscrit->created_at)->format('d/m/Y') }}</b>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="header-cell">Adressée à</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="line-height: 1.5; text-align: center;">
                            <b>{{ $auditeur->prenoms }} <span
                                    style="text-transform:uppercase;">{{ $auditeur->nom }}</span></b><br>
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

            <div class="subtitle">Détails du paiement</div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 50%;">Thème(s) de formation</th>
                        <th style="width: 15%;">Type de frais</th>
                        <th style="width: 15%;">Réduction</th>
                        <th style="width: 15%;">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i = 0;
                        $montant_total = 0;
                    @endphp
                    @foreach ($liste_formation as $formations)
                        @foreach (getTarifFormationCustom($formations->code_formation, $formations->tarif_formation_id, $formations->email) as $formation)
                            @php $i = $i + 1; @endphp
                            @empty($formations->tarif_formation_id)
                                <tr>
                                    <td style="text-align:center;">
                                        <b>{{ $i }}</b>
                                    </td>
                                    <td>
                                        <b>{{ $formation->code_formation }}</b> - {{ $formation->theme }}
                                    </td>
                                    <td style="text-align:center;">
                                        {{ $formation->libelle }}
                                    </td>
                                    <td style="text-align:center;">
                                        0
                                    </td>
                                    <td style="text-align:right;">
                                        {{ number_format($formation->montant, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @php $montant_total = $montant_total + $formation->montant; @endphp
                            @else
                                @php $pourcentage = 0; @endphp
                                @if ($formation->pourcentage != '')
                                    @php $pourcentage = $formation->pourcentage / 100; @endphp
                                @endif
                                <tr>
                                    <td style="text-align:center;">
                                        <b>{{ $i }}</b>
                                    </td>
                                    <td>
                                        <b>{{ $formation->code_formation }}</b> - {{ $formation->theme }}
                                    </td>
                                    <td style="text-align:center;">
                                        {{ $formation->libelle }}
                                    </td>
                                    <td style="text-align:center;">
                                        @if ($formation->pourcentage != '')
                                            {{ $formation->pourcentage }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td style="text-align:right;">
                                        {{ number_format($formation->montant - $formation->montant * $pourcentage, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @php $montant_total = $montant_total + ($formation->montant - $formation->montant * $pourcentage); @endphp
                            @endempty
                        @endforeach
                    @endforeach
                    <tr>
                        <th colspan="4" style="text-align:right;">Montant total hors taxe</th>
                        <th style="text-align:right;">
                            <b>{{ number_format($montant_total, 0, ',', '.') }}</b>
                        </th>
                    </tr>
                </tbody>
            </table>

            <p style="text-align: left; margin: 20px 0;">
                <span style="text-decoration: underline; font-weight: bold;">Montant en lettres</span> :
                <b>{{ $formatter->format($montant_total) }} ({{ number_format($montant_total, 0, ',', '.') }}) francs
                    CFA</b> (XOF).
            </p>

            <div class="signature-section">
                @php $signataire = getSignataireByDate(\Carbon\Carbon::parse(date('Y-m-d'))->format('Y-m-d')); @endphp
                <img src="storage/signature/{{ $signataire->signature }}" alt="Signature" style="max-height: 60px;">
                <div class="name">{{ $signataire->titre }} {{ $signataire->nom }} {{ $signataire->prenoms }}</div>
                <div class="title">{{ $signataire->fonction }}</div>
            </div>

        </div>

        <footer>
            <div class="footer-content">
                <div style="overflow: hidden;">
                    <div class="qr-code">
                        <img width="90" height="90" src="data:image/png;base64, {!! base64_encode(
                            QrCode::encoding('UTF-8')->format('svg')->size(95)->generate(
                                    'REFERENCE : ' .
                                        $inscrit->code_inscription .
                                        'Prénoms & nom : ' .
                                        $auditeur->prenoms .
                                        ' ' .
                                        $auditeur->nom,
                                ),
                        ) !!}">
                    </div>
                    <div class="footer-text">
                        <b>NB :</b> Traité relatif à l'Harmonisation du Droit des Affaires en Afrique <br>
                        <span><b>Article 51: </b></span><br>
                        <span>L'OHADA, ses avoirs, ses biens et ses revenus ainsi que les opérations autorisées par le
                            présent Traité sont exonérés de tous impôts, taxes et droits de douane.</span> <br>
                        <span>L'OHADA est également exempte de toute obligation relative au recouvrement ou au paiement
                            d'impôts, de taxes ou de droits de douane.</span>
                    </div>
                </div>
                <div style="margin-top: 20px;">
                    ERSUMA, Ouando, Carrefour Cinquantenaire - Route de Pobè, 02 BP 353 Porto-Novo, BENIN <br>
                    Tél. : +229 01 97 97 05 37 - E-mail : ersuma@ohada.org -
                    Web : <font color="#0563c1"><u><a href="https://ersuma.ohada.org">https://ersuma.ohada.org</a></u>
                    </font>
                </div>
            </div>
        </footer>
    </div>
</body>

</html>
