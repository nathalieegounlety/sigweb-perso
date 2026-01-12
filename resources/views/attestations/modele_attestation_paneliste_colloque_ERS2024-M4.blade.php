<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>

<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title></title>
    <meta name="generator" content="LibreOffice 6.4.7.2 (Linux)" />
    <meta name="author" content="Kodjo Patrice TOSSAVI" />
    <meta name="created" content="2020-09-18T06:01:00" />
    <meta name="changedby" content="Kodjo Patrice TOSSAVI" />
    <meta name="changed" content="2020-09-18T06:04:00" />
    <meta name="AppVersion" content="16.0000" />
    <meta name="DocSecurity" content="0" />
    <meta name="HyperlinksChanged" content="false" />
    <meta name="LinksUpToDate" content="false" />
    <meta name="ScaleCrop" content="false" />
    <meta name="ShareDoc" content="false" />
    <style type="text/css">
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

        header {
            position: fixed;
            top: 0cm;
            left: 0cm;
            right: 0cm;
            height: 3.0cm;3.5cm;
            border-bottom:6px solid #0d043d;
        }

        @page {
            size: 29.7cm 21cm;
            margin-left: 1.27cm;
            margin-right: 1.27cm;
            margin-top: 0.73cm;
            margin-bottom: 0.97cm;
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

        /** Define the footer rules **/
        footer {
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 5.2cm;
            margin:0;
        }
    </style>

</head>

@foreach ($formateur as $formateur)
     
<body lang="fr-BJ" link="#0563c1" vlink="#800000" dir="ltr"
    style="font-family: Tahoma, sans-serif; 
   z-index: 100;
  ">
  
  @php 
  $i = 0;
  $theme = '';
@endphp 
<table style="width: 100%;  padding: 0; margin: 0 ;">
  <tbody>
      <tr>
          <td style="vertical-align:top;">
            <header>
                <table style="width: 100%; padding: 0; margin: 0 ; text-align:center;">
                    <tr style=" border-bottom:4px solid #1a0642;">
                        <td style="width:20%;">
                            <img src="img/inc-attestations/logo_ersuma.jpg" alt="logo" height="90px" style="height: 90px;">
                        </td>
                        <td style="width:;20%; border:;2px solid red">
                            <img src="img/inc-attestations/logo-feg.png" alt="logo" height="75px" style="height: 75px;">
                        </td>
                        <td style="width:20%;">
                            <img src="img/inc-attestations/logo-iimac.jpg" alt="logo" height="80px" style="height: 80px;">
                        </td>
                        <td style="width:20%;">
                            <img src="img/inc-attestations/logo_apaa.jpg" alt="logo" height="85px" style="height: 85px;">
                        </td>
                    </tr>
                </table>
            </header>

          </td>
      </tr>
  </tbody>
</table>


      <div style="z-index: 10; background:none; margin-top:115px; margin-bottom:0;">


          <div >
              <!-- div opacity -->
              <div style="z-index: 300; opacity:0; margin:0;font-size:12pt;">
                    
                <div style="z-index: 3; display:block; text-align:center; font-size: 27pt; font-weight:bold; font-family:'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif; color:#1a0642;">Journées africaines de l'arbitrage et de la médiation</div>

                  <div style="z-index: 100;clear:both; display:block; margin: 10px 0 0px 0;text-align: center;line-height: 1.2;font-size: 30pt; color:#006fc0; font-weight:bold;">
                      <div style="margin: 10px 0 10px 0;">
                        ATTESTATION DE {{$formateur->role == 'MODERATEUR' ? "MODERATEUR" : "PANELISTE"}}
                        
                      </div>
                  </div>
                  <div style="clear:both; display:block; text-align:justify;">
                      <div style="line-height: 1.3; ">
                          <span style="font-size:11.8pt;">
                              <b>Vu</b> le Traité relatif à l’Harmonisation du droit des Affaires en Afrique signé
                              à Port-Louis le 17 octobre
                              1993, tel que révisé à Québec le 17 octobre 2008 notamment en son article 41 ; <br>
                              <b>Vu</b> le Statut de l’ERSUMA du 03 octobre 1995 révisé par les Règlements n°
                              décembre 2009, n° 003/2011/CM du 17 juin 2011 et n° 02/2016/CM/OHADA du 28 octobre 2016 portant Statut révisé
                              de l’ERSUMA ;
                          </span><br>
                          <div style="clear:both; display:block;text-align:center;font-size: 14pt; margin-top:8px;">
                              délivrée à
                          </div>
                          <div style="line-height: 1.3;font-size: 13pt; display:block; text-align:center; margin-bottom:10px;">
                              <span style="font-weight:bold; font-size:24pt;">{{ $formateur->getFormateur->titre }} {{ trim($formateur->getFormateur->prenoms) }}
                                  {{ trim($formateur->getFormateur->nom) }}</span> <br>
                                  pour sa participation, {{$formateur->role == 'MODERATEUR' ? "en qualité de modérateur" : "en qualité de panéliste/communicateur"}},  à la <span style="font-weight:bold; font-size:16pt;">
                                      <i>{{ $formateur->getFormation->theme }}</i> </span>
                                      qui s’est tenue @if($formateur->getFormation->lieu != "") à {{$formateur->getFormation->lieu}} @endif
                              @if ($formateur->getFormation->date_debut == $formateur->getFormation->date_fin)
                              le
                              {{ \Carbon\Carbon::parse($formateur->getFormation->date_debut)->isoFormat('DD MMM YYYY') }}
                              @else
                              du
                              {{ \Carbon\Carbon::parse($formateur->getFormation->date_debut)->isoFormat('DD MMM') }}
                              au
                              {{ \Carbon\Carbon::parse($formateur->getFormation->date_fin)->isoFormat('DD MMM YYYY') }}
                              @endif, correspondant à {{$formateur->role == 'MODERATEUR' ? "20 heures de participation" : "40 heures de préparation et d'exécution de travaux scientifiques"}}.
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
      <table style="width: 100%;  padding: 0; margin:-15px 0 O 0;">
        <tbody>
            <tr>
                <td style="vertical-align: bottom;">
                    <footer>                    
                        <div style="clear:both;">
                            <table style="width: 100%;  padding: 0; margin: -15px 0 0 0 ;">
                                <tbody>
                                    <tr>
                                        <td colspan="4" style="text-align: right;padding-bottom:10px;">
                                            <b>Fait à {{$formateur->getFormation->lieu}}, le {{ \Carbon\Carbon::parse(date('d-m-Y'))->isoFormat('DD MMM YYYY') }}</b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:15%;vertical-align: bottom;">
                                            <div style="margin-bottom: 1px; margin-left: 10px;0;">
                                                <img width="95" height="95"
                                                    src="data:image/png;base64, {!! base64_encode(
        QrCode::encoding('UTF-8')->format('svg')->size(100)->generate('Code Formation : ' . $formateur->code_formation . ' / Thème : ' . $formateur->theme . ' / Auditeur : ' . trim($formateur->prenoms) . ' ' . trim($formateur->nom)),
        ) !!}" />
                                                    </div>
                                        </td>

                                        <td style="width:20%;text-align:center;vertical-align: bottom;">
                                            {{--
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
                                            --}}
                                        </td>

                                        <td style="width:30%;text-align:center;vertical-align: bottom;">
                                            {{--
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
                                            --}}
                                        </td>


                                        <td style="width:30%; vertical-align: bottom; padding:0; ">
                                            @php
                                                $signataire = getSignataire($formateur->code_formation);
                                            @endphp
                                            @if ($signataire == false)
                                            {{--
                                            <div
                                                style=" text-align:left; margin:0; padding:0; z-index: 10;">
                                                <img src="img/signatdg.png" alt="Signature"
                                                    style="margin: -15px; padding:0; height: 145px; text-align:left; ">
                                            </div>
                                            <div
                                                style="padding:0; margin:-20px 0 0; text-align:center; z-index: 100;">
                                                <b><u>Pr. Mayatta Ndiaye MBAYE</u></b>
                                            </div>
                                            --}}
                                                
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
                                    style="display:block; clear:both; width:100%; background: #fff; border: 0; border-top: 12px solid #9cc2e5; border-bottom:  12px solid #1571c6;  margin: 5px 0 10px 0; color:#000000; font-size:12pt; text-align:center; padding:5px 0 5px 0">
                                    Sous le Haut Parrainage de SE Monsieur Paul-Marie GONDJOUT, Ministre de la Justice, Garde des Sceaux de la République Gabonaise
                                    
                                </div>

                    </footer>

                </td>
            </tr>
        </tbody>
    </table>
<!-- saut de page -->


  <!-- saut de page --> 
  @if ($loop->last)
  @else
      <div style="page-break-after: always;"></div>
  @endif
  
  @endforeach
</body>

</html>
