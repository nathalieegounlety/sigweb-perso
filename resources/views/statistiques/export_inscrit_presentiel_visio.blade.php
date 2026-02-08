 
<table>
    <thead>  
        <tr style="margin: 0;">
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:40px;"><b>N°</b></th>
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:110px;"><b>Code formation</b></th>
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:160px;"><b>Nom</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:160px;"><b>Prénoms</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:250px;"><b>Profession/Fonction</b></th>   
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:60px;"><b>Code Pays</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:100px;"><b>Nationnalité</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:120px;"><b>Téléphone</b></th>  
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:160px;"><b>E-mail</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:70px;"><b>Genre</b></th>  
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:100px;"><b>Mode</b></th>  
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:100px;"><b>Montant</b></th>  
            
        </tr> 
    </thead>

<tbody>  

@php
$i=0;
@endphp

@foreach($liste_inscrits_presentiel_visio as $item) 
 
<tr style="margin:0;">
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;"><b>{{ ++$i  }}</b></td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ $item->code_formation }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->getAuditeur->nom ? $item->getAuditeur->nom : '' }}</td>    
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->getAuditeur->prenoms ? $item->getAuditeur->prenoms : '' }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->getAuditeur->intitule_poste ? $item->getAuditeur->intitule_poste : '' }}</td>   
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ $item->getAuditeur->code_pays ? $item->getAuditeur->code_pays : '' }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->getAuditeur->nationalite ? $item->getAuditeur->nationalite : '' }}</td>    
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->getAuditeur->telephone ? $item->getAuditeur->telephone : '' }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->email ? $item->email : '' }}</td>    
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ $item->getAuditeur->genre ? $item->getAuditeur->genre : '' }}</td> 
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">
    @if ($item->tarif_formation_id)
        @if ($item->getTarifFormation->getGrilletarifaire->type_tarif == "GRATUIT")
            <!-- Vérifier si la formation est présentiel ou visio -->
            @if ($item->getFormation->fm_visio)
                VISIO
            @else
                PRESENTIEL
            @endif
        @else
            {{$item->getTarifFormation->getGrilletarifaire->type_tarif}}
        @endif
    @else
        
    @endif
</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ $item->tarif_formation_id ? $item->getTarifFormation->getGrilletarifaire->montant : '' }}</td> 
</tr>  
@endforeach

</tbody>
</table>
