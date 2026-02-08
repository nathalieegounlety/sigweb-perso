<table>
    <thead>
        <tr>
            <th colspan="7" style="text-align: center">
                <b> Liste des paiements validés </b>
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
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center; width:250px;"><b>Prénoms et Nom</b></td>
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center; width:150px;"><b>Reference</b></td>  
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center; width:100px;"><b>Montant</b></td>  
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center; width:250px;"><b>Code transaction</b></td>  
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center; width:150px;"><b>Type</b></td>  
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center; width:150px;"><b>Date</b></td>    
</tr>

@foreach($liste_paiement_partiel_dsc as $item) 
    @php
        $total = $total + $item->montant_paye;
    @endphp
<tr style="margin:0;">
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;"><b>{{ ++$i  }}</b></td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->prenoms }} {{ $item->nom }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->reference }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">{{ $item->montant_paye }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">{{ $item->num_transaction }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">DSC</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">{{ \Carbon\Carbon::parse($item->updated_at)->format('d-m-Y H:i:s') }}</td>    
</tr> 
@endforeach  

@foreach($liste_paiement as $paiement) 
    @php
        $total = $total + $paiement->montant_total;
    @endphp
<tr style="margin:0;">
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;"><b>{{ ++$i  }}</b></td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $paiement->prenoms }} {{ $paiement->nom }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $paiement->reference }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">{{ $paiement->montant_total }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">{{ $paiement->num_transaction }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">{{ $paiement->type }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">{{ \Carbon\Carbon::parse($paiement->created_at)->format('d-m-Y H:i:s') }}</td>    
</tr> 
@endforeach  

@foreach($liste_paiement as $paiement) 
    @php
        $total = $total + $paiement->montant_total;
    @endphp
<tr style="margin:0;">
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;"><b>{{ ++$i  }}</b></td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $paiement->prenoms }} {{ $paiement->nom }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $paiement->reference }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">{{ $paiement->montant_total }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">{{ $paiement->num_transaction }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">{{ $paiement->type }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">{{ \Carbon\Carbon::parse($paiement->created_at)->format('d-m-Y H:i:s') }}</td>    
</tr> 
@endforeach  
<tr style="margin:0;">
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;" colspan="3"><b>Total</b></td> 
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">{{ $total }}</td>  
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;"></td>     
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;"></td>     
    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;"></td>   
</tr>
</tbody>
</table>