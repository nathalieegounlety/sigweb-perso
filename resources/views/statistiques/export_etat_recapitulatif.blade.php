@php
$fm_gratuite = false;
@endphp
@foreach($formation->getTarification($formation->code_formation) as $tarification) 
@if ($tarification->get_grille_libelle == "GRATUIT")
  @php
      $fm_gratuite = true;
  @endphp  
@endif
@endforeach  
{{--
    
    <div style="z-index:30;">
        <div style="float:left;width:85%;">

        <div style=" border-bottom:5px double #2681ca; max-width:75%; padding: 0 0 10px 0; ">

        <div style="float:left; width: 90px; margin:0px; height:90px; ">
        <img src="img/logo/entete-ohada2024.jpg" alt="" height="75px" style="height: 75px;">
        </div>
        <div style=" font-size:65%; line-height:1.5; margin: -25px 0 0 95px; padding:0px; top:0; font-family: Tahoma, serif; font-weight:bold; text-align:justify;">
        Organisation  pour  l’Harmonisation en Afrique du Droit des &nbsp;Affaires <br>
        Organization &nbsp;&nbsp; for&nbsp;   the&nbsp;  Harmonization &nbsp;&nbsp;  of   Business &nbsp;&nbsp;  Law   in &nbsp;&nbsp; Africa <br>
        Organización  para la Armonización en África del Derecho  &nbsp;&nbsp;Mercantil <br>
        Organização para a Harmonização  em África do Direito dos Negócios
        </div>
        </div>
      </div>
        <div style="float:right;width:15%;position:relative; text-align:right;">
        <img src="img/logo/attestation_flyers2.png" alt="" height="80px" style="height: 80px;">
        </div>
    </div>
--}}
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
            <th colspan="10"></th>
        </tr>
        <tr>
            <th colspan="10" style="text-align: center; line-height:1.5pt; background:#dad5d5; padding:15px; height:90px; font-size:17px;">
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
            <th colspan="10" style="text-align: center; background:#83abd8; border:2px solid #000; line-height:1.5pt; padding:10px; min-height:35px; font-size:16px;">
                <h3><b>ETAT RECAPITULATIF</b></h3>
            </th>
        </tr>

        <tr>
            <th colspan="10"></th>
        </tr>
        <tr>
            <th colspan="10" style="text-align: left; line-height:1.5pt; padding:10px; min-height:35px; font-size:16px;">
                <h3><b>Statistiques de participation</b></h3>
            </th>
        </tr>
        <tr>
            <th colspan="2" style="text-align: center;min-height:35px; ">&nbsp;</th>
            <th style="text-align: center; background:#83abd8; border:2px solid #000; line-height:1.5pt; padding:10px; font-size:16px;">
                INSCRITS
            </th>
            <th style="text-align: center; background:#83abd8; border:2px solid #000; line-height:1.5pt; padding:10px; font-size:16px;">
                GRATUIT
            </th>
            <th colspan="2" style="text-align: center; background:#83abd8; border:2px solid #000; line-height:1.5pt; padding:10px; font-size:16px;">
                PAYANT
            </th>

            @if($fm_gratuite)
            <th colspan="2" style="text-align: center; background:#83abd8; border:2px solid #000; line-height:1.5pt; padding:10px; font-size:16px;">
                ATTESTATION
            </th>
            @endif

            <th colspan="2" style="text-align: center; background:#83abd8; border:2px solid #000; line-height:1.5pt; padding:10px; font-size:16px;">
                PARTICIPANTS
            </th>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;  border:1px solid #000; line-height:1.5pt; padding:10px; font-size:14px;">PRESENTIEL</td>
            {{-- Nbre Inscrits en présentiel --}}
            <td style="text-align: center;  border:1px solid #000; line-height:1.5pt; padding:10px; font-size:14px;"><b>{{$stats->inscrits_presentiel}}</b></td>
            {{-- Participants à titre gratuit en présentiel --}}
            <td style="text-align: center;  border:1px solid #000; line-height:1.5pt; padding:10px; font-size:14px;">@if($formation->is_gratuite) <b>{{$stats->participants_presentiel}}</b> @else <b>{{$stats->gratuits_presentiel}}</b> @endif</td>
            {{-- Participants à titre payant en présentiel --}}
            <td colspan="2" style="text-align: center;  border:1px solid #000; line-height:1.5pt; padding:10px; font-size:14px;">@if($formation->is_gratuite) <b>0</b> @else <b>{{$stats->inscrits_presentiel - $stats->gratuits_presentiel}}</b> @endif</td>
            {{-- Participants effectifs en présentiel --}}
            @if($formation->is_gratuite)<td colspan="2" style="text-align: center;  border:1px solid #000; line-height:1.5pt; padding:10px; font-size:14px;"><b>{{$stats->attestations_presentiel}}</b></td>@endif
            {{-- Participants effectifs en présentiel --}}
            <td colspan="2" style="text-align: center;  border:1px solid #000; line-height:1.5pt; padding:10px; font-size:14px;"><b>{{$stats->participants_presentiel}}</b></td>
            
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;  border:1px solid #000; line-height:1.5pt; padding:10px; font-size:14px;">VISIO</td>
            {{-- Nbre Inscrits en VISIO --}}
            <td style="text-align: center;  border:1px solid #000; line-height:1.5pt; padding:10px; font-size:14px;"><b>{{$stats->inscrits_visio}}</b></td>
            {{-- Participants à titre gratuit en VISIO --}}
            <td style="text-align: center;  border:1px solid #000; line-height:1.5pt; padding:10px; font-size:14px;">@if($formation->is_gratuite) <b>{{ $stats->total_participants - $stats->participants_presentiel }}</b> @else <b>{{$stats->gratuits_visio}}</b> @endif</td>
            {{-- Participants à titre payant en VISIO --}}
            <td colspan="2" style="text-align: center;  border:1px solid #000; line-height:1.5pt; padding:10px; font-size:14px;">@if($formation->is_gratuite) <b>0</b> @else <b>{{$stats->inscrits_visio - $stats->gratuits_visio}}</b> @endif</td>
            {{-- Participants effectifs en présentiel --}}
            @if($formation->is_gratuite)<td colspan="2" style="text-align: center;  border:1px solid #000; line-height:1.5pt; padding:10px; font-size:14px;"><b>{{$stats->attestations_visio}}</b></td>@endif
            {{-- Participants effectifs en VISIO --}}
            <td colspan="2" style="text-align: center;  border:1px solid #000; line-height:1.5pt; padding:10px; font-size:14px;"><b>{{$stats->participants_visio}}</b></td>
            
        </tr>
        <tr>
            <th colspan="2" style="text-align: center; background:#83abd8; border:2px solid #000; line-height:1.5pt; padding:10px; font-size:16px;">
                TOTAL
            </th>
            {{-- Total des inscrits --}}
            <th style="text-align: center; background:#83abd8; border:2px solid #000; line-height:1.5pt; padding:10px; font-size:16px;">
                <b>{{$stats->total_inscrits}}</b>
            </th>
            {{-- Total Participants à titre gratuit --}}
            <th style="text-align: center; background:#83abd8; border:2px solid #000; line-height:1.5pt; padding:10px; font-size:16px;">
                @if($formation->is_gratuite) <b>{{$stats->total_participants}}</b> @else <b>{{$stats->total_insc_gratuite}}</b> @endif
            </th>
            {{-- Total Participants à titre payant --}}
            <th colspan="2" style="text-align: center; background:#83abd8; border:2px solid #000; line-height:1.5pt; padding:10px; font-size:16px;">
                @if($formation->is_gratuite) <b>0</b> @else <b>{{$stats->total_inscrits - $stats->total_insc_gratuite}}</b> @endif
            </th>
            {{-- Total Participants effectifs --}}
            @if($formation->is_gratuite)
            <th colspan="2" style="text-align: center; background:#83abd8; border:2px solid #000; line-height:1.5pt; padding:10px; font-size:16px;">
                <b>{{$stats->total_attestations}}</b>
                
            </th>
            @endif
            {{-- Total Participants effectifs --}}
            <th colspan="2" style="text-align: center; background:#83abd8; border:2px solid #000; line-height:1.5pt; padding:10px; font-size:16px;">
                <b>{{$stats->total_participants}}</b>                
            </th>
        </tr> 
        <tr>
            <th colspan="10"></th>
        </tr>
        <tr>
            <th colspan="10" style="text-align: left; line-height:1.5pt; padding:10px; min-height:35px; font-size:16px;">
                <h3><b>Etat de paiement</b></h3>
            </th>
        </tr>
        <tr style="margin: 0;min-height:35px;">
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:35px;"><b>N°</b></th>
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:180px;"><b>Nom</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:180px;"><b>Prénoms</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:200px;"><b>E-mail</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:100px;"><b>Tel</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:100px;"><b>Mode</b></th> 
            @if ($formation->is_formation_dsc)
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:70px;"><b>Frais d'inscription</b></th>
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:70px;"><b>Frais de participation</b></th>                
            @endif
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:70px;"><b>Réduction</b></th>  
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:70px;"><b>Montant total</b></th>              
            
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:80px;"><b>Statut</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:100px;"><b>Montant Payé</b></th>  
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:100px;"><b>Montant Restant</b></th>  
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:100px;"><b>A participé</b></th>  
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:180px;"><b>Obs.</b></th> 
            <th></th>
        </tr> 
    </thead>

