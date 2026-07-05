 
<table>
    <thead>  
        <tr style="margin: 0;">
            <th colspan="11" style="margin:10px 0; padding:10px 5px; border: 0;1px solid black; text-align: center;font-size:26px;">
                <b>SIGWEB ERSUMA - LISTE DES INSCRIPTIONS ET PAIEMENTS DU {{ \Carbon\Carbon::parse($date_debut)->format('d/m/Y') }} AU {{ \Carbon\Carbon::parse($date_fin)->format('d/m/Y') }} </b>
            </th>
        </tr>
        <tr style="margin: 0;">
            <td style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;" width="40px"><b>N°</b></td>
            <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align: center;"><b>Auditeur</b></th>
            <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align: center;" width="130px"><b>E-mail</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align: center;" width="150px"><b>Référence</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align: center;"><b>Montant</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align: center;" width="150px"><b>Formation</b></th>  
            <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align: center;" width="220px"><b>Statut</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align: center;" width="300px"><b>Mode</b></th>   
            <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align: center;" width="300px"><b>Date</b></th>    
        </tr> 
    </thead>

<tbody>   
 
@php
$i=0;
@endphp

@foreach($liste as $item) 
 
<tr style="margin:0;"> 
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ ++$i }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center; width:300px">{{ $item->auditeur }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left; width:300px">{{ $item->email }}</td>    
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->reference_paiement }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ $item->montant }}</td>   
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->code_formation }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->statut_paiement }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->modepaiement }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ \Carbon\Carbon::parse($item->updated_at)->format('d/m/Y H:i:s') }}</td>  
</tr>  
@endforeach
  

</tbody>
</table>
