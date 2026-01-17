<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <title>Reçu de paiement</title>
    <style type="text/css">
        @page {
            size: 21cm 29.7cm;
            margin: 1.27cm 1.75cm 1.27cm 1.75cm;
            font-family: Tahoma, serif;
        }

        body {
            font-family: Tahoma, sans-serif;
            line-height: 1.5;
        }

        p {
            margin-bottom: 0.25cm;
            direction: ltr;
            line-height: 115%;
            text-align: justify;
            orphans: 2;
            widows: 2;
            background: transparent;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid black;
            padding: 5px;
        }

        @media print {
            header {
                position: fixed;
                top: 0;
            }
            footer {
                position: fixed;
                bottom: 0;
            }
        }
    </style>
</head>

<body lang="fr-BJ" dir="ltr">
    <header>
        <div style="width: 86%; float: left;">
            <div style="border-bottom: 5px double #2681ca; max-width: 75%; padding: 0 0 10px 0;">
                <div style="float: left; width: 90px; margin: 0; height: 90px;">
                    <img src="img/logo/logo-ohada.png" alt="Logo OHADA" style="height: 75px;">
                </div>
                <div style="font-size: 65%; line-height: 1.5; margin: -25px 0 0 95px; padding: 0; font-weight: bold; text-align: justify;">
                    Organisation pour l’Harmonisation en Afrique du Droit des Affaires<br>
                    Organization for the Harmonization of Business Law in Africa<br>
                    Organización para la Armonización en África del Derecho de los Negocios<br>
                    Organização para a Harmonização em África do Direito dos Negócios
                </div>
            </div>
        </div>
        <div style="width: 14%; float: right; text-align: right;">
            <img src="img/logo/attestation_flyers2.png" alt="Attestation" style="height: 80px;">
        </div>
    </header>

    <main>
        <div style="text-align: center; margin: 50px 0 20px; font-size: 22pt; color: #006fc0; font-weight: bold;">
            REÇU DE PAIEMENT
        </div>

        <table style="width: 100%; margin: 20px 0;">
            <tr>
                <td style="width: 45%; padding: 4px; background: #1571c6; color: #fff; text-align: center;">
                    N° RECU
                </td>
                <td style="width: 20%;">&nbsp;</td>
                <td style="width: 35%; padding: 4px; background: #1571c6; color: #fff; text-align: center;">
                    DATE
                </td>
            </tr>
            <tr>
                <td style="text-align: center;">
                    <b>{{ $paiement->reference }}</b>
                </td>
                <td>&nbsp;</td>
                <td style="text-align: center;">
                    <b>{{ \Carbon\Carbon::parse($paiement->updated_at)->format('d/m/Y') }}</b>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="padding: 4px; background: #1571c6; color: #fff; text-align: center;">
                    <b>DELIVRE A</b>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="line-height: 1.5; padding: 10px;">
                    <b>{{ $auditeur->prenoms ?? '' }} <span style="text-transform: uppercase;">{{ $auditeur->nom ?? '' }}</span></b><br>
                    @if ($auditeur->telephone)
                        Tél : {{ $auditeur->telephone }} -
                    @endif
                    E-mail : {{ $auditeur->email }}
                    @if ($auditeur->ville || $auditeur->pays)
                        <br>
                        {{ $auditeur->ville ?? '' }}, {{ $auditeur->pays ?? '' }}
                    @endif
                </td>
            </tr>
        </table>

        <div style="text-align: center; margin: 10px 0; padding: 4px; background: #1571c6; color: #fff; line-height: 1.3;">
            <b>Détails du paiement</b>
        </div>

        <table style="width: 100%; margin: 10px 0; font-size: 10.5pt;">
            <thead>
                <tr style="background-color: #abd;">
                    <th style="padding: 5px;">#</th>
                    <th style="padding: 5px; text-align: left;">Formation</th>
                    <th style="padding: 5px;">Frais</th>
                    <th style="padding: 5px;">Réf. transaction</th>
                    <th style="padding: 5px;">Réduction</th>
                    <th style="padding: 5px;">Montant</th>
                    <th style="padding: 5px;">Etat</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $i = 0;
                    $montant_paye = 0;
                    $montant_restant = 0;
                @endphp
                @foreach ($details_paiement as $item)
                    @php
                        $i++;
                        $pourcentage = $item->pourcentage ? $item->pourcentage / 100 : 0;
                        $montant = $item->montant - ($item->montant * $pourcentage);
                        if ($item->statut_paiement == "PAYE") {
                            $montant_paye += $montant;
                        } else {
                            $montant_restant += $montant;
                        }
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $i }}</td>
                        <td><b>{{ $item->code_formation }}</b> - {{ $item->theme }}</td>
                        <td style="text-align: center;">{{ $item->libelle }}</td>
                        <td>{{ $item->num_transaction }}</td>
                        <td style="text-align: center;">{{ $item->pourcentage ?? '-' }}</td>
                        <td style="text-align: right;">{{ number_format($montant, 0, ',', '.') }}</td>
                        <td>{{ $item->statut_paiement }}</td>
                    </tr>
                @endforeach
                <tr style="background-color: #abd;">
                    <th colspan="5" style="padding: 5px;">Montant total hors taxe</th>
                    <th style="padding: 5px; text-align: right;"><b>{{ number_format($paiement->montant_total, 0, ',', '.') }}</b></th>
                    <th style="border: none;"></th>
                </tr>
                <tr style="background-color: #abd;">
                    <th colspan="5" style="padding: 5px;">Montant payé</th>
                    <th style="padding: 5px; text-align: right;"><b>{{ number_format($montant_paye, 0, ',', '.') }}</b></th>
                    <th style="border: none;"></th>
                </tr>
                <tr style="background-color: #abd;">
                    <th colspan="5" style="padding: 5px;">Montant restant</th>
                    <th style="padding: 5px; text-align: right;"><b>{{ number_format($montant_restant, 0, ',', '.') }}</b></th>
                    <th style="border: none;"></th>
                </tr>
            </tbody>
        </table>

        <p>
            <span style="text-decoration: underline;">Montant total en lettres</span> :
            <b>{{ $formatter->format($paiement->montant_total) }} francs CFA</b> (XOF).
        </p>

        <table style="width: 100%; margin: 20px 0;">
            <tr>
                <td style="width: 40%;">&nbsp;</td>
                <td>&nbsp;</td>
                <td style="width: 45%; text-align: center;">
                    @php
                        $signataire = getSignataireByDate(\Carbon\Carbon::parse(date('Y-m-d'))->format('Y-m-d'));
                    @endphp
                    <div>
                        <img src="{{ public_path('storage/signature/' . $signataire->signature) }}" alt="Signature" style="height: 105px; opacity: 0.9;">
                    </div>
                    <div>
                        <b><u>{{ $signataire->titre }} {{ $signataire->nom }} {{ $signataire->prenoms }}</u></b><br>
                        <small>{{ $signataire->fonction }}</small>
                    </div>
                </td>
            </tr>
        </table>
    </main>

    <footer>
        <div style="margin-top: 20px;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 95px;">
                        <img width="80" height="80" src="data:image/png;base64, {!! base64_encode(QrCode::encoding('UTF-8')->format('svg')->size(95)->generate('REFERENCE : ' . $paiement->reference . ' Prénoms & nom : ' . ($auditeur->prenoms ?? '') . ' ' . ($auditeur->nom ?? ''))) !!}">
                    </td>
                    <td style="font-size: 8pt;">
                        <b>NB :</b> Traité relatif à l'Harmonisation du Droit des Affaires en Afrique<br>
                        <span><b>Article 51: </b></span><br>
                        <span>L'OHADA, ses avoirs, ses biens et ses revenus ainsi que les opérations autorisées par le présent Traité sont exonérés de tous impôts, taxes et droits de douane.</span><br>
                        <span>L'OHADA est également exempte de toute obligation relative au recouvrement ou au paiement d'impôts, de taxes ou de droits de douane.</span>
                    </td>
                </tr>
            </table>
        </div>
        <div style="width: 100%; background: #fff; border-top: 8px solid #9cc2e5; border-bottom: 8px solid #1571c6; margin: 10px 0; text-align: center; font-size: 9pt; padding: 5px;">
            ERSUMA, Ouando, Carrefour Cinquantenaire - Route de Pobè, 02 BP 353 Porto-Novo, BENIN<br>
            Tél. : +229 01 97 97 05 37 - E-mail : ersuma@ohada.org -
            Web : <a href="https://ersuma.ohada.org" style="color: #0563c1; text-decoration: underline;">https://ersuma.ohada.org</a>
        </div>
    </footer>
</body>
</html>
