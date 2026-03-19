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
    background-size: 500px;
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
    $nbre_jour = 0;
    $get_nbre_jour = round((strtotime($formateur->getFormation->date_fin) - strtotime($formateur->getFormation->date_debut)) / 86400);
    $nbre_jour = $get_nbre_jour+1;
@endphp 


    <!-- condition sur le type d"evenement  -->
    @php
        $type_evenement = "";
        $verbe_action = "";
    @endphp
    @switch($formateur->getFormation->typeformation_id)
        @case(1) {{-- Formation --}}

        {{-- ######################################################## Bloc Formation #############################################--}}
        @php
            $type_evenement = "au Formation ";
            $verbe_action = "tenue";
        @endphp

        {{-- ######################################################## Bloc Formation #############################################--}}

            
            @break
        @case(2) {{-- Conférence --}}

        {{-- ######################################################## Bloc Conférence #############################################--}}
        @php
            $type_evenement = "au Conférence ";
            $verbe_action = "tenue";
        @endphp


        {{-- ######################################################## Bloc Conférence #############################################--}}

            
            @break
        @case(3) {{-- Colloque --}}

        {{-- ######################################################## Bloc Colloque #############################################--}}
        @php
            $type_evenement = "au Colloque international";
            $verbe_action = "tenu";
        @endphp
        {{-- ######################################################## Bloc Colloque #############################################--}}

            
            @break
        @case(4) {{-- Forum --}}

        {{-- ######################################################## Bloc Forum #############################################--}}
        @php
            $type_evenement = "au Forum ";
            $verbe_action = "tenue";
        @endphp


        {{-- ######################################################## Bloc Forum #############################################--}}

            
            @break
        @case(5) {{-- Atelier --}}

        {{-- ######################################################## Bloc Atelier #############################################--}}
        @php
            $type_evenement = "à l'Atelier ";
            $verbe_action = "tenu";
        @endphp 
        {{-- ######################################################## Bloc Atelier #############################################--}}

            @break
        @case(6){{-- Université --}}

        {{-- ######################################################## Bloc Université #############################################--}}
        @php
            $type_evenement = "à l'Université";
            $verbe_action = "tenu";
        @endphp


        {{-- ######################################################## Bloc Université #############################################--}}
            
            @break
        @case(7){{-- Salon --}}

        {{-- ######################################################## Bloc Salon #############################################--}}
        @php
            $type_evenement = "au Salon ";
            $verbe_action = "tenue";
        @endphp


        {{-- ######################################################## Bloc Salon #############################################--}}
            
            @break
        @default
            
    @endswitch
    <!-- end condition sur le type d"evenement  -->


<table style="width: 100%;  padding: 0; margin: 0 ;">
  <tbody>
      <tr>
          <td style="vertical-align:top;">
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
                                <table style="width: 100%; padding: 0; margin: 0 ; text-align:center;">
                                    
                                    <tr style=" border-bottom:4px solid #1a0642;">
                                        @if ($formateur->getFormation->is_coorganisation)
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


      <div style="z-index: 10; background:none; margin-top:115px; margin-bottom:0;">


          <div >
              <!-- div opacity -->
              <div style="z-index: 300; opacity:1; margin:0;font-size:12pt;">
                    @if ($formateur->getFormation->titre_evenement)
                        <div style="z-index: 3; display:block; text-align:center; font-size: 20px; font-weight:bold; font-family:'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif; color:#1a0642;">{{$formateur->getFormation->titre_evenement}}</div>

                    @endif
                
                  <div style="z-index: 100;clear:both; display:block; margin: 10px 0 0px 0;text-align: center;line-height: 1.2;font-size: 36px; color:#006fc0; font-weight:bold;">
                      <div style="margin: 8px 0 ;"> 
                         <!-- 11/11/2025 Titre dynamique selon le rôle du formateur -->
                                @if ($formateur->roleformateur_id)
                                    @switch($formateur->getRoleformateur->libelle)
                                        @case("FORMATEUR / COMMUNICATEUR") 
                                            ATTESTATION DE PANELISTE
                                            @break
                                        @case("MODERATEUR")
                                            ATTESTATION DE MODERATEUR
                                            @break 
                                        @case("RAPPORTEUR")
                                            ATTESTATION DE RAPPORTEUR
                                            @break  
                                        @case("MODERATEUR / COMMUNICATEUR") 
                                            ATTESTATION DE PANELISTE
                                            @break
                                        @case("CONFERENCIER (LEÇON INAUGURALE)") 
                                            ATTESTATION DE CONFERENCIER
                                            @break
                                        @case("RAPPORTEUR GENERAL") 
                                            ATTESTATION DE RAPPORTEUR GENERAL
                                            @break
                                        @default
                                    <!-- valeur par défaut -->
                                    ATTESTATION DE {{$formateur->role == 'MODERATEUR' ? "MODERATEUR" : "PANELISTE"}}
                                    @endswitch
                                @else
                                    <!-- valeur par défaut -->
                                    ATTESTATION DE {{$formateur->role == 'MODERATEUR' ? "MODERATEUR" : "PANELISTE"}}
                                @endif
                        
                        
                      </div>
                  </div>
                  <div style="clear:both; display:block; text-align:justify;">
                      <div style="line-height: 1.3; ">
                          <span style="font-size:11.8pt;">
                              <b>Vu</b> le Traité relatif à l’Harmonisation du droit des Affaires en Afrique signé
                              à Port-Louis le 17 octobre
                              1993, tel que révisé à Québec le 17 octobre 2008 notamment en son article 41 ; <br>
                              <b>Vu</b> le Statut de l’ERSUMA du 03 octobre 1995 révisé par les Règlements n° 004/2009/CM/OHADA du décembre 2009, n° 003/2011/CM du 17 juin 2011 et n° 02/2016/CM/OHADA du 28 octobre 2016 portant Statut révisé
                              de l’ERSUMA ;
                          </span><br>
                          <div style="clear:both; display:block;text-align:center;font-size: 14pt; margin-top:8px;">
                              délivrée à
                          </div>
                                <!-- 11/11/2025 dynamique bloc selon le rôle -->
                        @if ($formateur->roleformateur_id)
                          @switch($formateur->getRoleformateur->libelle)
                                        @case("FORMATEUR / COMMUNICATEUR") 
                                        <!-- start bloc -->
                                        <div style="line-height: 1.3;font-size: 13pt; display:block; text-align:center; margin-bottom:10px;">
                                            <span style="font-weight:bold; font-size:28px;">{{ $formateur->getFormateur->titre }} {{ trim($formateur->getFormateur->prenoms) }}
                                                {{ trim($formateur->getFormateur->nom) }}</span> <br>
                                                pour sa participation, en qualité de <b>Communicateur</b>,  {{$type_evenement}} sur le thème<br> 
                                                <span style="font-weight:bold; font-size:16pt;">
                                                    <i>{{ $formateur->getFormation->theme }}</i> </span> <br>
                                                    qui s’est {{$verbe_action}} @if($formateur->getFormation->presentiel ) en presentiel @else par visioconférence @endif @if($formateur->getFormation->lieu != "") et à {{$formateur->getFormation->lieu}} @endif
                                            @if ($formateur->getFormation->date_debut == $formateur->getFormation->date_fin)
                                            le
                                            {{ \Carbon\Carbon::parse($formateur->getFormation->date_debut)->isoFormat('DD MMM YYYY') }}
                                            @else
                                            du
                                            {{ \Carbon\Carbon::parse($formateur->getFormation->date_debut)->isoFormat('DD MMM') }}
                                            au
                                            {{ \Carbon\Carbon::parse($formateur->getFormation->date_fin)->isoFormat('DD MMM YYYY') }},
                                            @endif 
                                            @php
                                                    $masse_horaire_formation = 8 * $nbre_jour;   
                                                    $masse_horaire = 8;                                  
                                            @endphp
                                            correspondant à {{$nbre_jour == 1 ? "$masse_horaire heures"  : "$masse_horaire_formation heures"}} de préparation et d'animation.
                                        </div>
                                        <!-- end start bloc --> 
                                            @break
                                        @case("MODERATEUR")
                                        <!-- start bloc -->
                                        <div style="line-height: 1.3;font-size: 13pt; display:block; text-align:center; margin-bottom:10px;">
                                            <span style="font-weight:bold; font-size:28px;">{{ $formateur->getFormateur->titre }} {{ trim($formateur->getFormateur->prenoms) }}
                                                {{ trim($formateur->getFormateur->nom) }}</span> <br>
                                                pour sa participation, en qualité de <b>Modérateur</b>,  {{$type_evenement}} sur le thème<br> 
                                                <span style="font-weight:bold; font-size:16pt;">
                                                    <i>{{ $formateur->getFormation->theme }}</i> </span> <br>
                                                    qui s’est {{$verbe_action}} @if($formateur->getFormation->presentiel ) en presentiel @else par visioconférence @endif @if($formateur->getFormation->lieu != "") @endif
                                            @if ($formateur->getFormation->date_debut == $formateur->getFormation->date_fin)
                                            le
                                            {{ \Carbon\Carbon::parse($formateur->getFormation->date_debut)->isoFormat('DD MMM YYYY') }}
                                            @else
                                            du
                                            {{ \Carbon\Carbon::parse($formateur->getFormation->date_debut)->isoFormat('DD MMM') }}
                                            au
                                            {{ \Carbon\Carbon::parse($formateur->getFormation->date_fin)->isoFormat('DD MMM YYYY') }},
                                            @endif 
                                            @php
                                                    $masse_horaire_formation = 8 * $nbre_jour;   
                                                    $masse_horaire = 8;                                  
                                            @endphp
                                            correspondant à {{$nbre_jour == 1 ? "$masse_horaire heures"  : "$masse_horaire_formation heures"}} de préparation et d'animation.
                                       
                                        </div>                                        
                                        <!-- end start bloc -->
                                            @break 
                                        @case("RAPPORTEUR")
                                        <!-- start bloc -->
                                        <div style="line-height: 1.3;font-size: 13pt; display:block; text-align:center; margin-bottom:10px;">
                                            <span style="font-weight:bold; font-size:28px;">{{ $formateur->getFormateur->titre }} {{ trim($formateur->getFormateur->prenoms) }}
                                                {{ trim($formateur->getFormateur->nom) }}</span> <br>
                                                a participé en qualité de <b>Rapporteur</b>,  {{$type_evenement}} sur le thème<br> 
                                                <span style="font-weight:bold; font-size:16pt;">
                                                    <i>{{ $formateur->getFormation->theme }}</i> </span> <br>
                                                    qui s’est {{$verbe_action}} @if($formateur->getFormation->presentiel ) en presentiel @else par visioconférence @endif @if($formateur->getFormation->lieu != "") et à {{$formateur->getFormation->lieu}} @endif
                                            @if ($formateur->getFormation->date_debut == $formateur->getFormation->date_fin)
                                            le
                                            {{ \Carbon\Carbon::parse($formateur->getFormation->date_debut)->isoFormat('DD MMM YYYY') }}
                                            @else
                                            du
                                            {{ \Carbon\Carbon::parse($formateur->getFormation->date_debut)->isoFormat('DD MMM') }}
                                            au
                                            {{ \Carbon\Carbon::parse($formateur->getFormation->date_fin)->isoFormat('DD MMM YYYY') }},
                                            @endif 
                                            @php
                                                    $masse_horaire_formation = 8 * $nbre_jour;   
                                                    $masse_horaire = 8;                                  
                                            @endphp
                                            correspondant à {{$nbre_jour == 1 ? "$masse_horaire heures"  : "$masse_horaire_formation heures"}} de travaux.
                                       
                                        </div>                                        
                                        <!-- end start bloc -->
                                            @break  
                                        @case("MODERATEUR / COMMUNICATEUR") 
                                        <!-- start bloc -->
                                        <div style="line-height: 1.3;font-size: 13pt; display:block; text-align:center; margin-bottom:10px;">
                                            <span style="font-weight:bold; font-size:28px;">{{ $formateur->getFormateur->titre }} {{ trim($formateur->getFormateur->prenoms) }}
                                                {{ trim($formateur->getFormateur->nom) }}</span> <br>
                                                pour sa participation en qualité de <b>Modérateur / Communicateur</b>,  {{$type_evenement}} sur le thème<br> 
                                                <span style="font-weight:bold; font-size:16pt;">
                                                    <i>{{ $formateur->getFormation->theme }}</i> </span> <br>
                                                    qui s’est {{$verbe_action}} @if($formateur->getFormation->presentiel ) en presentiel @else par visioconférence @endif @if($formateur->getFormation->lieu != "") et à {{$formateur->getFormation->lieu}} @endif
                                            @if ($formateur->getFormation->date_debut == $formateur->getFormation->date_fin)
                                            le
                                            {{ \Carbon\Carbon::parse($formateur->getFormation->date_debut)->isoFormat('DD MMM YYYY') }}
                                            @else
                                            du
                                            {{ \Carbon\Carbon::parse($formateur->getFormation->date_debut)->isoFormat('DD MMM') }}
                                            au
                                            {{ \Carbon\Carbon::parse($formateur->getFormation->date_fin)->isoFormat('DD MMM YYYY') }},
                                            @endif 
                                            @php
                                                    $masse_horaire_formation = 8 * $nbre_jour;   
                                                    $masse_horaire = 8;                                  
                                            @endphp
                                            correspondant à {{$nbre_jour == 1 ? "$masse_horaire heures"  : "$masse_horaire_formation heures"}} de préparation et d'animation.
                                       
                                        </div>                                        
                                        <!-- end start bloc -->
                                            @break
                                        @case("CONFERENCIER (LEÇON INAUGURALE)") 
                                        <!-- start bloc -->
                                        <div style="line-height: 1.3;font-size: 13pt; display:block; text-align:center; margin-bottom:10px;">
                                            <span style="font-weight:bold; font-size:28px;">{{ $formateur->getFormateur->titre }} {{ trim($formateur->getFormateur->prenoms) }}
                                                {{ trim($formateur->getFormateur->nom) }}</span> <br>
                                                pour sa participation en qualité de <b>Conférencier (Leçon inaugurale)</b>,  {{$type_evenement}} sur le thème<br> 
                                                <span style="font-weight:bold; font-size:16pt;">
                                                    <i>{{ $formateur->getFormation->theme }}</i> </span> <br>
                                                    qui s’est {{$verbe_action}} @if($formateur->getFormation->presentiel ) en presentiel @else par visioconférence @endif @if($formateur->getFormation->lieu != "") et à {{$formateur->getFormation->lieu}} @endif
                                            @if ($formateur->getFormation->date_debut == $formateur->getFormation->date_fin)
                                            le
                                            {{ \Carbon\Carbon::parse($formateur->getFormation->date_debut)->isoFormat('DD MMM YYYY') }}
                                            @else
                                            du
                                            {{ \Carbon\Carbon::parse($formateur->getFormation->date_debut)->isoFormat('DD MMM') }}
                                            au
                                            {{ \Carbon\Carbon::parse($formateur->getFormation->date_fin)->isoFormat('DD MMM YYYY') }},
                                            @endif 
                                            @php
                                                    $masse_horaire_formation = 8 * $nbre_jour;   
                                                    $masse_horaire = 8;                                  
                                            @endphp
                                            correspondant à {{$nbre_jour == 1 ? "$masse_horaire heures"  : "$masse_horaire_formation heures"}} de préparation et d'animation.
                                       
                                        </div>                                        
                                        <!-- end start bloc -->
                                            @break
                                        @case("RAPPORTEUR GENERAL") 
                                        <!-- start bloc -->
                                        <div style="line-height: 1.3;font-size: 13pt; display:block; text-align:center; margin-bottom:10px;">
                                            <span style="font-weight:bold; font-size:28px;">{{ $formateur->getFormateur->titre }} {{ trim($formateur->getFormateur->prenoms) }}
                                                {{ trim($formateur->getFormateur->nom) }}</span> <br>
                                                pour sa participation en qualité de <b>Rapporteur Général</b>,  {{$type_evenement}} sur le thème<br> 
                                                <span style="font-weight:bold; font-size:16pt;">
                                                    <i>{{ $formateur->getFormation->theme }}</i> </span> <br>
                                                    qui s’est {{$verbe_action}} @if($formateur->getFormation->presentiel ) en presentiel @else par visioconférence @endif @if($formateur->getFormation->lieu != "") et à {{$formateur->getFormation->lieu}} @endif
                                            @if ($formateur->getFormation->date_debut == $formateur->getFormation->date_fin)
                                            le
                                            {{ \Carbon\Carbon::parse($formateur->getFormation->date_debut)->isoFormat('DD MMM YYYY') }}
                                            @else
                                            du
                                            {{ \Carbon\Carbon::parse($formateur->getFormation->date_debut)->isoFormat('DD MMM') }}
                                            au
                                            {{ \Carbon\Carbon::parse($formateur->getFormation->date_fin)->isoFormat('DD MMM YYYY') }},
                                            @endif 
                                            @php
                                                    $masse_horaire_formation = 8 * $nbre_jour;   
                                                    $masse_horaire = 8;                                  
                                            @endphp
                                            correspondant à {{$nbre_jour == 1 ? "$masse_horaire heures"  : "$masse_horaire_formation heures"}} de travaux.
                                       
                                        </div>                                        
                                        <!-- end start bloc -->
                                            @break
                                        @default
                                        <!-- start bloc -->
                                        <div style="line-height: 1.3;font-size: 13pt; display:block; text-align:center; margin-bottom:10px;">
                                            <span style="font-weight:bold; font-size:28px;">{{ $formateur->getFormateur->titre }} {{ trim($formateur->getFormateur->prenoms) }}
                                                {{ trim($formateur->getFormateur->nom) }}</span> <br>
                                                pour sa participation, en qualité de <b>{{$formateur->role == 'MODERATEUR' ? "modérateur" : "panéliste/communicateur"}}</b>,  {{$type_evenement}} <br> 
                                                <span style="font-weight:bold; font-size:16pt;">
                                                    <i>{{ $formateur->getFormation->theme }}</i> </span> <br>
                                                    qui s’est {{$verbe_action}} @if($formateur->getFormation->lieu != "") à {{$formateur->getFormation->lieu}} @endif
                                            @if ($formateur->getFormation->date_debut == $formateur->getFormation->date_fin)
                                            le
                                            {{ \Carbon\Carbon::parse($formateur->getFormation->date_debut)->isoFormat('DD MMM YYYY') }}
                                            @else
                                            du
                                            {{ \Carbon\Carbon::parse($formateur->getFormation->date_debut)->isoFormat('DD MMM') }}
                                            au
                                            {{ \Carbon\Carbon::parse($formateur->getFormation->date_fin)->isoFormat('DD MMM YYYY') }},
                                            
                                            @endif correspondant à {{$formateur->role == 'MODERATEUR' ? "20 heures de participation" : "40 heures de préparation et d'exécution de travaux scientifiques"}}.
                                        </div>
                                        <!-- end start bloc -->
                                    @endswitch
                                <!-- end :  11/11/2025 dynamique bloc selon le rôle -->

                                @else
                                        <div style="line-height: 1.3;font-size: 13pt; display:block; text-align:center; margin-bottom:10px;">
                                            <span style="font-weight:bold; font-size:28px;">{{ $formateur->getFormateur->titre }} {{ trim($formateur->getFormateur->prenoms) }}
                                                {{ trim($formateur->getFormateur->nom) }}</span> <br>
                                                pour sa participation, en qualité de <b>{{$formateur->role == 'MODERATEUR' ? "modérateur" : "panéliste/communicateur"}}</b>,  {{$type_evenement}} <br> 
                                                <span style="font-weight:bold; font-size:16pt;">
                                                    <i>{{ $formateur->getFormation->theme }}</i> </span> <br>
                                                    qui s’est {{$verbe_action}} @if($formateur->getFormation->lieu != "") à {{$formateur->getFormation->lieu}} @endif
                                            @if ($formateur->getFormation->date_debut == $formateur->getFormation->date_fin)
                                            le
                                            {{ \Carbon\Carbon::parse($formateur->getFormation->date_debut)->isoFormat('DD MMM YYYY') }}
                                            @else
                                            du
                                            {{ \Carbon\Carbon::parse($formateur->getFormation->date_debut)->isoFormat('DD MMM') }}
                                            au
                                            {{ \Carbon\Carbon::parse($formateur->getFormation->date_fin)->isoFormat('DD MMM YYYY') }},
                                            
                                            @endif correspondant à {{$formateur->role == 'MODERATEUR' ? "20 heures de participation" : "40 heures de préparation et d'exécution de travaux scientifiques"}}.
                                        </div>
                                @endif

                          
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
                                             @php 
                                                $role = '';
                                                if ($formateur->role == 'MODERATEUR') {
                                                    # code...
                                                    $role = 'Modérateur';
                                                } else {
                                                    # code...
                                                    $role = 'Panéliste/Communicateur';
                                                }
                                                

                                                        $QrCodeText = "Attestation de :\n"
                                                                . "Auditeur : {$formateur->getFormateur->prenoms} {$formateur->getFormateur->nom}\n"
                                                                . "Rôle : {$role}\n"
                                                                . "Code Formation : {$formateur->getFormation->code_formation}\n"
                                                                . "Thème : {$formateur->getFormation->theme}";
                                                    @endphp
                                            <div style="margin-bottom: 1px; margin-left: 10px;0;">
                                                <img width="95" height="95"
                                                    src="data:image/png;base64, {!! base64_encode(
        QrCode::encoding('UTF-8')->format('svg')->size(100)->generate($QrCodeText),
        ) !!}" />
                                                    </div>
                                        </td>

                                        <td style="width:20%;text-align:center;vertical-align: bottom;"> 
                                        </td>

                                        <td style="width:30%;text-align:center;vertical-align: bottom;"> 
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
                                                    style="margin: -15px; padding:0; height: 105px; text-align:left; ">
                                            </div>
                                            <div
                                                style="padding:0; margin:-20px 0 0; text-align:center; z-index: 100;">
                                                <b><u> {{$signataire->titre}} {{$signataire->nom}} {{$signataire->prenoms}} 
                                                    </u></b><br> {{ trim($signataire->fonction) }}
                                            </div>                                                    
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div
                                    style="display:block; clear:both; width:100%; background: #fff; border: 0; border-top: 12px solid #9cc2e5; border-bottom:  12px solid #1571c6;  margin: 5px 0 10px 0; color:#000000; font-size:12px; text-align:center; padding:5px 0 5px 0">
                                    
                                        ERSUMA, Ouando, Carrefour Cinquantenaire - Route de Pobè, 02 BP 353 Porto-Novo, BENIN - Tél. : +229 01 97 97
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


  <!-- saut de page --> 
  @if ($loop->last)
  @else
      <div style="page-break-after: always;"></div>
  @endif
  
  @endforeach
</body>

</html>
