<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>Reçu de paiement</title>
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
            line-height: 1;
            color: #333;
            background: white !important;
            position: relative;
            min-height: 100vh;
        }

        .content {
            margin-top: 0.3cm;
            margin-bottom: 3cm; 
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

        th {
            background-color: #abd;
            color: rgb(0, 0, 0);
            font-weight: bold;
            text-align: center;
            padding: 6px;
            font-size: 11pt;
        }

        td {
            border: 1px solid black;
            padding: 6px;
            text-align: left;
            font-size: 9pt;
        }

        .title {
            text-align: center;
            font-size: 18pt;
            color: #006fc0;
            font-weight: bold;
            margin: 20px 0;
        }

        .subtitle {
            text-align: center;
            font-size: 14pt;
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
            font-size: 9pt;
        }

        .info-table .header-cell {
            background-color: #1571c6;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 10pt;
        }

        .signature-section {
            margin-top: 40px;
            text-align: right;
            margin-bottom: 20px;
            height: 100px;
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
            font-size: 10pt;
        }

        .signature-section .title {
            font-size: 10pt;
            margin: 0;
            line-height: 1.2;
            text-align: right;

        }

        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: auto; /* Changé de 3cm à auto */
            z-index: 1000;
            background: white;
            width: 100%;
        }

        .footer-content {
            border-top:; 8px solid #9cc2e5;
            border-bottom: 8px ;solid #1571c6;
            padding: 10px 0;
            text-align: center;
            font-size: 9pt;
            width: 100%;
            box-sizing: border-box;
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
        <!-- Contenu principal -->
        <header>
            <div style="z-index:30;">
                <div style="float:left;width:86%;">
                    <div style="border-bottom:5px double #2681ca; max-width:75%; padding: 0 0 10px 0;">
                        <div style="float:left; width: 90px; margin:0px; height:90px;">
                            <img src="img/logo/logo-ohada.png" alt="Logo OHADA" height="75px" style="height: 75px;">
                        </div>
                        <div style="font-size:65%; line-height:1.5; margin: -25px 0 0 95px; padding:0px; top:0; font-family: Tahoma, serif; font-weight:bold; text-align:justify;">
                            Organisation pour l'Harmonisation en Afrique du Droit des Affaires<br>
                            Organization for the Harmonization of Business Law in Africa<br>
                            Organización para la Armonización en África del Derecho de los Negocios<br>
                            Organização para a Harmonização em África do Direito dos Negócios
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

            <div class="title">REÇU DE PAIEMENT</div>

            <table class="info-table">
                <tbody>
                    <tr>
                        <td class="header-cell" style="width: 45%;">N° Reçu</td>
                        <td style="width: 20%;">&nbsp;</td>
                        <td class="header-cell" style="width: 35%;">Date</td>
                    </tr>
                    <tr>
                        <td style="text-align:center;">
                            <b>{{ $paiement->reference }}</b>
                        </td>
                        <td style="">&nbsp;</td>
                        <td style="text-align:center;">
                            <b>{{ \Carbon\Carbon::parse($paiement->updated_at)->format('d/m/Y') }}</b>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="header-cell">Délivré à</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="line-height: 1.3; padding: 10px; font-size: 11pt; text-align:center;">
                            <b>{{ $auditeur->prenoms }} <span style="text-transform:uppercase;">{{ $auditeur->nom }}</span></b><br>
                            @if ($auditeur->telephone)
                                Tél : {{ $auditeur->telephone }} -
                            @endif
                            E-mail : {{ $auditeur->email }}
                            @if ($auditeur->ville || $auditeur->pays)
                                <br>
                                {{ $auditeur->ville ?? '' }}{{ $auditeur->ville && $auditeur->pays ? ', ' : '' }}{{ $auditeur->pays ?? '' }}
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="subtitle p-4" style="background-color: #1571c6; color:white;  padding:8px; ">Détails du paiement</div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 30%;">Formation</th>
                        <th style="width: 15%;">Type</th>
                        <th style="width: 15%;">Réf. transaction</th>
                        <th style="width: 10%;">Réduction</th>
                        <th style="width: 10%;">Etat</th>
                        <th style="width: 15%;">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i = 1;
                        $have_reduction = false;
                        $montant_total = 0;
                        $montant_paye = 0;
                        $montant_restant = 0;
                    @endphp
                    @foreach ($details_paiement as $item)
                        @php
                            if ($item->have_reduction_dsc) {
                                $have_reduction = true;
                            }
                            $montant_total = $montant_total + $item->montant_paye;
                            $montant_restant = $item->recap_montant_restant;
                        @endphp
                        @if ($item->montant_paye > 0)
                            <tr style="font-size: 9pt;">
                                <td style="text-align:center;">
                                    <b>{{ $i++ }}</b>
                                </td>
                                <td>
                                    <b>{{ $item->code_formation }}</b><br><small>{{ $item->theme }}</small>
                                </td>
                                <td style="text-align:center;">
                                    {{ $item->type_tarif }}
                                </td>
                                <td>
                                    {{ $item->num_transaction }}
                                </td>
                                <td style="text-align:center;">
                                    @if ($item->have_reduction_dsc)
                                        5%
                                    @else
                                        -
                                    @endif
                                </td>
                                <td style="text-align:center;">
                                    PAYE
                                </td>
                                <td style="text-align:right;">
                                    {{ number_format($item->montant_paye, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    <tr style="background-color: #abd; font-weight: bold;">
                        <th colspan="6" style="padding:6px; font-size: 10pt;">Montant total hors taxe</th>
                        <th style="text-align:right; padding:6px; font-size: 10pt;">{{ number_format($item->montant_total, 0, ',', '.') }}</th>
                    </tr>
                    <tr style="background-color: #abd; font-weight: bold;">
                        <th colspan="6" style="padding:6px; font-size: 10pt;">Montant payé</th>
                        <th style="text-align:right; padding:6px; font-size: 10pt;">{{ number_format($montant_paye, 0, ',', '.') }}</th>
                    </tr>
                    <tr style="background-color: #abd; font-weight: bold;">
                        <th colspan="6" style="padding:6px; font-size: 10pt;">Montant restant</th>
                        <th style="text-align:right; padding:6px; font-size: 10pt;">{{ number_format($montant_restant, 0, ',', '.') }}</th>
                    </tr>
                </tbody>
            </table>

            <p style="text-align: left; margin: 20px 0; font-size: 9pt;">
                <span style="text-decoration: underline; font-weight: bold;">Montant en lettres</span> :
                <b>{{ $formatter->format($montant_paye) }} francs CFA</b> (XOF).
            </p>

            <div class="signature-section">
                @php
                    $signataire = getSignataireByDate(\Carbon\Carbon::parse(date('Y-m-d'))->format('Y-m-d'));
                @endphp
                <img src="storage/signature/{{ $signataire->signature }}" alt="Signature" style="max-height: 60px;">
                <div class="name">{{ $signataire->titre }} {{ $signataire->nom }} {{ $signataire->prenoms }}</div>
                <div class="title">{{ $signataire->fonction }}</div>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div style="overflow: hidden;">
                <div class="qr-code">
                    <img width="90" height="90" src="data:image/png;base64, {!! base64_encode(
                        QrCode::encoding('UTF-8')->format('svg')->size(95)->generate(
                                'REFERENCE : ' .
                                    $paiement->reference .
                                    ' Prénoms & nom : ' .
                                    $auditeur->prenoms .
                                    ' ' .
                                    $auditeur->nom,
                            ),
                    ) !!}">
                </div>
                <div class="footer-text">
                    <b>NB :</b> Traité relatif à l'Harmonisation du Droit des Affaires en Afrique <br>
                    <span><b>Article 51: </b></span><br>
                    <span>L'OHADA, ses avoirs, ses biens et ses revenus ainsi que les opérations autorisées par le présent Traité sont exonérés de tous impôts, taxes et droits de douane.</span> <br>
                    <span>L'OHADA est également exempte de toute obligation relative au recouvrement ou au paiement d'impôts, de taxes ou de droits de douane.</span>
                </div>
            </div>
            <div style="margin-top: 20px; font-size: 12px; padding: 10px 0; border-top: 8px solid #9cc2e5; border-bottom: 8px solid #1571c6;">
                ERSUMA, Ouando, Carrefour Cinquantenaire - Route de Pobè, 02 BP 353 Porto-Novo, BENIN <br>
                Tél. : +229 01 97 97 05 37 - E-mail : ersuma@ohada.org -
                Web : <font color="#0563c1"><u><a href="https://ersuma.ohada.org" style="color: #0563c1; text-decoration: underline; font-size: 14px;">https://ersuma.ohada.org</a></u></font>
            </div>
            
        </div>
    </footer>
</body>
</html>
