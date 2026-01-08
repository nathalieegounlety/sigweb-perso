<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>

<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <title></title>
    <style type="text/css">
        @page {
            size: 21cm 29.7cm;
            margin-left: 1.75cm;
            1.27cm;
            margin-right: 1.75cm;
            1.27cm;
            margin-top: 1.27cm;
            0.73cm;
            margin-bottom: 1.27cm;
            0.97cm
            font-family: Tahoma, serif;
        }

        p {
            margin-bottom: 0.25cm;
            direction: ltr;
            line-height: 115%;
            text-align: justify;
            font-family: Tahoma, serif;
            orphans: 2;
            widows: 2;
            background: transparent
        }

        p.western {
            font-family: Tahoma, serif;
            so-language: fr-FR
        }

        p.cjk {
            font-family: Tahoma, sans-serif;
        }

        p.ctl {
            font-family: Tahoma, sans-serif;
            font-size: 10pt
        }
        div, table, pr, span, p {
            font-family: Tahoma, sans-serif;
        }

        a:link {
            color: #1571c6;
            text-decoration: underline
        }

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
            height: 5cm;
        }
    </style>

</head>


<body lang="fr-BJ" link="#0563c1" vlink="#800000" dir="ltr"
      style="font-family: Tahoma, sans-serif;
   z-index: 100;">