<tbody>  

@php
    $i=0; 
    $total_frais_inscription = 0;
    $total_frais_scolarite = 0;
    $total_frais = 0;
    $total_frais_paye = 0;
    $total_frais_restant = 0; 
@endphp
@foreach($participants as $item) 
<!-- vérifier si le participant a effectuer un paiement sur la plateforme -->  
<tr style="margin:0; backround-color:#c6f7e6;">
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;"><b>{{ ++$i  }}</b></td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->nom }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->prenoms }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->email }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->telephone }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">{{ $item->type_formation }}</td>  
@if ($formation->is_formation_dsc)
@php
    
    $total_frais_inscription = $total_frais_inscription + $item->frais_inscription_paye + $item->frais_inscription_restant;
    $total_frais_scolarite = $total_frais_scolarite + $item->frais_scolarite_paye + $item->frais_scolarite_restant ; 
@endphp
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">{{ $item->frais_inscription_paye + $item->frais_inscription_restant }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">{{ $item->frais_scolarite_paye + $item->frais_scolarite_restant }}</td>      
@endif 
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">
    @if ($item->have_reduction_dsc)
        5%
    @else
        {{ $item->reduction_pourcentage }}%
    @endif
</td> 
<td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">
    @php 
    $total_frais = $item->montant_total + $total_frais; 
    @endphp
    {{ $item->montant_total }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">{{$item->statut_paiement ? $item->statut_paiement : 'IMPAYE'}}</td>   
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">
    @if ($item->type_formation === 'PROFESSIONNELS' || $item->type_formation === 'ETUDIANTS' || $item->type_formation === 'INSCRIPTION')
    @php  
    $total_frais_paye = $total_frais_paye + $item->frais_inscription_paye + $item->frais_scolarite_paye; 
    @endphp
        {{ $item->frais_inscription_paye + $item->frais_scolarite_paye }}
    @else
    @php  
    $total_frais_paye = $total_frais_paye + $item->montant_paye ; 
    @endphp

        {{ $item->montant_paye }}
    @endif
</td>   
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">
    @if ($item->type_formation === 'PROFESSIONNELS' || $item->type_formation === 'ETUDIANTS' || $item->type_formation === 'INSCRIPTION')
        @php 
            $total_frais_restant = $total_frais_restant + $item->frais_inscription_restant + $item->frais_scolarite_restant; 
        @endphp
        {{ $item->frais_inscription_restant + $item->frais_scolarite_restant }}
    @else
        @php 
            $total_frais_restant = $total_frais_restant + $item->montant_restant; 
        @endphp
        {{ $item->montant_restant }}
    @endif
</td>   
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">
    @if ($item->a_participe)
        Oui
    @else
        Non
    @endif
</td>   
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">{{$item->observations}}</td>   
</tr>  
@endforeach   

<tr style="margin:0;min-height:35px;">
    <td colspan="6" style="margin:0; padding:5px; background:#83abd8; border: 1px solid black; text-align:center;"><b>Total</b></td> 
    @if ($formation->is_formation_dsc)
    <td style="margin:0; padding:5px; background:#83abd8; border: 1px solid black; text-align:center;"><b>{{ $total_frais_inscription }}</b></td> 
    <td style="margin:0; padding:5px; background:#83abd8; border: 1px solid black; text-align:center;"><b>{{ $total_frais_scolarite }}</b></td>         
    @endif
    <td style="margin:0; padding:5px; background:#83abd8; border: 1px solid black; text-align:center;"><b></b></td> 
    <td style="margin:0; padding:5px; background:#83abd8; border: 1px solid black; text-align:center;"><b>{{ $total_frais }}</b></td>  
    <td style="margin:0; padding:5px; background:#83abd8; border: 1px solid black; text-align:center;"><b></b></td>   
    <td style="margin:0; padding:5px; background:#83abd8; border: 1px solid black; text-align:center;">{{ $total_frais_paye }}</td>  
    <td style="margin:0; padding:5px; background:#83abd8; border: 1px solid black; text-align:center;">{{ $total_frais_restant }}</td>   
    <td style="margin:0; padding:5px; background:#83abd8; border: 1px solid black; text-align:center;"></td>   
    <td style="margin:0; padding:5px; background:#83abd8; border: 1px solid black; text-align:center;"></td>   
</tr>
</tbody>
</table>
