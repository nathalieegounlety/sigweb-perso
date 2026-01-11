@extends('include/layout')

@section('contenu') 
        
        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Edition des attestations pour les formateurs</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ route('accueil')}}">Accueil</a></li>
              <li class="breadcrumb-item">Choix Formation</li>
              <li class="breadcrumb-item active" aria-current="page">Liste</li>
            </ol>
          </div>

          <!-- Row -->
          <div class="row"> 
          <!-- Dropdown Basic with Color -->
          <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Choix par Code Formation</h6>
                </div>
                <div class="card-body"> 
                  <!-- Example single danger button --> 
                  <div class="form-row">
                        <div class="col-lg-12 form-group">
                        <label for="label"><b>Code Formation <i class="text-danger">*</i></b></label> 
                        <select class="select2-single-placeholder form-control" name="code_formation" id="code_formation">
                            <option value=""></option>
                            @foreach($liste_formations as $formation)
                            <option value="{{ $formation->code_formation }}">{{ $formation->code_formation }} : {{ $formation->theme }}</option> 
                            @endforeach
                            {{--<option value="all">Tous</option>--}}
                        </select>
                        </div> 
                  </div>  
                </div>
              </div>
            </div>
            <!-- Dropdown split button with Color -->

            <!-- DataTable with Hover -->
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Impression des listes et attestations des bénéficiaires par lot <b style="color: #9C421E;" id="info"></b></h6>
                </div>
                <div class="row" style="padding-left: 21px;">
                  <div class="col-lg-3 col-md-6 col-sm-12">
                    <h6 class="m-0 font-weight-bold"> <b id="liste_visio"></b> <b id="nbre_liste_visio" style="color: #9C421E;"></b> <b id="icone_liste_visio"></b> </h6>
                  </div>
                  <div class="col-lg-3 col-md-6 col-sm-12">
                    <h6 class="m-0 font-weight-bold" id="attestation_visio"></h6>
                  </div>
                  <div class="col-lg-3 col-md-6 col-sm-12">
                    <h6 class="m-0 font-weight-bold"> <b id="liste_formateur"></b> <b id="nbre_liste_paneliste" style="color: #9C421E;"></b> <b id="icone_liste_formateur"></b></h6>
                  </div>
                  <div class="col-lg-3 col-md-6 col-sm-12">
                    <h6 class="m-0 font-weight-bold" id="attestation_formateur"> </h6>
                  </div>
                </div>
                <form  method="POST" action="{{route('attestation-formateur.sendmail_all')}}">
                  @csrf
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="myTable">
                    <thead class="thead-light">
                      <tr> 
                            <th>N°</th> 
                            <th> <input type="checkbox" class="" id="cocher" onClick="updateCheckbox(this.checked);"> Cocher </th>
                            <th>Nom & Prénoms</th>  
                            <th>E-mail</th>   
                            <th>Rôle</th>  
                            <th>Nationalité</th>  
                            <th>Action</th>
                      </tr>
                    </thead>
                    <tfoot>
                        <tr> 
                            <th>N°</th>  
                            <th>Cocher</th>
                            <th>Nom & Prénoms</th> 
                            <th>E-mail</th>   
                            <th>Rôle</th>  
                            <th>Nationalité</th> 
                            <th>Action</th>
                        </tr>
                    </tfoot>
                    <tbody> 
                                <tr>  
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td> 
                                    <td></td> 
                                    <td></td> 
                                    <td></td>  
                                </tr> 
                    </tbody>
                  </table>
                </div> 
                <div class="row mt-1 mb-1" style="padding-left: 21px;">
                  <div class="col-md-4 hidden" id="showbutton">
                    
                  </div>
                  <div class="col-md-4">
                    &nbsp;
                  </div>
                  <div class="col-md-4">
                    &nbsp;
                  </div>
                </div>
        </form>
              </div>
            </div>
          </div>
          <!--Row-->
 

        </div>
        <!---Container Fluid-->

@endsection

