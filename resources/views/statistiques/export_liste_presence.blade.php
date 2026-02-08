 
<table>
    <thead>  
        <tr>        
            <th>
                &nbsp;
            </th>          
            <th colspan="5" style="text-align: left; min-height:95px;">
                <img src="img/logo/entete-ohada2024.jpg" alt="Entete OHADA" height="100px">
            </th>      
            <th> 
                &nbsp;
            </th>      
            <th colspan="2" style="text-align: center;">
                <img src="img/logo/attestation_flyers2.png" alt="Flyer ERSUMA" height="95px">
            </th>
        </tr>
        <tr>
            <th colspan="9"></th>
        </tr>
        <tr>
            <th colspan="9" style="text-align: center; line-height:1.5pt; background:#dad5d5; padding:15px;height:90px; font-size:17px;">
                <b>
                Session  {{$formation->code_formation}} 
                    @if ($formation->date_debut == $formation->date_fin)
                       du
                       {{ \Carbon\Carbon::parse($formation->date_debut)->isoFormat('DD MMM YYYY') }}
                       @else
                       du
                       {{ \Carbon\Carbon::parse($formation->date_debut)->isoFormat('DD MMM') }}
                       au
                       {{ \Carbon\Carbon::parse($formation->date_fin)->isoFormat('DD MMM YYYY') }}
                    @endif
                    <br>
                    Thème :  {{$formation->theme}}
                </b>
            </th>
        </tr>

        <tr>
            <th colspan="9" style="text-align: center; background:#83abd8; border:2px solid #000; line-height:1.5pt; padding:10px; min-height:35px; font-size:16px;">
                <h3><b>LISTE DE PRESENCE</b></h3>
            </th>
        </tr>

        <tr>
            <th colspan="9"></th>
        </tr>
        <tr style="margin: 0; height:35px;">
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:35px;"><b>N°</b></th>
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:180px;"><b>Nom</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:180px;"><b>Prénoms</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:250px;"><b>Profession/Fonction</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:70px;"><b>Code Pays</b></th>
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:100px;"><b>Téléphone</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:200px;"><b>E-mail</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:70px;"><b>Genre</b></th>
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:100px;"><b>Emargement</b></th>              
    </thead>
<tbody>  
@php
$i=0;
@endphp

@foreach($liste_inscrits_presentiel as $item) 
<tr style="margin:0;"> 
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;"><b>{{ ++$i  }}</b></td>
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->nom ? $item->nom : '' }}</td>    
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->prenoms ? $item->prenoms : '' }}</td>
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->intitule_poste ? $item->intitule_poste : '' }}</td>   
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">{{ $item->code_pays ? $item->code_pays : '' }}</td>     
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->telephone ? $item->telephone : '' }}</td>
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->email ? $item->email : '' }}</td>     
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">{{ $item->genre ? $item->genre : '' }}</td>     
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;"></td> 
</tr>  
@endforeach

</tbody>
</table>
