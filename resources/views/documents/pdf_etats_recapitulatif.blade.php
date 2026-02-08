<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	<title></title>
	<style type="text/css">
		@page { size: 29.7cm 21cm; margin-left: 1.75cm;1.27cm; margin-right: 1.75cm;1.27cm; margin-top: 1.27cm;0.73cm; margin-bottom: 1.27cm;0.97cm }
		p { margin-bottom: 0.25cm; direction: ltr; line-height: 115%; text-align: justify; orphans: 2; widows: 2; background: transparent }
		p.western { font-family: Tahoma, sans-serif; so-language: fr-FR }
		p.cjk { font-family: Tahoma, sans-serif; }
		p.ctl { font-family: Tahoma, sans-serif; font-size: 10pt }
		a:link { color: #0563c1; text-decoration: underline }

            /** Define the header rules **/
            header {
                position: fixed;
                top: 0cm;
                left: 0cm;
                right: 0cm;
                height: 3cm;
            }

            /** Define the footer rules **/
            footer {
                position: fixed;
                bottom: 0cm;
                left: 0cm;
                right: 0cm;
                height: 3cm;
            }

            th, td{
              font-size: 13px;
              word-wrap: break-word; /* Permet de couper les mots trop longs */
              white-space: normal; /* Autorise le retour à la ligne automatique */
              
            }
        </style>

</head>


<body lang="fr-BJ" link="#0563c1" vlink="#800000" dir="ltr"
style="font-family: Tahoma, sans-serif; 
   z-index: 100;">

  <div style="z-index: 10; background:none;">


    <div style="z-index:30;">
        <div style="float:left;width:86%;">

        <div style=" border-bottom:5px double #2681ca; max-width:75%; padding: 0 0 10px 0; ">

        <div style="float:left; width: 90px; margin:0px; height:90px; ">
        <img src="img/logo/logo-ohada.png" alt="" height="75px" style="height: 75px;">
        </div>
        <div style=" font-size:65%; line-height:1.5; margin: -25px 0 0 95px; padding:0px; top:0; font-family: Tahoma, serif; font-weight:bold; text-align:justify;">
          Organisation &nbsp;pour &nbsp;l’Harmonisation &nbsp;en &nbsp;Afrique &nbsp;du &nbsp;Droit &nbsp;des &nbsp;Affaires <br>
          Organization &nbsp;&nbsp; for&nbsp; &nbsp;the&nbsp; &nbsp;Harmonization &nbsp;&nbsp; of &nbsp;&nbsp;Business
          &nbsp;&nbsp; Law &nbsp;in &nbsp;&nbsp; Africa <br>
          Organización para la Armonización en África del Derecho de los Negocios <br>
          Organização &nbsp;para &nbsp;a &nbsp;Harmonização &nbsp;em &nbsp;África &nbsp;do &nbsp;Direito dos Negócios
        </div>
        </div>
      </div>
        <div style="float:right;width:14%;position:relative; text-align:right;">
        <img src="img/logo/attestation_flyers2.png" alt="" height="80px" style="height: 80px;">
        </div>
    </div>

  <div style="background: url('img/logo/logo-ohada-new500.png');
    opacity: 0.1;
    background-repeat: no-repeat;
    background-attachment: fixed;
    background-position: center 70px bottom 20px;
    z-index: -1;
    bottom:0;
    display:block;
    margin-top:;150px;
    pdding-top:;10px;
    ">
    <!-- div opacity -->
      <div style="z-index: 300; opacity:0; margin:0;">

              <div style="z-index: 100;clear:both; display:block; margin: 50px 0 0px 0;text-align: center;line-height: 115%;font-size: 16pt; color:#0a0a0a; font-weight:bold;">
                <div style="margin: 30px 0 5px 0; background-color:#dad5d5; padding: 5px;">
                   {{$formation->theme}}  
                   @if ($formation->date_debut == $formation->date_fin)
                   du
                   {{ \Carbon\Carbon::parse($formation->date_debut)->isoFormat('DD MMM YYYY') }}
                   @else
                   du
                   {{ \Carbon\Carbon::parse($formation->date_debut)->isoFormat('DD MMM') }}
                   au
                   {{ \Carbon\Carbon::parse($formation->date_fin)->isoFormat('DD MMM YYYY') }}
                   @endif
                   - {{$formation->code_formation}}
                </div>
              </div>
              <div style="z-index: 100;clear:both; display:block; margin: 10px 0 0px 0;text-align: center;line-height: 115%;font-size: 16pt; color:#006fc0; font-weight:bold;">
                <div style="margin: 5px 0;">
                  ETAT RECAPITULATIF
                </div>
              </div>

        <div style="clear:both; display:block;">  </div>
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

        <div style="clear:both; ">
          <table class="table align-items-center table-flush table-hover table-default" cellspacing="0" cellpadding="0" style=" cellspacing : 0; cellpadding:0; border:0; width: 100%; z-index:1000;  padding: 20px 0 0 0; margin: 0 ;">
            <thead>
              <tr class="text-center" style="margin:2px;background-color: #abd">
                <th colspan="2" style="text-align: center;min-height:35px;background-color: #fff ">&nbsp;</th>
                <th style="text-align: center; border:2px solid #000; padding:10px; font-size:16px;">
                    INSCRITS
                </th>
                <th style="text-align: center; border:2px solid #000; padding:10px; font-size:16px;">
                    GRATUIT
                </th>
                <th colspan="2" style="text-align: center; border:2px solid #000; padding:10px; font-size:16px;">
                    PAYANT
                </th>
    
                @if($fm_gratuite)
                <th colspan="2" style="text-align: center; border:2px solid #000; padding:10px; font-size:16px;">
                    ATTESTATION
                </th>
                @endif
    
                <th colspan="2" style="text-align: center; border:2px solid #000; padding:10px; font-size:16px;">
                    PARTICIPANTS
                </th>
            </tr>
            </thead>
            <tbody>
              <tr>
            <td colspan="2" style="text-align: center;  border:1px solid #000; padding:10px; font-size:14px;">PRESENTIEL</td>
            {{-- Nbre Inscrits en présentiel --}}
            <td style="text-align: center;  border:1px solid #000; padding:10px; font-size:14px;"><b>{{$total_inscrit_presentiel}}</b></td>
            {{-- Participants à titre gratuit en présentiel --}}
            <td style="text-align: center;  border:1px solid #000; padding:10px; font-size:14px;">@if($fm_gratuite) <b>{{$total_participant_presentiel->count()}}</b> @else <b>{{getInscriptionGratuitePresentiel($formation->code_formation)}}</b> @endif</td>
            {{-- Participants à titre payant en présentiel --}}
            <td colspan="2" style="text-align: center;  border:1px solid #000; padding:10px; font-size:14px;">@if($fm_gratuite) <b>0</b> @else <b>{{$total_inscrit_presentiel - getInscriptionGratuitePresentiel($formation->code_formation)}}</b> @endif</td>
            {{-- Participants effectifs en présentiel --}}
            @if($fm_gratuite)<td colspan="2" style="text-align: center;  border:1px solid #000; padding:10px; font-size:14px;"><b>{{getPaiementAll($formation->code_formation)->count()}}</b></td>@endif
            {{-- Participants effectifs en présentiel --}}
            <td colspan="2" style="text-align: center;  border:1px solid #000; padding:10px; font-size:14px;"><b>{{$total_participant_presentiel}}</b></td>
            
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;  border:1px solid #000; padding:10px; font-size:14px;">VISIO</td>
            {{-- Nbre Inscrits en VISIO --}}
            <td style="text-align: center;  border:1px solid #000; padding:10px; font-size:14px;"><b>{{$total_inscrit_visio}}</b></td>
            {{-- Participants à titre gratuit en VISIO --}}
            <td style="text-align: center;  border:1px solid #000; padding:10px; font-size:14px;">@if($fm_gratuite) <b>{{$liste_participants->count() - $total_participant_presentiel->count()}}</b> @else <b>{{getInscriptionGratuiteVisio($formation->code_formation)}}</b> @endif</td>
            {{-- Participants à titre payant en VISIO --}}
            <td colspan="2" style="text-align: center;  border:1px solid #000; padding:10px; font-size:14px;">@if($fm_gratuite) <b>0</b> @else <b>{{$total_inscrit_visio - getInscriptionGratuiteVisio($formation->code_formation)}}</b> @endif</td>
            {{-- Participants effectifs en présentiel --}}
            @if($fm_gratuite)<td colspan="2" style="text-align: center;  border:1px solid #000; padding:10px; font-size:14px;"><b>{{getPaiementAll($formation->code_formation)->count()}}</b></td>@endif
            {{-- Participants effectifs en VISIO --}}
            <td colspan="2" style="text-align: center;  border:1px solid #000; padding:10px; font-size:14px;"><b>{{$total_participant - $total_participant_presentiel}}</b></td>
            
        </tr>
        <tr>
            <th colspan="2" style="text-align: center; background:#83abd8; border:2px solid #000; padding:10px; font-size:16px;">
                TOTAL
            </th>
            {{-- Total des inscrits --}}
            <th style="text-align: center; background:#83abd8; border:2px solid #000; padding:10px; font-size:16px;">
                <b>{{$total_inscrit}}</b>
            </th>
            {{-- Total Participants à titre gratuit --}}
            <th style="text-align: center; background:#83abd8; border:2px solid #000; padding:10px; font-size:16px;">
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
            </tbody>
          </table>
        </div>
        <div style="line-height: 1.3; margin:10px 0 5px;">
          <!-- titre --> 
          <h3>ETAT DE PAIEMENT</h3>
        </div>
        <div style="clear:both; ">
          <table cellspacing="0" cellpadding="0"
              style=" cellspacing : 0; cellpadding:0; border:0; width: 100%; z-index:1000;  padding: 5px 0 0 0; margin: 0 ;">
              <thead>
                <tr style="margin:0;background-color: #abd">
                    <th style="padding:3px; margin:0; border: 1px solid black; text-align:center;">N°</th>
                    <th style="padding:3px; margin:0; border: 1px solid black; text-align:center;">Nom</th>
                    <th style="padding:3px; margin:0; border: 1px solid black; text-align:center;">Prénom</th>
                    <th style="padding:3px; margin:0; border: 1px solid black; text-align:center;">Email</th>
                    <th style="padding:3px; margin:0; border: 1px solid black; text-align:center;">Tel</th>
                    <th style="padding:3px; margin:0; border: 1px solid black; text-align:center;">Mode</th>
                    <th style="padding:3px; margin:0; border: 1px solid black; text-align:center;">Part.?</th>
                    <th style="padding:3px; margin:0; border: 1px solid black; text-align:center;">Statut</th>
                    <th style="padding:3px; margin:0; border: 1px solid black; text-align:center;">Montant</th>
                    <th style="padding:3px; margin:0; border: 1px solid black; text-align:center;">Payé</th>
                    <th style="padding:3px; margin:0; border: 1px solid black; text-align:center;">Restant</th>
                    <th style="padding:3px; margin:0; border: 1px solid black; text-align:center;">Obs.</th>
                </tr>
            </thead>
              <tbody>
                  @php
                      $i = 0;
                      $total_paye = 0;
                  @endphp
                  @foreach ($liste_participants as $item)
                      <!-- vérifier si le participant a effectuer un paiement sur la plateforme -->
                      @if (getPaiement($formation->code_formation, $item->email, $item->tarif_formation_id)->count() > 0)
                          @foreach (getPaiement($formation->code_formation, $item->email, $item->tarif_formation_id) as $data)
                              <tr tyle="margin:0;">
                                  <td
                                      style="margin:0; padding:3px; border: 1px solid black; text-align:center;">
                                      <b>{{ ++$i }}</b>
                                  </td>
                                  <td
                                      style="margin:0; padding:3px; border: 1px solid black; text-align:left;">
                                      {{ $item->nom }}
                                  </td>
                                  <td
                                      style="margin:0; padding:3px; border: 1px solid black; text-align:left; width:100px;">
                                      {{ $item->prenoms }} 
                                  </td>
                                  <td
                                      style="margin:0; padding:3px; border: 1px solid black; text-align:left;">
                                      {{ $item->email }}
                                  </td>
                                  <td
                                      style="margin:0; padding:3px; border: 1px solid black; text-align:left;">
                                      {{ $item->telephone }}
                                  </td>
                                  <td style="margin:0; padding:3px; border: 1px solid black; text-align:center;">
                                        {{ $item->type_formation}}
                                    </td>                                    
                                  <td
                                      style="margin:0; padding:3px; border: 1px solid black; text-align:center;">
                                      @if (checkAParticiper($formation->code_formation, $item->email))
                                          OUI
                                      @else
                                          NON
                                      @endif
                                  </td>
                                  <td
                                      style="margin:0; padding:3px; border: 1px solid black; text-align:center;">
                                      PAYE
                                  </td>
                                  <td style="border: 1px solid black; text-align:right;">{{ number_format($data->montant_paye+$data->montant_restant, 0, ',', '.') }}</td> 
                                  @if ($item->type_formation = 'ETUDIANTS' || ($item->type_formation = 'PROFESSIONNELS'))
                                      <td
                                          style="margin:0; padding:3px; border: 1px solid black; text-align:right;">
                                          {{ number_format($data->montant_paye, 0, ',', '.') }}</td>
                                  @else
                                      <td
                                          style="margin:0; padding:3px; border: 1px solid black; text-align:right;">
                                          {{ number_format($data->montant_paye, 0, ',', '.') }}
                                        </td>
                                  @endif
                                  <td
                                      style="margin:0; padding:3px; border: 1px solid black; text-align:right;">
                                      {{ number_format($data->montant_restant, 0, ',', '.') }}
                                  </td>
                                  <td style="border: 1px solid black; text-align:center;">{{$data->modepaiement}}</td>   
                              </tr>
                              @if ($data->montant_paye)
                                  @php
                                      $total_paye = $total_paye + $data->montant_paye;
                                  @endphp
                              @endif
                          @endforeach
                      @elseif(getPaiementratuit($formation->code_formation, $item->email, $item->tarif_formation_id)->count() > 0)
                          <tr tyle="margin:0;">
                              <td style="margin:0; padding:3px; border: 1px solid black; text-align:center;">
                                  <b>{{ ++$i }}</b></td>
                              <td style="margin:0; padding:3px; border: 1px solid black; text-align:left;">
                                  {{ $item->nom }}</td>
                              <td style="margin:0; padding:3px; border: 1px solid black; text-align:left;">
                                  {{ $item->prenoms }}</td>
                              <td style="margin:0; padding:3px; border: 1px solid black; text-align:left;">
                                  {{ $item->email }}</td>
                              <td style="margin:0; padding:3px; border: 1px solid black; text-align:left;">
                                  {{ $item->telephone }}</td>
                                  <td style="margin:0; padding:3px; border: 1px solid black; text-align:center;">
                                    {{ $item->type_formation}}
                                </td>                                
                              <td style="margin:0; padding:3px; border: 1px solid black; text-align:center;">
                                  @if (checkAParticiper($formation->code_formation, $item->email))
                                      OUI
                                  @else
                                      NON
                                  @endif
                              </td>
                              <td style="margin:0; padding:3px; border: 1px solid black; text-align:center;">
                                  GRATUIT</td>
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
                              <td style="border: 1px solid black; text-align:right;">
                                  {{ number_format($mtPaye + $mtRestant, 0, ',', '.') }}</td>
                              <td style="border: 1px solid black; text-align:right;">{{ number_format($mtPaye, 0, ',', '.') }}
                              </td>
                              <td style="border: 1px solid black; text-align:right;">
                                  {{ number_format($mtRestant, 0, ',', '.') }}</td>
                              <td style="border: 1px solid black;"></td>
                          </tr>
                      @else
                          <tr tyle="margin:0;">
                              <td style="margin:0; padding:3px; border: 1px solid black; text-align:center;">
                                  <b>{{ ++$i }}</b></td>
                              <td style="margin:0; padding:3px; border: 1px solid black; text-align:left;">
                                  {{ $item->nom }}</td>
                              <td style="margin:0; padding:3px; border: 1px solid black; text-align:left;">
                                  {{ $item->prenoms }}</td>
                              <td style="margin:0; padding:3px; border: 1px solid black; text-align:left;">
                                  {{ $item->email }}</td>
                              <td style="margin:0; padding:3px; border: 1px solid black; text-align:left;">
                                  {{ $item->telephone }}</td>
                                  <td style="margin:0; padding:3px; border: 1px solid black; text-align:center;">
                                    {{ $item->type_formation}}
                                </td>                                
                              <td style="margin:0; padding:3px; border: 1px solid black; text-align:center;">
                                  @if (checkAParticiper($formation->code_formation, $item->email))
                                      OUI
                                  @else
                                      NON
                                  @endif
                              </td>
                              <td style="margin:0; padding:3px; border: 1px solid black; text-align:center;">
                                  IMPAYE</td>
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
                              <td style="border: 1px solid black; text-align:right;">{{ number_format($mtRestant+$mtPaye, 0, ',', '.') }}</td>  
                        <td style="border: 1px solid black; text-align:right;">{{ number_format($mtPaye, 0, ',', '.') }}</td>  
                        <td style="border: 1px solid black; text-align:right;">{{ number_format($mtRestant, 0, ',', '.') }}</td>
                              <td style="margin:0; padding:3px; border: 1px solid black; text-align:center;">
                              </td>
                          </tr>
                      @endif
                  @endforeach

                  <tr tyle="margin:0;">
                      <td colspan="9"
                          style="margin:0; padding:3px; border: 1px solid black; text-align:center;">
                          <b>Total</b></td>
                      <td style="margin:0; padding:3px; border: 1px solid black; text-align:center;">
                          {{ number_format($total_paye, 0, ',', '.') }}</td>
                      <td colspan="2" style="margin:0; padding:3px; border: 1px solid black; text-align:center;"></td>
                     
                  </tr>
              </tbody>
          </table>

      </div> 
      </div>
    <!-- end div opacity -->

  </div><!-- fermeture div background-image -->

  <div style="width: 100%; text-align:center; margin-top:30px;">
    <b>Fait à Porto-Novo, le {{ \Carbon\Carbon::parse(date('Y-m-d'))->isoFormat('DD MMM YYYY') }}</b>
  </div>
  <table style="width: 100%;  padding: 0; margin: 0 ;">
    <tbody>
        <tr>
            <td style="vertical-align: bottom;">
                <footer>
 
                    <div style="clear:both; ">
                        <table style="width: 100%;  padding: 0; margin: 0 ;">
                            <tbody>
                                <tr>
                                    <td style="width:40%;vertical-align: bottom;">
                                        <div style="margin-bottom: 1px; margin-left: 10px;0;">
                                          Le Chef du Service Financier et Comptable
                                          <br><br><br>
                                          <b>Sessi Chimène AYADOKOUN-BANKOLE</b>
                                         </div>
                                    </td>

                                    <td style="text-align:center;vertical-align: bottom;">
                                        &nbsp;
                                    </td>
 
                                    @php
                                      $signataire =  getNewSignataire();
                                    @endphp
                                    <td style="width:30%; vertical-align: bottom; padding:0; "> 
                                        <div style="padding:0; margin:-20px 0 0; text-align:center; z-index: 100;">
                                            {{$signataire->fonction}}
                                            <br><br><br>
                                            <b>{{$signataire->titre}} {{$signataire->nom}} {{$signataire->prenoms}}</b>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div
                                style="display:block; clear:both; width:100%; background: #fff; border: 0; border-top: 10px solid #9cc2e5; border-bottom:  10px solid #1571c6;  margin: 5px 0 10px 0; color:#000000; font-size:10pt; text-align:center; padding:3px 0 5px 0">
                                ERSUMA, Ouando, Carrefour Cinquantenaire - Route de Pobè, 02 BP 353 Porto-Novo, BENIN - Tél. : +229 01 97 97
                                05 37 - E-mail : ersuma@ohada.org -
                                Web : <font color="#0563c1"><u><a href="https://ersuma.ohada.org/">https://ersuma.ohada.org</a></u></font>
                            </div> 
                </footer>

            </td>
        </tr>
    </tbody>
</table>
 

</div>
</body>
</html>
