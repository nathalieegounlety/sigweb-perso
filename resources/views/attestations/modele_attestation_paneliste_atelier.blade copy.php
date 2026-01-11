<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	<title></title>
	<meta name="generator" content="LibreOffice 6.4.7.2 (Linux)"/>
	<meta name="author" content="Kodjo Patrice TOSSAVI"/>
	<meta name="created" content="2020-09-18T06:01:00"/>
	<meta name="changedby" content="Kodjo Patrice TOSSAVI"/>
	<meta name="changed" content="2020-09-18T06:04:00"/>
	<meta name="AppVersion" content="16.0000"/>
	<meta name="DocSecurity" content="0"/>
	<meta name="HyperlinksChanged" content="false"/>
	<meta name="LinksUpToDate" content="false"/>
	<meta name="ScaleCrop" content="false"/>
	<meta name="ShareDoc" content="false"/>
	<style type="text/css">
		@page { size: 29.7cm 21cm; margin-left: 1.27cm; margin-right: 1.27cm; margin-top: 0.73cm; margin-bottom: 0.97cm }
		p { margin-bottom: 0.25cm; direction: ltr; line-height: 115%; text-align: justify; orphans: 2; widows: 2; background: transparent }
		p.western { font-family: Tahoma, sans-serif; so-language: fr-FR }
		p.cjk { font-family: Tahoma, sans-serif; }
		p.ctl { font-family: Tahoma, sans-serif; font-size: 10pt }
		a:link { color: #0563c1; text-decoration: underline }

	</style>
<style>
            /**
                Set the margins of the page to 0, so the footer and the header
                can be of the full height and width !
             **/


            /** Define now the real margins of every page in the PDF **/


            /** Define the header rules **/
            header {
                position: fixed;
                top: 0cm;
                left: 0cm;
                right: 0cm;
                height: 3cm;
            }

            /** Define the footer rules **/
            footer {
                position: fixed;
                bottom: 0cm;
                left: 0cm;
                right: 0cm;
                height: 2cm;
            }
        </style>

</head>


<body lang="fr-BJ" link="#0563c1" vlink="#800000" dir="ltr"
style="font-family: Tahoma, sans-serif; 
   z-index: 100;
  "> 

@php $duree_formation = 0; $i = 0; $theme = ""; @endphp
            @foreach($durees as $duree)
              @php
              $duree_formation = $duree->duree ;
              $theme = $duree->getFormation->theme;
               @endphp
            @endforeach

@foreach($liste_participants as $participant)

@php
          $duree_reel = $duree_formation - 30 ;
          $scoring = ($duree_reel * 2) / 3;
        @endphp
        @if($participant->temps_presence >= $scoring)



<div style="z-index: 10; background:none;">
<div  style="display:block; clear:both; width:100%; background: #fff; border: 0; border-top: 15px solid #1571c6; border-bottom:  15px solid #9cc2e5;  margin: 0 0 10px 0; color:#000000; text-align:center; font-family:Tahoma, sans-serif; ">
&nbsp;
</div>

<div style="z-index:30;">
    <div style="float:left;width:79%;">

    <div style=" border-bottom:5px double #2681ca; max-width:77%; padding: 0 0 10px 0; ">

    <div style="float:left; width: 100px; margin:0px; height:100px; ">
    <img src="img/logo/logo-ohada.png" alt="" height="90px">
    </div>
    <div style=" line-height:1.2; margin: -25px 0 0 105px; padding:0px; top:0; font-family: Tahoma, serif; font-weight:bold; text-align:justify;">
      Organisation &nbsp;&nbsp;pour &nbsp;l’Harmonisation &nbsp;en &nbsp;Afrique &nbsp;du &nbsp;Droit &nbsp;des &nbsp;&nbsp;Affaires <br>
      Organization &nbsp;&nbsp; for&nbsp; &nbsp;the&nbsp; &nbsp;Harmonization &nbsp;&nbsp; of &nbsp;&nbsp;Business
      &nbsp;&nbsp; Law &nbsp;in &nbsp;&nbsp; Africa <br>
      Organización para la Armonización en África del Derecho de los Negocios <br>
      Organização &nbsp;para &nbsp;a &nbsp;Harmonização &nbsp;em &nbsp;África &nbsp;do &nbsp;Direito dos Negócios
    </div>
    </div>
  </div>
    <div style="float:right;width:25%;position:relative; text-align:right;">
    <img src="img/logo/attestation_flyers2.png" alt="" height="90px">
    </div>
</div>

<div style="background: url('img/logo/logo-ohada-new500.png');
  opacity: 0.1;
  background-repeat: no-repeat;
  background-attachment: fixed;
  background-position: center;
  z-index: -1;">
  <!-- div opacity -->
            <div style="z-index: 300; opacity:0; margin:0;">

            <div style="z-index: 100;clear:both; display:block; margin: 20px 0 0px 0;text-align: center;line-height: 115%;font-size: 28pt; color:#006fc0; font-weight:bold;">
            <div style="margin: 20px 0 10px 0;">
            ATTESTATION DE PARTICIPATION
            </div>
</div>
<div  style="clear:both; display:block; width:100%; margin-top:5px; text-align:center; color : #000000; font-size: 16pt; font-weight:bold;">
            <div style="margin: 5px 0 10px 0;">
            LE DIRECTEUR GENERAL
            </div>
</div>
 <div style="clear:both; display:block;">
 <div style="line-height: 1.7;">
 Vu le Traité relatif à l’Harmonisation du droit des Affaires en Afrique signe à Port-Louis le 17 octobre 1993, tel que révise à Québec le 17 octobre 2008 notamment en son article 41 ; <br>
 Vu le Statut de l’ERSUMA du 03 octobre 1995 révise par les Règlements n° 004/2009/CM/OHADA du 19 décembre 2009, n° 003/2011/CM du 17 juin 2011 et n° 02/2016/CM/OHADA du 28 octobre 2016 portant Statut révise de l’ERSUMA ;

 <div style="clear:both; display:block; text-align:center;">
 Atteste que : <span style="font-weight:bold; font-size:150%;">{{ trim($participant->prenoms) }} {{ trim($participant->nom) }}</span>

 </div>

<div style="clear:both; display:block; text-align:center;">
a animé en qualité de formateur la session de formation virtuelle sur le thème
</div>

 <div style="clear:both; display:block; text-align:center;">

<span style="font-weight:bold; font-size:150%;"> «{{ $participant->theme }}»</span>

 </div>

<div style="clear:both; display:block; text-align:center;">
qui s’est tenue sur Zoom le «date», correspondant à 6 heures de préparation et d’animation.
</div>

 </div>

 </div>

 <div style="clear:both; display:block; margin:15px 0; width: 100%;">
            <div style="float:left;">
            Le titulaire
            </div>
            <div style=" float:right;">
            <b>Délivrée à Porto-Novo, le {{$date}}</b>
            </div>
 </div>

 <div style="clear:both; display:block; width: 100%; padding: 20px 0 0 0;">
            <div style="float:left; margin:30px 0 0px 0; padding:5px 0 -20px 0; border-top: 2px solid #000;  ">

            <b>{{ trim($participant->prenoms) }} {{ trim($participant->nom) }}</b>
            </div>
            <div style="float:right; margin-top:25px; padding:5px 0 ; border-top: 2px solid #000;">

             <b>Pr. Mayatta Ndiaye MBAYE</b>

            </div>
 </div>

            </div>
  <!-- end div opacity -->
</div><!-- fermeture div background-image -->

<div  style="display:block; clear:both; width:100%; background: #fff; border: 0; border-top: 15px solid #9cc2e5; border-bottom:  15px solid #1571c6;  margin: 10px 0 10px 0; color:#000000; text-align:center; font-family:Tahoma, sans-serif;  font-size:13px; padding:5px 0 5px 0">
ERSUMA, Ouando, Carrefour Cinquantenaire - Route de Pobè, 02 BP 353 Porto-Novo - Tél. : +229 01 97 97 05 37 -  E-mail : ersuma@ohada.org -
Site Web :<font color="#0563c1"><u><a href="https://ersuma.ohada.org/">https://ersuma.ohada.org/</a></u></font>
</div>

</div>
        @endif

@endforeach

</body>
</html>
