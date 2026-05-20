<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>

<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title></title>
    <style type="text/css">
        :root {
            --primary-color: #1571c6;
            --secondary-color: #9cc2e5;
            --text-color: #000000;
            --font-size: 12pt;
        }

        body {
            font-family: Tahoma, sans-serif;
            font-size: var(--font-size);
            margin: 0;
            padding: 0;
            z-index: 100;
        }

       
        .watermark-container {
    position: fixed;
    top: 4.5cm;      
    left: 4.5cm;     
    width: 20.7cm;    
    height: 12cm;
    text-align: center;
    opacity: 0.06;
    z-index: -1000;
}

.watermark-container img {
    width: 500px;     
    height: auto;
}

        @page {
            size: 21cm 29.7cm landscape;
    margin: 1.27cm 1.75cm;
        
        }

        p {
            margin-bottom: 0.25cm;
            direction: ltr;
            line-height: 115%;
            text-align: justify;

            orphans: 2;
            widows: 2;
            background: transparent
        }

        p.western {

            so-language: fr-FR
        }

        p.cjk {}

        p.ctl {

            font-size: 10pt
        }

        div,
        table,
        pr,
        span,
        p {}

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


<body lang="fr-BJ" link="#0563c1" vlink="#800000" dir="ltr" style="font-family: Tahoma, sans-serif;z-index: 100;">
    <div class="watermark-container">
        <img src="{{ public_path('img/logo/Logo_OHADA_DSC.png') }}" alt="Filigrane">
    </div>

    <div style="z-index: 10; background:none;">


        <div style="z-index:100;">
            <div style="float:left; width:72%;">

                <div style="z-index:100; max-width:92%; padding: 10px 0;">

                    <div style="z-index:100;float:left; width: 105px; margin:-10px 0 0 0; height:92px; ">
                        <img src="img/logo/logo-ohada.png" alt="" height="85px" style="height: 85px; ">
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
            <img src="img/logo/Logo_ERSUMA-1.jpg" alt="logo" style="height: 70px; margin-top:8px;">
        </div>
<div style="clear:both;"></div>



<div style="z-index: 300; margin:0;">

            <!-- div opacity -->
        
                 <div
                        style="z-index: 100;clear:both; display:block; margin: 0; text-align: center;line-height: 1.2;font-size: 28px; color:#233468; font-weight:bold; border-bottom:  8px solid #233468; padding:0px;">

                        <div style="margin: 8px 0; padding:3px 6px;">
                            Diplômes de Spécialité et Certificats OHADA
                        </div>
                    </div>

                    <div
                        style="z-index: 100;clear:both; display:block; margin:0; text-align: center;line-height: 1.2;font-size: 15pt; color:#006fc0; font-weight:bold;">

                        <div style="margin: 10px 0;">
                            <small style="color: black;">{{$formation->theme}}</small> <br>
                             REGISTRE DES NOTES - {{$candidat->annee_academique}} - {{$candidat->annee_academique+1}}
                        </div>
                    </div>
                <!--  -->
                <div style="clear:both; display:block;"></div> 
                <!-- <div
                    style="line-height: 1.3; margin:10px 0; padding:4px; text-align:center;text-transform:uppercase;background:#1571c6; color:#fff;">

                  <b>Détails des notes</b>

                </div>-->
                <div style="clear:both; ">

                    <table cellspacing="0" cellpadding="0"
                        style=" cellspacing : 0; cellpadding:0; border:0; width: 100%; z-index:1000;  padding: 10px 0 0 0; margin: 0; font-size:10pt;">
                        <thead>
                            <tr style="margin:0;background-color: #abd">
                                <th style="padding:5px; margin:0; border: 1px solid black; text-align:center;width:5%;">N°
                                </th>
                                <th style="padding:5px; margin:0; border: 1px solid black; text-align:left; width:25%;">Nom
                                </th>
                                <th style="padding:5px; margin:0; border: 1px solid black; width:30%;">Prénom(s)</th>
                                <th style="padding:5px; margin:0; border: 1px solid black;width:10%;">Moyenne</th>
                                <th style="padding:5px; margin:0; border: 1px solid black;width:15%;">Décision</th>
                                <th style="padding:5px; margin:0; border: 1px solid black;width:15%;">Mention</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php  
                                $i = 1;  
                            @endphp

                            @foreach ($participants as $item)  

                            @if (checkAjourne($item->code_formation,$item->email) || $item->moyenne < 12)
                                <tr style="margin:0; font-size:10.5pt; background:#dab" class="table-dark">
                            @else 
                                <tr style="margin:0; font-size:10.5pt;" class="table-dark">

                            @endif 
                                    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">
                                        <b>{{ $i++ }}</b>
                                    </td>
                                    <td style="margin:0; padding:5px; border: 1px solid black; text-wrap: wrap;">
                                        {{ $item->nom }}
                                    </td>
                                    <td style="margin:0; padding:5px; border: 1px solid black; text-wrap: wrap;">
                                        {{ $item->prenoms }}
                                    </td>
                                    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">
                                        {{ number_format($item->moyenne, 2, ',', '') }}
                                    </td>
                                    
                                    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">
                                         @if (checkAjourne($item->code_formation,$item->email))
                                        @php 
                                        echo 'Refusé' ;
                                        @endphp 
                                        @else
                                            @php
                                            $moyenne = $item->moyenne;
                                            echo $moyenne < 12 ? 'Refusé' : 'Admis';
                                            @endphp 
                                        @endif

                                    </td>

                                    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">
                                        <!-- vérifier si ajourné -->
                                        @if (checkAjourne($item->code_formation,$item->email))
                                            N/A 
                                        @else
                                            @php
                                                $moyenne = $item->moyenne;
                                                if ($moyenne < 12) {
                                                    echo 'N/A';
                                                } elseif ($moyenne >= 12 && $moyenne < 14) {
                                                    echo 'ASSEZ BIEN';
                                                } elseif ($moyenne >= 14 && $moyenne < 16) {
                                                    echo 'BIEN';
                                                } else {
                                                    echo 'TRÈS BIEN';
                                                }
                                            @endphp 
                                        @endif 
                                    </td> 
                                </tr>
                            @endforeach 
                        </tbody>

                    </table>


                </div>
                <div style="clear:both; "> 
                </div>

        </div><!-- fermeture div background-image -->

       


        <footer>

                            <div style="clear:both; display:block; margin:5px 0 10px 0; width: 100%; ">
                                <div style="float:left;">
                                    &nbsp;
                                </div>
                                <div style=" float:right; padding-right:5px;font-size: 11pt; margin-bottom: 15px;">
                                    Fait à Porto-Novo, le
                                        {{ \Carbon\Carbon::parse(date('d-m-Y'))->isoFormat('DD MMMM YYYY') }}
                                </div>
                            </div>
                            <div style="clear:both; padding:0px; margin:10px 0 0 0;">
                                <table style="width: 100%;  padding: 0; margin: 0 ;">
                                    <tbody>
                                        <tr>
                                            <td style="width:100px;vertical-align: bottom;">
                                                <div style="margin-bottom: 1px; margin-left: 10px;0;">

                                                    @php 

                                                        $QrCodeText = "Relevé de notes globale :\n" 
                                                                . "Thème : {$formation->theme}\n"
                                                                . "Code Formation : {$formation->code_formation}\n";
                                                    @endphp     
                                                    
                                                         <img width="75" height="75"
                                                         src="data:image/png;base64, {!! base64_encode(
                                                             QrCode::encoding('UTF-8')->format('svg')->size(95)->generate($QrCodeText),
                                                         ) !!} ">
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

    </div>
</body>

</html>
