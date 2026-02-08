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
            <td style="text-align: center;  border:1px solid #000; line-height:1.5pt; padding:10px; font-size:14px;"><b>{{$total_inscrit_presentiel}}</b></td>
            {{-- Participants à titre gratuit en présentiel --}}
            <td style="text-align: center;  border:1px solid #000; line-height:1.5pt; padding:10px; font-size:14px;">@if($fm_gratuite) <b>{{$total_participant_presentiel->count()}}</b> @else <b>{{getInscriptionGratuitePresentiel($formation->code_formation)}}</b> @endif</td>
            {{-- Participants à titre payant en présentiel --}}
            <td colspan="2" style="text-align: center;  border:1px solid #000; line-height:1.5pt; padding:10px; font-size:14px;">@if($fm_gratuite) <b>0</b> @else <b>{{$total_inscrit_presentiel - getInscriptionGratuitePresentiel($formation->code_formation)}}</b> @endif</td>
            {{-- Participants effectifs en présentiel --}}
            @if($fm_gratuite)<td colspan="2" style="text-align: center;  border:1px solid #000; line-height:1.5pt; padding:10px; font-size:14px;"><b>{{getPaiementAll($formation->code_formation)->count()}}</b></td>@endif
            {{-- Participants effectifs en présentiel --}}
            <td colspan="2" style="text-align: center;  border:1px solid #000; line-height:1.5pt; padding:10px; font-size:14px;"><b>{{$total_participant_presentiel}}</b></td>
            
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;  border:1px solid #000; line-height:1.5pt; padding:10px; font-size:14px;">VISIO</td>
            {{-- Nbre Inscrits en VISIO --}}
            <td style="text-align: center;  border:1px solid #000; line-height:1.5pt; padding:10px; font-size:14px;"><b>{{$total_inscrit_visio}}</b></td>
            {{-- Participants à titre gratuit en VISIO --}}
            <td style="text-align: center;  border:1px solid #000; line-height:1.5pt; padding:10px; font-size:14px;">@if($fm_gratuite) <b>{{$liste_participants->count() - $total_participant_presentiel->count()}}</b> @else <b>{{getInscriptionGratuiteVisio($formation->code_formation)}}</b> @endif</td>
            {{-- Participants à titre payant en VISIO --}}
            <td colspan="2" style="text-align: center;  border:1px solid #000; line-height:1.5pt; padding:10px; font-size:14px;">@if($fm_gratuite) <b>0</b> @else <b>{{$total_inscrit_visio - getInscriptionGratuiteVisio($formation->code_formation)}}</b> @endif</td>
            {{-- Participants effectifs en présentiel --}}
            @if($fm_gratuite)<td colspan="2" style="text-align: center;  border:1px solid #000; line-height:1.5pt; padding:10px; font-size:14px;"><b>{{getPaiementAll($formation->code_formation)->count()}}</b></td>@endif
            {{-- Participants effectifs en VISIO --}}
            <td colspan="2" style="text-align: center;  border:1px solid #000; line-height:1.5pt; padding:10px; font-size:14px;"><b>{{$total_participant - $total_participant_presentiel}}</b></td>
            
        </tr>
        <tr>
            <th colspan="2" style="text-align: center; background:#83abd8; border:2px solid #000; line-height:1.5pt; padding:10px; font-size:16px;">
                TOTAL
            </th>
            {{-- Total des inscrits --}}
            <th style="text-align: center; background:#83abd8; border:2px solid #000; line-height:1.5pt; padding:10px; font-size:16px;">
                <b>{{$total_inscrit}}</b>
            </th>
            {{-- Total Participants à titre gratuit --}}
            <th style="text-align: center; background:#83abd8; border:2px solid #000; line-height:1.5pt; padding:10px; font-size:16px;">
                @if($fm_gratuite) <b>{{$liste_participants->count()}}</b> @else <b>{{getInscriptionGratuite($formation->code_formation)}}</b> @endif
            </th>
            {{-- Total Participants à titre payant --}}
            <th colspan="2" style="text-align: center; background:#83abd8; border:2px solid #000; line-height:1.5pt; padding:10px; font-size:16px;">
                @if($fm_gratuite) <b>0</b> @else <b>{{$total_inscrit - getInscriptionGratuite($formation->code_formation)}}</b> @endif
            </th>
            {{-- Total Participants effectifs --}}
            @if($fm_gratuite)
            <th colspan="2" style="text-align: center; background:#83abd8; border:2px solid #000; line-height:1.5pt; padding:10px; font-size:16px;">
                <b>{{getPaiementAll($formation->code_formation)->count()}}</b>
                
            </th>
            @endif
            {{-- Total Participants effectifs --}}
            <th colspan="2" style="text-align: center; background:#83abd8; border:2px solid #000; line-height:1.5pt; padding:10px; font-size:16px;">
                <b>{{$total_participant}}</b>                
            </th>
        </tr>
        {{--
            
        <tr>
            <th colspan="10"></th>
        </tr>
        <tr>
            <th colspan="2" style="border: 1px solid black; text-align:left;">TOTAL DES INSCRITS : </th>
            <th style="border: 1px solid black; text-align:center;"><b>{{$total_inscrit}}</b></th>
        </tr> 
        <tr>
            <th colspan="2" style="border: 1px solid black; text-align:left;">PARTICIPANTS A TITRE GRATUIT : </th>
            <th style="border: 1px solid black; text-align:center;"><b>@if($fm_gratuite) <b>{{$liste_participants->count()}}</b> @else <b>{{getInscriptionGratuite($formation->code_formation)}}</b> @endif</b></th>
        </tr> 
        <tr>
            <th colspan="2" style="border: 1px solid black; text-align:left;">PARTICIPANTS A TITRE PAYANT EN PRESENTIEL : </th>
            <th style="border: 1px solid black; text-align:center;"><b>{{$total_inscrit}}</b></th>
        </tr> 
        <tr>
            <th colspan="2" style="border: 1px solid black; text-align:left;">PARTICIPANTS A TITRE PAYANT EN VISIO : </th>
            <th style="border: 1px solid black; text-align:center;"><b>@if($fm_gratuite) <b>0</b> @else <b>{{$total_inscrit - getInscriptionGratuite($formation->code_formation)}}</b> @endif</b></th>
        </tr> 
        <tr>
            <th colspan="2" style="border: 1px solid black; text-align:left;">PARTICIPANTS EFFECTIFS</th>
            <th style="border: 1px solid black; text-align:center;"><b>{{$liste_participants->count()}}</b></th>
        </tr> 
        --}}
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
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:70px;"><b>Part.?</b></th>
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:80px;"><b>Statut</b></th> 
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:100px;"><b>Montant</b></th>  
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:100px;"><b>Payé</b></th>  
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:100px;"><b>Restant</b></th>  
            <th style="margin:0; padding:5px; border: 1px solid black; background:#83abd8; text-align: center; width:180px;"><b>Obs.</b></th> 
            <th></th>
        </tr> 
    </thead>

