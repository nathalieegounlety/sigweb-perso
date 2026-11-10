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


.page-background {
    position: absolute;
    top: -0.73cm;    
    left: -1.27cm;   
    width: 29.7cm;  
    height: 21.0cm;  
    z-index: -1000;  
}

body::before {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%; 
    background-size: 500px;
    opacity: 0.1;
    z-index: -1;
}
        @page {
            size: A4 landscape;
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
 
     <img src="img/BG_Diplomes-2026.jpg" class="page-background">
        <div style="z-index: 10; background:none; margin-top:115px">

            <div>
                <!-- div opacity -->
                <div style="z-index: 300; opacity:1; margin:0;font-size:12pt;">
                    <div
                        style="z-index: 100;clear:both; display:block; margin: 0; text-align: center;line-height: 1.2;font-size: 28px; color:#233468; font-weight:bold;">

                        <div style="margin: 8px 0; padding:3px 6px;">
                            L'Ecole Régionale Supérieur de la Magistrature (ERSUMA)
                        </div>
                    </div> 
                    <div style="clear:both; display:block; text-align:justify; padding:8px 15px;">
                        <div style="line-height: 1.4;">
                            <span style="font-size:11pt; margin: 8px 0;">
                               <li><b>Vu</b> le Traité relatif à l’Harmonisation du droit des Affaires en Afrique signé à Port-Louis le 17 octobre 1993, tel que révisé à Québec le 17 octobre 2008 notamment en son article 41 ; </li>
                                <li><b>Vu</b> le Statut de l’ERSUMA du 03 octobre 1995 révisé par les Règlements n° 004/2009/CM/OHADA du 19 décembre 2009, n° 003/2011/CM du 17 juin 2011 et n° 02/2016/CM/OHADA du 28 octobre 2016 portant Statut révisé de l’ERSUMA ; </li>
                                <li><b>Vu</b> la Décision n° 002/2019/CA/ERSUMA/OHADA du 24 août 2019 portant création et organisation du Diplôme de Spécialité en Droit OHADA à l'ERSUMA ;</li>
                                <li><b>Vu</b> la Décision n° 017-2025 du 10 octobre 2025 portant délibération et publication du résultat final aux examens de fin d’année au titre de l’année académique 2024-2025 ; </li>
                            </span>
                            <div style="clear:both; display:block;text-align:center;font-size: 14pt;"> Délivré(e) à :
                            </div>
                            <div
                                style="line-height: 1.3;font-size: 11pt; display:block; text-align:center; margin-bottom:10px;">
                                <span
                                    style="font-weight:bold; font-size:22pt; color:#233468;">{{ trim($participant->prenoms) }}
                                    {{ trim($participant->nom) }}</span> 
                                     <br>
                                     @if (!empty($participant->ddn) || !empty($participant->lieu_naissance))
                                        <p style="font-weight:bold; font-size:20pt; margin-top:5px;  border-bottom:  2px solid #233468; padding:0px 0px 0px 0px;"> Né(e) le {{$participant->ddn}} à {{$participant->lieu_naissance}}</p>
                                     @endif 
                                     <span style="font-weight:bold; font-size:20pt; line-height:1.1;">Le  <span style="color:#233468;">{{ $formation->theme }}</span></span><br>
                              <!--  au titre de l'année académique 2024-2025.-->
                               <p style="text-align: center;font-size: 18pt; line-height:1.3; clear:both;">
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
                            </p> 
                            </div> 
                            <div class="clearfix"></div>
                        </div>
                    </div>

                </div>
                <!-- end div opacity -->

            </div><!-- fermeture div background-image -->


        </div>  
        <table style="width: 100%;  padding: 0; margin: 5px 0px 0px 0px ; ">
            <tbody>
                <tr>
                    <td style="vertical-align: bottom;">
                        <footer>
                            <br>
                            <div style="clear:both; display:block; margin:10px 0 0 0; width: 100%; ">
                                <div style="float:left; color:#233468; font-weight:bold;">
                                    &nbsp; Avec les droits et prérogatives qui y sont attachés.
                                </div>
                                <div style=" float:right; padding-right:5px;font-size: 11pt; margin-bottom: 15px; color:#233468; font-weight:bold;">
                                    Délivrée à Porto-Novo, le
                                        <span style="color: #000000">{{ \Carbon\Carbon::parse($participant->date_remise_diplomes)->isoFormat('DD MMMM YYYY') }}</span>
                                        <br>
                                        <span style="font-size: 13pt;">Le Directeur Général,</span>
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
                                                            style="padding:0 0 0 50px; margin:-20px 0 0; text-align:center; z-index: 100;">
                                                            <b><u> {{ $signataire->titre }}
                                                                    {{ trim($signataire->prenoms) }}
                                                                    {{ trim($signataire->nom) }}
                                                                </u></b>  
                                                        </div>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
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
