@extends('include/layout')
@section('title')
Etat Récapitulatif Ouvrages & Publications
@endsection
@section('contenu') 
        
        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Etat Récapitulatif Ouvrages & Publications
              <a style="margin-top: 2px" target="_blank" href="{{route('stats.pdf-etatrecapitulatif-ouvrage',$ouvrage->id)}}" target="_blank" class="btn btn-danger btn-sm" title="Imprimer en pdf">
                <i class="fas fa-print"></i>
              </a>&nbsp;
              <a style="margin-top: 2px" target="_blank" href="{{route('stats.export-etatrecapitulatif-ouvrage',$ouvrage->id)}}" target="_blank" class="btn btn-success btn-sm" title="Exporter en excel">
                <i class="fas fa-file"></i>
              </a>
            </h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ route('accueil')}}">Accueil</a></li>
              <li class="breadcrumb-item">Etat Récapitulatif</li>
              <li class="breadcrumb-item active" aria-current="page">Ouvrages & Publications</li>
            </ol>
          </div>

          <!-- Row -->
          <div class="row"> 
            <!-- DataTable with Hover -->
            <div class="col-lg-12">
              <div class="card mb-4"> 
                <div class="card-header py-3 d-flexx flex-row align-items-center justify-content-between">
                  <div class="row">
                    <div class="col-md-12">
                      <h6 class="m-0 font-weight-bold text-dark" style="font-size: 100%">ETAT DE PAIEMENT || {{$ouvrage->libelle}}</h6> <b class="breadcrumb-item"></b>
                    </div> 
                  </div>
                </div>
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTableHover" style="border: 2px solid black;">
                    <thead class="thead-light">
                    <tr class="text-center">
                        <th style="border: 1px solid black;">N° d'ordre</th>
                        <th style="border: 1px solid black;">Identité</th>
                        <th style="border: 1px solid black;">E-mail</th>
                        <th style="border: 1px solid black; font-size:100%; width:15%;">Montant payé</th>  
                        <th style="border: 1px solid black;">Observation</th> 
                      </tr>
                    </thead> 
                    <tbody>
                            @php $i=0 @endphp
                            @foreach($details_paiements as $item)  
                                <tr>
                                  <td style="border: 1px solid black;" scope="row" class="text-center"><b>{{ ++$i  }}</b></td>
                                  <td style="border: 1px solid black;">{{ $item->getPaiement->getAuditeur ? $item->getPaiement->getAuditeur->prenoms : '' }} {{ $item->getPaiement->getAuditeur ? $item->getPaiement->getAuditeur->nom : '' }}</td>
                                  <td style="border: 1px solid black;">{{ $item->getPaiement->email ? $item->getPaiement->email : '' }}</td>
                                  <td style="border: 1px solid black;">{{ number_format($item->montant, 0, ',', '.') }}</td>  
                                  <td style="border: 1px solid black;">{{$item->getPaiement->id_modepaiement ? $item->getPaiement->getModepaiement->libelle : ''}}</td>   
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

