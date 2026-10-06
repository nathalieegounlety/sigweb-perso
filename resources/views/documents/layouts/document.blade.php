@php
    $qrTexte = 'REFERENCE : ' . trim($__env->yieldContent('qr_reference'))
        . ' Prénoms & nom : ' . ($auditeur->prenoms ?? '') . ' ' . ($auditeur->nom ?? '');
@endphp
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <title>@yield('titre_document')</title>
    <style type="text/css">
        /*
         * MISE EN PAGE
         * La place de l'en-tête et du pied est réservée dans @page :
         * contrairement à une marge CSS, elle s'applique à CHAQUE page.
         *   haut : 1,27 cm (marge) + 2,5 cm (en-tête) = 3,8 cm
         *   bas  : 1,2 cm  (marge) + 4,6 cm (pied)    = 5,8 cm
         * Pour ajuster : modifier ensemble @page, header (top) et footer (bottom).
         */
        @page {
            size: 21cm 29.7cm;
            margin: 3.8cm @yield('marge_laterale', '1.75cm') 5.8cm @yield('marge_laterale', '1.75cm');
        }

        body {
            font-family: Tahoma, sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.3;
            color: #333;
        }

        p {
            margin: 0 0 0.25cm;
            line-height: 115%;
            text-align: justify;
            orphans: 2;
            widows: 2;
        }

        a:link {
            color: #1571c6;
            text-decoration: underline;
        }

        /* En-tête et pied : DANS les marges de @page (top / bottom négatifs).
           position: fixed => DomPDF les répète sur chaque page. */
        header {
            position: fixed;
            top: -3.3cm;
            left: 0;
            right: 0;
            height: 2.5cm;
        }

        footer {
            position: fixed;
            bottom: -5.3cm;
            left: 0;
            right: 0;
            height: 4.6cm;
        }

        /* Filigrane : bloc séparé du contenu (pas d'opacité héritée), sur chaque page */
        .background-image {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('img/logo/logo-ohada-new500.png') no-repeat center center;
            opacity: 0.1;
        }

        /* ===== Éléments communs ===== */
        .titre {
            text-align: center;
            line-height: 115%;
            font-size: 22pt;
            color: #006fc0;
            font-weight: bold;
            margin: 0 0 12px;
        }

        .bandeau {
            line-height: 1.3;
            margin: 10px 0;
            padding: 4px;
            text-align: center;
            text-transform: uppercase;
            background: #1571c6;
            color: #fff;
        }

        /* Bloc N° / Date / Destinataire */
        .info {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        .info td {
            padding: 4px;
        }

        .info .cellule-titre {
            background: #1571c6;
            color: #fff;
            text-align: center;
        }

        /* ===== Tableau des détails ===== */
        .details {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5pt;
        }

        /* Indispensable pour que DomPDF répète l'en-tête du tableau sur chaque page */
        .details thead {
            display: table-header-group;
        }

        /* Une ligne n'est jamais coupée en deux entre deux pages */
        .details tr {
            page-break-inside: avoid;
        }

        .details th,
        .details td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: middle;
        }

        .details thead th,
        .details .total th {
            background-color: #abd;
            color: #000;
            font-weight: bold;
        }

        /* Colonne vide à droite des totaux (reçu classique) */
        .details .total th.vide {
            border: 0;
            border-left: 1px solid #000;
            background: none;
        }

        .details tr.total-premier th.vide {
            border-top: 1px solid #000;
        }

        .c { text-align: center; }
        .r { text-align: right; }
        .l { text-align: left; }

        /* Montant en lettres + signature : toujours ensemble sur la même page */
        .bloc-final {
            page-break-inside: avoid;
            margin-top: 10px;
        }


        @yield('styles')
        
    </style>
    

</head>

<body lang="fr-BJ" link="#0563c1" vlink="#800000" dir="ltr">
    <div class="background-image"></div>

    {{-- ===================== EN-TÊTE (répété sur chaque page) ===================== --}}
    
    <header>
        <div style="z-index: 30;">
            <div style="float:left;width:86%;">
                <div style="border-bottom:5px double #2681ca; max-width:75%; padding: 0 0 10px 0;">
                    <div style="float:left; width: 90px; margin:0px; height:90px;">
                        <img src="img/logo/logo-ohada.png" alt="" height="75px" style="height: 75px;">
                    </div>
                    <div style="font-size:65%; line-height:1.5; margin: -25px 0 0 95px; padding:0px; top:0; font-family: Tahoma, serif; font-weight:bold; text-align:justify;">
                        Organisation &nbsp;pour &nbsp;l’Harmonisation &nbsp;en &nbsp;Afrique &nbsp;du &nbsp;Droit &nbsp;des &nbsp;Affaires <br>
                        Organization &nbsp;&nbsp; for&nbsp; &nbsp;the&nbsp; &nbsp;Harmonization &nbsp;&nbsp; of &nbsp;&nbsp;Business
                        &nbsp;&nbsp; Law &nbsp;in &nbsp;&nbsp; Africa <br>
                        Organización para la Armonización en África del Derecho de los Negocios <br>
                        Organização &nbsp;para &nbsp;a &nbsp;Harmonização &nbsp;em &nbsp;África &nbsp;do &nbsp;Direito dos Negócios
                    </div>
                </div>
            </div>
            <div style="float:right;width:14%;position:relative; text-align:right;">
                <img src="img/logo/attestation_flyers2.png" alt="" height="80px">
            </div>
        </div>
    </header>

    {{-- ===================== PIED DE PAGE (répété sur chaque page) ===================== --}}
    <footer>
        <table style="width: 100%; border-collapse: collapse; margin: 0;">
            <tbody>
                <tr>
                    <td style="width: 100px; vertical-align: top; padding: 0;">
                        <img width="85" height="85"
                            src="data:image/png;base64, {!! base64_encode(QrCode::encoding('UTF-8')->format('svg')->size(95)->generate($qrTexte)) !!}">
                    </td>
                    <td style="font-size: 8pt; vertical-align: top; padding: 0;">
                        <b>NB :</b> Traité relatif à l'Harmonisation du Droit des Affaires en Afrique <br>
                        <b>Article 51 :</b><br>
                        L'OHADA, ses avoirs, ses biens et ses revenus ainsi que les opérations autorisées par le présent Traité sont exonérés de tous impôts, taxes et droits de douane.<br>
                        L'OHADA est également exempte de toute obligation relative au recouvrement ou au paiement d'impôts, de taxes ou de droits de douane.
                    </td>
                </tr>
            </tbody>
        </table>
        <div style="margin-top: 8px; padding: 5px 0; text-align: center; font-size: 9pt; border-top: 8px solid #9cc2e5; border-bottom: 8px solid #1571c6;">
            ERSUMA, Ouando, Carrefour Cinquantenaire - Route de Pobè, 02 BP 353 Porto-Novo, BENIN <br>
            Tél. : +229 01 97 97 05 37 - E-mail : ersuma@ohada.org -
            Web : <a href="https://ersuma.ohada.org">https://ersuma.ohada.org</a>
        </div>
    </footer>

    {{-- ===================== CONTENU DU DOCUMENT ===================== --}}
    <main>
        @yield('contenu')
    </main>
</body>
</html>