<tbody>  

@php
    $i=0;
    $somme_montant_total_paye = 0;
    $somme_montant_total = 0;
    $somme_montant_total_restant = 0;
    $total_paye = 0;
@endphp
@foreach($liste_participants as $item) 
<!-- vérifier si le participant a effectuer un paiement sur la plateforme -->
@if (getPaiement($formation->code_formation,$item->email,$item->tarif_formation_id)->count() > 0)
@foreach (getPaiement($formation->code_formation,$item->email,$item->tarif_formation_id) as $data) 
<tr style="margin:0; backround-color:#c6f7e6;">
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;"><b>{{ ++$i  }}</b></td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->nom }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->prenoms }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->email }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->telephone }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">{{ $item->type_formation }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">    
    @if (checkAParticiper($formation->code_formation,$item->email))
      OUI                                      
    @else
      NON                                      
    @endif                               
</td> 
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">PAYE</td> 
<td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">{{ $data->montant_paye+$data->montant_restant }}</td>  
@if ($item->type_formation = "ETUDIANTS" || $item->type_formation = "PROFESSIONNELS")
<td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">{{ $data->montant_paye }}</td>  
@else
<td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">{{ $data->montant_paye }}</td>  
@endif 
@php 
    $somme_montant_total_paye = $somme_montant_total_paye + $data->montant_paye;
    $somme_montant_total_restant = $somme_montant_total_restant + $data->montant_paye;
    $somme_montant_total = $somme_montant_total + $somme_montant_total_restant + $somme_montant_total_paye;
@endphp
<td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">{{ $data->montant_restant }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">{{$data->modepaiement}}</td>   
</tr> 
@if ($data->montant_paye)
    
@endif 
@endforeach 
@elseif(getPaiementratuit($formation->code_formation,$item->email,$item->tarif_formation_id)->count() > 0)
<tr style="margin:0; backround-color:#aba3a3;">
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;"><b>{{ ++$i  }}</b></td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->nom }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->prenoms }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->email }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->telephone }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">{{ $item->type_formation }}</td> 
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">    
    @if (checkAParticiper($formation->code_formation,$item->email))
      OUI                                      
    @else
      NON                                      
    @endif                               
</td> 
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">GRATUIT</td>  
@php
                                      $getMontants = getMontantFm($formation->code_formation,$item->email);
                                      $mtFm = 0; $mtPaye = 0; $mtRestant = 0;
                                  @endphp
                                  @foreach ($getMontants as $mt)
                                      @php
                                          $mtPaye = $mtPaye+$mt->montant_paye; 
                                          $mtRestant = $mtRestant +$mt->montant_restant;
                                          $mtFm = $mtFm + $mt->mtPaye + $mtRestant; 
                                      @endphp
                                  @endforeach
                                  @php 
                                    $somme_montant_total_paye = $somme_montant_total_paye + $mtPaye;
                                    $somme_montant_total_restant = $somme_montant_total_restant + $mtRestant;
                                    $somme_montant_total = $somme_montant_total + $mtRestant + $mtPaye;
                                @endphp
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">{{$mtPaye + $mtRestant}}</td> 
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">{{$mtPaye}}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">{{$mtRestant}}</td>   
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;"></td>   
</tr>  
@else 
<tr style="margin:0; backround-color:#aba3a3;">
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;"><b>{{ ++$i  }}</b></td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->nom }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->prenoms }}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->email }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->telephone }}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">{{ $item->type_formation }}</td> 
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">    
    @if (checkAParticiper($formation->code_formation,$item->email))
      OUI                                    
    @else
      NON                                     
    @endif                               
</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">IMPAYE</td>  
@php
$getMontants = getMontantFm($formation->code_formation, $item->email);
$mtFm = 0;
$mtPaye = 0;
$mtRestant = 0;
@endphp
@foreach ($getMontants as $mt)
@php
    $mtRestant = $mtRestant + $mt->montant_restant;
    $mtFm = $mtFm + $mt->mtPaye + $mtRestant;
@endphp
@endforeach
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">{{$mtRestant+$mtPaye}}</td>  
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">{{$mtPaye}}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">{{$mtRestant}}</td>
<td style="margin:0; padding:5px; border: 1px solid black; text-align:center;"></td>   
</tr>  
@endif
@endforeach

<tr style="margin:0;min-height:35px;">
    <td colspan="8" style="margin:0; padding:5px; background:#83abd8; border: 1px solid black; text-align:center;"><b>Total</b></td> 
    <td style="margin:0; padding:5px; background:#83abd8; border: 1px solid black; text-align:center;"><b>{{ $somme_montant_total }}</b></td> 
    <td style="margin:0; padding:5px; background:#83abd8; border: 1px solid black; text-align:center;"><b>{{ $somme_montant_total_paye }}</b></td>    
    <td style="margin:0; padding:5px; background:#83abd8; border: 1px solid black; text-align:center;">{{ $somme_montant_total_restant }}</td>    
    <td style="margin:0; padding:5px; background:#83abd8; border: 1px solid black; text-align:center;"></td>   
</tr>
</tbody>
</table>
