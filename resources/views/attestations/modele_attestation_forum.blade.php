<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <title>Attestation de formation - ERSUMA</title>
    <style type="text/css">
        /* Réinitialisation et base */
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

        /* Configuration de la page */
        @page {
            size: 29.7cm 21cm;
            margin: 1.27cm;
        }

        /* En-tête */
        header {
            position: relative;
            margin-bottom: 1cm;
            padding: 0;
            width: 100%;
        }

        /* Conteneur principal */
        .attestation-container {
            position: relative;
            min-height: 29.7cm;
            padding: 0;
            margin-bottom: 20px;
        }

        /* Contenu principal */
        .content {
            position: relative;
            margin-top: 1cm;
            padding: 0 1cm;
        }

        /* Pied de page */
        footer {
            position: relative;
            width: 100%;
            height: auto;
            margin-top: 20px;
            font-size: 10pt;
            padding: 0.5cm 1cm;
            border-top: 10px solid #9cc2e5;
        }

        /* Titres et sections */
        .main-title {
            text-align: center;
            font-size: 36px;
            color: #006fc0;
            font-weight: bold;
            margin: 10px 0;
            line-height: 1.1;
        }

        .sub-title {
            text-align: center;
            font-size: 20px;
            color: #1a0642;
            font-weight: bold;
            margin: 10px 0;
        }

        .director-title {
            text-align: center;
            font-size: 20px;
            color: #000000;
            font-weight: bold;
            margin: 10px 0;
        }

        /* Texte principal */
        .main-text {
            text-align: justify;
            line-height: 1.5;
            font-size: 11.6pt;
            margin: 15px 0;
        }

        .participant-name {
            text-align: center;
            font-size: 20pt;
            font-weight: bold;
            margin: 10px 0;
        }

        .theme-title {
            text-align: center;
            font-size: 16pt;
            font-style: italic;
            font-weight: bold;
            margin: 10px 0;
        }

        /* Section signature */
        .signature-section {
            margin: 20px 0;
            position: relative;
        }

        .qr-code {
            text-align: left;
            margin-left: 10px;
        }

        /* Tableaux */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            padding: 0;
        }

        /* Liens */
        a {
            color: #0563c1;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Correction pour l'impression */
        @media print {
            body::before {
                opacity: 0.1;
            }

            body {
                margin: 0;
                padding: 0;
            }

            .content {
                margin-top: 0;
            }
        }
    </style>
</head>

<body>
    @php
        $duree_formation = 0;
        $i = 0;
        $theme = '';         
        $duree_formation = 0;
        $duree_enregistrement = getDureeEnregistrement($code_formation);
        foreach ($duree_session as $duree) {
            $duree_formation += $duree->duree_session;
        }

    @endphp 

    @foreach ($liste_participants as $participant)
        <div class="attestation-container">
            @if ($participant->temps_presence == 0 && $participant->presentiel == 1)
                @if ($logo_partenaires->count() == 0)
                    <!-- Entête standard -->
                    <header> 
                        <div style="display: block; clear: both; width: 100%; background: #fff; border: 0; border-top: 10px solid #1571c6; border-bottom: 10px solid #9cc2e5; margin: 0 0 10px 0; color: #000000; padding: 0; height: 10px; text-align: center;"></div>
                        <div style="width: 100%;">
                            <div style="float: left; width: 82%;">
                                <div style="border-bottom: 5px double #2681ca; max-width: 75.7%; padding: 0 0 10px 0;">
                                    <div style="float: left; width: 105px; margin: 0; height: 92px;">
                                        <img src="img/logo/logo-ohada.png" alt="Logo OHADA" style="height: 85px;">
                                    </div>
                                    <div style="line-height: 1.17; margin: -15px 0 0 100px; padding: 0; font-size: 12pt; text-align: justify;">
                                        Organisation &nbsp;&nbsp;pour &nbsp;l’Harmonisation &nbsp;en &nbsp;Afrique &nbsp;du &nbsp;Droit &nbsp;des &nbsp;&nbsp;Affaires <br>
                                        Organization &nbsp;&nbsp; for&nbsp; &nbsp;the&nbsp; &nbsp;Harmonization &nbsp;&nbsp; of &nbsp;&nbsp;Business &nbsp;&nbsp; Law &nbsp;in &nbsp;&nbsp; Africa <br>
                                        Organización para la Armonización en África del Derecho de los Negocios <br>
                                        Organização &nbsp;para &nbsp;a &nbsp;Harmonização &nbsp;em &nbsp;África &nbsp;do &nbsp;Direito dos Negócios
                                    </div>
                                </div>
                            </div>
                            <img src="img/logo/attestation_flyers2.png" alt="Logo Attestation" style="height: 90px; float: right;">
                        </div>
                    </header>
                    <!-- Fin Entête standard -->
                @else
                    <!-- Entête dynamique -->
                    <header>
                        <table style="width: 100%; padding: 0; margin: 0; text-align: center; border-bottom: 4px solid #1a0642;">
                            <tr style="border-bottom: 4px solid #1a0642;">
                                @if ($formation->is_coorganisation)
                                    <!-- Logo OHADA par défaut -->
                                    <td style="width: 40%;">
                                        <img src="img/inc-attestations/logo_ohada-fr.jpg" alt="Logo OHADA" style="height: 95px;">
                                    </td>
                                @else
                                    <!-- Logo ERSUMA par défaut -->
                                    <td style="width: 15%;">
                                        <img src="img/inc-attestations/logo_ersuma.jpg" alt="Logo ERSUMA" style="height: 80px;">
                                    </td>
                                @endif
                                @foreach ($logo_partenaires as $logo)
                                    @if ($logo_partenaires->count() == 1)
                                        <td style="text-align: right;">
                                            <img src="storage/logo_partenaire/{{ $logo->logo_path }}" alt="Logo Partenaire" style="height: 80px;">
                                        </td>
                                    @else
                                        <td>
                                            <img src="storage/logo_partenaire/{{ $logo->logo_path }}" alt="Logo Partenaire" style="height: 80px;">
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        </table>
                    </header>
                    <!-- Fin Entête dynamique -->
                @endif

                <div class="content">
                    @php
                        $nbre_jour = 0; 
                        $masse_horaire = 8 * $nbre_jour;

                         $nbre_jour = round((strtotime($participant->date_fin) - strtotime($participant->date_debut)) / 86400) + 1;
                        $masse_horaire = 8 * $nbre_jour;
                    @endphp

                    @if ($formation->titre_evenement)
                        <div style="text-align: center; font-size: 20px; font-weight: bold; color: #1a0642; margin-bottom: 10px;">
                            {{ $formation->titre_evenement }}
                        </div>
                    @endif

                    <div class="main-title">
                        @if ($typeformation_id == 1)
                            ATTESTATION DE FORMATION
                        @else
                            ATTESTATION DE PARTICIPATION
                        @endif
                    </div>

                    <!--<div class="director-title">
                        LE DIRECTEUR GENERAL
                    </div>-->

                    <div class="main-text">
                        <span style="font-size: 11.6pt;">
                                <b>Vu</b> le Traité relatif à l’Harmonisation du droit des Affaires en Afrique signé à
                                Port-Louis le 17 octobre 1993, tel que révisé à Québec le 17 octobre 2008 notamment en
                                son article 41 ;<br>
                                <b>Vu</b> le Statut de l’ERSUMA du 03 octobre 1995 révisé par les Règlements n°
                                004/2009/CM/OHADA du 19 décembre 2009, n° 003/2011/CM du 17 juin 2011 et n°
                                02/2016/CM/OHADA du 28 octobre 2016 portant Statut révisé de l’ERSUMA ;
                            </span><br>

                        <div style="text-align: center; font-size: 14pt;">
                            délivrée à
                        </div>

                        <div style="line-height: 1.5; font-size: 13pt; text-align: center; margin-bottom: 10px;">
                            <span style="font-weight: bold; font-size: 20pt;">
                               {{ trim($participant->prenoms) }} {{ trim($participant->nom) }}
                            </span><br>
                                pour sa participation au <span
                                    style="font-weight: bold; font-size: 16pt;"><i>{{ $participant->theme }}</i></span><br>
                                qui s’est tenu @if ($participant->lieu != '')
                                    à <b>{{ $participant->lieu }}</b>
                                @endif
                                @if ($participant->date_debut == $participant->date_fin)
                                    le {{ \Carbon\Carbon::parse($participant->date_debut)->isoFormat('DD MMM YYYY') }},
                                @else
                                    du {{ \Carbon\Carbon::parse($participant->date_debut)->isoFormat('DD MMM') }} au
                                    {{ \Carbon\Carbon::parse($participant->date_fin)->isoFormat('DD MMM YYYY') }},
                                @endif
                                correspondant à {{ $nbre_jour == 1 ? '4 heures' : "$masse_horaire heures" }} de
                                participation. 
                        </div>

                        <p style="text-align: justify;">
                           La présente attestation lui est délivrée pour en jouir avec les droits et prérogatives qui y sont attachés.
                        </p>
                    </div>
                </div>

                <footer>
                    <div style="clear: both; margin: 10px 0 0; width: 100%;">
                        <div style="float: right; padding-right: 5px;">
                            <b>Délivrée à {{ $participant->lieu }}, le {{ \Carbon\Carbon::parse(date('d-m-Y'))->isoFormat('DD MMM YYYY') }}</b>
                        </div>
                    </div>

                    <div style="clear: both;">
                        <table style="width: 100%; padding: 0; margin: 0;">
                            <tbody>
                                <tr>
                                    <td style="width: 40%; vertical-align: bottom;">
                                        <div class="qr-code">
                                            <img width="95" height="95" src="data:image/png;base64, {!! base64_encode(QrCode::encoding('UTF-8')->format('svg')->size(100)->generate('Code Formation : ' . $participant->code_formation . ' / Thème : ' . $participant->theme . ' / Auditeur : ' . trim($participant->prenoms) . ' ' . trim($participant->nom))) !!}" />
                                        </div>
                                    </td>

                                    <td style="text-align: center; vertical-align: bottom;">
                                        &nbsp;
                                    </td>

                                    <td style="width: 30%; vertical-align: bottom; padding: 0;">
                                        @php
                                            $signataire = getSignataire($participant->code_formation);
                                        @endphp
                                        @if ($signataire == false)
                                            <div style="text-align: left; margin: 0; padding: 0;">
                                                <img src="img/signatdg.png" alt="Signature" style="height: 130px;">
                                            </div>
                                            <div style="text-align: center; margin: -20px 0 0;">
                                                <b><u>Dr. DOGUE Karel Osiris Coffi</u></b>
                                            </div>
                                        @else
                                            <div style="text-align: left; margin: 0; padding: 0;">
                                                <img src="storage/signature/{{ $signataire->signature }}" alt="Signature" style="height: 130px;">
                                            </div>
                                            <div style="text-align: center; margin: -20px 0 0;">
                                                <b><u>{{ $signataire->titre }} {{ trim($signataire->nom) }} {{ trim($signataire->prenoms) }}</u></b>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div style="width: 100%; background: #fff; border: 0; border-top: 10px solid #9cc2e5; border-bottom: 10px solid #1571c6; margin: 5px 0 10px; text-align: center; padding: 5px 0; font-size: 10pt;">
                        ERSUMA, Ouando, Carrefour Cinquantenaire - Route de Pobè, 02 BP 353 Porto-Novo, BENIN <br>
                        Tél. : +229 01 97 97 05 37 - E-mail : ersuma@ohada.org - Web : <a href="https://ersuma.ohada.org/">https://ersuma.ohada.org</a>
                    </div>
                </footer>
                @if (!$loop->last)
                <div style="page-break-after: always;"></div>
                @endif
            @else
                @php
                $duree_reel = $duree_enregistrement > 0 ? $duree_enregistrement : $duree_formation;
                $scoring = intval(($duree_reel * 2) / 3);
                 
                @endphp

                @if ($participant->temps_presence >= $scoring)
                    @if ($logo_partenaires->count() == 0)
                        <!-- Entête standard -->
                        <header>
                            <div style="display: block; clear: both; width: 100%; background: #fff; border: 0; border-top: 10px solid #1571c6; border-bottom: 10px solid #9cc2e5; margin: 0 0 10px 0; color: #000000; padding: 0; height: 10px; text-align: center;"></div>
                            <div style="width: 100%;">
                                <div style="float: left; width: 82%;">
                                    <div style="border-bottom: 5px double #2681ca; max-width: 75.7%; padding: 0 0 10px 0;">
                                        <div style="float: left; width: 105px; margin: 0; height: 92px;">
                                            <img src="img/logo/logo-ohada.png" alt="Logo OHADA" style="height: 85px;">
                                        </div>
                                        <div style="line-height: 1.17; margin: -15px 0 0 100px; padding: 0; font-size: 12pt; text-align: justify;">
                                            Organisation &nbsp;&nbsp;pour &nbsp;l’Harmonisation &nbsp;en &nbsp;Afrique &nbsp;du &nbsp;Droit &nbsp;des &nbsp;&nbsp;Affaires <br>
                                            Organization &nbsp;&nbsp; for&nbsp; &nbsp;the&nbsp; &nbsp;Harmonization &nbsp;&nbsp; of &nbsp;&nbsp;Business &nbsp;&nbsp; Law &nbsp;in &nbsp;&nbsp; Africa <br>
                                            Organización para la Armonización en África del Derecho de los Negocios <br>
                                            Organização &nbsp;para &nbsp;a &nbsp;Harmonização &nbsp;em &nbsp;África &nbsp;do &nbsp;Direito dos Negócios
                                        </div>
                                    </div>
                                </div>
                                <img src="img/logo/attestation_flyers2.png" alt="Logo Attestation" style="height: 90px; float: right;">
                            </div>
                        </header>
                        <!-- Fin Entête standard -->
                    @else
                        <!-- Entête dynamique -->
                        <header>
                            <table style="width: 100%; padding: 0; margin: 0; text-align: center; border-bottom: 4px solid #1a0642;">
                                <tr style="border-bottom: 4px solid #1a0642;">
                                    @if ($formation->is_coorganisation)
                                        <!-- Logo OHADA par défaut -->
                                        <td style="width: 40%;">
                                            <img src="img/inc-attestations/logo_ohada-fr.jpg" alt="Logo OHADA" style="height: 95px;">
                                        </td>
                                    @else
                                        <!-- Logo ERSUMA par défaut -->
                                        <td style="width: 15%;">
                                            <img src="img/inc-attestations/logo_ersuma.jpg" alt="Logo ERSUMA" style="height: 80px;">
                                        </td>
                                    @endif
                                    @foreach ($logo_partenaires as $logo)
                                        @if ($logo_partenaires->count() == 1)
                                            <td style="text-align: right;">
                                                <img src="storage/logo_partenaire/{{ $logo->logo_path }}" alt="Logo Partenaire" style="height: 80px;">
                                            </td>
                                        @else
                                            <td>
                                                <img src="storage/logo_partenaire/{{ $logo->logo_path }}" alt="Logo Partenaire" style="height: 80px;">
                                            </td>
                                        @endif
                                    @endforeach
                                </tr>
                            </table>
                        </header>
                        <!-- Fin Entête dynamique -->
                    @endif

                    <div class="content">
                        @php
                     $nbre_jour = round((strtotime($participant->date_fin) - strtotime($participant->date_debut)) / 86400) + 1;
                        $masse_horaire = 8 * $nbre_jour;
                        @endphp

                        @if ($formation->titre_evenement)
                            <div style="text-align: center; font-size: 20px; font-weight: bold; color: #1a0642; margin-bottom: 10px;">
                                {{ $formation->titre_evenement }}
                            </div>
                        @endif

                        <div class="main-title">
                            @if ($typeformation_id == 1)
                                ATTESTATION DE FORMATION
                            @else
                                ATTESTATION DE PARTICIPATION
                            @endif
                        </div>

                        <div class="director-title">
                            LE DIRECTEUR GENERAL
                        </div>

                        <div class="main-text">
                           <span >
                                    <b>Vu</b> le Traité relatif à l’Harmonisation du droit des Affaires en Afrique signé
                                    à Port-Louis le 17 octobre 1993, tel que révisé à Québec le 17 octobre 2008
                                    notamment en son article 41 ;<br>
                                    <b>Vu</b> le Statut de l’ERSUMA du 03 octobre 1995 révisé par les Règlements n°
                                    004/2009/CM/OHADA du 19 décembre 2009, n° 003/2011/CM du 17 juin 2011 et n°
                                    02/2016/CM/OHADA du 28 octobre 2016 portant Statut révisé de l’ERSUMA ;
                                </span><br>

                            <div style="text-align: center; font-size: 14pt; margin: 10px 0;">
                                délivrée à
                            </div>

                            <div style="line-height: 1.5; font-size: 13pt; text-align: center; margin-bottom: 10px;">
                                <span style="font-weight: bold; font-size: 20pt;">
                                   {{ trim($participant->prenoms) }} {{ trim($participant->nom) }}
                                </span> <br>
                                pour sa participation au <span
                                        style="font-weight: bold; font-size: 16pt;"><i>{{ $participant->theme }}</i></span><br>
                                    qui s’est tenue en visioconférence
                                    @if ($participant->date_debut == $participant->date_fin)
                                        le
                                        {{ \Carbon\Carbon::parse($participant->date_debut)->isoFormat('DD MMM YYYY') }},
                                    @else
                                        du {{ \Carbon\Carbon::parse($participant->date_debut)->isoFormat('DD MMM') }}
                                        au
                                        {{ \Carbon\Carbon::parse($participant->date_fin)->isoFormat('DD MMM YYYY') }},
                                    @endif
                                    correspondant à {{ $nbre_jour == 1 ? '4 heures' : "$masse_horaire heures" }} de
                                    participation. 
                            </div>

                            <p style="text-align: justify;">
                                La présente attestation lui est délivrée pour en jouir avec les droits et prérogatives qui y sont attachés.
                            </p>
                        </div>
                    </div>

                    <footer>
                        <div style="clear: both; margin: 10px 0 0; width: 100%;">
                            <div style="float: right; padding-right: 5px;">
                                <b>Délivrée à {{ $participant->lieu }}, le {{ \Carbon\Carbon::parse(date('d-m-Y'))->isoFormat('DD MMM YYYY') }}</b>
                            </div>
                        </div>

                        <div style="clear: both;">
                            <table style="width: 100%; padding: 0; margin: 0;">
                                <tbody>
                                    <tr>
                                        <td style="width: 40%; vertical-align: bottom;">
                                            <div class="qr-code">
                                                <img width="95" height="95" src="data:image/png;base64, {!! base64_encode(QrCode::encoding('UTF-8')->format('svg')->size(100)->generate('Code Formation : ' . $participant->code_formation . ' / Thème : ' . $participant->theme . ' / Auditeur : ' . trim($participant->prenoms) . ' ' . trim($participant->nom))) !!}" />
                                            </div>
                                        </td>

                                        <td style="text-align: center; vertical-align: bottom;">
                                            &nbsp;
                                        </td>

                                        <td style="width: 30%; vertical-align: bottom; padding: 0;">
                                            @php
                                                $signataire = getSignataire($participant->code_formation);
                                            @endphp
                                            @if ($signataire == false)
                                                <div style="text-align: left; margin: 0; padding: 0;">
                                                    <img src="img/signatdg.png" alt="Signature" style="height: 130px;">
                                                </div>
                                                <div style="text-align: center; margin: -20px 0 0;">
                                                    <b><u>Pr. Mayatta Ndiaye MBAYE</u></b>
                                                </div>
                                            @else
                                                <div style="text-align: left; margin: 0; padding: 0;">
                                                    <img src="storage/signature/{{ $signataire->signature }}" alt="Signature" style="height: 130px;">
                                                </div>
                                                <div style="text-align: center; margin: -20px 0 0;">
                                                    <b><u>{{ $signataire->titre }} {{ trim($signataire->nom) }} {{ trim($signataire->prenoms) }}</u></b>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div style="width: 100%; background: #fff; border: 0; border-top: 10px solid #9cc2e5; border-bottom: 10px solid #1571c6; margin: 5px 0 10px; text-align: center; padding: 5px 0; font-size: 10pt;">
                            ERSUMA, Ouando, Carrefour Cinquantenaire - Route de Pobè, 02 BP 353 Porto-Novo, BENIN <br>
                            Tél. : +229 01 97 97 05 37 - E-mail : ersuma@ohada.org - Web : <a href="https://ersuma.ohada.org/">https://ersuma.ohada.org</a>
                        </div>
                    </footer>
                @endif
            @endif
        </div>

        @if (!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach
</body>
</html>