<div style="z-index: 10; background:none;">


    <div style="z-index:30;">
        <div style="float:left;width:86%;">

            <div style=" border-bottom:5px double #2681ca; max-width:75%; padding: 0 0 10px 0; ">

                <div style="float:left; width: 90px; margin:0px; height:90px; ">
                    <img src="img/logo/logo-ohada.png" alt="" height="75px" style="height: 75px;">
                </div>
                <div
                    style=" font-size:65%; line-height:1.5; margin: -25px 0 0 95px; padding:0px; top:0; font-family: Tahoma, serif; font-weight:bold; text-align:justify;">
                    Organisation &nbsp;pour &nbsp;l’Harmonisation &nbsp;en &nbsp;Afrique &nbsp;du &nbsp;Droit &nbsp;des &nbsp;Affaires <br>
                    Organization &nbsp;&nbsp; for&nbsp; &nbsp;the&nbsp; &nbsp;Harmonization &nbsp;&nbsp; of &nbsp;&nbsp;Business
                    &nbsp;&nbsp; Law &nbsp;in &nbsp;&nbsp; Africa <br>
                    Organización para la Armonización en África del Derecho de los Negocios <br>
                    Organização &nbsp;para &nbsp;a &nbsp;Harmonização &nbsp;em &nbsp;África &nbsp;do &nbsp;Direito dos Negócios
                </div>
            </div>
        </div>
        <div style="float:right;width:15%;position:relative; text-align:right;">
            <img src="img/logo/attestation_flyers2.png" alt="" height="80px">
        </div>
    </div>

    <div
        style="background: url('img/logo/logo-ohada-new500.png');
    opacity: 0.1;
    background-repeat: no-repeat;
    background-attachment: fixed;
    background-position: center 70px bottom 20px;
    z-index: -1;
    bottom:0;
    display:block;
    ">
        <!-- div opacity -->
        <div style="z-index: 300; opacity:0; margin:0;">

            <div
                style="z-index: 100;clear:both; display:block; margin: 10px 0 0px 0;text-align: center;line-height: 115%;font-size: 22pt; color:#006fc0; font-weight:bold;">
                <div style="margin: 30px 0 5px 0;">
                    FACTURE PROFORMA
                </div>
            </div>

            <div style="clear:both; display:block;"> </div>

            <div style="clear:both; ">
                <table style="width: 100%; z-index:1000;  padding: 20px 0 0 0; margin: 0 ;">
                    <tbody>
                    <tr>
                        <td style="width: 45%;padding:4px;background:#1571c6; color:#fff; text-align:center;">
                            N° PROFORMA
                        </td>
                        <td style="width: 20%;">
                            &nbsp;
                        </td>
                        <td style="width: 35%;padding:4px;background:#1571c6; color:#fff; text-align:center;">
                            DATE
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:center;">
                            <b>ERS-{{ date('Y') }}-PF-{{ \Carbon\Carbon::parse($inscrit->created_at)->format('mdhs') }}</b>
                        </td>
                        <td style="">
                            &nbsp;
                        </td>
                        <td style="text-align:center;">
                            <b>{{ \Carbon\Carbon::parse($inscrit->created_at)->format('d/m/Y') }}</b>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" style="padding:4px; margin-top:10px;background:#1571c6; color:#fff; text-align:center; ">
                            <b>ADRESSEE A</b>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" style="line-height: 1.5;  ">
                            <b>{{ $auditeur->prenoms }} <span style="text-transform:uppercase;">{{ $auditeur->nom }}</span></b><br>

                            @if ($auditeur->telephone)
                                Tél : {{ $auditeur->telephone }} -
                            @endif
                            E-mail : {{ $auditeur->email }}
                            @if ($auditeur->ville)
                                <br>
                                {{ $auditeur->ville }},
                            @endif
                            @if ($auditeur->pays)
                                {{ $auditeur->pays }}
                            @endif
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div style="line-height: 1.3; margin:10px 0; padding:4px; text-align:center;text-transform:uppercase;background:#1571c6; color:#fff;">

                <b>Détails du paiement</b>

            </div>
            <div style="clear:both; ">

                <table cellspacing="0" cellpadding="0"
                       style=" cellspacing : 0; cellpadding:0; border:0; width: 100%; z-index:1000;   padding: 10px 0 0 0; margin: 0; font-size:10.5pt;">
                    <thead>
                    <tr style="margin:0;background-color: #abd">
                        <th style="padding:5px; margin:0; border: 1px solid black;">#</th>
                        <th style="padding:5px; margin:0; border: 1px solid black; text-align:left;">Thème(s) de
                            formation</th>
                        <th style="padding:5px; margin:0; border: 1px solid black;">Type de frais</th>
                        <th style="padding:5px; margin:0; border: 1px solid black;">Réduction</th>
                        <th style="padding:5px; margin:0; border: 1px solid black;">Montant</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $i = 0;
                        $montant_total = 0;
                    @endphp
                    @foreach ($liste_formation as $formations)
                        @foreach (getTarifFormationCustom($formations->code_formation, $formations->tarif_formation_id, $formations->email) as $formation)
                            {{-- @if ($formation->libelle == 'PRESENTIEL') --}}
                            @php  $i = $i+1;  @endphp

                            @empty($formations->tarif_formation_id)
                                <tr style="margin:0;">
                                    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">
                                        <b>{{ $i }}</b>
                                    </td>
                                    <td style="margin:0; padding:5px; border: 1px solid black;">
                                        <b>{{ $formation->code_formation }}</b> - {{ $formation->theme }}
                                    </td>
                                    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">
                                        {{ $formation->libelle }}</td>
                                    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">
                                        0</td>
                                    <td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">
                                        {{ number_format($formation->montant, 0, ',', '.') }}</td>
                                </tr>
                                @php
                                    $montant_total = $montant_total + $formation->montant;
                                @endphp
                            @else
                                @php
                                    $pourcentage = 0;
                                @endphp
                                @if ($formation->pourcentage != '')
                                    @php
                                        $pourcentage = $formation->pourcentage / 100;
                                    @endphp
                                @endif

                                <tr style="margin:0;">
                                    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">
                                        <b>{{ $i }}</b>
                                    </td>
                                    <td style="margin:0; padding:5px; border: 1px solid black;">
                                        <b>{{ $formation->code_formation }}</b> - {{ $formation->theme }}
                                    </td>
                                    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">
                                        {{ $formation->libelle }}</td>


                                    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">
                                        @if ($formation->pourcentage != '')
                                            {{ $formation->pourcentage }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">
                                        {{ number_format($formation->montant - $formation->montant * $pourcentage, 0, ',', '.') }}
                                    </td>

                                </tr>


                                @php
                                    $montant_total = $montant_total + ($formation->montant - $formation->montant * $pourcentage);
                                @endphp
                            @endempty
                            {{-- @endif --}}
                        @endforeach
                    @endforeach
                    <tr style="margin:0;background-color: #abd">
                        <th colspan="4" style="padding:5px; border: 1px solid black;">Montant total hors taxe
                        </th>
                        <th style="padding:5px; border: 1px solid black; text-align:right;">
                            <b>{{ number_format($montant_total, 0, ',', '.') }}</b>
                        </th>
                    </tr>
                    </tbody>
                </table>
            </div>
            <p>
                <pr style="text-decoration: underline;">Montant en lettres</pr> :
                <b>{{ $formatter->format($montant_total) }} ({{ number_format($montant_total, 0, ',', '.') }})
                    francs CFA</b> (XOF).
            </p>


            <p>&nbsp;</p>
            @php
                $date = date('d M Y');
            @endphp



            <table style="width: 100%;  padding: 0; margin: 0 ;">
                <tbody>
                <tr>
                    <td style="width:40%;vertical-align: bottom;">
                        &nbsp;
                    </td>

                    <td style="vertical-align: bottom;">
                        &nbsp;
                    </td>

                    <td style="width:40%;vertical-align: top;padding:0; text-align:center;">
                        <div style="padding:0; margin:-20px 0 0;-5px auto 0; text-align:center; z-index: 100;">

                            &nbsp;

                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="width:40%;vertical-align: bottom;">
                        &nbsp;
                    </td>

                    <td style="vertical-align: bottom;">
                        &nbsp;
                    </td>

                    <td style="width:40%;vertical-align: bottom;padding:0; ">
                        @php
                            $signataire = getSignataireByDate(\Carbon\Carbon::parse(date('Y-m-d'))->format('Y-m-d'));
                        @endphp

                        <div style=" text-align:left;margin:0; -15px; auto; 0px;;padding:0; z-index: 10;">
                            <img src="storage/signature/{{$signataire->signature}}" alt="Signature"
                                 style="margin: -15px; padding:0;height: 145px; opacity:;0.9;text-align:left;center; ">
                        </div>
                        <div style="padding:0; margin:-20px 0 0;-20px auto 0; text-align:center; z-index: 100;">
                            <b><u>{{$signataire->titre}} {{$signataire->nom}} {{$signataire->prenoms}}</u></b><br>
                            <small>{{$signataire->fonction}}</small>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>


        </div>
        <!-- end div opacity -->

    </div><!-- fermeture div background-image -->

    <footer>
        <div style="clear:both; margin-top:20px; ">
            <table style="width: 100%; z-index:1000;  padding: 20px 0 0 0; margin: 0 ;">
                <tbody>
                <tr>
                    <td style="width: 105px; margin:0; border: 0px solid black;">
                        <img width="90" height="90"
                             src="data:image/png;base64, {!! base64_encode(
                                        QrCode::encoding('UTF-8')->format('svg')->size(95)->generate(
                                                'REFERENCE : ' . $inscrit->code_inscription . 'Prénoms & nom : ' . $auditeur->prenoms . ' ' . $auditeur->nom,
                                            ),
                                    ) !!} ">
                    </td>

                    <td style="margin:0;  font-size:9pt;  border: 0px solid black;">
                        <b>NB :</b> Traité relatif à l'Harmonisation du Droit des Affaires en Afrique <br>
                        <span><b>Article 51: </b></span><br>
                        <span>L'OHADA, ses avoirs, ses biens et ses revenus ainsi que les opérations autorisées
                                    par le présent Traité sont exonérés de tous impôts, taxes et droits de douane.
                                </span> <br>
                        <span>L'OHADA est également exempte de toute obligation relative au recouvrement ou au
                                    paiement d'impôts, de taxes ou de droits de douane. </span>

                    </td>

                </tr>
                </tbody>
            </table>
        </div>
        <div
            style="display:block; clear:both; width:100%; background: #fff; border: 0; border-top: 8px solid #9cc2e5; border-bottom:  8px solid #1571c6;  margin: 10px 0 10px 0; color:#000000; text-align:center;  font-size:9pt; padding:5px 0 5px 0">
            ERSUMA, Ouando, Carrefour Cinquantenaire - Route de Pobè, 02 BP 353 Porto-Novo, BENIN <br>Tél. : +229 01 97
            97 05 37 - E-mail : ersuma@ohada.org -
            Web : <font color="#0563c1"><u><a href="https://ersuma.ohada.org">https://ersuma.ohada.org</a></u>
            </font>
        </div>
    </footer>

</div>
</body>

</html>
