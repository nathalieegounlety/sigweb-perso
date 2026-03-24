 
<table>
    <thead>  
        <tr style="margin: 0;">
            <th colspan="11" style="margin:10px 0; padding:10px 5px; border: 0;1px solid black; text-align: center;font-size:26px;">
                <b>SIGWEB ERSUMA - LISTE DES FORMATEURS</b>
            </th>
        </tr>
        <tr style="margin: 0;">
            <td style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align:center;" width="40px"><b>N°</b></td>
            <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align: center;"><b>Civilité</b></th>
            <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align: center;" width="130px"><b>Nom</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align: center;" width="150px"><b>Prénoms</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align: center;"><b>Genre</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align: center;" width="150px"><b>Téléphone</b></th>  
            <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align: center;" width="220px"><b>E-mail</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align: center;" width="300px"><b>Profession/Fonction</b></th>   
            <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align: center;" width="300px"><b>Spécialité</b></th>   
            <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align: center;" width="300px"><b>Organisation</b></th>  
            <th style="margin:0; padding:5px; border: 1px solid black; background:#698fc0; text-align: center;" width="100px"><b>Nationalité</b></th>  
        </tr> 
    </thead>

<tbody>   
 
@php
$i=0;
@endphp

@foreach($liste as $item) 
 
<tr style="margin:0;"> 
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ ++$i }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ $item->titre }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->nom }}</td>    
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->prenoms }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ $item->genre }}</td>   
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->telephone }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->email }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;" width="300px">{{ $item->fonction }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;" width="300px">{{ $item->specialite }}</td> 
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{ $item->organisme }}</td> 
<td style="margin:0; padding:5px; border: 1px solid black; text-align: center;">{{$item->nationalite ? $item->getNationalite->nationalite : ''}}</td>
</tr>  
@endforeach
  

</tbody>
</table>
