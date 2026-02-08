@extends('include/layout')
@section('contenu')
@php
$user_id = Auth::user()->id ;
$role_user = Auth::user()->getRolesUser($user_id);
@endphp
        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Etats & Stats</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
          </div>
          <!-- ################## Dashboard profil utilisateur et administrateur ########## -->
           
              <div class="row mb-3">
                <div class="col-xl-6 col-md-6 mb-1">
                  <div class="card">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                      <h6 class="m-0 font-weight-bold text-primary">Modes de formation</h6>
                      <a title="exporter" href="{{route('stats.export-modeformation')}}"><i class="fa fa-file"></i></a>
                    </div>
                    <div class="card-body">
                      <div class="chart-bar">
                        <canvas id="mode_formation"></canvas>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="col-xl-6 col-md-6 mb-1">
                  <div class="card">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                      <h6 class="m-0 font-weight-bold text-primary">Evolution des formations de {{$start_year}} à {{$end_year}}</h6>
                      <a title="exporter" href="{{route('stats.export-evolutionformation')}}"><i class="fa fa-file"></i></a>
                    </div>
                    <div class="card-body">
                      <div class="chart-bar">
                        <canvas id="revolution_formation"></canvas>
                      </div>
                    </div>
                  </div>
                </div>
              </div>  
              <div class="row">
                  <!-- Area Chart -->
                  <div class="col-xl-6 col-lg-6">
                    <div class="card mb-4"> 
                      <div class="card-body">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                          <h6 class="m-0 font-weight-bold text-primary">Nombre d'auditeurs par année</h6>
                          <a title="exporter" href="{{route('stats.export-auditeurannee')}}"><i class="fa fa-file"></i></a>
                        </div>
                        <div class="chart-area">
                          <canvas id="nbre_auditeur_annee"></canvas>
                        </div>
                      </div>
                    </div>
                  </div> 

                  <div class="col-xl-6 col-lg-6">
                    <div class="card mb-4"> 
                      <div class="card-body">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                          <h6 class="m-0 font-weight-bold text-primary">Nombre de formateurs par pays</h6>
                          <a title="exporter" href="{{route('stats.export-formateurpays')}}"><i class="fa fa-file"></i></a>
                        </div>
                        <div class="chart-area">
                          <canvas id="nbre_formateur_pays"></canvas>
                        </div>
                      </div>
                    </div>
                  </div>
                </div> 
                <div class="row">
                  <!-- Area Chart -->
                  <div class="col-xl-6 col-lg-6">
                    <div class="card mb-4"> 
                      <div class="card-body">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                          <h6 class="m-0 font-weight-bold text-primary">Stats des auditeurs suivant le genre</h6>
                          <a title="exporter" href="{{route('stats.export-auditeurgenre')}}"><i class="fa fa-file"></i></a>
                        </div>
                        <div class="chart-area">
                          <canvas id="auditeur_genres"></canvas>
                        </div>
                      </div>
                    </div>
                  </div> 
 
                  <div class="col-xl-6 col-lg-6">
                    <div class="card mb-4"> 
                      <div class="card-body">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                          <h6 class="m-0 font-weight-bold text-primary">Nombre d'auditeurs par  pays</h6>
                          <a title="exporter" href="{{route('stats.export-auditeurpays')}}"><i class="fa fa-file"></i></a>
                        </div>
                        <div class="chart-area">
                          <canvas id="auditeur_pays"></canvas>
                        </div>
                      </div>
                    </div>
                  </div> 
                </div>
              </div>  
          </div>
          <!--Row-->

        </div> 

        <!---Container Fluid-->
@endsection

@section('jsScript')
<script src="{{asset('vendor/chart.js/Chart.min.js')}}"></script>
 
 
<script> 
// stat paiement par statut
Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#858796';

function number_format(number, decimals, dec_point, thousands_sep) {
  // *     example: number_format(1234.56, 2, ',', ' ');
  // *     return: '1 234,56'
  number = (number + '').replace(',', '').replace(' ', '');
  var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
    s = '',
    toFixedFix = function(n, prec) {
      var k = Math.pow(10, prec);
      return '' + Math.round(n * k) / k;
    };
  // Fix for IE parseFloat(0.55).toFixed(0) = 0;
  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }
  if ((s[1] || '').length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1).join('0');
  }
  return s.join(dec);
}
  
