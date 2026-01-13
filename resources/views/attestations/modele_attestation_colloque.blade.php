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
            opacity: 0.1;
            z-index: -1;
        }
    
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 3.5cm;
            border-bottom: 6px solid #0d043d;
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
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 5.2cm;
            margin: 0;
        }
    
        .attestation-content {
            margin-top: 115px;
            position: relative;
            z-index: 1;
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

    @php
        $duree_formation = 0;
        $i = 0;
        $theme = '';
        $duree_enregistrement = getDureeEnregistrement($code_formation);
    @endphp
    @foreach ($duree_session as $duree)
        @php
            $duree_formation = $duree_formation + $duree->duree_session;
           // $theme = $duree->getFormation->theme;
          //  $type_formation = $duree->getFormation->typeformation_id;
        @endphp 
    @endforeach

    @foreach ($liste_participants as $participant)

    @if ($participant->temps_presence == 0 && $participant->presentiel == 1)
        
    <table style="width: 100%;  padding: 0; margin: 0 ;">
        <tbody>
            <tr>
                <td style="vertical-align:top;">
                    <header>
                        <table style="width: 100%; padding: 0; margin: 0 ; text-align:center;">
                            <tr style=" border-bottom:4px solid #1a0642;">
                                <td style="width:20%;">
                                    <img src="img/inc-attestations/logo_ccja.jpg" alt="logo" height="95px" style="height: 95px;">
                                </td>
                                <td style="">
                                    <img src="img/inc-attestations/logo_apaa.jpg" alt="logo" height="100px" style="height: 100px;">
                                </td>
                                <td style="">
                                    <img src="img/inc-attestations/logo_nicarb.jpg" alt="logo" height="100px" style="height: 100px;">
                                </td>
                                <td style="width:20%;">
                                    <img src="img/inc-attestations/logo_ersuma.jpg" alt="logo" height="95px" style="height: 95px;">
                                </td>
                            </tr>
                        </table>
                    </header>

                </td>
            </tr>
        </tbody>
    </table>
        <div style="z-index: 10; background:none; margin-top:115px; margin-bottom:0;">
            
            @php
                $nbre_jour = 0;
                $get_nbre_jour = round((strtotime($participant->date_fin) - strtotime($participant->date_debut)) / 86400);
                $nbre_jour = $get_nbre_jour+1;
            @endphp 

            <div >
                <!-- div opacity -->
                <div style="z-index: 300; opacity:0; margin:0;font-size:12pt;">
                    
                    <div style="z-index: 3; display:block; text-align:center; font-size: 27pt; font-weight:bold; font-family:'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif; color:#1a0642;">Journées africaines de l'arbitrage et de la médiation</div>
                    <div
                        style="z-index: 100;clear:both; display:block; margin: 5px 0 0px 0;text-align: center;line-height: 1.2;font-size: 30pt; color:#006fc0; font-weight:bold;">
                        <div style="margin: 10px 0 10px 0;">  
                            {{$nbre_jour >= 5 ? "CERTIFICAT DE FORMATION" : "ATTESTATION DE PARTICIPATION"}}    
                        </div>
                    </div>
                    {{--
                    <div
                        style="clear:both; display:block; width:100%; margin-top:5px; text-align:center; color : #000000; font-size: 20pt; font-weight:bold;">
                        <div style="margin: 5px 0 7px 0;">
                            LE DIRECTEUR GENERAL
                        </div>
                    </div>
                    --}}
                    <div style="clear:both; display:block; text-align:justify;">
                        <div style="line-height: 1.3; ">
                            <span style="font-size:11.8pt;">
                                <b>Vu</b> le Traité relatif à l’Harmonisation du droit des Affaires en Afrique signé
                                à Port-Louis le 17 octobre
                                1993, tel que révisé à Québec le 17 octobre 2008 notamment en son article 41 ; <br>
                                <b>Vu</b> le Statut de l’ERSUMA du 03 octobre 1995 révisé par les Règlements n°
                                004/2009/CM/OHADA du 19 décembre
                                2009, n° 003/2011/CM du 17 juin 2011 et n° 02/2016/CM/OHADA du 28 octobre 2016 portant Statut révisé
                                de l’ERSUMA ;
                            </span><br>
                            <div style="clear:both; margin-top:8px; display:block;text-align:center;font-size: 14pt;">
                                délivrée à
                            </div>
                            <div style="line-height: 1.3;font-size: 13pt; display:block; text-align:center; margin-bottom:10px;">
                                <span style="font-weight:bold; font-size:24pt;">{{ trim($participant->prenoms) }}
                                    {{ trim($participant->nom) }}</span> <br>
                                pour sa participation à la 
                                <span style="font-weight:bold; font-size:16pt;">
                                     <i>{{ $participant->theme }}</i> </span> <br>
                                     qui s’est tenue @if($participant->lieu != "") à <b>{{$participant->lieu}}</b> @endif
                                @if ($participant->date_debut == $participant->date_fin)
                                le
                                {{ \Carbon\Carbon::parse($participant->date_debut)->isoFormat('DD MMM YYYY') }}
                                @else
                                du
                                {{ \Carbon\Carbon::parse($participant->date_debut)->isoFormat('DD MMM') }}
                                au
                                {{ \Carbon\Carbon::parse($participant->date_fin)->isoFormat('DD MMM YYYY') }}
                                @endif, correspondant à 20 heures de participation.
                            </div>
                            <p>
                                {{$nbre_jour >= 5 ? "Le présent certificat lui est délivré" : "La présente attestation lui est délivrée"}} pour en jouir avec les droits et
                                prérogatives qui y sont attachés.
                            </p>
                        </div>
                    </div>

                </div>
                <!-- end div opacity -->

            </div><!-- fermeture div background-image -->


        </div>

    <table style="width: 100%;  padding: 0; margin:-20px 0 O 0;">
        <tbody>
            <tr>
                <td style="vertical-align: bottom;">
                    <footer>                    
                        <div style="clear:both;">
                            <table style="width: 100%;  padding: 0; margin: -15px 0 0 0 ;">
                                <tbody>
                                    <tr>
                                        <td colspan="4" style="text-align: right;padding-bottom:10px;">
                                            <b>Délivrée à {{$participant->lieu}}, le {{ \Carbon\Carbon::parse(date('d-m-Y'))->isoFormat('DD MMM YYYY') }}</b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:15%;vertical-align: bottom;">
                                            <div style="margin-bottom: 1px; margin-left: 10px;0;">
                                                <img width="95" height="95"
                                                    src="data:image/png;base64, {!! base64_encode(
        QrCode::encoding('UTF-8')->format('svg')->size(100)->generate('Code Formation : ' . $participant->code_formation . ' / Thème : ' . $participant->theme . ' / Auditeur : ' . trim($participant->prenoms) . ' ' . trim($participant->nom)),
        ) !!}" />
                                                    </div>
                                        </td>

                                        <td style="width:20%;text-align:center;vertical-align: bottom;">
                                            <div
                                                style=" text-align:left; margin:0; padding:0; z-index: 10;">
                                                <img src="img/inc-attestations/signat_pdte-ccja.jpg" alt="Signature"
                                                    style="margin: -15px; padding:0; height: 125px; text-align:left; ">
                                            </div>
                                            <div
                                                style="padding:0; margin:-20px 0 0; text-align:center; z-index: 100;">
                                                <b><u>Esther NGO MOUTNGUI</u></b><br>
                                                Présidente de la CCJA
                                            </div>
                                        </td>

                                        <td style="width:30%;text-align:center;vertical-align: bottom;">
                                            <div
                                                style=" text-align:center; margin:0; padding:0; z-index: 10;">
                                                <img src="img/inc-attestations/signat_pdt-apaa_tr.png" alt="Signature"
                                                    style="margin: -10px; padding:0; height: 120px; text-align:left; ">
                                            </div>
                                            <div
                                                style="padding:0; margin:-20px 0 0; text-align:center; z-index: 100;">
                                                <b><u>Dr Gaston KENFACK DOUAJNI</u></b><br>
                                                Président de l’APAA
                                                
                                            </div>
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
                                                <img src="storage/signature/{{$signataire->signature}}" alt="Signature"
                                                    style="margin: -15px; padding:0; height: 145px; text-align:left; ">
                                            </div>
                                            <div
                                                style="padding:0; margin:-20px 0 0; text-align:center; z-index: 100;">
                                                <b><u> {{$signataire->titre}} {{$signataire->nom}} {{$signataire->prenoms}}</u></b>
                                            </div>                                                    
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div
                                    style="display:block; clear:both; width:100%; background: #fff; border: 0; border-top: 12px solid #9cc2e5; border-bottom:  12px solid #1571c6;  margin: 5px 0 10px 0; color:#000000; font-size:10pt; text-align:center; padding:5px 0 5px 0">
                                    Sous le Haut Parrainage de SE Monsieur Jean SANSAN KAMBILÉ, Garde des Sceaux, Ministre de la Justice et des Droits de l’Homme de la République de Côte d’Ivoire
                                    {{--
                                        ERSUMA, Ouando, Carrefour Cinquantenaire - Route de Pobè, 02 BP 353 Porto-Novo, BENIN - Tél. : +229 01 97 97
                                    05 37 - E-mail : ersuma@ohada.org -
                                    Web : <font color="#0563c1"><u><a
                                                href="https://ersuma.ohada.org/">https://ersuma.ohada.org</a></u></font>
                                        --}}
                                </div>

                    </footer>

                </td>
            </tr>
        </tbody>
    </table>
