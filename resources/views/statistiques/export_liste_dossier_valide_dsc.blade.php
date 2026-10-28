 
<table>
    <thead>  
        <tr style="margin: 0;">
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:110px;"><b>Date inscription</b></th>
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:160px;"><b>Prénoms</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:160px;"><b>Nom</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:250px;"><b>Profession/Fonction</b></th>   
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:60px;"><b>Code Pays</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:100px;"><b>Nationnalité</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:120px;"><b>Téléphone</b></th>  
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:160px;"><b>E-mail</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:70px;"><b>Genre</b></th>  
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:110px;"><b>Code formation</b></th>
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:70px;"><b>Mode</b></th>  
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:70px;"><b>Montant à payer</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:70px;"><b>Montant payé</b></th> 
            {{-- <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:70px;"><b>Statut</b></th>    --}}
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:70px;"><b>Observation</b></th>  
        </tr> 
    </thead>

<tbody>  

@php
$i=0;
@endphp

@foreach($liste_inscrits as $item) 
 
<tr style="margin:0;"> 
    <td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d') }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->prenoms ? $item->prenoms : '' }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->nom ? $item->nom : '' }}</td>    
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->intitule_poste ? $item->intitule_poste : '' }}</td>   
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ $item->code_pays ? $item->code_pays : '' }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->nationalite ? $item->nationalite : '' }}</td>    
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->telephone ? $item->telephone : '' }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->email ? $item->email : '' }}</td>    
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ $item->genre ? $item->genre : '' }}</td> 
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ $item->code_formation ? $item->code_formation : '' }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ $item->mode ? $item->mode : '' }}</td> 
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ $item->montant_total_a_payer ? number_format($item->montant_total_a_payer, 0, ',', ' ') : 0 }}</td> 
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ $item->montant_total_paye ? number_format($item->montant_total_paye, 0, ',', ' ') : 0 }}</td>
{{-- <td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ $item->statut_paiement ? $item->statut_paiement : 'VIDE' }}</td>  --}}
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ $item->observations ? $item->observations : '' }}</td> 
</tr>  
@endforeach

</tbody>
</table>
