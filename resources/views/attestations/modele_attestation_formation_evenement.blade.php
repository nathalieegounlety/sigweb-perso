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
}

body::before {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('img/logo/logo-ohada-new500.png') no-repeat fixed center 100px / contain;
    opacity: 0.1;
    z-index: -1;
}
    
        /* Configuration de la page */
        @page {
            size: 29.7cm 21cm;
            margin: 1.27cm;
        }
    
        /* Conteneur principal */
        .attestation-container {
            position: relative;
            min-height: 29.7cm;
            padding: 0;
        }
    
        /* Fond d'eau (filigrane) */
        .watermark {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('img/logo/logo-ohada-new500.png') no-repeat center;
            background-size: contain;
            opacity: 0.1;
            z-index: -1;
        }
    
        /* En-tête */
        header {
            position: relative;
            margin-bottom: 1cm;
            padding: 0;
        }
    
        /* Contenu principal */
        .content {
            position: relative;
            min-height: calc(100% - 8.7cm);
            margin-top: 0.5cm;
            padding: 0 1cm;
            z-index: 1;
        }
    
        /* Pied de page */
        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 5.2cm;
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
            .watermark {
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
        $duree_enregistrement = getDureeEnregistrement($code_formation);
    @endphp
    @foreach ($duree_session as $duree)
        @php
            $duree_formation = $duree_formation + $duree->duree_session;
            //$theme = $duree->getFormation->theme;
            //  $type_formation = $duree->getFormation->typeformation_id;
        @endphp
    @endforeach

    @foreach ($liste_participants as $participant)

        @if ($participant->temps_presence == 0 && $participant->presentiel == 1)
            <table style="width: 100%;  padding: 0; margin: 0 ;">
                <tbody>
                    <tr>
                        <td style="vertical-align:top;">
                            {{--<header>
                                <div
                                    style="display:block; clear:both; width:100%; background: #fff; border: 0; border-top: 10px solid #1571c6; border-bottom:  10px solid #9cc2e5;  margin: 0 0 10px 0; color:#000000; padding:0; height:10px; text-align:center;">

                                </div>

                                <div style="z-index:100;">
                                    <div style="float:left; width:82%;">

                                        <div
                                            style="z-index:100; border-bottom:5px double #2681ca; max-width:75.7%; padding: 0 0 10px 0; ">

                                            <div style="z-index:100;float:left; width: 105px; margin:0px; height:92px; ">
                                                <img src="img/logo/logo-ohada.png" alt="" height="85px"
                                                    style="height: 85px;">
                                            </div>
                                            <div
                                                style=" line-height:1.17; margin: -15px 0 0 100px; padding:0px; top:0; font-size:12pt; text-align:justify;">
                                                Organisation &nbsp;&nbsp;pour &nbsp;l’Harmonisation &nbsp;en
                                                &nbsp;Afrique &nbsp;du &nbsp;Droit &nbsp;des &nbsp;&nbsp;Affaires <br>
                                                Organization &nbsp;&nbsp; for&nbsp; &nbsp;the&nbsp; &nbsp;Harmonization
                                                &nbsp;&nbsp; of &nbsp;&nbsp;Business
                                                &nbsp;&nbsp; Law &nbsp;in &nbsp;&nbsp; Africa <br>
                                                Organización para la Armonización en África del Derecho de los Negocios
                                                <br>
                                                Organização &nbsp;para &nbsp;a &nbsp;Harmonização &nbsp;em &nbsp;África
                                                &nbsp;do &nbsp;Direito dos Negócios
                                            </div>
                                        </div>
                                    </div>
                                    <img src="img/logo/attestation_flyers2.png" alt="logo" height="90px"
                                        style="height: 90px;">
                                </div>
                                </div>

                            </header>--}}

                            @if ($logo_partenaires->count() == 0)
                        <!-- Entête standard -->
                        <header>
                                <div
                                    style="display:block; clear:both; width:100%; background: #fff; border: 0; border-top: 10px solid #1571c6; border-bottom:  10px solid #9cc2e5;  margin: 0 0 10px 0; color:#000000; padding:0; height:10px; text-align:center;">

                                </div>

                                <div style="z-index:100;">
                                    <div style="float:left; width:82%;">

                                        <div
                                            style="z-index:100; border-bottom:5px double #2681ca; max-width:75.7%; padding: 0 0 10px 0; ">

                                            <div style="z-index:100;float:left; width: 105px; margin:0px; height:92px; ">
                                                <img src="img/logo/logo-ohada.png" alt="" height="85px"
                                                    style="height: 85px;">
                                            </div>
                                            <div
                                                style=" line-height:1.17; margin: -15px 0 0 100px; padding:0px; top:0; font-size:12pt; text-align:justify;">
                                                Organisation &nbsp;&nbsp;pour &nbsp;l’Harmonisation &nbsp;en
                                                &nbsp;Afrique &nbsp;du &nbsp;Droit &nbsp;des &nbsp;&nbsp;Affaires <br>
                                                Organization &nbsp;&nbsp; for&nbsp; &nbsp;the&nbsp; &nbsp;Harmonization
                                                &nbsp;&nbsp; of &nbsp;&nbsp;Business
                                                &nbsp;&nbsp; Law &nbsp;in &nbsp;&nbsp; Africa <br>
                                                Organización para la Armonización en África del Derecho de los Negocios
                                                <br>
                                                Organização &nbsp;para &nbsp;a &nbsp;Harmonização &nbsp;em &nbsp;África
                                                &nbsp;do &nbsp;Direito dos Negócios
                                            </div>
                                        </div>
                                    </div>
                                    <img src="img/logo/attestation_flyers2.png" alt="logo" height="90px"
                                        style="height: 90px;">
                                </div>
                                </div>

                            </header>
                        <!-- End Entête standard -->
                    @else
                        <!-- Entête dynamique -->
                            <header>
                                <table style="width: 100%; padding: 0; margin: 0 ; text-align:center;border-bottom:4px solid #1a0642;">
                                    
                                    <tr style="border-bottom:4px solid #1a0642;">
                                        @if ($formation->is_coorganisation)
                                            <!-- logo OHADA par defaut -->
                                            <td style="width:40%;">
                                                <img src="img/inc-attestations/logo_ohada-fr.jpg" alt="logo" height="95px" style="height: 95px;">
                                            </td>
                                        @else
                                            <!-- logo ERSUMA par defaut -->
                                            <td style="width:15%;">
                                                <img src="img/inc-attestations/logo_ersuma.jpg" alt="logo" height="80px" style="height: 80px;">
                                            </td>
                                        @endif
                                        @foreach ($logo_partenaires as $logo)
                                            @if ($logo_partenaires->count() == 1)
                                            <td style="text-align:right;">
                                                <img src="storage/logo_partenaire/{{$logo->logo_path}}" alt="logo" height="80px" style="height: 80px;">
                                            </td>
                                                    
                                            @else
                                            <td style="">
                                                <img src="storage/logo_partenaire/{{$logo->logo_path}}" alt="logo" height="80px" style="height: 80px;">
                                            </td>
                                                
                                            @endif
                                        @endforeach 
                                    </tr>
                                </table>
                            </header>

                        <!-- End Entête dynamique -->
                        
                    @endif 
                        </td>
                    </tr>
                </tbody>
            </table> 
             <!-- <div style="z-index: 10; background:none;"> -->
                <div style="z-index: 10; background:none; margin-top:85px; margin-bottom:0;">
                @php
                    $nbre_jour = 0;
                    $get_nbre_jour = round(
                        (strtotime($participant->date_fin) - strtotime($participant->date_debut)) / 86400,
                    );
                    $nbre_jour = $get_nbre_jour + 1;
                @endphp

                <div>
                    <!-- div opacity -->
                    <div style="z-index: 300; opacity:0; margin:0;font-size:12pt;">
                          @if ($formation->titre_evenement)
                                <div style="z-index: 3; display:block; text-align:center; font-size: 20px; font-weight:bold; color:#1a0642;">{{$formation->titre_evenement}}</div>
                            @endif
                            <div
                            style="z-index: 100;clear:both; display:block; margin: 10px 0 0px 0;text-align: center;line-height: 1.2;font-size: 36px; color:#006fc0; font-weight:bold;">
                            <div style="margin: 08px 0;">
                                @if ($typeformation_id == 1)
                                    ATTESTATION DE FORMATION
                                @else
                                    ATTESTATION DE PARTICIPATION
                                @endif

                            </div>
                        </div>
                        <div
                            style="clear:both; display:block; width:100%; margin-top:2px; text-align:center; color : #000000; font-size: 20pt; font-weight:bold;">
                            <div style="margin: 5px 0 7px 0;">
                                LE DIRECTEUR GENERAL
                            </div>
                        </div>
                        <div style="clear:both; display:block; text-align:justify;">
                            <div style="line-height: 1.4; ">
                                <span style="font-size:11.6pt;">
                                    <b>Vu</b> le Traité relatif à l’Harmonisation du droit des Affaires en Afrique signé
                                    à Port-Louis le 17 octobre
                                    1993, tel que révisé à Québec le 17 octobre 2008 notamment en son article 41 ; <br>
                                    <b>Vu</b> le Statut de l’ERSUMA du 03 octobre 1995 révisé par les Règlements n°
                                    004/2009/CM/OHADA du 19 décembre
                                    2009, n° 003/2011/CM du 17 juin 2011 et n° 02/2016/CM/OHADA du 28 octobre 2016 portant Statut révisé
                                    de l’ERSUMA ;
                                </span><br>
                                <div style="clear:both; display:block;text-align:center;font-size: 14pt;"> Atteste que :
                                </div>
                                <div
                                    style="line-height: 1.5;font-size: 13pt; display:block; text-align:center; margin-bottom:10px;">
                                    <span style="font-weight:bold; font-size:20pt;">{{ trim($participant->prenoms) }}
                                        {{ trim($participant->nom) }}</span> <br>a pris
                                    part à la {{ $typeformation_id == 1 ? 'session de formation' : 'conférence' }}, sur
                                    le thème<br>
                                    <span style="font-weight:bold; font-size:16pt;">
                                        <i>{{ $participant->theme }}</i></span>,<br>
                                    qui s’est tenue
                                    @if ($participant->lieu != '')
                                        à {{ $participant->lieu }}
                                    @endif
                                    @if ($participant->date_debut == $participant->date_fin)
                                        le
                                        {{ \Carbon\Carbon::parse($participant->date_debut)->isoFormat('DD MMM YYYY') }}
                                    @else
                                        du
                                        {{ \Carbon\Carbon::parse($participant->date_debut)->isoFormat('DD MMM') }}
                                        au
                                        {{ \Carbon\Carbon::parse($participant->date_fin)->isoFormat('DD MMM YYYY') }}
                                    @endif
                                    @php
                                        $masse_horaire = 8 * $nbre_jour;
                                    @endphp
                                    correspondant à {{ $nbre_jour == 1 ? '4 heures' : "$masse_horaire heures" }} de
                                    formation.
                                </div>
                                {{-- {{$nbre_jour}} --}}
                                <p>
                                    La présente attestation lui est délivrée pour en jouir avec les droits et
                                    prérogatives qui y sont attachés.
                                </p>
                            </div>
                        </div>

                    </div>
                    <!-- end div opacity -->

                </div><!-- fermeture div background-image -->


            </div>

            <table style="width: 100%;  padding: 0; margin: 0 ;">
                <tbody>
                    <tr>
                        <td style="vertical-align: bottom;">
                            <footer>

                                <div style="clear:both; display:block; margin:10px 0 0 0; width: 100%; ">
                                    <div style="float:left;">
                                        &nbsp;
                                    </div>
                                    <div style=" float:right; padding-right:5px;">
                                        <b>Délivrée à Porto-Novo, le
                                            {{ \Carbon\Carbon::parse(date('d-m-Y'))->isoFormat('DD MMM YYYY') }}</b>
                                    </div>
                                </div>
                                <div style="clear:both; ">
                                    <table style="width: 100%;  padding: 0; margin: 0 ;">
                                        <tbody>
                                            <tr>
                                                <td style="width:40%;vertical-align: bottom;">
                                                    <div style="margin-bottom: 1px; margin-left: 10px;0;">
                                                        <img width="95" height="95"
                                                            src="data:image/png;base64, {!! base64_encode(
                                                                QrCode::encoding('UTF-8')->format('svg')->size(100)->generate(
                                                                        'Code Formation : ' .
                                                                            $participant->code_formation .
                                                                            ' / Thème : ' .
                                                                            $participant->theme .
                                                                            ' / Auditeur : ' .
                                                                            $participant->prenoms .
                                                                            ' ' .
                                                                            $participant->nom,
                                                                    ),
                                                            ) !!}" />
                                                    </div>
                                                </td> 

                                                <td style="text-align:center;vertical-align: bottom;">
                                                    &nbsp;
                                                </td>

                                                <td style="width:30%; vertical-align: bottom; padding:0; ">
                                                    @php
                                                        $signataire = getSignataire($participant->code_formation);
                                                    @endphp
                                                    @if ($signataire == false)
                                                        <div
                                                            style=" text-align:left; margin:0; padding:0; z-index: 10;">
                                                            <img src="img/signatdg.png" alt="Signature"
                                                                style="margin: -15px; padding:0; height: 145px; text-align:left; ">
                                                        </div>
                                                        <div
                                                            style="padding:0; margin:-20px 0 0; text-align:center; z-index: 100;">
                                                            <b><u>Pr. Mayatta Ndiaye MBAYE</u></b>
                                                        </div>
                                                    @else
                                                        <div
                                                            style=" text-align:left; margin:0; padding:0; z-index: 10;">
                                                            <img src="storage/signature/{{ $signataire->signature }}"
                                                                alt="Signature"
                                                                style="margin: -15px; padding:0; height: 130px; text-align:left; ">
                                                        </div>
                                                        <div
                                                            style="padding:0 0 0 50px; margin:-20px 0 0; text-align:center; z-index: 100;">
                                                            <b><u> {{ $signataire->titre }}
                                                                    {{ trim($signataire->nom) }}
                                                                    {{ trim($signataire->prenoms) }}</u></b>
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div
                                    style="display:block; clear:both; width:100%; background: #fff; border: 0; border-top: 10px solid #9cc2e5; border-bottom:  10px solid #1571c6;  margin: 5px 0 10px 0; color:#000000; font-size:10pt; text-align:center; padding:5px 0 5px 0">
                                    ERSUMA, Ouando, Carrefour Cinquantenaire - Route de Pobè, 02 BP 353 Porto-Novo,
                                    BENIN 
                                    <br>Tél. : +229 01 97 97
                                    05 37 - E-mail : ersuma@ohada.org -
                                    Web : <font color="#0563c1"><u><a
                                                href="https://ersuma.ohada.org/">https://ersuma.ohada.org</a></u></font>
                                </div>

                            </footer>

                        </td>
                    </tr>
                </tbody>
            </table>
            <!-- saut de page -->
            @if ($loop->last)
            @else
                <div style="page-break-after: always;"></div>
            @endif
        @else
            @php
                if ($duree_enregistrement > 0) {
                    # code...
                    $duree_reel = $duree_enregistrement;
                } else {
                    # code...
                    $duree_reel = $duree_formation;
                }

                $scoring = intval(($duree_reel * 2) / 3);
            @endphp 

            {{--{{ $participant->temps_presence }} = {{ $scoring }}--}}

            @if ($participant->temps_presence >= $scoring)
                <table style="width: 100%;  padding: 0; margin: 0 ;">
                    <tbody>
                        <tr>
                            <td style="vertical-align:top;">
                                {{--<header>
                                    <div
                                        style="display:block; clear:both; width:100%; background: #fff; border: 0; border-top: 10px solid #1571c6; border-bottom:  10px solid #9cc2e5;  margin: 0 0 10px 0; color:#000000; padding:0; height:10px; text-align:center;">

                                    </div>

                                    <div style="z-index:100;">
                                        <div style="float:left; width:82%;">

                                            <div
                                                style="z-index:100; border-bottom:5px double #2681ca; max-width:75.7%; padding: 0 0 10px 0;">

                                                <div
                                                    style="z-index:100;float:left; width: 105px; margin:0px; height:86px; ">
                                                    <img src="img/logo/logo-ohada.png" alt="" height="85px"
                                                        style="height: 85px;">
                                                </div>
                                                <div
                                                    style=" line-height:1.17; margin: -15px 0 0 100px; padding:0px; top:0; font-size:12pt; text-align:justify;">
                                                    Organisation &nbsp;&nbsp;pour &nbsp;l’Harmonisation &nbsp;en
                                                    &nbsp;Afrique &nbsp;du &nbsp;Droit &nbsp;des &nbsp;&nbsp;Affaires
                                                    <br>
                                                    Organization &nbsp;&nbsp; for&nbsp; &nbsp;the&nbsp;
                                                    &nbsp;Harmonization &nbsp;&nbsp; of &nbsp;&nbsp;Business
                                                    &nbsp;&nbsp; Law &nbsp;in &nbsp;&nbsp; Africa <br>
                                                    Organización para la Armonización en África del Derecho de los
                                                    Negocios <br>
                                                    Organização &nbsp;para &nbsp;a &nbsp;Harmonização &nbsp;em
                                                    &nbsp;África &nbsp;do &nbsp;Direito dos Negócios
                                                </div>
                                            </div>
                                        </div>
                                        <img src="img/logo/attestation_flyers2.png" alt="logo" height="90px"
                                            style="height: 90px;">
                                    </div>

                                </header>--}}

                                @if ($logo_partenaires->count() == 0)
                                <!-- Entête standard -->
                                <header>
                                        <div
                                            style="display:block; clear:both; width:100%; background: #fff; border: 0; border-top: 10px solid #1571c6; border-bottom:  10px solid #9cc2e5;  margin: 0 0 10px 0; color:#000000; padding:0; height:10px; text-align:center;">

                                        </div>

                                        <div style="z-index:100;">
                                            <div style="float:left; width:82%;">

                                                <div
                                                    style="z-index:100; border-bottom:5px double #2681ca; max-width:75.7%; padding: 0 0 10px 0; ">

                                                    <div style="z-index:100;float:left; width: 105px; margin:0px; height:92px; ">
                                                        <img src="img/logo/logo-ohada.png" alt="" height="85px"
                                                            style="height: 85px;">
                                                    </div>
                                                    <div
                                                        style=" line-height:1.17; margin: -15px 0 0 100px; padding:0px; top:0; font-size:12pt; text-align:justify;">
                                                        Organisation &nbsp;&nbsp;pour &nbsp;l’Harmonisation &nbsp;en
                                                        &nbsp;Afrique &nbsp;du &nbsp;Droit &nbsp;des &nbsp;&nbsp;Affaires <br>
                                                        Organization &nbsp;&nbsp; for&nbsp; &nbsp;the&nbsp; &nbsp;Harmonization
                                                        &nbsp;&nbsp; of &nbsp;&nbsp;Business
                                                        &nbsp;&nbsp; Law &nbsp;in &nbsp;&nbsp; Africa <br>
                                                        Organización para la Armonización en África del Derecho de los Negocios
                                                        <br>
                                                        Organização &nbsp;para &nbsp;a &nbsp;Harmonização &nbsp;em &nbsp;África
                                                        &nbsp;do &nbsp;Direito dos Negócios
                                                    </div>
                                                </div>
                                            </div>
                                            <img src="img/logo/attestation_flyers2.png" alt="logo" height="90px"
                                                style="height: 90px;">
                                        </div>
                                        </div>
                                    </header>
                                <!-- End Entête standard -->
                            @else
                                <!-- Entête dynamique -->
                                    <header> 
                                        <table style="width: 100%; padding: 0; margin: 0 ; text-align:center; border-bottom:4px solid #1a0642;">
                                            <tr style="border-bottom:4px solid #1a0642;">
                                                @if ($formation->is_coorganisation)
                                                    <!-- logo OHADA par defaut -->
                                                    <td style="width:40%;">
                                                        <img src="img/inc-attestations/logo_ohada-fr.jpg" alt="logo" height="95px" style="height: 95px;">
                                                    </td>
                                                @else
                                                    <!-- logo ERSUMA par defaut -->
                                                    <td style="width:15%;">
                                                        <img src="img/inc-attestations/logo_ersuma.jpg" alt="logo" height="80px" style="height: 80px;">
                                                    </td>
                                                @endif
                                                @foreach ($logo_partenaires as $logo)
                                                    @if ($logo_partenaires->count() == 1)
                                                    <td style="text-align:right;">
                                                        <img src="storage/logo_partenaire/{{$logo->logo_path}}" alt="logo" height="80px" style="height: 80px;">
                                                    </td>
                                                            
                                                    @else
                                                    <td style="">
                                                        <img src="storage/logo_partenaire/{{$logo->logo_path}}" alt="logo" height="80px" style="height: 80px;">
                                                    </td>
                                                        
                                                    @endif
                                                @endforeach
                                            </tr>
                                        </table>
                                    </header>

                                <!-- End Entête dynamique -->
                                
                            @endif 

                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- <div style="z-index: 10; background:none;"> -->
                <div style="z-index: 10; background:none; margin-top:85px; margin-bottom:0;">
                    @php
                        $nbre_jour = 0;
                        $get_nbre_jour = round(
                            (strtotime($participant->date_fin) - strtotime($participant->date_debut)) / 86400,
                        );
                        $nbre_jour = $get_nbre_jour + 1;
                    @endphp

                    <div>
                        <!-- div opacity -->
                        <div style="z-index: 300; opacity:0; margin:0;font-size:12pt;">
                             @if ($formation->titre_evenement)
                                <div style="z-index: 3; display:block; text-align:center; font-size: 20px; font-weight:bold; color:#1a0642;">{{$formation->titre_evenement}}</div>
                            @endif
                            <div
                            style="z-index: 100;clear:both; display:block; margin: 10px 0 0px 0;text-align: center;line-height: 1.2;font-size: 36px; color:#006fc0; font-weight:bold;">
                            <div style="margin: 08px 0;">
                                    @if ($typeformation_id == 1)
                                        ATTESTATION DE FORMATION
                                    @else
                                        ATTESTATION DE PARTICIPATION
                                    @endif

                                </div>
                            </div>
                            <div
                                style="clear:both; display:block; width:100%; margin-top:2px; text-align:center; color : #000000; font-size: 20pt; font-weight:bold;">
                                <div style="margin: 5px 0 7px 0;">
                                    LE DIRECTEUR GENERAL
                                </div>
                            </div>
                            <div style="clear:both; display:block; text-align:justify;">
                                <div style="line-height: 1.4; ">
                                    <span style="font-size:11.6pt;">
                                        <b>Vu</b> le Traité relatif à l’Harmonisation du droit des Affaires en Afrique
                                        signé
                                        à Port-Louis le 17 octobre
                                        1993, tel que révisé à Québec le 17 octobre 2008 notamment en son article 41 ;
                                        <br>
                                        <b>Vu</b> le Statut de l’ERSUMA du 03 octobre 1995 révisé par les Règlements n°
                                        004/2009/CM/OHADA du 19 décembre
                                        2009, n° 003/2011/CM du 17 juin 2011 et n° 02/2016/CM/OHADA du 28 octobre 2016 portant Statut
                                        révisé
                                        de l’ERSUMA ;
                                    </span><br>
                                    <div style="clear:both; display:block;text-align:center;font-size: 14pt;">
                                        Atteste que :
                                    </div>
                                    <div
                                        style="line-height: 1.5;font-size: 13pt; display:block; text-align:center; margin-bottom:10px;">
                                        <span
                                            style="font-weight:bold; font-size:20pt;">{{ trim($participant->prenoms) }}
                                            {{ trim($participant->nom) }}</span> <br>a pris
                                        part à la {{ $typeformation_id == 1 ? 'session de formation' : 'conférence' }},
                                        sur le thème<br>
                                        <span style="font-weight:bold; font-size:16pt;">
                                            <i>{{ $participant->theme }}</i></span>,<br>
                                        qui s’est tenue par visioconférence
                                        {{-- --}}
                                        @if ($participant->date_debut == $participant->date_fin)
                                            le
                                            {{ \Carbon\Carbon::parse($participant->date_debut)->isoFormat('DD MMM YYYY') }}
                                        @else
                                            du
                                            {{ \Carbon\Carbon::parse($participant->date_debut)->isoFormat('DD MMM') }}
                                            au
                                            {{ \Carbon\Carbon::parse($participant->date_fin)->isoFormat('DD MMM YYYY') }}
                                        @endif
                                        @php
                                            $masse_horaire = 8 * $nbre_jour;
                                        @endphp
                                        correspondant à {{ $nbre_jour == 1 ? '4 heures' : "$masse_horaire heures" }}
                                        de formation.
                                    </div>
                                    <p>
                                        La présente attestation lui est délivrée pour en jouir avec les droits et
                                        prérogatives qui y sont attachés.
                                    </p>

                                </div>
                            </div>

                        </div>
                        <!-- end div opacity -->

                    </div><!-- fermeture div background-image -->

                </div>

                <table style="width: 100%;  padding: 0; margin: 0 ;">
                    <tbody>
                        <tr>
                            <td style="vertical-align: bottom;">
                                <footer>

                                    <div style="clear:both; display:block; margin:10px 0 0 0; width: 100%; ">
                                        <div style="float:left;">
                                            &nbsp;
                                        </div>
                                        <div style=" float:right; padding-right:5px;">
                                            <b>Délivrée à Porto-Novo, le
                                                {{ \Carbon\Carbon::parse(date('d-m-Y'))->isoFormat('DD MMM YYYY') }}</b>
                                        </div>
                                    </div>
                                    <div style="clear:both; ">
                                        <table style="width: 100%;  padding: 0; margin: 0 ;">
                                            <tbody>
                                                <tr>
                                                    <td style="width:40%;vertical-align: bottom;">
                                                        <div style="margin-bottom: 1px; margin-left: 10px;0;">
                                                            <img width="95" height="95"
                                                                src="data:image/png;base64, {!! base64_encode(
                                                                    QrCode::encoding('UTF-8')->format('svg')->size(100)->generate(
                                                                            'Code Formation : ' .
                                                                                $participant->code_formation .
                                                                                ' / Thème : ' .
                                                                                $participant->theme .
                                                                                ' / Auditeur : ' .
                                                                                trim($participant->prenoms) .
                                                                                ' ' .
                                                                                trim($participant->nom),
                                                                        ),
                                                                ) !!}" />
                                                        </div>
                                                    </td>

                                                    <td style="text-align:center;vertical-align: bottom;">
                                                        &nbsp;
                                                    </td>

                                                    <td style="width:30%; vertical-align: bottom; padding:0; ">
                                                        @php
                                                            $signataire = getSignataire($participant->code_formation);
                                                        @endphp
                                                        @if ($signataire == false)
                                                            <div
                                                                style=" text-align:left; margin:0; padding:0; z-index: 10;">
                                                                <img src="img/signatdg.png" alt="Signature"
                                                                    style="margin: -15px; padding:0; height: 145px; text-align:left; ">
                                                            </div>
                                                            <div
                                                                style="padding:0; margin:-20px 0 0; text-align:center; z-index: 100;">
                                                                <b><u>Pr. Mayatta Ndiaye MBAYE</u></b>
                                                            </div>
                                                        @else
                                                            <div
                                                                style=" text-align:left; margin:0; padding:0; z-index: 10;">
                                                                <img src="storage/signature/{{ $signataire->signature }}"
                                                                    alt="Signature"
                                                                    style="margin: -15px; padding:0; height: 130px; text-align:left; ">
                                                            </div>
                                                            <div
                                                                style="padding:0 0 0 50px; margin:-20px 0 0; text-align:center; z-index: 100;">
                                                                <b><u> {{ $signataire->titre }}
                                                                        {{ trim($signataire->nom) }}
                                                                        {{ trim($signataire->prenoms) }}</u></b>
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div
                                        style="display:block; clear:both; width:100%; background: #fff; border: 0; border-top: 10px solid #9cc2e5; border-bottom:  10px solid #1571c6;  margin: 5px 0 10px 0; color:#000000; font-size:10pt; text-align:center; padding:5px 0 5px 0">
                                        ERSUMA, Ouando, Carrefour Cinquantenaire - Route de Pobè, 02 BP 353 Porto-Novo,
                                        BENIN <br> Tél. : +229 01 97 97 05 37 
                                        - E-mail : ersuma@ohada.org 
                                        - Web : <font color="#0563c1"><u><a href="https://ersuma.ohada.org/">https://ersuma.ohada.org</a></u>
                                        </font>
                                    </div>

                                </footer>

                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- saut de page -->
                @if ($loop->last)
                @else
                    <div style="page-break-after: always;"></div>
                @endif
            @endif
  
        @endif
    @endforeach
</body>

</html>