// nbre de formation par année
// récupérer l'objet provenant du controller 
var formations_par_annees = {!!json_encode($formations_par_annees)!!};
var formations_par_annees_statique = {!!json_encode($formations_par_annees_statique)!!};
// définir les tables des variables pour la persistance
var df_tab_annee_formation = [];
var df_tab_nbre_formation = []; 
// fonction permettant de récupérer le nbre d'auditeur & l'année 
      for (let index = 0; index < formations_par_annees_statique.length; index++) { 
        df_tab_annee_formation[index]= formations_par_annees_statique[index].dates;
        df_tab_nbre_formation[index]= parseInt(formations_par_annees_statique[index].nbre_formation);  
      } 
// define variable tempons to add new index
var tempons = formations_par_annees_statique.length;

      for (let index = 0; index < formations_par_annees.length; index++) { 
        df_tab_annee_formation[tempons+index]= formations_par_annees[index].dates;
        df_tab_nbre_formation[tempons+index]= parseInt(formations_par_annees[index].nbre_formation);  
      }  
 
var ctx = document.getElementById("revolution_formation");
var myLineChart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: df_tab_annee_formation,
    datasets: [{
      label: "Nbre de formation ",
      lineTension: 0.3,
      backgroundColor: "rgba(78, 115, 223, 0.5)",
      borderColor: "rgba(78, 115, 223, 1)",
      pointRadius: 3,
      pointBackgroundColor: "rgba(78, 115, 223, 1)",
      pointBorderColor: "rgba(78, 115, 223, 1)",
      pointHoverRadius: 3,
      pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
      pointHoverBorderColor: "rgba(78, 115, 223, 1)",
      pointHitRadius: 10,
      pointBorderWidth: 2,
      data: df_tab_nbre_formation,
    }],
  },
  options: {
    maintainAspectRatio: false,
    layout: {
      padding: {
        left: 10,
        right: 25,
        top: 25,
        bottom: 0
      }
    },
    scales: {
      xAxes: [{
        time: {
          unit: 'date'
        },
        gridLines: {
          display: false,
          drawBorder: true
        },
        ticks: {
          maxTicksLimit: 1000
        }
      }],
      yAxes: [{
        ticks: {
          maxTicksLimit: 1000,
          padding: 10,
          // Include a dollar sign in the ticks
          callback: function(value, index, values) {
            return number_format(value)+ '';
          }
        },
        gridLines: {
          color: "rgb(234, 236, 244)",
          zeroLineColor: "rgb(234, 236, 244)",
          drawBorder: false,
          borderDash: [2],
          zeroLineBorderDash: [2]
        }
      }],
    },
    legend: {
      display: true
    },
    tooltips: {
      backgroundColor: "rgb(255,255,255)",
      bodyFontColor: "#858796",
      titleMarginBottom: 10,
      titleFontColor: '#6e707e',
      titleFontSize: 14,
      borderColor: '#dddfeb',
      borderWidth: 1,
      xPadding: 15,
      yPadding: 15,
      displayColors: true,
      intersect: true,
      mode: 'index',
      caretPadding: 10,
      callbacks: {
        label: function(tooltipItem, chart) {
          var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
          return datasetLabel  + number_format(tooltipItem.yLabel)+ '';
        }
      }
    }
  }
});
 
 
// gestion menu actif
$(document).ready(function () { 
// ############### gestion de l'ancre au niveau des menus ###################
//$('#stats_modeformation').addClass("active");
$('#collapseGS').addClass("show");
$("#stats_modeformation").addClass("active");
// ################### end gestion des menus ##################################
});
 // ******************************** mode de formation *****************************************************************  

 // mode des formations
var md_formations_visio = {!!json_encode($md_formations_visio)!!};
var md_formations_presentielle = {!!json_encode($md_formations_presentielle)!!};
var md_formations_bimodale = {!!json_encode($md_formations_bimodale)!!};

console.log(md_formations_bimodale);

var ctx = document.getElementById('mode_formation').getContext('2d');
var data_md_formation = {
  labels: [
    'Visio',
    'Présentielle', 
    'Bimodal'
  ],
  datasets: [{
    label: 'Mode des formations',
    data: [md_formations_visio, md_formations_presentielle, md_formations_bimodale],
    backgroundColor: [
      'rgba(78, 115, 223, 0.5)',
      '#832424',
      'rgba(78, 115, 223, 1)'
    ],
    hoverOffset: 4
  }]
};
var myChart = new Chart(ctx, {
    type: 'pie',
    data: data_md_formation,
    
});


 // auditeurs par genres
 var auditeur_genre_masculin = {!!json_encode($auditeur_genre_masculin)!!};
 var auditeur_genre_feminin = {!!json_encode($auditeur_genre_feminin)!!};

console.log(md_formations_bimodale);

