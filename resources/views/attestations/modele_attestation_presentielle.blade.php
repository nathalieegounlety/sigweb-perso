<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>Attestation de formation - ERSUMA</title>
    <style type="text/css">
        body {
            font-family: Tahoma, sans-serif;
            font-size: 12pt;
            margin: 0;
            padding: 0;
            position: relative;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('img/logo/logo-ohada-new500.png') no-repeat fixed center 100px / contain;
            background-size: 500px;
            opacity: 0.1;
            z-index: -1;
        }

        header {
            position: relative;
            width: 100%;
            height: 4.5cm;
            border-bottom: 6px solid #0d043d;
            margin-bottom: 20px;
        }

        @page {
            size: 29.7cm 21cm;
            margin: 0.73cm 1.27cm 0.97cm;
        }

        p {
            margin-bottom: 0.25cm;
            direction: ltr;
            line-height: 115%;
            text-align: justify;
            orphans: 2;
            widows: 2;
            background: transparent;
            font-size: 12pt;
        }

        footer {
            position: relative;
            width: 100%;
            height: 5.2cm;
            margin-top: 20px;
        }

        .attestation-content {
            margin-top: 20px;
            position: relative;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo-section img {
            height: 95px;
        }

        .footer-content {
            font-size: 10pt;
            text-align: center;
            padding: 5px 0;
        }

        .signature-section {
            margin-top: 20px;
        }

        .signature-section img {
            height: 145px;
        }
    </style>
</head>

<body>
    @foreach ($liste_participants as $participant)
        <table style="width: 100%; padding: 0; margin: 0;">
            <tbody>
                <tr>
                    <td style="vertical-align: top;">
                        <header>
                            <div style="display: block; clear: both; width: 100%; background: #fff; border: 0; border-top: 10px solid #1571c6; border-bottom: 10px solid #9cc2e5; margin: 0 0 10px 0; color: #000000; padding: 0; height: 10px; text-align: center;"></div>

                            <div style="width: 100%;">
                                <div style="float: left; width: 90%;">
                                    <div style="border-bottom: 5px double #2681ca; max-width: 72.7%; padding: 0 0 10px 0;">
                                        <div style="float: left; width: 110px; margin: 0; height: 92px;">
                                            <img src="img/logo/logo-ohada.png" alt="Logo OHADA" style="height: 86px;">
                                        </div>
                                        <div style="line-height: 1.17; margin: -25px 0 0 100px; padding: 0; font-size: 10pt; text-align: justify;">
                                            Organisation &nbsp;&nbsp;pour &nbsp;l’Harmonisation &nbsp;en &nbsp;Afrique
                                            &nbsp;du &nbsp;Droit &nbsp;des &nbsp;&nbsp;Affaires <br>
                                            Organization &nbsp;&nbsp; for&nbsp; &nbsp;the&nbsp; &nbsp;Harmonization
                                            &nbsp;&nbsp; of &nbsp;&nbsp;Business
                                            &nbsp;&nbsp; Law &nbsp;in &nbsp;&nbsp; Africa <br>
                                            Organización para la Armonización en África del Derecho de los Negocios <br>
                                            Organização &nbsp;para &nbsp;a &nbsp;Harmonização &nbsp;em &nbsp;África
                                            &nbsp;do &nbsp;Direito dos Negócios
                                        </div>
                                    </div>
                                </div>
                                <div style="float: right; width: 25%; text-align: right;">
                                    <img src="img/logo/attestation_flyers2.png" alt="Logo Attestation" style="height: 90px;">
                                </div>
                            </div>
                        </header>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="attestation-content">
            @php
                $nbre_jour = 0;
                $get_nbre_jour = round((strtotime($participant->date_fin) - strtotime($participant->date_debut)) / 86400);
                $nbre_jour = $get_nbre_jour + 1;
                $masse_horaire = 8 * $nbre_jour;
            @endphp

            <div style="clear: both; display: block; text-align: center; line-height: 1.2; font-size: 30pt; color: #006fc0; font-weight: bold; margin: 20px 0 10px 0;">
                {{ $nbre_jour >= 5 ? 'ATTESTATION DE FORMATION' : 'ATTESTATION DE FORMATION' }}
            </div>

            <div style="clear: both; display: block; width: 100%; margin-top: 5px; text-align: center; color: #000000; font-size: 20pt; font-weight: bold; margin: 5px 0 7px 0;">
                LE DIRECTEUR GENERAL
            </div>

            <div style="clear: both; display: block; text-align: justify; line-height: 1.4;">
                <span style="font-size: 11.6pt;">
                    <b>Vu</b> le Traité relatif à l’Harmonisation du droit des Affaires en Afrique signé à Port-Louis le 17 octobre 1993, tel que révisé à Québec le 17 octobre 2008 notamment en son article 41 ; <br>
                    <b>Vu</b> le Statut de l’ERSUMA du 03 octobre 1995 révisé par les Règlements n° 004/2009/CM/OHADA du 19 décembre 2009, n° 003/2011/CM du 17 juin 2011 et n° 02/2016/CM/OHADA du 28 octobre 2016 portant Statut révisé de l’ERSUMA ;
                </span>

                <div style="clear: both; display: block; text-align: center; font-size: 14pt; margin: 10px 0;">
                    {{ $nbre_jour >= 5 ? 'Certifie' : 'Atteste' }} que :
                </div>

                <div style="line-height: 1.5; font-size: 13pt; display: block; text-align: center; margin-bottom: 10px;">
                    <span style="font-weight: bold; font-size: 20pt;">{{ trim($participant->prenoms) }} {{ trim($participant->nom) }}</span> <br>
                    a pris part à la session de formation, sur le thème<br>
                    <span style="font-weight: bold; font-size: 16pt;"><i>{{ $participant->theme }}</i></span> <br>

                    qui s’est tenue
                    @if ($participant->lieu != '')
                        à {{ $participant->lieu }}
                    @endif

                    @if ($participant->date_debut == $participant->date_fin)
                        le {{ \Carbon\Carbon::parse($participant->date_debut)->isoFormat('DD MMM YYYY') }}
                    @else
                        du {{ \Carbon\Carbon::parse($participant->date_debut)->isoFormat('DD MMM') }}
                        au {{ \Carbon\Carbon::parse($participant->date_fin)->isoFormat('DD MMM YYYY') }}
                    @endif

                    correspondant à {{ $nbre_jour == 1 ? '4 heures' : "$masse_horaire heures" }} de formation.
                </div>

                <p style="text-align: justify;">
                    La présente attestation lui est délivrée pour en jouir avec les droits et prérogatives qui y sont attachés.
                </p>
            </div>
        </div>

        <table style="width: 100%; padding: 0; margin: 0;">
            <tbody>
                <tr>
                    <td style="vertical-align: bottom;">
                        <footer>
                            <div style="clear: both; display: block; margin: 10px 0 0 0; width: 100%;">
                                <div style="float: left;">&nbsp;</div>
                                <div style="float: right; padding-right: 5px;">
                                    <b>Délivrée à Porto-Novo, le {{ \Carbon\Carbon::parse(date('d-m-Y'))->isoFormat('DD MMM YYYY') }}</b>
                                </div>
                            </div>

                            <div style="clear: both;">
                                <table style="width: 100%; padding: 0; margin: 0;">
                                    <tbody>
                                        <tr>
                                            <td style="width: 40%; vertical-align: bottom;">
                                                <div style="margin-bottom: 1px; margin-left: 10px;">
                                                    <img width="95" height="95" src="data:image/png;base64, {!! base64_encode(QrCode::encoding('UTF-8')->format('svg')->size(100)->generate('Code Formation : ' . $participant->code_formation . ' / Thème : ' . $participant->theme . ' / Auditeur : ' . trim($participant->prenoms) . ' ' . trim($participant->nom))) !!}" />
                                                </div>
                                            </td>

                                            <td style="text-align: center; vertical-align: bottom;">&nbsp;</td>

                                            <td style="width: 30%; vertical-align: bottom; padding: 0;">
                                                @php
                                                    $signataire = getSignataire($participant->code_formation);
                                                @endphp
                                                @if ($signataire == false)
                                                    <div style="text-align: left; margin: 0; padding: 0;">
                                                        <img src="img/signatdg.png" alt="Signature" style="margin: -15px; padding: 0; height: 145px;">
                                                    </div>
                                                    <div style="padding: 0; margin: -20px 0 0; text-align: center;">
                                                        <b><u>Pr. Mayatta Ndiaye MBAYE</u></b>
                                                    </div>
                                                @else
                                                    <div style="text-align: left; margin: 0; padding: 0;">
                                                        <img src="storage/signature/{{ $signataire->signature }}" alt="Signature" style="margin: -15px; padding: 0; height: 145px;">
                                                    </div>
                                                    <div style="padding: 0; margin: -20px 0 0; text-align: center;">
                                                        <b><u>{{ $signataire->titre }} {{ $signataire->nom }} {{ $signataire->prenoms }}</u></b>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div style="display: block; clear: both; width: 100%; background: #fff; border: 0; border-top: 10px solid #9cc2e5; border-bottom: 10px solid #1571c6; margin: 5px 0 10px 0; color: #000000; font-size: 10pt; text-align: center; padding: 5px 0;">
                                ERSUMA, Ouando, Carrefour Cinquantenaire - Route de Pobè, 02 BP 353 Porto-Novo, BENIN - Tél. : +229 01 97 97 05 37 - E-mail : ersuma@ohada.org - Web : <font color="#0563c1"><u><a href="https://ersuma.ohada.org/">https://ersuma.ohada.org</a></u></font>
                            </div>
                        </footer>
                    </td>
                </tr>
            </tbody>
        </table>

        @if (!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach
</body>
</html>