@section('jsScript') 
<script>
$(document).ready(function () { 
  var table = $('#myTable').DataTable({
            "language": {
                "sProcessing": "Traitement en cours ...",
                "sLengthMenu": "Afficher _MENU_ lignes",
                "sZeroRecords": "Aucun résultat trouvé",
                "sEmptyTable": "Aucune donnée disponible",
                "sInfo": "Lignes _START_ à _END_ sur _TOTAL_",
                "sInfoEmpty": "Aucune ligne affichée",
                "sInfoFiltered": "(Filtrer un maximum de_MAX_)",
                "sInfoPostFix": "",
                "sSearch": "Chercher:",
                "sUrl": "",
                "sInfoThousands": ",",
                "sLoadingRecords": "Chargement...",
                "oPaginate": {
                    "sFirst": "Premier",
                    "sLast": "Dernier",
                    "sNext": "Suivant",
                    "sPrevious": "Précédent"
                },
                "oAria": {
                    "sSortAscending": ": Trier par ordre croissant",
                    "sSortDescending": ": Trier par ordre décroissant"
                }

            },
            dom: 'lBfrtip',
            buttons: [
                'copy',
                {
                    extend: 'excel',
                    title: function() {
                      return 'Liste : ';
                      //  return 'Liste des recettes : ( ' + titre_type_taxe + ' - ' + titre_type_emplacement + ' ) du ' + $('#periode_debut').val() + ' à ' + $('#periode_fin').val();
                    }
                    //messageTop: 'The information in this table is copyright to Sirius Cybernetics Corp.'
                },
                {
                    
                }

            ]

        });
// ############### gestion de l'ancre au niveau des menus ###################
$('#menu_attestation').addClass("active");
$('#collapseGA').addClass("show");
$("#attestation_paneliste").addClass("active");
// ################### end gestion des menus ##################################

//******************************* choix d'une code_formation ***********************************************************
$('#code_formation').on('change', function() {

            var code_formation = $('#code_formation').val();

            if (code_formation != "") { 
                $.ajax({
                    url: "{{ route('ajax_search') }}",
                    data: {
                      code_formation_attestation_paneliste: code_formation,
                        _token: '{{csrf_token()}}'
                    },
                    type: 'POST',
                    dataType: 'json',
                    //contentType: true, 
                    processData: true,
                    //       timeout: 500000,
                    success: function(data, status) { 
                      $('#showbutton').empty();
                      $('#showbutton').append('<input type="submit" class="btn btn-danger" id="send_all">');
      
                        var code = data['code_formation']; 
                        var nbr_total_paneliste = 0; 

                        $("#info").html(" "+code+"");

                        // console.log(data);

                        // variable pour formateur 
                        var url_exports_liste_paneliste = "{{ route('exportation_formateur_pdf',':code') }}"; 
                        var url_export_attestations_presentielle = "{{ route('exportation_attestation.all_formateurs',':code') }}";
                        url_exports_liste_paneliste = url_exports_liste_paneliste.replace(':code', code);
                        url_export_attestations_presentielle = url_export_attestations_presentielle.replace(':code', code);
                        $("#liste_formateur").html("Liste des formateurs") ;
                        $("#icone_liste_formateur").html("<a class='btn btn-danger btn-sm' title='Liste des formateurs' href='"+ url_exports_liste_paneliste +"' target='_blank'> <i class='far fa-file-alt'></i> </a>") ;
                        $("#attestation_formateur").html("Attestations Formateurs <a class='btn btn-danger btn-sm' href='"+ url_export_attestations_presentielle +"'  title='Attestations des formateurs' target='_blank'> <i class='fas fa-graduation-cap'></i> </a>") ;
                        var panelistes = data['panelistes'];   
                        var liste_paneliste = new Array;
                        var compte = 0 ;
                        for (let index = 0; index < panelistes.length; index++) {
                          const element = panelistes[index];   
                         // console.log(element); 
                          if(parseInt(element.id_formateur) >= 0){ 
                            var nbr_total_paneliste = nbr_total_paneliste + 1;
                            var url_export_attestation_paneliste = "{{ route('exportation_attestation_by_formateur',':code') }}";
                            url_export_attestation_paneliste = url_export_attestation_paneliste.replace(':code', element.id_formateur);
                            // link to send email
                            var send_email_attestation_formateur = "{{ route('attestation.sendmail_formateur',':code') }}";
                            send_email_attestation_formateur = send_email_attestation_formateur.replace(':code', element.id_formateur);
                            // edit route replace variable 
                             
                            compte = compte + 1 ;
                            var donnees = [];
                            var nom = "";
                            var prenoms = "";
                            var role = "";

                            if (element.nom == "" || element.nom == null) {
                              nom = ""
                            } else {
                              nom = element.nom;
                            }
                            if (element.role_formateur == "" || element.role_formateur == null) {
                              role = element.role
                            } else {
                              role = element.role_formateur;
                            }

                            donnees.push(compte); 
                donnees.push("<input type='checkbox' name='choisir[]' class='form-control-sm add_attestation' value='"+ element.email +"/"+ code +"'>");
                            donnees.push(nom+' '+prenoms); 
                            donnees.push(element.email);  
                            donnees.push(role);
                            donnees.push(element.nationalite); 
                            donnees.push("<a style='text-decoration:none;' href='"+ url_export_attestation_paneliste +"'  title='Imprimer Attestation pour présentiel' target='_blank'> <i class='fas fa-graduation-cap fa-1x text-danger'></i> </a>&nbsp;&nbsp;<a style='text-decoration:none;' href='"+ send_email_attestation_formateur +"'  title='Envoyer Attestation par mail'> <i class='fas fa-share fa-1x text-success'></i> </a> &nbsp;&nbsp;");
                            liste_paneliste.push(donnees);
                          } 
                           
                        } 
                       // console.log(nbr_total_paneliste);
                        $("#nbre_liste_paneliste").html("("+nbr_total_paneliste+")") ;
  
                        table.clear();
                        if(panelistes != ""){table.rows.add(liste_paneliste);} 
                        table.draw();
 
                    },
                    error: function(xhr) {
                        console.log(xhr);
                        alert('Une erreur s\'est produite');

                    }
                });
                
            }
        });

});

function updateCheckbox(isChecked) {
    
    if(isChecked) {
      $.each($('.add_attestation'), function(){ 
        $(this).prop('checked', true);
      });  
    } else { 
      $.each($('.add_attestation'), function(){
        $(this).prop('checked', false);
      });
  
    }
  }
</script>
@endsection
