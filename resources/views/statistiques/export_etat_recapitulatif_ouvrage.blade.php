<table>
    <thead>
        <tr>
            <th colspan="5" style="text-align: center">
                <b> {{$ouvrage->libelle}}  </b>
            </th>
        </tr> 
    </thead>

<tbody>  

@php
$i=0;
$total = 0;
@endphp
<tr style="margin:0;">
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;"><b>N° d'ordre</b></td>
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center; width:150px;"><b>Identité</b></td>
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center; width:150px;"><b>Email</b></td>  
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center; width:150px;"><b>Montant payé</b></td>  
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center; width:150px;"><b>Observation</b></td>   
</tr>
@foreach($details_paiements as $item) 
    @php
        $total = $total + $item->montant;
    @endphp
<tr style="margin:0;">
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;"><b>{{ ++$i  }}</b></td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->getPaiement->prenoms ? $item->getPaiement->prenoms : '' }} {{ $item->getPaiement->nom ? $item->getPaiement->nom : '' }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->getPaiement->email }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">{{ $item->montant }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">{{$item->getPaiement->id_modepaiement ? $item->getPaiement->getModepaiement->libelle : ''}}</td>   
</tr> 
@endforeach  
<tr style="margin:0;">
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;" colspan="3"><b>Total</b></td> 
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">{{ $total }}</td>  
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;"></td>   
</tr>
</tbody>
</table>