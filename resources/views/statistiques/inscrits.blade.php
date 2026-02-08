@extends('include/layout')

@section('title')
    Statistiques des inscriptions || SIGWEB ERSUMA
@endsection

@section('contenu') 
        
        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Statistiques des inscriptions</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ route('accueil')}}">Accueil</a></li>
              <li class="breadcrumb-item">Statistiques des inscriptions</li>
              <li class="breadcrumb-item active" aria-current="page">Statistiques</li>
            </ol>
          </div>

          <!-- Row -->
          <div class="row"> 
            <!-- DataTable with Hover -->
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Statistiques des inscriptions par formation
                    <a href="{{ route('exportation_inscrits_formations')}}" class="btn btn-success btn-sm" title="Exporter en Excel">
                      <i class="far fa-file-alt"></i>
                    </a>
                  </h6>
                </div>
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTableCustom">
                    <thead class="thead-light"> 
                        <tr> 
                            <th>FORMATION</th> 
                            <th>DATE DEBUT</th> 
                            <th>VISIO</th> 
                            <th>PRESENTIEL</th> 
                            <th>INSCRITS</th>
                            <th>PARTICIPANTS</th>
                          </tr> 
                    </thead>
                    <tfoot> 
                      <tr> 
                        <th>FORMATION</th> 
                        <th>DATE DEBUT</th> 
                        <th>VISIO</th> 
                        <th>PRESENTIEL</th> 
                        <th>INSCRITS</th>
                        <th>PARTICIPANTS</th>
                      </tr>
                    </tfoot>
                    <tbody>
                            @php $i=0 @endphp
                            @foreach($stat_formation as $item)  
                                <tr> 
                                  <td style="max-width:300px;"><b>{{ $item->code }}</b> - {{ $item->theme }}</td> 
                                  <td style="max-width:100px;">{{ \Carbon\Carbon::parse($item->date_debut)->format('Y-m-d')  }}</td> 
                                  <td style="text-align: center;">{{ $item->nbre_visio }}</td> 
                                  <td style="text-align: center;">{{ $item->nbre_presentiel  }}</td> 
                                  <td style="text-align: center; font-weight:bold;">{{ $item->nbre_presentiel+$item->nbre_visio  }}</td> 
                                  <td style="text-align: center; font-weight:bold; color:#2347b2;">{{ $item->nbre_participant  }}</td> 
                              </tr>  
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
//$('#stats_modeformation').addClass("active");
$('#collapseGS').addClass("show");
$("#etat_recapitulatif_inscrits").addClass("active");
// ################### end gestion des menus ##################################

});
 
</script>
@endsection