var ctx = document.getElementById('auditeur_genres').getContext('2d');
var data_md_formation = {
  labels: [
    'Masculin',
    'Féminin',  
  ],
  datasets: [{
    label: 'Auditeurs suivant le genre',
    data: [auditeur_genre_masculin, auditeur_genre_feminin],
    backgroundColor: [
      'rgba(78, 115, 223, 0.5)',
      '#832424', 
    ],
    hoverOffset: 4
  }]
};
var myChart = new Chart(ctx, {
    type: 'pie',
    data: data_md_formation,
    
});

// ******************************** nbre auditeurs par années *****************************************************************  

// auditeur par année
// récupérer l'objet provenant du controller 
var table_auditeur = {!!json_encode($auditeur_annees)!!};
var table_auditeur_statique = {!!json_encode($auditeur_annees_statique)!!};
// définir les tables des variables pour la persistance
var df_tab_annee = [];
var df_tab_auditeur = []; 
// fonction permettant de récupérer le nbre d'auditeur & l'année 
//*********** ancienne données **************** 
for (let index = 0; index < table_auditeur_statique.length; index++) { 
        df_tab_annee[index]= table_auditeur_statique[index].dates;
        df_tab_auditeur[index]= parseInt(table_auditeur_statique[index].nbre_auditeur);  
      } 
// define variable tempons to add new index
var tempons = table_auditeur_statique.length;
// ******* nouvelle donnée ***********************
for (let index = 0; index < table_auditeur.length; index++) { 
  df_tab_annee[tempons+index]= table_auditeur[index].dates;
  df_tab_auditeur[tempons+index]= parseInt(table_auditeur[index].nbre_auditeur);  
} 
       
// stats fonction auditeur par année

var ctx = document.getElementById("nbre_auditeur_annee");
var myLineChart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: df_tab_annee,
    datasets: [{
      label: "Nbre d\'auditeurs par année ",
      lineTension: 0.3,
      backgroundColor: "#83242466",
      borderColor: "#832424",
      pointRadius: 3,
      pointBackgroundColor: "#632424",
      pointBorderColor: "#632424",
      pointHoverRadius: 3,
      pointHoverBackgroundColor: "#632424",
      pointHoverBorderColor: "#632424",
      pointHitRadius: 10,
      pointBorderWidth: 2,
      data: df_tab_auditeur,
    }],
  },
  options: {
    maintainAspectRatio: false,
    layout: {
      padding: {
        left: 10,
        right: 25,
        top: 25,
        bottom: 0
      }
    },
    scales: {
      xAxes: [{
        time: {
          unit: 'date'
        },
        gridLines: {
          display: false,
          drawBorder: true
        },
        ticks: {
          maxTicksLimit: 1000
        }
      }],
      yAxes: [{
        ticks: {
          maxTicksLimit: 1000,
          padding: 10,
          // Include a dollar sign in the ticks
          callback: function(value, index, values) {
            return number_format(value)+ '';
          }
        },
        gridLines: {
          color: "rgb(234, 236, 244)",
          zeroLineColor: "rgb(234, 236, 244)",
          drawBorder: false,
          borderDash: [2],
          zeroLineBorderDash: [2]
        }
      }],
    },
    legend: {
      display: true
    },
    tooltips: {
      backgroundColor: "rgb(255,255,255)",
      bodyFontColor: "#858796",
      titleMarginBottom: 10,
      titleFontColor: '#6e707e',
      titleFontSize: 14,
      borderColor: '#dddfeb',
      borderWidth: 1,
      xPadding: 15,
      yPadding: 15,
      displayColors: true,
      intersect: true,
      mode: 'index',
      caretPadding: 10,
      callbacks: {
        label: function(tooltipItem, chart) {
          var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
          return datasetLabel  + number_format(tooltipItem.yLabel)+ '';
        }
      }
    }
  }
});
  
// ******************************** nbre formateur par pays *****************************************************************  
 
// récupérer l'objet provenant du controller 
var table_formateur = {!!json_encode($paneliste)!!}; 
// définir les tables des variables pour la persistance
var df_tab_nbre = [];
var df_tab_pays = []; 
// fonction permettant de récupérer le nbre formateur & pays
//*********** ancienne données **************** 
for (let index = 0; index < table_formateur.length; index++) { 
  df_tab_nbre[index]= parseInt(table_formateur[index].nbre);  
  df_tab_pays[index]= table_formateur[index].get_pays;
      }      
// stats fonction auditeur par année

