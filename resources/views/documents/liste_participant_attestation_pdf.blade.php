<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SIGWEB-ERSUMA</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
</head>
<style>
    body {
        font-family: Tahoma, sans-serif;
        font-size: 11px;
        line-height: 1.5;
    }
    #header,
    #footer {
        position: fixed;
        left: 0;
        right: 0;
        color: #aaa;
        font-size: 0.9em;
    }
    #header {
        top: 0;
        border-bottom: 0.1pt solid #aaa;
    }
    #footer {
        bottom: 0;
        border-top: 0.1pt solid #aaa;
    }
    .page {
        width:100%;
        display:block;
        padding:10px 20px;
        margin:0 auto;
    }
    .nav {
        padding:0 10px;
        display:block;
        margin:0 ;
    }
    .nav .nav-left {
        float: left;
        text-align: center;
        max-width: 30%;
        padding:5px 10px;
    }
    .nav .nav-right {
        text-align: center;
        left:30%;
    }
    .contenu {
        clear:both;
        display:block;
        margin:0 auto;
    }
    .bloc-titre {
        clear:both;
        display:block;
        background-color: #eff5ef;
        padding:5px 10px;
        margin:10px 0 10px 0;
        font-size: 120%;
        font-weight: bold;
        text-transform: uppercase;
    }
    .bloc-texte {
        display:block;
        padding:5px 0 5px 10px;
        margin:0;
        line-height: 1.8;
    }
    .bloc-texte .image {
        float: right;
        top : 0;
        text-align: center;
        max-width: 30%;
        padding:0;
        margin:0;
    }
    .bloc-texte .doc {
        text-align: center;
        max-width: 30%;
        padding:5px;
        margin:0 1% 20px 0;
        border:1px solid #aaa;
    }
    h2, h3, h4 {
        font-weight: bold;
        text-transform: uppercase;
        margin:5px;

    }
    h2 {
        font-size: 150%;
        margin-bottom: 15px;
    }
    h3 {
        font-size: 125%;
        margin-bottom: 15px;
    }
    h4 {
        font-size: 110%;
        margin-top: 8px;
    }
    table {
        border-spacing: 0;
        border-collapse: collapse;
    }
    td,
    th {
        padding: 0;
    }
    table {
        background-color: transparent;
    }
    caption {
        padding-top: 8px;
        padding-bottom: 8px;
        color: #777;
        text-align: left;
    }
    th {
        text-align: left;
    }
    tr:nth-child(even) {background-color: #eee}
    tr:nth-child(odd) {background-color: #fff}
    .table {
        width: 100%;
        max-width: 100%;
        margin-bottom: 20px;
    }
    .table > thead > tr > th,
    .table > tbody > tr > th,
    .table > tfoot > tr > th,
    .table > thead > tr > td,
    .table > tbody > tr > td,
    .table > tfoot > tr > td {
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
        border-top: 1px solid #ddd;
    }
    .table > thead > tr > th {
        vertical-align: bottom;
        border-bottom: 2px solid #ddd;
    }
    .table > caption + thead > tr:first-child > th,
    .table > colgroup + thead > tr:first-child > th,
    .table > thead:first-child > tr:first-child > th,
    .table > caption + thead > tr:first-child > td,
    .table > colgroup + thead > tr:first-child > td,
    .table > thead:first-child > tr:first-child > td {
        border-top: 0;
    }
    .table > tbody + tbody {
        border-top: 2px solid #ddd;
    }
    .table .table {
        background-color: #fff;
    }
    .table-condensed > thead > tr > th,
    .table-condensed > tbody > tr > th,
    .table-condensed > tfoot > tr > th,
    .table-condensed > thead > tr > td,
    .table-condensed > tbody > tr > td,
    .table-condensed > tfoot > tr > td {
        padding: 5px;
    }
    .table-bordered {
        border: 1px solid #ddd;
    }
    .table-bordered > thead > tr > th,
    .table-bordered > tbody > tr > th,
    .table-bordered > tfoot > tr > th,
    .table-bordered > thead > tr > td,
    .table-bordered > tbody > tr > td,
    .table-bordered > tfoot > tr > td {
        border: 1px solid #ddd;
    }
    .table-bordered > thead > tr > th,
    .table-bordered > thead > tr > td {
        border-bottom-width: 2px;
    }
    .table-striped > tbody > tr:nth-of-type(odd) {
        background-color: #f9f9f9;
    }
    .table-hover > tbody > tr:hover {
        background-color: #f5f5f5;
    }
    table col[class*="col-"] {
        position: static;
        display: table-column;
        float: none;
    }
    table td[class*="col-"],
    table th[class*="col-"] {
        position: static;
        display: table-cell;
        float: none;
    }
    .table > thead > tr > td.active,
    .table > tbody > tr > td.active,
    .table > tfoot > tr > td.active,
    .table > thead > tr > th.active,
    .table > tbody > tr > th.active,
    .table > tfoot > tr > th.active,
    .table > thead > tr.active > td,
    .table > tbody > tr.active > td,
    .table > tfoot > tr.active > td,
    .table > thead > tr.active > th,
    .table > tbody > tr.active > th,
    .table > tfoot > tr.active > th {
        background-color: #f5f5f5;
    }
    .table-hover > tbody > tr > td.active:hover,
    .table-hover > tbody > tr > th.active:hover,
    .table-hover > tbody > tr.active:hover > td,
    .table-hover > tbody > tr:hover > .active,
    .table-hover > tbody > tr.active:hover > th {
        background-color: #e8e8e8;
    }
    .table > thead > tr > td.success,
    .table > tbody > tr > td.success,
    .table > tfoot > tr > td.success,
    .table > thead > tr > th.success,
    .table > tbody > tr > th.success,
    .table > tfoot > tr > th.success,
    .table > thead > tr.success > td,
    .table > tbody > tr.success > td,
    .table > tfoot > tr.success > td,
    .table > thead > tr.success > th,
    .table > tbody > tr.success > th,
    .table > tfoot > tr.success > th {
        background-color: #dff0d8;
    }
    .table-hover > tbody > tr > td.success:hover,
    .table-hover > tbody > tr > th.success:hover,
    .table-hover > tbody > tr.success:hover > td,
    .table-hover > tbody > tr:hover > .success,
    .table-hover > tbody > tr.success:hover > th {
        background-color: #d0e9c6;
    }
    .table > thead > tr > td.info,
    .table > tbody > tr > td.info,
    .table > tfoot > tr > td.info,
    .table > thead > tr > th.info,
    .table > tbody > tr > th.info,
    .table > tfoot > tr > th.info,
    .table > thead > tr.info > td,
    .table > tbody > tr.info > td,
    .table > tfoot > tr.info > td,
    .table > thead > tr.info > th,
    .table > tbody > tr.info > th,
    .table > tfoot > tr.info > th {
        background-color: #d9edf7;
    }
    .table-hover > tbody > tr > td.info:hover,
    .table-hover > tbody > tr > th.info:hover,
    .table-hover > tbody > tr.info:hover > td,
    .table-hover > tbody > tr:hover > .info,
    .table-hover > tbody > tr.info:hover > th {
        background-color: #c4e3f3;
    }
    .table > thead > tr > td.warning,
    .table > tbody > tr > td.warning,
    .table > tfoot > tr > td.warning,
    .table > thead > tr > th.warning,
    .table > tbody > tr > th.warning,
    .table > tfoot > tr > th.warning,
    .table > thead > tr.warning > td,
    .table > tbody > tr.warning > td,
    .table > tfoot > tr.warning > td,
    .table > thead > tr.warning > th,
    .table > tbody > tr.warning > th,
    .table > tfoot > tr.warning > th {
        background-color: #fcf8e3;
    }
    .table-hover > tbody > tr > td.warning:hover,
    .table-hover > tbody > tr > th.warning:hover,
    .table-hover > tbody > tr.warning:hover > td,
    .table-hover > tbody > tr:hover > .warning,
    .table-hover > tbody > tr.warning:hover > th {
        background-color: #faf2cc;
    }
    .table > thead > tr > td.danger,
    .table > tbody > tr > td.danger,
    .table > tfoot > tr > td.danger,
    .table > thead > tr > th.danger,
    .table > tbody > tr > th.danger,
    .table > tfoot > tr > th.danger,
    .table > thead > tr.danger > td,
    .table > tbody > tr.danger > td,
    .table > tfoot > tr.danger > td,
    .table > thead > tr.danger > th,
    .table > tbody > tr.danger > th,
    .table > tfoot > tr.danger > th {
        background-color: #f2dede;
    }
    .table-hover > tbody > tr > td.danger:hover,
    .table-hover > tbody > tr > th.danger:hover,
    .table-hover > tbody > tr.danger:hover > td,
    .table-hover > tbody > tr:hover > .danger,
    .table-hover > tbody > tr.danger:hover > th {
        background-color: #ebcccc;
    }
    .table-responsive {
        min-height: .01%;
        overflow-x: auto;
    }
</style>
<body>
@php
    $duree_formation = 0; $i = 0;
    $theme = "";
    $duree_enregistrement = getDureeEnregistrement($code_formation);
@endphp

@foreach($durees as $duree)
    @php
        $theme = $duree->getFormation->theme;
    @endphp
@endforeach

@foreach($duree_session as $duree)
    @php
        $duree_formation = $duree_formation + $duree->duree_session ;
    @endphp
@endforeach

@php
    if ($duree_enregistrement > 0) {
      # code...
      $duree_reel = $duree_enregistrement ;
    } else {
      # code...
      $duree_reel = $duree_formation ;
    }

      $scoring = (int)(($duree_reel * 2) / 3);
@endphp

<div class="nav">
    <div class="nav-left">
        <img src="img/logo/logo-ohada-new500.png" alt="" height="75px" style="height: 75px;">
    </div>
    <div class="nav-right" style="text-align: center;padding:5px 10px;">
        <h6 style="color:#1979D7;text-align: center;">{{ $theme }} ( {{ $code_formation }} )</h6>
        <h6 style="text-align: center;">LISTE DES PARTICIPANTS ELIGIBLES</h6>
        <p style="text-align: center; font-size:120%"><span >Durée session :</span>
            @if ($duree_enregistrement > 0)
                {{ $duree_enregistrement }}
            @else
                {{ $duree_formation }}
            @endif
            min <span style="color: black;">- Durée minimale de présence :</span> {{ $scoring }} min</p>
    </div>
</div>
<div>

    <table class="table table-bordered">
        <thead>
        <tr class="text-dark text-center" style="background-color: #bdd1e6;">
            <th>N°</th>
            <th>Nom</th>
            <th>Prénoms</th>
            <th>E-Mail</th>
            <th>Pays</th>
            <th>Durée</th>
        </tr>
        </thead>
        <tbody>

        @foreach ($liste_participants as $participant)
            <!-- calculer le timing pour chaque participant -->

            @if($participant->temps_presence >= $scoring)
                @php
                    $i = $i+1;
                @endphp
                <tr>
                    <td class="text-center"><b>{{ $i }}</b></td>
                    <td style="text-transform: uppercase">{{ $participant->nom }}</td>
                    <td style="text-transform: uppercase">{{ $participant->prenoms }}</td>
                    <td>{{ $participant->email }}</td>
                    <td>{{ $participant->pays }}</td>
                    <td>{{ $participant->temps_presence }}</td>
                </tr>
            @endif

        @endforeach
        </tbody>
    </table>

</div>

</body>
</html>
