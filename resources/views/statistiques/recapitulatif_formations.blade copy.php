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
                <table class="table align-items-center table-flush table-hover table-defaultx" style="border: 2px solid black; margin:;20px;">
                  <thead>
                    <tr class="text-center" style="background-color: #abd">
                      <th style="border: 2px solid black;">Inscrits</th>
                      <th style="border: 2px solid black;">Gratuits</th>
                      <th style="border: 2px solid black;">Payants</th>
                      @if($fm_gratuite)<th style="border: 2px solid black;">Attestations</th>@endif
                      <th style="border: 2px solid black;">Participants</th>
                    </tr> 
                  </thead>
                  <tbody>
                    <tr class="text-center">
                      <td style="border: 2px solid black;"><b>{{$total_inscrit}}</b></td>
                      <td style="border: 2px solid black;">@if($fm_gratuite) <b>{{$liste_participants->count()}}</b> @else <b>0</b> @endif</td>
                      <td style="border: 2px solid black;">@if($fm_gratuite) <b>0</b> @else <b>{{$liste_participants->count()}}</b> @endif</td>
                      @if($fm_gratuite)<td style="border: 2px solid black;"><b>{{getPaiementAll($formation->code_formation)->count()}}</b></td>@endif
                      <td style="border: 2px solid black;">{{$total_participant}}</td>
                    </tr>
                  </tbody>
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
                        <th style="border: 1px solid black;">E-mail</th>
                        <th style="border: 1px solid black;">Mode</th>
                        <th style="border: 1px solid black;">Statut</th>
                        <th style="border: 1px solid black; font-size:100%; width:15%;">Montant payé</th>  
                        <th style="border: 1px solid black;">Observation</th> 
                      </tr>
                    </thead> 
                    <tbody>
                            @php $i=0 @endphp
                            @foreach($liste_participants as $item) 
 
                                    <!-- vérifier si le participant a effectuer un paiement sur la plateforme -->
                                @if (getPaiement($formation->code_formation,$item->email,$item->tarif_formation_id)->count() > 0)
                                @foreach (getPaiement($formation->code_formation,$item->email,$item->tarif_formation_id) as $data) 
                                <tr>
                                  <td style="border: 1px solid black;" scope="row" class="text-center"><b>{{ ++$i  }}</b></td>
                                  <td style="border: 1px solid black;">{{ $item->nom }}</td>
                                  <td style="border: 1px solid black;">{{ $item->prenoms }}</td>
                                  <td style="border: 1px solid black;">{{ $item->email }} </td>
                                  <td style="border: 1px solid black;">{{ $item->type_formation }} </td>
                                  <td style="border: 1px solid black;">PAYE</td>
                                  <td style="border: 1px solid black;">{{ number_format($data->montant, 0, ',', '.') }}</td>  
                                  <td style="border: 1px solid black;">{{$data->modepaiement}}</td>   
                                </tr> 
                                @endforeach
                                @else 
                                <tr>
                                  <th style="border: 1px solid black;" scope="row" class="text-center"><b>{{ ++$i  }}</b></th>
                                  <td style="border: 1px solid black;">{{ $item->nom }}</td>
                                  <td style="border: 1px solid black;">{{ $item->prenoms }} </td>
                                  <td style="border: 1px solid black;">{{ $item->email }} </td>
                                  <td style="border: 1px solid black;">{{ $item->type_formation }} </td>
                                  <td style="border: 1px solid black;">IMPAYE</td>
                                  <td style="border: 1px solid black;"></td>  
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