var ctx = document.getElementById("nbre_formateur_pays");
var myLineChart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: df_tab_pays,
    datasets: [{
      label: "Nbre de formateurs par pays ",
      lineTension: 0.3,
      backgroundColor: "#24832466",
      borderColor: "#248324",
      pointRadius: 3,
      pointBackgroundColor: "#245524",
      pointBorderColor: "#245524",
      pointHoverRadius: 3,
      pointHoverBackgroundColor: "#245524",
      pointHoverBorderColor: "#245524",
      pointHitRadius: 10,
      pointBorderWidth: 2,
      data: df_tab_nbre,
    }],
  },
  options: {
    maintainAspectRatio: false,
    layout: {
      padding: {
        left: 10,
        right: 25,
        top: 25,
        bottom: 0
      }
    },
    scales: {
      xAxes: [{
        time: {
          unit: 'date'
        },
        gridLines: {
          display: false,
          drawBorder: true
        },
        ticks: {
          maxTicksLimit: 1000
        }
      }],
      yAxes: [{
        ticks: {
          maxTicksLimit: 1000,
          padding: 10,
          // Include a dollar sign in the ticks
          callback: function(value, index, values) {
            return number_format(value)+ '';
          }
        },
        gridLines: {
          color: "rgb(234, 236, 244)",
          zeroLineColor: "rgb(234, 236, 244)",
          drawBorder: false,
          borderDash: [2],
          zeroLineBorderDash: [2]
        }
      }],
    },
    legend: {
      display: true
    },
    tooltips: {
      backgroundColor: "rgb(255,255,255)",
      bodyFontColor: "#858796",
      titleMarginBottom: 10,
      titleFontColor: '#6e707e',
      titleFontSize: 14,
      borderColor: '#dddfeb',
      borderWidth: 1,
      xPadding: 15,
      yPadding: 15,
      displayColors: true,
      intersect: true,
      mode: 'index',
      caretPadding: 10,
      callbacks: {
        label: function(tooltipItem, chart) {
          var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
          return datasetLabel  + number_format(tooltipItem.yLabel)+ '';
        }
      }
    }
  }
});
 
 
// ******************************** nbre auditeur par pays *****************************************************************  
 
// récupérer l'objet provenant du controller 
var table_auditeur = {!!json_encode($auditeur_pays)!!}; 
// définir les tables des variables pour la persistance
var df_tab_nbre = [];
var df_tab_pays = []; 
// fonction permettant de récupérer le nbre formateur & pays
//*********** ancienne données **************** 
for (let index = 0; index < table_auditeur.length; index++) { 
  df_tab_nbre[index]= parseInt(table_auditeur[index].nbre);  
  df_tab_pays[index]= table_auditeur[index].get_pays;
      }      
// stats fonction auditeur par année

var ctx = document.getElementById("auditeur_pays");
var myLineChart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: df_tab_pays,
    datasets: [{
      label: "Nbre d'auditeurs par pays ",
      lineTension: 0.3,
      backgroundColor: "#24832466",
      borderColor: "#248324",
      pointRadius: 3,
      pointBackgroundColor: "#245524",
      pointBorderColor: "#245524",
      pointHoverRadius: 3,
      pointHoverBackgroundColor: "#245524",
      pointHoverBorderColor: "#245524",
      pointHitRadius: 10,
      pointBorderWidth: 2,
      data: df_tab_nbre,
    }],
  },
  options: {
    maintainAspectRatio: false,
    layout: {
      padding: {
        left: 10,
        right: 25,
        top: 25,
        bottom: 0
      }
    },
    scales: {
      xAxes: [{
        time: {
          unit: 'date'
        },
        gridLines: {
          display: false,
          drawBorder: true
        },
        ticks: {
          maxTicksLimit: 1000
        }
      }],
      yAxes: [{
        ticks: {
          maxTicksLimit: 1000,
          padding: 10,
          // Include a dollar sign in the ticks
          callback: function(value, index, values) {
            return number_format(value)+ '';
          }
        },
        gridLines: {
          color: "rgb(234, 236, 244)",
          zeroLineColor: "rgb(234, 236, 244)",
          drawBorder: false,
          borderDash: [2],
          zeroLineBorderDash: [2]
        }
      }],
    },
    legend: {
      display: true
    },
    tooltips: {
      backgroundColor: "rgb(255,255,255)",
      bodyFontColor: "#858796",
      titleMarginBottom: 10,
      titleFontColor: '#6e707e',
      titleFontSize: 14,
      borderColor: '#dddfeb',
      borderWidth: 1,
      xPadding: 15,
      yPadding: 15,
      displayColors: true,
      intersect: true,
      mode: 'index',
      caretPadding: 10,
      callbacks: {
        label: function(tooltipItem, chart) {
          var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
          return datasetLabel  + number_format(tooltipItem.yLabel)+ '';
        }
      }
    }
  }
});
 

</script> 
@endsection
