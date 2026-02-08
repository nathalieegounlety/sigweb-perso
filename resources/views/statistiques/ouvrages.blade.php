@extends('include/layout')

@section('title')
    Ouvrages & Publications || SIGWEB ERSUMA
@endsection

@section('contenu') 
        
        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Ouvrages & Publications</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ route('accueil')}}">Accueil</a></li>
              <li class="breadcrumb-item">Ouvrages & Publications</li>
              <li class="breadcrumb-item active" aria-current="page">Liste</li>
            </ol>
          </div>

          <!-- Row -->
          <div class="row"> 
            <!-- DataTable with Hover -->
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Liste des ouvrages & publications</h6>
                </div>
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                    <thead class="thead-light">
                    <tr>
                        <tr>
                            <th>N°</th>
                            <th>Libellé</th> 
                            <th>Montant</th> 
                            <th>Date création</th> 
                            <th>Action</th>
                          </tr>
                      </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>N°</th>
                            <th>Libellé</th> 
                            <th>Montant</th> 
                            <th>Date création</th>
                            <th>Créer par</th> 
                          </tr>
                    </tfoot>
                    <tbody>
                            @php $i=0 @endphp
                            @foreach($liste_ouvrages as $item)  
                                <tr>
                                  <th scope="row" class="text-center">{{ ++$i  }}</th>
                                  <td>{{ $item->libelle }}</td> 
                                  <td>{{ $item->montant }}</td> 
                                  <td>{{ $item->created_at->format('d-m-Y H:i:s')  }}</td> 
                                  <td class="text-center"> 
                                    <a style="margin: 5px" href="{{ route('stats.etat_recapitulatif-ouvrage',$item->id)}}" title="Etat récapitulatif" class="btn btn-info btn-sm">
                                      <i class="fas fa-list"></i>
                                    </a>  
                                  </td> 
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
$('#menu_formation').addClass("active");
$('#collapseGS').addClass("show");
$("#etat_recapitulatif_ouvrage").addClass("active");
// ################### end gestion des menus ##################################
});
 
</script>
@endsection

