@extends('include/layout')

@section('contenu') 
        
        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Etat Récapitulatif - {{$formation->code_formation}}
              <a style="margin-top: 2px" target="_blank" href="{{route('stats.pdf-etatrecapitulatif',$formation->code_formation)}}" target="_blank" class="btn btn-danger btn-sm" title="Imprimer en pdf">
                <i class="fas fa-print"></i>
              </a>&nbsp;
              <a style="margin-top: 2px" target="_blank" href="{{route('stats.export-etatrecapitulatif',$formation->code_formation)}}" target="_blank" class="btn btn-success btn-sm" title="Exporter en excel">
                <i class="fas fa-file"></i>
              </a>
            </h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ route('accueil')}}">Accueil</a></li>
              <li class="breadcrumb-item">Etat Récapitulatif</li>
              <li class="breadcrumb-item active" aria-current="page">Formation {{$formation->code_formation}}</li>
            </ol>
          </div>

          <!-- Row -->
          <div class="row"> 
            <!-- DataTable with Hover -->
            <div class="col-lg-12">
              <div class="card mb-4">

                  <!-- vérifier si la formation est gratuite ou payante -->
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
                <div class="rowx table-responsive p-3">
                <table class="table align-items-center table-flush table-hover table-defaultx" style="border:; 2px solid black; margin:;20px;">
                  <thead>
                    <tr class="text-center" style="">
                      <th style="border-bottom: 2px solid black;"></th>
                      <th style="border: 2px solid black;background-color: #abd;">Inscrits</th>
                      <th style="border: 2px solid black;background-color: #abd;">Gratuits</th>
                      <th style="border: 2px solid black;background-color: #abd;">Payants</th>
                      @if($fm_gratuite)<th style="border: 2px solid black;background-color: #abd;">Attestations</th>@endif
                      <th style="border: 2px solid black;background-color: #abd;">Ont participé</th>
                    </tr> 
                  </thead>
                  <tbody>
                    <tr class="text-center">
                      <td style="border: 2px solid black;"><b>PRESENTIEL</b></td>
                      <td style="border: 2px solid black;"><b>{{$total_inscrit_presentiel}}</b></td>
                      <td style="border: 2px solid black;">@if($fm_gratuite) <b>{{$total_participant_presentiel->count()}}</b> @else <b>{{getInscriptionGratuitePresentiel($formation->code_formation)}}</b> @endif</td>
                      <td style="border: 2px solid black;">@if($fm_gratuite) <b>0</b> @else <b>{{$total_inscrit_presentiel - getInscriptionGratuitePresentiel($formation->code_formation)}}</b> @endif</td>
                      @if($fm_gratuite)<td style="border: 2px solid black;"><b>{{getPaiementAll($formation->code_formation)->count()}}</b></td>@endif
                      <td style="border: 2px solid black;">{{$total_participant_presentiel}}</td>
                    </tr>
                    
                    <tr class="text-center">
                      <td style="border: 2px solid black;"><b>VISIO</b></td> 
                      <td style="border: 2px solid black;"><b>{{$total_inscrit_visio}}</b></td>
                      <td style="border: 2px solid black;">@if($fm_gratuite) <b>{{$liste_participants->count() - $total_participant_presentiel->count()}}</b> @else <b>{{getInscriptionGratuiteVisio($formation->code_formation)}}</b> @endif</td>
                      <td style="border: 2px solid black;">@if($fm_gratuite) <b>0</b> @else <b>{{$total_inscrit_visio - getInscriptionGratuiteVisio($formation->code_formation)}}</b> @endif</td>
                      @if($fm_gratuite)<td style="border: 2px solid black;"><b>{{getPaiementAll($formation->code_formation)->count()}}</b></td>@endif
                      <td style="border: 2px solid black;">{{$total_participant - $total_participant_presentiel}}</td>
                    </tr>
                  </tbody>
                  <tfoot style="background-color: #abd">                    
                    <tr class="text-center">
                      <td style="border: 2px solid black;"><b>TOTAL</b></td>
                      <td style="border: 2px solid black;"><b>{{$total_inscrit}}</b></td>
                      <td style="border: 2px solid black;">@if($fm_gratuite) <b>{{$liste_participants->count()}}</b> @else <b>{{getInscriptionGratuite($formation->code_formation)}}</b> @endif</td>
                      <td style="border: 2px solid black;">@if($fm_gratuite) <b>0</b> @else <b>{{$total_inscrit - getInscriptionGratuite($formation->code_formation)}}</b> @endif</td>
                      @if($fm_gratuite)<td style="border: 2px solid black;"><b>{{getPaiementAll($formation->code_formation)->count()}}</b></td>@endif
                      <td style="border: 2px solid black;">{{$total_participant}}</td>
                    </tr>
                  </tfoot>
                </table>
              </div>
                <div class="card-header py-3 d-flexx flex-row align-items-center justify-content-between">
                  <div class="row">
                    <div class="col-md-3">
                      <h6 class="m-0 font-weight-bold text-dark" style="font-size: 120%">ETAT DE PAIEMENT</h6>
                    </div> 
                  </div>
                </div>
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTableHover" style="border: 2px solid black;">
                    <thead class="thead-light">
                    <tr>
                        <th style="border: 1px solid black;">N°</th>
                        <th style="border: 1px solid black;">Nom</th>
                        <th style="border: 1px solid black;">Prénoms</th>
                        <th style="border: 1px solid black;">E-mail<br>Téléphone</th>
                        <th style="border: 1px solid black;">Mode</th>
                        @if ($formation->is_formation_dsc)
                          <th style="border: 1px solid black;">Frais d'inscription</th>                            
                        @endif
                        <th style="border: 1px solid black;">Frais de participation</th>
                        <th style="border: 1px solid black;">Réduction</th>
                        <th style="border: 1px solid black; font-size:100%; width:15%;">Montant<br>Total</th> 
                        <th style="border: 1px solid black;">Statut</th> 
                        <th style="border: 1px solid black; font-size:100%; width:15%;">Montant payé</th>  
                        <th style="border: 1px solid black; font-size:100%; width:15%;">Montant restant</th>  
                        <th style="border: 1px solid black;">A participé ?</th>
                        <th style="border: 1px solid black;">Observation</th> 
                      </tr>
                    </thead> 
                    <tbody>
                            @php $i=0 @endphp
                            @foreach($liste_participants as $item)   
                                    <!-- vérifier si le participant a effectué un paiement sur la plateforme -->
                                @if (getPaiement($formation->code_formation,$item->email,$item->tarif_formation_id)->count() > 0)
                                  @foreach (getPaiement($formation->code_formation,$item->email,$item->tarif_formation_id) as $data) 
                                  <tr>
                                    <td style="border: 1px solid black;" scope="row" class="text-center"><b>{{ ++$i  }}</b></td>
                                    <td style="border: 1px solid black;">{{ $item->nom }}</td>
                                    <td style="border: 1px solid black;">{{ $item->prenoms }}</td>
                                    <td style="border: 1px solid black;">{{ $item->email }} <br>{{ $item->telephone }}</td>
                                    <td style="border: 1px solid black;">{{ $item->type_formation }} </td>
                                    @php
                                        $frais_inscription = 0;
                                    @endphp
                                    @if ($formation->is_formation_dsc)
                                      @php
                                          $frais_inscription = getFraisInscription($formation->code_formation,$item->email,$data->reference_paiement)->montant_paye ?? 0;
                                      @endphp
                                      <td style="border: 1px solid black;">{{ number_format($frais_inscription, 0, ',', '.') }}</td>                           
                                    @endif                                    
                                    <td style="border: 1px solid black;">
                                      {{ number_format($data->montant_paye, 0, ',', '.') }}
                                    </td>
                                    <td style="border: 1px solid black;">
                                      @if ($data->have_reduction_dsc)
                                        5%
                                      @elseif ($data->pourcentage)
                                        {{$data->pourcentage}}%
                                      @else
                                        0%
                                      @endif 
                                    </td>
                                    <td style="border: 1px solid black;">{{ number_format($data->montant_total, 0, ',', '.') }}</td> 
                                    <td style="border: 1px solid black;color:green;font-weight:bold;">PAYE</td>
                                    @if ($item->type_formation == "ETUDIANTS" || $item->type_formation == "PROFESSIONNELS")
                                    <td style="border: 1px solid black;">{{ number_format($data->montant_paye+$frais_inscription, 0, ',', '.') }}</td>  
                                    @else
                                    <td style="border: 1px solid black;">{{ number_format($data->montant_paye, 0, ',', '.') }}</td>  
                                    @endif
                                    <td style="border: 1px solid black;">{{ number_format($data->montant_restant, 0, ',', '.') }}</td> 
                                    <td style="border: 1px solid black;">   
                                      @if (checkAParticiper($formation->code_formation,$item->email))
                                        <button type="button" class="btn btn-outline-success">OUI</button>                                      
                                      @else
                                        <button type="button" class="btn btn-outline-danger">NON</button>                                      
                                      @endif                               
                                    </td>
                                    <td style="border: 1px solid black;">{{$data->modepaiement}}</td>   
                                  </tr> 
                                  @endforeach

                                @elseif(getPaiementratuit($formation->code_formation,$item->email,$item->tarif_formation_id)->count() > 0)
                                    <tr>
                                      <th style="border: 1px solid black;" scope="row" class="text-center"><b>{{ ++$i  }}</b></th>
                                      <td style="border: 1px solid black;">{{ $item->nom }}</td>
                                      <td style="border: 1px solid black;">{{ $item->prenoms }} </td>
                                      <td style="border: 1px solid black;">{{ $item->email }} <br>{{ $item->telephone }}</td>
                                      <td style="border: 1px solid black;">{{ $item->type_formation }} </td>
                                      @if ($formation->is_formation_dsc)
                                        <td style="border: 1px solid black;">{{ number_format(150000, 0, ',', '.') }}</td>                           
                                      @endif
                                      <td style="border: 1px solid black;"></td>
                                      <td style="border: 1px solid black;"></td>
                                      @php
                                          $getMontants = getMontantFm($formation->code_formation,$item->email);
                                          $mtFm = 0; $mtPaye = 0; $mtRestant = 0;
                                      @endphp
                                      @foreach ($getMontants as $mt)
                                          @php 
                                              $mtRestant = $mtRestant +$mt->montant_restant;
                                              $mtFm = $mtFm + $mt->montant_paye + $mtRestant; 
                                          @endphp
                                      @endforeach
                                      <td style="border: 1px solid black;">{{ number_format($mtPaye+$mtRestant, 0, ',', '.') }}</td>  
                                      <td style="border: 1px solid black;">GRATUIT</td>
                                      <td style="border: 1px solid black;">{{ number_format($mtPaye, 0, ',', '.') }}</td>  
                                      <td style="border: 1px solid black;">{{ number_format($mtRestant, 0, ',', '.') }}</td>  
                                      <td style="border: 1px solid black;">    
                                        @if (checkAParticiper($formation->code_formation,$item->email))
                                          <button type="button" class="btn btn-outline-success">OUI</button>                                      
                                        @else
                                          <button type="button" class="btn btn-outline-danger">NON</button>                                      
                                        @endif                               
                                      </td>
                                      <td style="border: 1px solid black;"></td>   
                                  </tr> 
                                @else 
                                    <tr>
                                      <th style="border: 1px solid black;" scope="row" class="text-center"><b>{{ ++$i  }}</b></th>
                                      <td style="border: 1px solid black;">{{ $item->nom }}</td>
                                      <td style="border: 1px solid black;">{{ $item->prenoms }} </td>
                                      <td style="border: 1px solid black;">{{ $item->email }} <br>{{ $item->telephone }}</td>
                                      <td style="border: 1px solid black;">{{ $item->type_formation }} </td>
                                      {{-- @if ($formation->is_formation_dsc)
                                        <td style="border: 1px solid black;">{{ number_format(150000, 0, ',', '.') }}</td>                           
                                      @endif --}}
                                      @php
                                          $getMontants = getMontantFm($formation->code_formation,$item->email);
                                          $reduction_id = 0;  
                                          $mtFm = 0; $mtPaye = 0; $mtRestant = 0;
                                          $have_reduction_dsc = false;
                                          $frais_inscription = 0;
                                      @endphp
                                      @foreach ($getMontants as $mt)
                                          @php  
                                              $mtRestant = $mtRestant +$mt->montant_restant;
                                              $mtFm = $mtFm + $mt->montant_paye + $mtRestant; 
                                              $mtPaye =  $mt->montant_paye;
                                              $reduction_id = $mt->id_reduction ?? 0;
                                              $have_reduction_dsc = $mt->id_reduction; 
                                              $ref_paiement = $mt->reference_paiement;
                                              $frais_inscription = getFraisInscription($formation->code_formation,$item->email,$mt->reference_paiement)->montant_paye ?? 0;
                                          @endphp
                                      @endforeach 
                                      @if ($formation->is_formation_dsc)
                                      <td style="border: 1px solid black;">{{ number_format($frais_inscription, 0, ',', '.') }}</td>     
                                      <td style="border: 1px solid black;">{{ number_format($mtFm-$frais_inscription, 0, ',', '.') }}</td>  
                                      @else
                                      
                                      <td style="border: 1px solid black;">{{ number_format($mtPaye, 0, ',', '.') }}</td>  

                                      @endif                           
                                         

                                      <td style="border: 1px solid black;">
                                        @if ($have_reduction_dsc)
                                          5%
                                        @elseif ($reduction_id > 0)
                                          {{getReduction($reduction_id)}}%
                                        @else
                                          0%
                                        @endif 
                                      </td>                                
                                      
                                      <td style="border: 1px solid black;">{{ number_format($mtRestant+$mtPaye, 0, ',', '.') }}</td> 
                                      @php
                                        $montantpaye = $mtRestant+$mtPaye ;
                                      @endphp 
                                      @if ($mtPaye == 0)
                                        <td style="border: 1px solid black;color:red;">IMPAYE</td>    
                                      @else  
                                        @if ($mtPaye > 0 && $mtRestant > 0)
                                            <td style="border: 1px solid black;">PAIEMENT PARTIEL</td>
                                        @else
                                            <td style="border: 1px solid black;">PAYE</td>
                                        @endif                                    
                                      @endif
                                      <td style="border: 1px solid black;">{{ number_format($mtPaye, 0, ',', '.') }}</td>  
                                      <td style="border: 1px solid black;">{{ number_format($mtRestant, 0, ',', '.') }}</td>
                                      <td style="border: 1px solid black;">    
                                        @if (checkAParticiper($formation->code_formation,$item->email))
                                          <button type="button" class="btn btn-outline-success">OUI</button>                                      
                                        @else
                                          <button type="button" class="btn btn-outline-danger">NON</button>                                      
                                        @endif                               
                                      </td>  
                                      <td style="border: 1px solid black;"></td>   
                                  </tr> 
                                @endif
                            @endforeach 
                            
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <!--Row-->

        <!---Container Fluid-->

@endsection

@section('jsScript') 
<script>
$(document).ready(function () { 
// ############### gestion de l'ancre au niveau des menus ###################
$('#menu_formation').addClass("active");
$('#collapseGS').addClass("show");
$("#etat_recapitulatif_formation").addClass("active");
// ################### end gestion des menus ##################################
});
 
</script>
@endsection
