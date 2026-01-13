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
            height: 3.0cm;
            3.5cm;
            border-bottom: 6px solid #0d043d;
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
        }
    </style>
</head>

<body>

    @php
        $i = 0;
        $theme = '';
    @endphp

    @foreach ($liste_formateurs as $formateur)
        <table style="width: 100%;  padding: 0; margin: 0 ;">
            <tbody>
                <tr>
                    <td style="vertical-align:top;">
                        <header>
                            <div
                                style="display:block; clear:both; width:100%; background: #fff; border: 0; border-top: 10px solid #1571c6; border-bottom:  10px solid #9cc2e5;  margin: 0 0 10px 0; color:#000000; padding:0; height:10px; text-align:center;">

                            </div>

                            <div style="z-index:100;">
                                <div style="float:left; width:79%;">

                                    <div
                                        style="z-index:100; border-bottom:5px double #2681ca; max-width:72.7%; padding: 0 0 10px 0; ">

                                        <div style="z-index:100;float:left; width: 110px; margin:0px; height:92px; ">
                                            <img src="img/logo/logo-ohada.png" alt="" height="85px"
                                                style="height: 85px;">
                                        </div>
                                        <div
                                            style=" line-height:1.17; margin: -25px 0 0 100px; padding:0px; top:0; font-size:12pt; text-align:justify;">
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
                                <div style="float:right;width:25%;position:relative; text-align:right;">
                                    <img src="img/logo/attestation_flyers2.png" alt="" height="90px"
                                        style="height: 90px;">
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
                <div style="z-index: 300; opacity:0; margin:0;font-size:12pt;">

                    <div
                        style="z-index: 100;clear:both; display:block; margin: 20px 0 0px 0;text-align: center;line-height: 1.2;font-size: 30pt; color:#006fc0; font-weight:bold;">
                        <div style="margin: 20px 0 10px 0;">
                            <!-- 11/11/2025 Titre dynamique selon le rôle du formateur -->
                            @if ($formateur->roleformateur_id)
                                @switch($formateur->getRoleformateur->libelle)
                                    @case('FORMATEUR / COMMUNICATEUR')
                                        ATTESTATION DE FORMATEUR
                                    @break

                                    @case('MODERATEUR')
                                        ATTESTATION DE MODERATEUR
                                    @break

                                    @case('RAPPORTEUR')
                                        ATTESTATION DE RAPPORTEUR
                                    @break

                                    @default
                                        ATTESTATION DE FORMATEUR
                                @endswitch
                            @else
                                <!-- valeur par défaut -->
                                @if ($formateur->getFormation->is_formation)
                                    ATTESTATION DE FORMATEUR
                                @else
                                    ATTESTATION DE PANELISTE
                                @endif
                            @endif
                        </div>
                    </div>
                    <div
                        style="clear:both; display:block; width:100%; margin-top:5px; text-align:center; color : #000000; font-size: 20pt; font-weight:bold;">
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
                                2009, n° 003/2011/CM du 17 juin 2011 et n° 02/2016/CM/OHADA du 28 octobre 2016 portant
                                Statut révisé
                                de l’ERSUMA ;
                            </span><br>
                            <div style="clear:both; display:block;text-align:center;font-size: 14pt;">
                                Atteste que :
                            </div>
                            <!-- 11/11/2025 dynamique bloc selon le rôle -->
                            @if ($formateur->roleformateur_id)
                                @switch($formateur->getRoleformateur->libelle)
                                    @case('FORMATEUR / COMMUNICATEUR')
                                        <!-- start bloc -->
                                        <div
                                            style="line-height: 1.5;font-size: 13pt; display:block; text-align:center; margin-bottom:10px;">
                                            <span
                                                style="font-weight:bold; font-size:20pt;">{{ $formateur->getFormateur->titre }}
                                                {{ $formateur->getFormateur->prenoms }}
                                                {{ $formateur->getFormateur->nom }}</span> <br>
                                            a animé en qualité de <b>Formateur</b> la session de formation sur le thème <span
                                                style="font-weight:bold; font-size:16pt;">
                                                <i>{{ $formateur->getFormation->theme }}</i> </span>
                                            qui s’est tenue @if ($formateur->getFormation->presentiel)
                                                en presentiel
                                            @else
                                                par visioconférence
                                                @endif @if ($formateur->getFormation->lieu != '')
                                                    et à {{ $formateur->getFormation->lieu }}
                                                @endif
                                                @if ($formateur->getFormation->date_debut == $formateur->getFormation->date_fin)
                                                    le
                                                    {{ \Carbon\Carbon::parse($formateur->getFormation->date_debut)->isoFormat('DD MMM YYYY') }}
                                                @else
                                                    du
                                                    {{ \Carbon\Carbon::parse($formateur->getFormation->date_debut)->isoFormat('DD MMM') }}
                                                    au
                                                    {{ \Carbon\Carbon::parse($formateur->getFormation->date_fin)->isoFormat('DD MMM YYYY') }}
                                                @endif
                                                @php
                                                    $masse_horaire_formation = 16 * $nbre_jour;
                                                    $masse_horaire_conf = 16;
                                                @endphp
                                                correspondant à
                                                {{ $nbre_jour == 1 ? "$masse_horaire_conf heures" : "$masse_horaire_formation heures" }}
                                                de préparation et d'exécution.
                                        </div>
                                        <!-- end start bloc -->
                                    @break

                                    @case('MODERATEUR')
                                        <!-- start bloc -->
                                        <div
                                            style="line-height: 1.5;font-size: 13pt; display:block; text-align:center; margin-bottom:10px;">
                                            <span
                                                style="font-weight:bold; font-size:20pt;">{{ $formateur->getFormateur->titre }}
                                                {{ $formateur->getFormateur->prenoms }}
                                                {{ $formateur->getFormateur->nom }}</span> <br>
                                            a animé en qualité de <b>Modérateur</b> de la session de formation sur le thème
                                            <span style="font-weight:bold; font-size:16pt;">
                                                <i>{{ $formateur->getFormation->theme }}</i> </span>
                                            qui s’est tenue @if ($formateur->getFormation->presentiel)
                                                en presentiel
                                            @else
                                                par visioconférence
                                                @endif @if ($formateur->getFormation->lieu != '')
                                                    et à {{ $formateur->getFormation->lieu }}
                                                @endif
                                                @if ($formateur->getFormation->date_debut == $formateur->getFormation->date_fin)
                                                    le
                                                    {{ \Carbon\Carbon::parse($formateur->getFormation->date_debut)->isoFormat('DD MMM YYYY') }}
                                                @else
                                                    du
                                                    {{ \Carbon\Carbon::parse($formateur->getFormation->date_debut)->isoFormat('DD MMM') }}
                                                    au
                                                    {{ \Carbon\Carbon::parse($formateur->getFormation->date_fin)->isoFormat('DD MMM YYYY') }}
                                                @endif
                                                @php
                                                    $masse_horaire_formation = 8 * $nbre_jour;
                                                    $masse_horaire_conf = 8;
                                                @endphp
                                                correspondant à
                                                {{ $nbre_jour == 1 ? "$masse_horaire_conf heures" : "$masse_horaire_formation heures" }}
                                                de travaux.
                                        </div>
                                        <!-- end  start bloc -->
                                    @break

                                    @case('RAPPORTEUR')
                                        <!-- start bloc -->
                                        <div
                                            style="line-height: 1.5;font-size: 13pt; display:block; text-align:center; margin-bottom:10px;">
                                            <span
                                                style="font-weight:bold; font-size:20pt;">{{ $formateur->getFormateur->titre }}
                                                {{ $formateur->getFormateur->prenoms }}
                                                {{ $formateur->getFormateur->nom }}</span> <br>
                                            a participé en qualité de <b>Rapporteur</b> (de la session de formation) sur le
                                            thème <span style="font-weight:bold; font-size:16pt;">
                                                <i>{{ $formateur->getFormation->theme }}</i> </span>
                                            qui s’est tenue @if ($formateur->getFormation->presentiel)
                                                en presentiel
                                            @else
                                                par visioconférence
                                                @endif @if ($formateur->getFormation->lieu != '')
                                                    et à {{ $formateur->getFormation->lieu }}
                                                @endif
                                                @if ($formateur->getFormation->date_debut == $formateur->getFormation->date_fin)
                                                    le
                                                    {{ \Carbon\Carbon::parse($formateur->getFormation->date_debut)->isoFormat('DD MMM YYYY') }}
                                                @else
                                                    du
                                                    {{ \Carbon\Carbon::parse($formateur->getFormation->date_debut)->isoFormat('DD MMM') }}
                                                    au
                                                    {{ \Carbon\Carbon::parse($formateur->getFormation->date_fin)->isoFormat('DD MMM YYYY') }}
                                                @endif
                                                @php
                                                    $masse_horaire_formation = 8 * $nbre_jour;
                                                    $masse_horaire_conf = 2 * 4;
                                                @endphp
                                                correspondant à
                                                {{ $nbre_jour == 1 ? "$masse_horaire_conf heures" : "$masse_horaire_formation heures" }}
                                                de travaux.
                                        </div>
                                        <!-- end  start bloc -->
                                    @break

                                    @default
                                        <!-- start bloc -->
                                        <div
                                            style="line-height: 1.5;font-size: 13pt; display:block; text-align:center; margin-bottom:10px;">
                                            <span style="font-weight:bold; font-size:20pt;">{{ $formateur->titre }}
                                                {{ $formateur->prenoms }}
                                                {{ $formateur->nom }}</span> <br>
                                            a animé
                                            {{ $formateur->role == 'MODERATEUR' ? 'en qualité de modérateur' : 'en qualité de panéliste/communicateur' }}
                                            de la session de formation sur le thème
                                            <br><span style="font-weight:bold; font-size:16pt;">
                                                <i>{{ $formation->theme }}</i> </span><br>
                                            qui s’est tenue @if ($formation->lieu != '')
                                                à {{ $formation->lieu }}
                                            @endif
                                            @if ($formation->date_debut == $formation->date_fin)
                                                le
                                                {{ \Carbon\Carbon::parse($formation->date_debut)->isoFormat('DD MMM YYYY') }}
                                            @else
                                                du
                                                {{ \Carbon\Carbon::parse($formation->date_debut)->isoFormat('DD MMM') }}
                                                au
                                                {{ \Carbon\Carbon::parse($formation->date_fin)->isoFormat('DD MMM YYYY') }}
                                            @endif
                                            @php
                                                $masse_horaire_formation = 2 * 8 * $nbre_jour;
                                                $masse_horaire_conf = 2 * 4;
                                            @endphp
                                            correspondant à
                                            {{ $nbre_jour == 1 ? "$masse_horaire_conf heures" : "$masse_horaire_formation heures" }}
                                            de formation.
                                        </div>
                                        <!-- end  start bloc -->
                                @endswitch
                            @else
                                <div
                                    style="line-height: 1.5;font-size: 13pt; display:block; text-align:center; margin-bottom:10px;">
                                    <span style="font-weight:bold; font-size:20pt;">{{ $formateur->titre }}
                                        {{ $formateur->prenoms }}
                                        {{ $formateur->nom }}</span> <br>
                                    a animé
                                    {{ $formateur->role == 'MODERATEUR' ? 'en qualité de modérateur' : 'en qualité de panéliste/communicateur' }}
                                    de la session de formation sur le thème
                                    <br><span style="font-weight:bold; font-size:16pt;">
                                        <i>{{ $formation->theme }}</i> </span><br>
                                    qui s’est tenue @if ($formation->lieu != '')
                                        à {{ $formation->lieu }}
                                    @endif
                                    @if ($formation->date_debut == $formation->date_fin)
                                        le
                                        {{ \Carbon\Carbon::parse($formation->date_debut)->isoFormat('DD MMM YYYY') }}
                                    @else
                                        du
                                        {{ \Carbon\Carbon::parse($formation->date_debut)->isoFormat('DD MMM') }}
                                        au
                                        {{ \Carbon\Carbon::parse($formation->date_fin)->isoFormat('DD MMM YYYY') }}
                                    @endif
                                    @php
                                        $masse_horaire_formation = 2 * 8 * $nbre_jour;
                                        $masse_horaire_conf = 2 * 4;
                                    @endphp
                                    correspondant à
                                    {{ $nbre_jour == 1 ? "$masse_horaire_conf heures" : "$masse_horaire_formation heures" }}
                                    de formation.
                                </div>
                            @endif
                            <!-- End : 11/11/2025 dynamique bloc selon le rôle -->


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
                                    <b>Fait à Porto-Novo, le
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
                                                                        $formation->code_formation .
                                                                        ' / Thème : ' .
                                                                        $formation->theme .
                                                                        ' / Auditeur : ' .
                                                                        $formateur->prenoms .
                                                                        ' ' .
                                                                        $formateur->nom,
                                                                ),
                                                        ) !!}" />
                                                </div>
                                            </td>

                                            <td style="text-align:center;vertical-align: bottom;">
                                                &nbsp;
                                            </td>

                                            <td style="width:30%; vertical-align: bottom; padding:0; ">
                                                @php
                                                    $signataire = getSignataire($formation->code_formation);
                                                @endphp
                                                @if ($signataire == false)
                                                    <div style=" text-align:left; margin:0; padding:0; z-index: 10;">
                                                        <img src="img/signatdg.png" alt="Signature"
                                                            style="margin: -15px; padding:0; height: 145px; text-align:left; ">
                                                    </div>
                                                    <div
                                                        style="padding:0; margin:-20px 0 0; text-align:center; z-index: 100;">
                                                        <b><u>Pr. Mayatta Ndiaye MBAYE</u></b>
                                                    </div>
                                                @else
                                                    <div style=" text-align:left; margin:0; padding:0; z-index: 10;">
                                                        <img src="storage/signature/{{ $signataire->signature }}"
                                                            alt="Signature"
                                                            style="margin: -15px; padding:0; height: 145px; text-align:left; ">
                                                    </div>
                                                    <div
                                                        style="padding:0; margin:-20px 0 0; text-align:center; z-index: 100;">
                                                        <b><u> {{ $signataire->titre }} {{ $signataire->nom }}
                                                                {{ $signataire->prenoms }}</u></b>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div
                                style="display:block; clear:both; width:100%; background: #fff; border: 0; border-top: 10px solid #9cc2e5; border-bottom:  10px solid #1571c6;  margin: 5px 0 10px 0; color:#000000; font-size:10pt; text-align:center; padding:5px 0 5px 0">
                                ERSUMA, Ouando, Carrefour Cinquantenaire - Route de Pobè, 02 BP 353 Porto-Novo, BENIN -
                                Tél. : +229 01 97 97
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
    @endforeach
</body>

</html>
