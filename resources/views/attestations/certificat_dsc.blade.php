<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <title>Certificat de formation - ERSUMA</title>
    <style>
        body { font-family: Tahoma, sans-serif; font-size: 12pt; margin: 0; padding: 0; }
.page-background {
    position: absolute;
    top: -0.73cm;    
    left: -1.27cm;   
    width: 29.7cm;   
    height: 21.0cm;  
    z-index: -1000; 
}
        @page { size: A4 landscape; margin: 0.73cm 1.27cm 0.97cm; }
        header { width: 100%; background: white; z-index: 10; margin-bottom: 10px; }
        .content { margin-top: 20px; margin-bottom: 15px; }
        .footer-table { width: 100%; margin-top: 30px; }
        .signature-zone { text-align: right; padding-right: 50px; line-height: 1.5; }
        .signature-space { height: 85px; }
    </style>
</head>
<body>
<img src="img/BG_Certificats-2026.jpg" class="page-background" alt="">
    <table style="width: 100%; padding: 0; margin: 0;">
        <tbody>
            <tr>
                <td style="vertical-align:top;">
                    <header>
                        <div style="z-index:100;">
                            <div style="float:left; width:72%;">
                                <div style="z-index:100; max-width:92%; padding: 10px 0;">
                                    <div style="z-index:100; float:left; width: 105px; margin:-10px 0 0 0; height:92px;">
                                        <img src="img/logo/logo-ohada.png" alt="Logo OHADA" height="85px" style="height: 85px;">
                                    </div>
                                    <div style="line-height:1.3; margin: -5px 0 0 95px; padding:0px; top:0; font-size:11px; text-align:justify; border-bottom:5px double #2681ca; padding-bottom:8px;">
                                        Organisation &nbsp;&nbsp;pour &nbsp;l’Harmonisation &nbsp;en &nbsp;Afrique &nbsp;du &nbsp;Droit &nbsp;des &nbsp;&nbsp;Affaires <br>
                                        Organization &nbsp;&nbsp; for&nbsp; &nbsp;the&nbsp; &nbsp;Harmonization &nbsp;&nbsp; of &nbsp;&nbsp;Business &nbsp;&nbsp; Law &nbsp;in &nbsp;&nbsp; Africa <br>
                                        Organización para la Armonización en África del Derecho de los Negocios <br>
                                        Organização &nbsp;para &nbsp;a &nbsp;Harmonização &nbsp;em &nbsp;África &nbsp;do &nbsp;Direito dos Negócios
                                    </div>
                                </div>
                            </div>
                            <div style="float:right; width:28%; text-align:right;">
                                <img src="img/logo/Logo_ERSUMA-1.jpg" alt="Logo ERSUMA" style="height: 70px; margin-top:8px;">
                            </div>
                        </div>
                    </header>
                </td>
            </tr>
        </tbody>
    </table> 
    <div class="content">
        <div style="text-align: center; line-height: 1.2; font-size: 28px; color:#233468; font-weight:bold; margin-bottom: 10px;">
            L'Ecole Régionale Supérieure de la Magistrature (ERSUMA)
        </div> 

        <div style="text-align:justify; padding:0px 15px; line-height: 1.4; font-size:11pt;">
            <b>Vu</b> le Traité relatif à l’Harmonisation du droit des Affaires en Afrique signé à Port-Louis le 17 octobre 1993, tel que révisé à Québec le 17 octobre 2008 notamment en son article 41 ; <br>
            <b>Vu</b> le Statut de l’ERSUMA du 03 octobre 1995 révisé par les Règlements n° 004/2009/CM/OHADA du 19 décembre 2009, n° 003/2011/CM du 17 juin 2011 et n° 02/2016/CM/OHADA du 28 octobre 2016 portant Statut révisé de l’ERSUMA ; <br>
            <b>Vu</b> la Décision n° 002/2019/CA/ERSUMA/OHADA du 24 août 2019 portant création et organisation du Diplôme de Spécialité en Droit OHADA à l'ERSUMA ;<br>
            <b>Vu</b> la Décision n° 017-2025 du 10 octobre 2025 portant délibération et publication du résultat final aux examens de fin d’année au titre de l’année académique 2024-2025 ; <br>
            
            <div style="text-align:center; font-size: 14pt; margin: 15px 0 5px 0;">Délivre à :</div>

            <div style="text-align:center;">
                <span style="font-weight:bold; font-size:22pt; color:#233468;">
                    {{ trim($participant->prenoms) }} {{ trim($participant->nom) }}
                </span> 
                <br>
                @if (!empty($participant->ddn) || !empty($participant->lieu_naissance))
                    <span style="font-weight:bold; font-size:14pt; color:#000;">
                        Né(e) le {{ $participant->ddn }} à {{ $participant->lieu_naissance }}
                    </span>
                @endif 
                <br><br>
                
                <span style="font-weight:bold; font-size:18pt; color:#000;">
                    le <span style="color:#233468;">Certificat en {{ $formation->theme }}</span>
                </span>
                
                <p style="font-size: 16pt; margin-top: 15px;">
                    Mention : <b>
                    @php
                        $moyenne = $participant->moyenne ?? 0;
                        if ($moyenne >= 16) { echo "Très Bien"; }
                        elseif ($moyenne >= 14) { echo "Bien"; }
                        elseif ($moyenne >= 12) { echo "Assez-Bien"; }
                        elseif ($moyenne >= 10) { echo "Passable"; }
                        else { echo "Insuffisant"; }
                    @endphp
                    </b>
                </p>
            </div>
        </div>
    </div>
    <table class="footer-table">
        <tbody>
            <tr>
                <td style="width: 50%; vertical-align: bottom;">
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <div class="signature-zone">
                        <span style="font-size: 11pt;">Porto-Novo, le 16 octobre 2025</span><br>
                        <span style="font-weight: bold; font-size: 13pt; color:#233468;">Le Directeur Général,</span>
                        <div class="signature-space"></div>
                        <span style="font-weight: bold; font-size: 13pt;">Dr Karel Osiris Coffi DOGUÉ (LL.D.)</span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>