<!-- saut de page -->
@if($loop->last)

@else 
<div style="page-break-after: always;" ></div>
@endif

    @else
        

        @php
            if ($duree_enregistrement > 0) {
              # code...
              $duree_reel = $duree_enregistrement ;
            } else {
              # code...
              $duree_reel = $duree_formation ;
            }
            $scoring = intval(($duree_reel * 2) / 3);
        @endphp
        @if ($participant->temps_presence >= $scoring)

        <table style="width: 100%;  padding: 0; margin: 0 ;">
            <tbody>
                <tr>
                    <td style="vertical-align:top;">
                        <header>
                            <table style="width: 100%; min-height:;90px;  padding: 0; margin: 0 ; text-align:center;">
                                <tr style=" border-bottom:4px solid #1a0642;">
                                    <td style="width:20%;">
                                        <img src="img/inc-attestations/logo_ccja.jpg" alt="logo" height="95px" style="height: 95px;">
                                    </td>
                                    <td style="">
                                        <img src="img/inc-attestations/logo_apaa.jpg" alt="logo" height="100px" style="height: 100px;">
                                    </td>
                                    <td style="">
                                        <img src="img/inc-attestations/logo_nicarb.jpg" alt="logo" height="100px" style="height: 100px;">
                                    </td>
                                    <td style="width:20%;">
                                        <img src="img/inc-attestations/logo_ersuma.jpg" alt="logo" height="95px" style="height: 95px;">
                                    </td>
                                </tr>
                                {{--
                                
                                <tr>
                                    <td colspan="4" style="font-size: 36px; color:#0d043d;">Journées africaines de l'arbitrage et de la médiation</td>
                                </tr>
                            --}}
                            </table>
                            {{--
                            <div
                                    style="display:block; clear:both; width:100%; background: #fff; border: 0; border-top: 10px solid #1571c6; border-bottom:  10px solid #9cc2e5;  margin: 0 0 10px 0; color:#000000; padding:0; height:10px; text-align:center;">
    
                                </div>
    
                                <div style="z-index:100;">
                                    <div style="float:left; width:75%;">
    
                                        <div style="z-index:100; border-bottom:5px double #2681ca; max-width:72.7%; padding: 0 0 10px 0; ">
    
                                            <div style="z-index:100;float:left; width: 110px; margin:0px; height:92px; ">
                                                <img src="img/logo/logo-ohada.png" alt="" height="85px" style="height: 85px;">
                                            </div>
                                            <div
                                                style=" line-height:1.17; margin: -25px 0 0 100px; padding:0px; top:0; font-size:12pt; text-align:justify;">
                                                Organisation pour &nbsp;l’Harmonisation en Afrique du &nbsp;Droit des &nbsp;Affaires <br>
                                                Organization &nbsp;&nbsp; for&nbsp; the&nbsp; Harmonization &nbsp; of Business &nbsp;
                                                Law in &nbsp; Africa <br>
                                                Organización para la Armonización en África del Derecho &nbsp;&nbsp;Mercantil <br>
                                                Organização para a Harmonização em África do Direito dos Negócios
                                            </div>
                                        </div>
                                    </div>
                                    <img src="img/logo/attestation_flyers2.png" alt="logo" height="90px" style="height: 90px;">
                                    </div>
                                </div>
                            --}}
                        </header>
    
                    </td>
                </tr>
            </tbody>
        </table>
            <div style="z-index: 10; background:none; margin-top:115px; margin-bottom:0;">
            
                @php
                    $nbre_jour = 0;
                    $get_nbre_jour = round((strtotime($participant->date_fin) - strtotime($participant->date_debut)) / 86400);
                    $nbre_jour = $get_nbre_jour+1;
                @endphp 

                <div >
                    <!-- div opacity -->
                    <div style="z-index: 300; opacity:0; margin:0;font-size:12pt;">
                    
                        <div style="z-index: 3; display:block; text-align:center; font-size: 27pt; font-weight:bold; font-family:'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif; color:#1a0642;">Journées africaines de l'arbitrage et de la médiation</div>

                        <div
                            style="z-index: 100;clear:both; display:block; margin: 10px 0 0px 0;text-align: center;line-height: 1.2;font-size: 30pt; color:#006fc0; font-weight:bold;">
                            <div style="margin: 10px 0 10px 0;">
                            {{$nbre_jour >= 5 ? "CERTIFICAT DE FORMATION" : "ATTESTATION DE PARTICIPATION"}}    
                            </div>
                        </div>
                        {{--
                            <div
                            style="clear:both; display:block; width:100%; margin-top:5px; text-align:center; color : #000000; font-size: 20pt; font-weight:bold;">
                            <div style="margin: 5px 0 7px 0;">
                                LE DIRECTEUR GENERAL
                            </div>
                        </div>
                        --}}
                        <div style="clear:both; display:block; text-align:justify;">
                            <div style="line-height: 1.3; ">
                                <span style="font-size:11.8pt;">
                                    <b>Vu</b> le Traité relatif à l’Harmonisation du droit des Affaires en Afrique signé
                                    à Port-Louis le 17 octobre
                                    1993, tel que révisé à Québec le 17 octobre 2008 notamment en son article 41 ; <br>
                                    <b>Vu</b> le Statut de l’ERSUMA du 03 octobre 1995 révisé par les Règlements n°
                                    004/2009/CM/OHADA du 19 décembre
                                    2009, n° 003/2011/CM du 17 juin 2011 et n° 02/2016/CM/OHADA du 28 octobre 2016 portant Statut révisé
                                    de l’ERSUMA ;
                                </span><br>
                                <div style="clear:both; margin-top:8px; display:block;text-align:center;font-size: 14pt;">
                                    délivrée à
                                </div>
                                <div style="line-height: 1.5;font-size: 13pt; display:block; text-align:center; margin-bottom:10px;">
                                    <span style="font-weight:bold; font-size:20pt;">{{ trim($participant->prenoms) }}
                                        {{ trim($participant->nom) }}</span> <br>
                                        pour sa participation à la <span style="font-weight:bold; font-size:16pt;">
                                        <i>{{ $participant->theme }}</i> </span> <br>
                                    qui s’est tenue en visioconférence 
                                    {{-- --}}
                                    @if ($participant->date_debut == $participant->date_fin)
                                    le
                                    {{ \Carbon\Carbon::parse($participant->date_debut)->isoFormat('DD MMM YYYY') }}
                                    @else
                                    du
                                    {{ \Carbon\Carbon::parse($participant->date_debut)->isoFormat('DD MMM') }}
                                    au
                                    {{ \Carbon\Carbon::parse($participant->date_fin)->isoFormat('DD MMM YYYY') }}
                                    @endif, correspondant à 20 heures de participation.
                                    
                                    
                                </div>
                                <p>
                                    {{$nbre_jour >= 5 ? "Le présent certificat lui est délivré" : "La présente attestation lui est délivrée"}} pour en jouir avec les droits et
                                    prérogatives qui y sont attachés.
                                </p>
                            </div>
                        </div>

                    </div>
                    <!-- end div opacity -->

                </div><!-- fermeture div background-image -->


            </div>
            <table style="width: 100%;  padding: 0; margin:-20px 0 O 0;">
                <tbody>
                    <tr>
                        <td style="vertical-align: bottom;">
                            <footer>                    
                                <div style="clear:both;">
                                    <table style="width: 100%;  padding: 0; margin: -15px 0 0 0 ;">
                                        <tbody>
                                            <tr>
                                                <td colspan="4" style="text-align: right;padding-bottom:10px;">
                                                    <b>Délivrée à Abidjan, le {{ \Carbon\Carbon::parse(date('d-m-Y'))->isoFormat('DD MMM YYYY') }}</b>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width:15%;vertical-align: bottom;">
                                                    <div style="margin-bottom: 1px; margin-left: 10px;0;">
                                                        <img width="95" height="95"
                                                            src="data:image/png;base64, {!! base64_encode(
                QrCode::encoding('UTF-8')->format('svg')->size(100)->generate('Code Formation : ' . $participant->code_formation . ' / Thème : ' . $participant->theme . ' / Auditeur : ' . trim($participant->prenoms) . ' ' . trim($participant->nom)),
                ) !!}" />
                                                            </div>
                                                </td>
        
                                                <td style="width:20%;text-align:center;vertical-align: bottom;">
                                                    <div
                                                        style=" text-align:left; margin:0; padding:0; z-index: 10;">
                                                        <img src="img/inc-attestations/signat_pdte-ccja.jpg" alt="Signature"
                                                            style="margin: -15px; padding:0; height: 125px; text-align:left; ">
                                                    </div>
                                                    <div
                                                        style="padding:0; margin:-20px 0 0; text-align:center; z-index: 100;">
                                                        <b><u>Esther NGO MOUTNGUI</u></b><br>
                                                        Présidente de la CCJA
                                                    </div>
                                                </td>
        
                                                <td style="width:30%;text-align:center;vertical-align: bottom;">
                                                    <div
                                                        style=" text-align:center; margin:0; padding:0; z-index: 10;">
                                                        <img src="img/inc-attestations/signat_pdt-apaa_tr.png" alt="Signature"
                                                            style="margin: -10px; padding:0; height: 120px; text-align:left;">
                                                    </div>
                                                    <div
                                                        style="padding:0; margin:-20px 0 0; text-align:center; z-index: 100;">
                                                        <b><u>Dr Gaston KENFACK DOUAJNI</u></b><br>
                                                        Président de l’APAA
                                                        
                                                    </div>
                                                </td>
        
        
                                                <td style="width:35%; vertical-align: bottom; padding:0; ">
                                                    <div
                                                        style=" text-align:left; margin:0; padding:0; z-index: 10;">
                                                        <img src="img/signatdg.png" alt="Signature"
                                                            style="margin: -15px; padding:0; height: 145px; text-align:left; ">
                                                    </div>
                                                    <div
                                                        style="padding:0; margin:-20px 0 0; text-align:center; z-index: 100;">
                                                        <b><u>Pr. Mayatta Ndiaye MBAYE</u></b><br>
                                                        Directeur Général de l’ERSUMA
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div
                                            style="display:block; clear:both; width:100%; background: #fff; border: 0; border-top: 12px solid #9cc2e5; border-bottom:  12px solid #1571c6;  margin: 5px 0 10px 0; color:#000000; font-size:10pt; text-align:center; padding:5px 0 5px 0">
                                            Sous le Haut Parrainage de SE Monsieur Jean SANSAN KAMBILÉ, Garde des Sceaux, Ministre de la Justice et des Droits de l’Homme de la République de Côte d’Ivoire
                                            {{--
                                                ERSUMA, Ouando, Carrefour Cinquantenaire - Route de Pobè, 02 BP 353 Porto-Novo, BENIN - Tél. : +229 01 97 97
                                            05 37 - E-mail : ersuma@ohada.org -
                                            Web : <font color="#0563c1"><u><a
                                                        href="https://ersuma.ohada.org/">https://ersuma.ohada.org</a></u></font>
                                                --}}
                                        </div>
        
                            </footer>
        
                        </td>
                    </tr>
                </tbody>
            </table>
 <!-- saut de page -->
 @if($loop->last)

 @else 
 <div style="page-break-after: always;" ></div>
 @endif

        @endif
       

        @endif
    @endforeach
</body>

</html>
