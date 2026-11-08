<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>

<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <title>Attestation de formation - ERSUMA</title>
    <style>
        :root {
            --primary-color: #1571c6;
            --secondary-color: #9cc2e5;
            --text-color: #000000;
            --font-size: 12pt;
        }

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
    background-size: 500px;
    opacity: 0.1;
    z-index: -1;
}
        @page {
            size: portrait;
            margin: 0.73cm 1.27cm 0.97cm;
        }

        header {
            width: 100%;
            background: white;
            z-index: 10;
            margin-bottom: 10px;
        }

        .flags-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 5px;
            padding: 10px 0;
            border: 2px solid #9cc2e5;
            background-color: #d8e1eb;
            width: 100%;
            box-sizing: border-box;
            margin: 0 auto;
            z-index: 20;
        }

        .flag {
            width: auto;
            height: 26px;
        }

        .content {
            margin-top: 20px;
            margin-bottom: 5cm;
        }

        footer {
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 5cm;
            z-index: 10;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
 
    @foreach ($participants as $participant)
        @php
            $candidat = getCandidat($participant->email);
            $checkAjourne = checkAjourne($participant->code_formation,$participant->email);
        @endphp

        @if (!$checkAjourne && $participant->moyenne > 12)

        <table style="width: 100%;  padding: 0; margin: 0 ;">
            <tbody>
                <tr>
                    <td style="vertical-align:top;">
                        <header>
                            {{--   <div
                                style="display:block; clear:both; width:100%; background: #fff; border: 0; border-top: 10px solid #1571c6; border-bottom:  10px solid #9cc2e5;  margin: 0 0 10px 0; color:#000000; padding:0; height:10px; text-align:center;">

                            </div> --}}
                            <div style="z-index:100;">
                                <div style="float:left; width:72%;">

                                    <div style="z-index:100; max-width:92%; padding: 10px 0;">

                                        <div
                                            style="z-index:100;float:left; width: 105px; margin:-10px 0 0 0; height:92px; ">
                                            <img src="img/logo/logo-ohada.png" alt="" height="85px"
                                                style="height: 85px; ">
                                        </div>
                                        <div
                                            style=" line-height:1.3; margin: -5px 0 0 95px; padding:0px; top:0; font-size:11px; text-align:justify; border-bottom:5px double #2681ca; padding-bottom:8px;">
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
                                <img src="img/logo/Logo_ERSUMA-1.jpg" alt="logo"
                                    style="height: 70px; margin-top:8px;">
                            </div>
                            </div>

                        </header>

                    </td>
                </tr>
            </tbody>
        </table> 
        <div style="z-index: 10; background:none;">

            <div>
                <!-- div opacity -->
                <div style="z-index: 300; opacity:1; margin:0;font-size:12pt;">
                    <div
                        style="z-index: 100;clear:both; display:block; margin: 0; text-align: center;line-height: 1.2;font-size: 28px; color:#233468; font-weight:bold; border-bottom:  8px solid #233468; padding:0px;">

                        <div style="margin: 8px 0; padding:3px 6px;">
                            Diplômes de Spécialité et Certificats OHADA
                        </div>
                    </div>

                    <div
                        style="z-index: 100;clear:both; display:block; margin:0; text-align: center;line-height: 1.2;font-size: 30pt; color:#006fc0; font-weight:bold;">

                        <div style="margin: 10px 0;">
                            ATTESTATION DE REUSSITE
                        </div>
                    </div>
                    <div
                        style="clear:both; display:block; width:100%; margin-top:5px; text-align:center; color : #000000; font-size: 20pt; font-weight:bold;">
                        <div style="margin: 5px 0 7px 0;">
                            LE DIRECTEUR GENERAL
                        </div>
                    </div>
                    <div style="clear:both; display:block; text-align:justify; padding:10px 15px;">
                        <div style="line-height: 1.4; ">
                            <span style="font-size:11pt;">
                                <b>Vu</b> le Traité relatif à l’Harmonisation du droit des Affaires en Afrique signé à
                                Port-Louis le 17 octobre 1993, tel que révisé à Québec le 17 octobre 2008 notamment en
                                son article 41 ; <br>
                                <b>Vu</b> le Statut de l’ERSUMA du 03 octobre 1995 révisé par les Règlements 
                                n° 004/2009/CM/OHADA du 19 décembre 2009, n° 003/2011/CM du 17 juin 2011 et n° 02/2016/CM/OHADA du 28
                                octobre 2016 portant Statut révisé de l’ERSUMA ; <br>
                                <b>Vu</b> la Décision n° 002/2019/CA/ERSUMA/OHADA du 24 août 2019 portant création et organisation du
                                Diplôme de Spécialité en Droit OHADA à l'ERSUMA ;<br>
                                <b>Vu</b> la Décision n° 017-2025 du 10 octobre 2025 portant délibération et
                                publication du
                                résultat final aux examens de fin d’année au titre de l’année académique 2024-2025 ;
                                <br>
                            </span><br> 
                            <div style="clear:both; display:block;text-align:center;font-size: 14pt;"> Atteste que :
                            </div>
                            <div
                                style="line-height: 1.3;font-size: 11pt; display:block; text-align:center; margin-bottom:10px;">
                                <span
                                    style="font-weight:bold; font-size:22pt; color:#233468;">{{ trim($participant->prenoms) }}
                                    {{ trim($participant->nom) }}</span> 
                                     <br>
                                     @if (!empty($participant->ddn) || !empty($participant->lieu_naissance))
                                         Né(e) le {{$participant->ddn}} à {{$participant->lieu_naissance}}
                                     @endif
                                     <br>a subi avec succès les épreuves écrites d'admissibilité et celle du grand oral (soutenance) conduisant à l'obtention du
                                     <br>
                                <span style="font-weight:bold; font-size:14pt;">
                                    <i>{{ $formation->theme }}</i></span>.<br>
                              <!--  au titre de l'année académique 2024-2025.-->
                               <p style="text-align: left;font-size: 11pt; line-height:1.3;">Suivant les procès verbaux, l'intérressé(e) a obtenu les résultats ci-après : 
                                <table style="width: 100%;  padding: 0; margin: 0 ; margin-top:-5px;">
                                    <tr>
                                        <td style="text-align:left; width:50%;font-size: 11pt;">
                                            Moyenne générale : <b>{{ $participant->moyenne }}</b>/20
                                        </td>
                                        <td style="text-align:right; width:50%;font-size: 11pt;">
                                            Mention : 
                                            @php
                                                $moyenne = $participant->moyenne;
                                                if ($moyenne < 12) {
                                                    $mention = 'N/A';
                                                } elseif ($moyenne >= 12 && $moyenne < 14) {
                                                    $mention =  'ASSEZ BIEN';
                                                } elseif ($moyenne >= 14 && $moyenne < 16) {
                                                    $mention =  'BIEN';
                                                } else {
                                                    $mention =  'TRÈS BIEN';
                                                }
                                            @endphp 
                                            <b>
                                                {{$mention}}
                                            </b>
                                        </td>
                                    </tr>
                                </table>
                            </p> 
                            </div>
                            <p style="font-size: 11pt;">
                                La présente attestation lui est délivrée pour en jouir avec les droits et
                                prérogatives qui y sont attachés.
                            </p>
                        </div>
                    </div>

                </div>
                <!-- end div opacity -->

            </div><!-- fermeture div background-image -->


        </div>

        <table style="width: 100%;  padding: 0; margin: 0 ; ">
            <tbody>
                <tr>
                    <td style="vertical-align: bottom;">
                        <footer>

                            <div style="clear:both; display:block; margin:10px 0 0 0; width: 100%; ">
                                <div style="float:left;">
                                    &nbsp;
                                </div>
                                <div style=" float:right; padding-right:5px;font-size: 11pt; margin-bottom: 15px;">
                                    Délivrée à Porto-Novo, le
                                        {{ \Carbon\Carbon::parse($participant->date_remise_diplomes)->isoFormat('DD MMMM YYYY') }}
                                </div>
                            </div>
                            <div style="clear:both;">
                                <table style="width: 100%;  padding: 0; margin: 0 ;">
                                    <tbody>
                                        <tr>
                                            <td style="width:100px;vertical-align: ;bottom;">
                                                <div style="margin-bottom: 1px; margin-left: 10px;0;">

                                                     @php
                                                        $annee_debut = (int) $participant->annee_academique;
                                                        $annee_fin  = $annee_debut + 1;

                                                        /* $QrCodeText = "Attestation de :\n"
                                                                . "Auditeur : {$participant->prenoms} {$participant->nom}\n"
                                                                . "Thème : {$formation->theme}\n"
                                                                . "Code Formation : {$formation->code_formation}\n"
                                                                . "Année académique : {$annee_debut}-{$annee_fin}"; */

                                                        $QrCodeText =  env('FRONTEND_URL').'/consultation/diplome/'.$participant->code_user.'/'.$code_formation;
                                                    @endphp

                                                    <img width="75" height="75"
                                                        src="data:image/png;base64, {!! base64_encode(
                                                            QrCode::encoding('UTF-8')->format('svg')->size(100)->generate($QrCodeText),
                                                        ) !!}" />
                                                </div>
                                            </td>
                                            <td style="width:30%;margin:0; font-size:9pt; border: 0px solid black;">
                                                <span><b> Avis important :</b> <br> 
                                                    Il ne peut être délivré qu'un seul exemplaire de la présente attestation. <br>
                                                    Aucun duplicata ne sera fourni.</span>
                                            </td>

                                            <td style="text-align:center;vertical-align: bottom;">
                                                &nbsp; 
                                            </td>

                                            <td style="width:40%; vertical-align: bottom; padding:0; font-size:11pt;">
                                                @php
                                                    $signataire = getSignataire($formation->code_formation);
                                                @endphp
                                                @if ($signataire == false)
                                                    
                                                    @else
                                                        <div
                                                            style=" text-align:left; margin:0; padding:0; z-index: 10;">
                                                            <img src="storage/signature/{{ $signataire->signature }}"
                                                                alt="Signature"
                                                                style="margin: -15px; padding:0; height: 105px; text-align:left; ">
                                                        </div>
                                                        <div
                                                            style="padding:0 0 0 50px; margin:-20px 0 0; text-align:center; z-index: 100;">
                                                            <b><u> {{ $signataire->titre }}
                                                                    {{ trim($signataire->prenoms) }}
                                                                    {{ trim($signataire->nom) }}
                                                                </u></b> <br>
                                                                Directeur Général
                                                        </div>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div
                                style="display:block; clear:both; width:100%; background: #fff; border: 0; border-top: 6px solid #9cc2e5; border-bottom:  6px solid #1571c6;  margin: 5px 0 10px 0; color:#000000; font-size:9pt; text-align:center; padding:5px 0 5px 0">
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
        
            
        @endif
    @endforeach

</body>

</html>
