@extends('include/layout')

@section('contenu')
    <!-- Container Fluid-->
    <div class="container-fluid" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Formations</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('accueil') }}">Accueil</a></li>
                <li class="breadcrumb-item">Formations</li>
                <li class="breadcrumb-item active" aria-current="page">Liste</li>
            </ol>
        </div>

        <!-- Row -->
        <div class="row">
            <!-- DataTable with Hover -->
            <div class="col-lg-12">
                <div class="card mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Liste des formations</h6>
                    </div>
                    <div class="table-responsive p-3">
                        <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                            <thead class="thead-light">
                                <tr>
                                    <th>N°</th>
                                    <th>Code Formation</th>
                                    <th width="25%">Thème</th>
                                    <th width="25%">Tarif</th>
                                    <th>Date début</th>
                                    <th>Date fin</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>N°</th>
                                    <th>Code Formation</th>
                                    <th>Thème</th>
                                    <th>Tarif</th>
                                    <th>Date début</th>
                                    <th>Date fin</th>
                                    <th>Action</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                @php $i=0 @endphp
                                @foreach ($liste_formations as $formation)
                                    <!-- vérifier si la formation est déjà réalisé -->
                                    {{-- @if (checkFormationRealisee($formation->code_formation)) --}}
                                    <tr>
                                        <th scope="row" class="text-center">{{ ++$i }}</th>
                                        <td>{{ $formation->code_formation }}</td>
                                        <td>{{ $formation->theme }}</td>
                                        <td>
                                            @foreach ($formation->getTarification($formation->code_formation) as $tarification)
                                                <label class="badge badge-primary">{{ $tarification->get_grille_libelle }} :
                                                    {{ $tarification->montant }}</label>
                                            @endforeach
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($formation->date_debut)->format('Y-m-d') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($formation->date_fin)->format('Y-m-d') }}</td>
                                        <td class="text-center">
                                            <!-- contrôler si la date du début de la formation est supérieur à la date du jour afin de permettre la modification -->
                                            <div class="d-flex justify-content-evenly">
                                                <a  href="{{ route('stats.etat_recapitulatif-formation', $formation->code_formation) }}"
                                                    title="Etat récapitulatif" class="btn btn-info btn-sm" title="Modifier">
                                                    <i class="fas fa-list"></i>
                                                </a>
    
                                                <a href="" data-toggle="modal"
                                                    data-target="#exampleModalCenter{{ $formation->id }}"
                                                    id="#modalCenter{{ $formation->id }}" title="Détails"
                                                    class="btn btn-danger btn-sm" title="Description">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>


                                    <!-- Modal Center -->
                                    <div class="modal fade" id="exampleModalCenter{{ $formation->id }}" tabindex="-1"
                                        role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalCenterTitle"><b>Code Formation
                                                            :</b> {{ $formation->code_formation }} </h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><b>Montant :</b></p>
                                                    <p>
                                                        @foreach ($formation->getTarification($formation->code_formation) as $tarification)
                                                            <div style="display: inline-block; width:100%">
                                                                <code>{{ $tarification->get_grille_libelle }} :
                                                                    {{ $tarification->montant }}</code></div>
                                                        @endforeach
                                                    </p>
                                                    <p><b>Description : </b></p>
                                                    {!! $formation->description !!}
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-primary"
                                                        data-dismiss="modal">Fermer</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {{-- @endif --}}
                                <!--  -->
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
            $(document).ready(function() {
                // ############### gestion de l'ancre au niveau des menus ###################
                $('#menu_formation').addClass("active");
                $('#collapseGS').addClass("show");
                $("#etat_recapitulatif_formation").addClass("active");
                // ################### end gestion des menus ##################################
            });
        </script>
    @endsection
