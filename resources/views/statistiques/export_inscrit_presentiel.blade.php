 
<table>
    <thead>  
        <tr style="margin: 0;">
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:110px;"><b>Code formation</b></th>
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:160px;"><b>Prénoms</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:160px;"><b>Nom</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:250px;"><b>Profession/Fonction</b></th>   
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:60px;"><b>Code Pays</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:100px;"><b>Nationnalité</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:120px;"><b>Téléphone</b></th>  
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:160px;"><b>E-mail</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:70px;"><b>Genre</b></th>  
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:80px;"><b>A participé</b></th>
        </tr> 
    </thead>

<tbody>  

@php
$i=0;
@endphp

@foreach($liste_inscrits_presentiel as $item) 
 
<tr style="margin:0;"> 
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ $item->code_formation }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->prenoms ? $item->prenoms : '' }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->nom ? $item->nom : '' }}</td>    
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->intitule_poste ? $item->intitule_poste : '' }}</td>   
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ $item->code_pays ? $item->code_pays : '' }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->nationalite ? $item->nationalite : '' }}</td>    
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->telephone ? $item->telephone : '' }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->email ? $item->email : '' }}</td>    
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ $item->genre ? $item->genre : '' }}</td> 
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;"></td> 
</tr>  
@endforeach

</tbody>
</table>
