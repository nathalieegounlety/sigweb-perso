<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <title></title>
    <style type="text/css">
        @page {
            size: 21cm 29.7cm;
            margin-left: 1.75cm;
            margin-right: 1.75cm;
            margin-top: 1.27cm;
            margin-bottom: 1.27cm;
            font-family: Tahoma, serif;
        }
        body {
            font-family: Tahoma, sans-serif;
            position: relative;
            min-height: 100vh;
            margin: 0;
            padding: 0;
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
        div, table, pr, span, p {
            font-family: Tahoma, sans-serif;
        }
        a:link {
            color: #1571c6;
            text-decoration: underline;
        }
        header {
            position: fixed;
            top: 0cm;
            left: 0cm;
            right: 0cm;
            height: 2cm;
            z-index: 2;
            background: white;
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
        .background-image {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 0;
            background: url('img/logo/logo-ohada-new500.png') no-repeat center center;
            background-size: contain;
            opacity: 0.1;
            pointer-events: none;
        }
        .content {
            position: relative;
            z-index: 1;
            background:; white;
            padding: 20px;
            margin-top: 3cm;
            margin-bottom: 5cm;
        }
    </style>
</head>

<body lang="fr-BJ" link="#0563c1" vlink="#800000" dir="ltr" style="font-family: Tahoma, sans-serif;">
    <div class="background-image"></div>

    <header>
        <div style="z-index: 30;">
            <div style="float:left;width:86%;">
                <div style="border-bottom:5px double #2681ca; max-width:75%; padding: 0 0 10px 0;">
                    <div style="float:left; width: 90px; margin:0px; height:90px;">
                        <img src="img/logo/logo-ohada.png" alt="" height="75px" style="height: 75px;">
                    </div>
                    <div style="font-size:65%; line-height:1.5; margin: -25px 0 0 95px; padding:0px; top:0; font-family: Tahoma, serif; font-weight:bold; text-align:justify;">
                        Organisation &nbsp;pour &nbsp;l’Harmonisation &nbsp;en &nbsp;Afrique &nbsp;du &nbsp;Droit &nbsp;des &nbsp;Affaires <br>
                        Organization &nbsp;&nbsp; for&nbsp; &nbsp;the&nbsp; &nbsp;Harmonization &nbsp;&nbsp; of &nbsp;&nbsp;Business
                        &nbsp;&nbsp; Law &nbsp;in &nbsp;&nbsp; Africa <br>
                        Organización para la Armonización en África del Derecho de los Negocios <br>
                        Organização &nbsp;para &nbsp;a &nbsp;Harmonização &nbsp;em &nbsp;África &nbsp;do &nbsp;Direito dos Negócios
                    </div>
                </div>
            </div>
            <div style="float:right;width:14%;position:relative; text-align:right;">
                <img src="img/logo/attestation_flyers2.png" alt="" height="80px">
            </div>
        </div>
    </header>

    <div class="content">
        <div style="z-index: 100; clear: both; display: block; margin: -45px 0 0px 0; text-align: center; line-height: 115%; font-size: 22pt; color: #006fc0; font-weight: bold;">
            <div style="margin: 30px 0 5px 0;">
                REÇU DE PAIEMENT
            </div>
        </div>

        <div style="clear: both; display: block;"></div>
        <div style="clear: both;">
            <table style="width: 100%; z-index: 1000; padding: 20px 0 0 0; margin: 0;">
                <tbody>
                    <tr>
                        <td style="width: 45%; padding:4px; background:#1571c6; color:#fff; text-align:center;">N° RECU</td>
                        <td style="width: 20%;">&nbsp;</td>
                        <td style="width: 35%; padding:4px; background:#1571c6; color:#fff; text-align:center;">DATE</td>
                    </tr>
                    <tr>
                        <td style="text-align:center;"><b>{{ $paiement->reference }}</b></td>
                        <td style="">&nbsp;</td>
                        <td style="text-align:center;"><b>{{ \Carbon\Carbon::parse($paiement->updated_at)->format('d/m/Y') }}</b></td>
                    </tr>
                    <tr>
                        <td colspan="3" style="padding:4px; margin-top:10px; background:#1571c6; color:#fff; text-align:center;">
                            <b>DELIVRE A</b>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" style="line-height: 1.5;">
                            <b>{{ $auditeur->prenoms ? $auditeur->prenoms : '' }}
                                <span style="text-transform:uppercase;">{{ $auditeur->nom ? $auditeur->nom : '' }}</span></b><br>
                            @if ($auditeur->telephone)
                            Tél : {{ $auditeur->telephone }} -
                            @endif
                            E-mail : {{ $auditeur->email }}
                            @if ($auditeur->ville)
                                <br>{{ $auditeur->ville }},
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
            <table cellspacing="0" cellpadding="0" style="spacing: 0; cellpadding:0; border:0; width: 100%; z-index:1000; padding: 10px 0 0 0; margin: 0; font-size:10.5pt;">
                <thead>
                    <tr style="margin:0; background-color: #abd">
                        <th style="padding:5px; margin:0; border: 1px solid black;">#</th>
                        <th style="padding:5px; margin:0; border: 1px solid black; text-align:left;">Formation</th>
                        <th style="padding:5px; margin:0; border: 1px solid black; min-width:85%;">Frais</th>
                        <th style="padding:5px; margin:0; border: 1px solid black;">Réf. transaction</th>
                        <th style="padding:5px; margin:0; border: 1px solid black;">Réduction</th>
                        <th style="padding:5px; margin:0; border: 1px solid black; min-width:90%;">Montant</th>
                        <th style="padding:5px; margin:0; border: 1px solid black;">Etat</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i = 0; $montant_paye = 0; $montant_restant = 0;
                    @endphp
                    @foreach ($details_paiement as $item)
                        @php
                            $pourcentage = 0;
                            $i = $i + 1;
                        @endphp
                        @if ($item->pourcentage != '')
                            @php
                                $pourcentage = $item->pourcentage / 100;
                            @endphp
                        @endif
                        @if ($item->statut_paiement == "PAYE")
                            @php
                                $montant_paye = $montant_paye + ($item->montant - ($item->montant * $pourcentage));
                            @endphp
                        @else
                            @php
                            $montant_restant = $montant_restant + ($item->montant - ($item->montant * $pourcentage));
                            @endphp
                        @endif
                        <tr style="margin:0;">
                            <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;"><b>{{ $i }}</b></td>
                            <td style="margin:0; padding:5px; border: 1px solid black;"><b>{{ $item->code_formation }}</b> - {{ $item->theme }}</td>
                            <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">{{ $item->libelle }}</td>
                            <td style="margin:0; padding:5px; border: 1px solid black;">{{ $item->num_transaction }}</td>
                            <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">
                                @if ($item->pourcentage != '')
                                    {{ $item->pourcentage }}
                                @else
                                    -
                                @endif
                            </td>
                            <td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">
                                {{ number_format($item->montant - $item->montant * $pourcentage, 0, ',', '.') }}
                            </td>
                            <td style="margin:0; padding:5px; border: 1px solid black;">{{ $item->statut_paiement }}</td>
                        </tr>
                    @endforeach
                    <tr style="margin:0; background-color: #abd">
                        <th colspan="5" style="padding:5px; border: 1px solid black;">Montant total hors taxe</th>
                        <th style="padding:5px; border: 1px solid black; text-align:right;"><b>{{ number_format($paiement->montant_total, 0, ',', '.') }}</b></th>
                        <th style="border: 0; border-left:1px solid black; border-top:1px solid black; background:none;">&nbsp;</th>
                    </tr>
                    <tr style="margin:0; background-color: #abd">
                        <th colspan="5" style="padding:5px; border: 1px solid black;">Montant payé</th>
                        <th style="padding:5px; border: 1px solid black; text-align:right;"><b>{{ number_format($montant_paye, 0, ',', '.') }}</b></th>
                        <th style="border: 0; border-left:1px solid black; background:none;">&nbsp;</th>
                    </tr>
                    <tr style="margin:0; background-color: #abd">
                        <th colspan="5" style="padding:5px; border: 1px solid black;">Montant restant</th>
                        <th style="padding:5px; border: 1px solid black; text-align:right;"><b>{{ number_format($montant_restant, 0, ',', '.') }}</b></th>
                        <th style="border: 0; border-left:1px solid black; background:none;">&nbsp;</th>
                    </tr>
                </tbody>
            </table>
        </div>
        <p>
            <pr style="text-decoration: underline;">Montant total en lettres</pr> :
            <b>{{ $formatter->format($paiement->montant_total) }} francs CFA</b> (XOF).
        </p>
        @php
            $date = date('d M Y');
        @endphp
        <table style="width: 100%; padding: 0; margin: 0;">
            <tbody>
                <tr>
                    <td style="width:40%; vertical-align: bottom;">&nbsp;</td>
                    <td style="vertical-align: bottom;">&nbsp;</td>
                    <td style="width:45%; vertical-align: bottom; padding:0;">
                        @php
                            $signataire = getSignataireByDate(\Carbon\Carbon::parse(date('Y-m-d'))->format('Y-m-d'));
                        @endphp
                        <div style="text-align:left; margin:0; padding:0; z-index: 10;">
                            <img src="storage/signature/{{$signataire->signature}}" alt="Signature" style="margin: 0 0 -20px -45px; padding:0; height: 105px; opacity:0.9; text-align:left;">
                        </div>
                        <div style="padding:0; margin:-10px 0 0; text-align:center; z-index: 100;">
                            <b><u>{{$signataire->titre}} {{$signataire->nom}} {{$signataire->prenoms}}</u></b><br>
                            <small>{{$signataire->fonction}}</small>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <footer>
        <div style="clear:both; margin-top:-45px;">
            <table style="width: 100%; z-index:1000; padding: 20px 0 0 0; margin: 0;">
                <tbody>
                    <tr>
                        @php
                            $nom_auditeur = $auditeur->nom ?? '';
                            $prenoms_auditeur = $auditeur->prenoms ?? '';
                        @endphp
                        <td style="width: 95px; margin:0; border: 0px solid black;">
                            <img width="80" height="80" src="data:image/png;base64, {!! base64_encode(
                                QrCode::encoding('UTF-8')->format('svg')->size(95)->generate(
                                        'REFERENCE : ' . $paiement->reference . 'Prénoms & nom : ' . $prenoms_auditeur . ' ' . $nom_auditeur,
                                    ),
                            ) !!} ">
                        </td>
                        <td style="margin:0; font-size:8pt; border: 0px solid black;">
                            <b>NB :</b> Traité relatif à l'Harmonisation du Droit des Affaires en Afrique <br>
                            <span><b>Article 51: </b></span><br>
                            <span>L'OHADA, ses avoirs, ses biens et ses revenus ainsi que les opérations autorisées par le présent Traité sont exonérés de tous impôts, taxes et droits de douane.</span> <br>
                            <span>L'OHADA est également exempte de toute obligation relative au recouvrement ou au paiement d'impôts, de taxes ou de droits de douane.</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div style="display:block; clear:both; width:100%; background: #fff; border: 0; border-top: 8px solid #9cc2e5; border-bottom: 8px solid #1571c6; margin: 10px 0 10px 0; color:#000000; text-align:center; font-size:9pt; padding:5px 0 5px 0">
            ERSUMA, Ouando, Carrefour Cinquantenaire - Route de Pobè, 02 BP 353 Porto-Novo, BENIN <br>Tél. : +229 01 97 97 05 37 - E-mail : ersuma@ohada.org -
            Web : <font color="#0563c1"><u><a href="https://ersuma.ohada.org">https://ersuma.ohada.org</a></u></font>
        </div>
    </footer>
</body>
</html>
