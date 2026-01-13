<!DOCTYPE html>
<html lang="fr">

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
            background: url('img/logo/logo-ohada-new500.png') no-repeat center 100px / contain;
            opacity: 0.1;
            z-index: -1;
        }

        @page {
            size: 29.7cm 21cm;
            margin: 1.27cm;
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
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 5.2cm;
        }

        .attestation-content {
            margin-top: 30px;
            position: relative;
            z-index: 1;
        }

        .header-section {
            margin-bottom: 20px;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo-section img {
            height: 90px;
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
    @php
        $duree_formation = 0;
        $duree_enregistrement = getDureeEnregistrement($code_formation);
        foreach ($duree_session as $duree) {
            $duree_formation += $duree->duree_session;
        }
    @endphp

    @foreach ($liste_participants as $participant)
        @if ($participant->temps_presence == 0 && $participant->presentiel == 1)
            <!-- En-tête -->
            <div class="header-section">
                <table style="width: 100%; padding: 0; margin: 0;">
                    <tbody>
                        <tr>
                            <td style="vertical-align: top;">
                                <div style="display: block; clear: both; width: 100%; background: #fff; border: 0; border-top: 10px solid #1571c6; border-bottom: 10px solid #9cc2e5; margin: 0 0 10px 0; color: #000000; padding: 0; height: 10px; text-align: center;"></div>
                                <div style="overflow: hidden;">
                                    <div style="float: left; width: 79%;">
                                        <div style="border-bottom: 5px double #2681ca; max-width: 72.7%; padding: 0 0 10px 0;">
                                            <div style="float: left; width: 110px; margin: 0; height: 92px;">
                                                <img src="img/logo/logo-ohada.png" alt="Logo OHADA" style="height: 85px;">
                                            </div>
                                            <div style="line-height: 1.17; margin: -25px 0 0 100px; padding: 0; font-size: 11pt; text-align: justify;">
                                                Organisation pour l’Harmonisation en Afrique du Droit des Affaires <br>
                                                Organization for the Harmonization of Business Law in Africa <br>
                                                Organización para la Armonización en África del Derecho de los Negocios <br>
                                                Organização para a Harmonização em África do Direito dos Negócios
                                            </div>
                                        </div>
                                    </div>
                                    <img src="img/logo/attestation_flyers2.png" alt="Logo Attestation" style="height: 90px; float: right;">
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Logos des partenaires -->
                <div class="logo-section">
                    <table style="width: 100%; padding: 0; margin: 0;">
                        <tbody>
                            <tr style="border-bottom: 4px solid #1a0642;">
                                <td style="width: 15%;">
                                    <img src="img/inc-attestations/logo_ersuma.jpg" alt="Logo ERSUMA" style="height: 95px;">
                                </td>
                                @foreach ($logo_partenaires as $logo)
                                    <td>
                                        <img src="{{ asset('storage/logo_partenaire/' . $logo->logo_path) }}" alt="Logo Partenaire" style="height: 95px;">
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Contenu de l'attestation -->
            <div class="attestation-content">
                @php
                    $nbre_jour = round((strtotime($participant->date_fin) - strtotime($participant->date_debut)) / 86400) + 1;
                    $masse_horaire = 8 * $nbre_jour;
                @endphp

                <div style="text-align: center; margin: 10px 0; font-size: 30pt; color: #006fc0; font-weight: bold; line-height: 1.2;">
                    ATTESTATION DE PARTICIPATION
                </div>

                <div style="text-align: center; margin: 5px 0; font-size: 20pt; font-weight: bold;">
                    LE DIRECTEUR GENERAL
                </div>

                <div style="text-align: justify; line-height: 1.4; font-size: 11.6pt;">
                    <p>
                        <b>Vu</b> le Traité relatif à l’Harmonisation du droit des Affaires en Afrique signé à Port-Louis le 17 octobre 1993, tel que révisé à Québec le 17 octobre 2008 notamment en son article 41 ; <br>
                        <b>Vu</b> le Statut de l’ERSUMA du 03 octobre 1995 révisé par les Règlements n° 004/2009/CM/OHADA du 19 décembre 2009, n° 003/2011/CM du 17 juin 2011 et n° 02/2016/CM/OHADA du 28 octobre 2016 portant Statut révisé de l’ERSUMA ;
                    </p>

                    <div style="text-align: center; font-size: 14pt; margin-top: 8px;">
                        Atteste que :
                    </div>

                    <div style="text-align: center; line-height: 1.5; font-size: 13pt; margin-bottom: 10px;">
                        <span style="font-weight: bold; font-size: 20pt;">
                            {{ trim($participant->prenoms) }} {{ trim($participant->nom) }}
                        </span> <br>
                        a pris part à {{ $typeformation_id == 1 ? "la session de formation" : "l'atelier" }} sur le thème <br>
                        <span style="font-weight: bold; font-size: 16pt;">
                            <i>{{ $participant->theme }}</i>
                        </span>, <br>
                        {{ $typeformation_id == 1 ? "qui s’est tenue" : "qui s’est tenu" }}
                        @if($participant->lieu != "")
                            à <b>{{ $participant->lieu }}</b>
                        @endif
                        @if($participant->date_debut == $participant->date_fin)
                            le {{ \Carbon\Carbon::parse($participant->date_debut)->isoFormat('DD MMM YYYY') }}
                        @else
                            du {{ \Carbon\Carbon::parse($participant->date_debut)->isoFormat('DD MMM') }} au {{ \Carbon\Carbon::parse($participant->date_fin)->isoFormat('DD MMM YYYY') }}
                        @endif
                        correspondant à {{ $nbre_jour == 1 ? "4 heures" : "$masse_horaire heures" }} de formation.
                    </div>

                    <p style="text-align: center;">
                        La présente attestation lui est délivrée pour en jouir avec les droits et prérogatives qui y sont attachés.
                    </p>
                </div>
            </div>

            <!-- Pied de page -->
            <div style="position: relative; z-index: 1;">
                <footer>
                    <div style="clear: both; margin: 10px 0;">
                        <div style="float: right; padding-right: 5px; font-weight: bold;">
                            Délivrée à Porto-Novo, le {{ \Carbon\Carbon::parse(date('d-m-Y'))->isoFormat('DD MMM YYYY') }}
                        </div>
                    </div>

                    <div class="signature-section" style="clear: both; margin-top: 20px;">
                        <table style="width: 100%;">
                            <tbody>
                                <tr>
                                    <td style="width: 40%; vertical-align: bottom;">
                                        <img width="95" height="95" src="data:image/png;base64, {!! base64_encode(QrCode::encoding('UTF-8')->format('svg')->size(100)->generate('Code Formation : ' . $participant->code_formation . ' / Thème : ' . $participant->theme . ' / Auditeur : ' . trim($participant->prenoms) . ' ' . trim($participant->nom))) !!}" alt="QR Code">
                                    </td>
                                    <td style="width: 30%; vertical-align: bottom; text-align: center;">
                                        @php
                                            $signataire = getSignataire($participant->code_formation);
                                        @endphp
                                        @if($signataire == false)
                                            <img src="img/signatdg.png" alt="Signature" style="height: 145px; margin-bottom: -20px;">
                                            <div style="text-align: center; font-weight: bold;">
                                                <u>Pr. Mayatta Ndiaye MBAYE</u>
                                            </div>
                                        @else
                                            <img src="storage/signature/{{ $signataire->signature }}" alt="Signature" style="height: 145px; margin-bottom: -20px;">
                                            <div style="text-align: center; font-weight: bold;">
                                                <u>{{ $signataire->titre }} {{ $signataire->nom }} {{ $signataire->prenoms }}</u>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="footer-content" style="border-top: 10px solid #9cc2e5; border-bottom: 10px solid #1571c6; margin: 5px 0;">
                        ERSUMA, Ouando, Carrefour Cinquantenaire - Route de Pobè, 02 BP 353 Porto-Novo, BENIN - Tél. : +229 01 97 97 05 37 - E-mail : ersuma@ohada.org - Web : <a href="https://ersuma.ohada.org/" style="color: #0563c1; text-decoration: none;">https://ersuma.ohada.org</a>
                    </div>
                </footer>
            </div>

            <!-- Saut de page -->
            @if(!$loop->last)
                <div style="page-break-after: always;"></div>
            @endif

        @else
            @php
                $duree_reel = ($duree_enregistrement > 0) ? $duree_enregistrement : $duree_formation;
                $scoring = intval(($duree_reel * 2) / 3);
            @endphp

            @if($participant->temps_presence >= $scoring)
                <!-- En-tête pour les participants avec temps de présence suffisant -->
                <div class="header-section">
                    <table style="width: 100%; padding: 0; margin: 0;">
                        <tbody>
                            <tr>
                                <td style="vertical-align: top;">
                                    <div style="display: block; clear: both; width: 100%; background: #fff; border: 0; border-top: 10px solid #1571c6; border-bottom: 10px solid #9cc2e5; margin: 0 0 10px 0; color: #000000; padding: 0; height: 10px; text-align: center;"></div>
                                    <div style="overflow: hidden;">
                                        <div style="float: left; width: 79%;">
                                            <div style="border-bottom: 5px double #2681ca; max-width: 72.7%; padding: 0 0 10px 0;">
                                                <div style="float: left; width: 110px; margin: 0; height: 92px;">
                                                    <img src="img/logo/logo-ohada.png" alt="Logo OHADA" style="height: 85px;">
                                                </div>
                                                <div style="line-height: 1.17; margin: -25px 0 0 100px; padding: 0; font-size: 11pt; text-align: justify;">
                                                    Organisation pour l’Harmonisation en Afrique du Droit des Affaires <br>
                                                    Organization for the Harmonization of Business Law in Africa <br>
                                                    Organización para la Armonización en África del Derecho de los Negocios <br>
                                                    Organização para a Harmonização em África do Direito dos Negócios
                                                </div>
                                            </div>
                                        </div>
                                        <img src="img/logo/attestation_flyers2.png" alt="Logo Attestation" style="height: 90px; float: right;">
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Logos des partenaires -->
                    <div class="logo-section">
                        <table style="width: 100%; padding: 0; margin: 0;">
                            <tbody>
                                <tr style="border-bottom: 4px solid #1a0642;">
                                    <td style="width: 15%;">
                                        <img src="img/inc-attestations/logo_ersuma.jpg" alt="Logo ERSUMA" style="height: 95px;">
                                    </td>
                                    @foreach ($logo_partenaires as $logo)
                                        <td>
                                            <img src="{{ asset('storage/logo_partenaire/' . $logo->logo_path) }}" alt="Logo Partenaire" style="height: 95px;">
                                        </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Contenu de l'attestation pour les participants avec temps de présence suffisant -->
                <div class="attestation-content">
                    @php
                        $nbre_jour = round((strtotime($participant->date_fin) - strtotime($participant->date_debut)) / 86400) + 1;
                        $masse_horaire = 8 * $nbre_jour;
                    @endphp

                    <div style="text-align: center; margin: 10px 0; font-size: 30pt; color: #006fc0; font-weight: bold; line-height: 1.2;">
                        {{ $nbre_jour >= 5 ? "CERTIFICAT DE FORMATION" : "ATTESTATION DE PARTICIPATION" }}
                    </div>

                    <div style="text-align: center; margin: 5px 0; font-size: 20pt; font-weight: bold;">
                        LE DIRECTEUR GENERAL
                    </div>

                    <div style="text-align: justify; line-height: 1.4; font-size: 11.6pt;">
                        <p>
                            <b>Vu</b> le Traité relatif à l’Harmonisation du droit des Affaires en Afrique signé à Port-Louis le 17 octobre 1993, tel que révisé à Québec le 17 octobre 2008 notamment en son article 41 ; <br>
                            <b>Vu</b> le Statut de l’ERSUMA du 03 octobre 1995 révisé par les Règlements n° 004/2009/CM/OHADA du 19 décembre 2009, n° 003/2011/CM du 17 juin 2011 et n° 02/2016/CM/OHADA du 28 octobre 2016 portant Statut révisé de l’ERSUMA ;
                        </p>

                        <div style="text-align: center; font-size: 14pt; margin-top: 8px;">
                            {{ $nbre_jour >= 5 ? "Certifie" : "Atteste" }} que :
                        </div>

                        <div style="text-align: center; line-height: 1.5; font-size: 13pt; margin-bottom: 10px;">
                            <span style="font-weight: bold; font-size: 20pt;">
                                {{ trim($participant->prenoms) }} {{ trim($participant->nom) }}
                            </span> <br>
                            a pris part à {{ $typeformation_id == 1 ? "la session de formation" : "l'atelier" }} sur le thème <br>
                            <span style="font-weight: bold; font-size: 16pt;">
                                <i>{{ $participant->theme }}</i>
                            </span>, <br>
                            {{ $typeformation_id == 1 ? "qui s’est tenue" : "qui s’est tenu" }} en visioconférence
                            @if($participant->date_debut == $participant->date_fin)
                                le {{ \Carbon\Carbon::parse($participant->date_debut)->isoFormat('DD MMM YYYY') }}
                            @else
                                du {{ \Carbon\Carbon::parse($participant->date_debut)->isoFormat('DD MMM') }} au {{ \Carbon\Carbon::parse($participant->date_fin)->isoFormat('DD MMM YYYY') }}
                            @endif
                            correspondant à {{ $nbre_jour == 1 ? "4 heures" : "$masse_horaire heures" }} de formation.
                        </div>

                        <p style="text-align: center;">
                            La présente attestation lui est délivrée pour en jouir avec les droits et prérogatives qui y sont attachés.
                        </p>
                    </div>
                </div>

                <!-- Pied de page pour les participants avec temps de présence suffisant -->
                <div style="position: relative; z-index: 1;">
                    <footer>
                        <div style="clear: both; margin: 10px 0;">
                            <div style="float: right; padding-right: 5px; font-weight: bold;">
                                Délivrée à Porto-Novo, le {{ \Carbon\Carbon::parse(date('d-m-Y'))->isoFormat('DD MMM YYYY') }}
                            </div>
                        </div>

                        <div class="signature-section" style="clear: both; margin-top: 20px;">
                            <table style="width: 100%;">
                                <tbody>
                                    <tr>
                                        <td style="width: 40%; vertical-align: bottom;">
                                            <img width="95" height="95" src="data:image/png;base64, {!! base64_encode(QrCode::encoding('UTF-8')->format('svg')->size(100)->generate('Code Formation : ' . $participant->code_formation . ' / Thème : ' . $participant->theme . ' / Auditeur : ' . trim($participant->prenoms) . ' ' . trim($participant->nom))) !!}" alt="QR Code">
                                        </td>
                                        <td style="width: 30%; vertical-align: bottom; text-align: center;">
                                            @php
                                                $signataire = getSignataire($participant->code_formation);
                                            @endphp
                                            @if($signataire == false)
                                                <img src="img/signatdg.png" alt="Signature" style="height: 145px; margin-bottom: -20px;">
                                                <div style="text-align: center; font-weight: bold;">
                                                    <u>Pr. Mayatta Ndiaye MBAYE</u>
                                                </div>
                                            @else
                                                <img src="storage/signature/{{ $signataire->signature }}" alt="Signature" style="height: 145px; margin-bottom: -20px;">
                                                <div style="text-align: center; font-weight: bold;">
                                                    <u>{{ $signataire->titre }} {{ $signataire->nom }} {{ $signataire->prenoms }}</u>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="footer-content" style="border-top: 10px solid #9cc2e5; border-bottom: 10px solid #1571c6; margin: 5px 0;">
                            ERSUMA, Ouando, Carrefour Cinquantenaire - Route de Pobè, 02 BP 353 Porto-Novo, BENIN - Tél. : +229 01 97 97 05 37 - E-mail : ersuma@ohada.org - Web : <a href="https://ersuma.ohada.org/" style="color: #0563c1; text-decoration: none;">https://ersuma.ohada.org</a>
                        </div>
                    </footer>
                </div>

                @if(!$loop->last)
                    <div style="page-break-after: always;"></div>
                @endif
            @endif
        @endif
    @endforeach
</body>

</html>
