 
<table>
    <thead>  
        <tr style="margin: 0;">
            <th colspan="9" style="margin:0; padding:5px; border: 1px solid black; text-align: center;"><b>LISTE DES PAIEMENTS</b></th>
        </tr>
        <tr style="margin: 0;">
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:110px;"><b>N°</b></th>
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:160px;"><b>Prénoms et Nom</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:160px;"><b>E-mail</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:250px;"><b>Réference</b></th>   
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:60px;"><b>Montant</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:100px;"><b>Formation</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:120px;"><b>Statut</b></th>  
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:160px;"><b>Mode de règlement</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:70px;"><b>Date</b></th>   
        </tr> 
    </thead>

<tbody>  

@php
$i=0;
@endphp 
 @foreach ($liste_paiements as $item)
<tr style="margin:0;"> 
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{$i++}}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{$item->auditeur}}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{$item->email}}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{$item->reference_paiement}}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ $item->montant }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->code_formation }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->statut_customer }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->modepaiement }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ $item->date_customer }}</td> 
</tr>   
@endforeach
</tbody>
</table>
