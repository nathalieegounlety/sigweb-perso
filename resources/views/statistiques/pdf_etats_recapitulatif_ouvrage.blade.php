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

   
    <!-- div opacity -->
      <div style="z-index: 300; opacity:1; margin:0;">

              <div style="z-index: 100;clear:both; display:block; margin: 50px 0 0px 0;text-align: center;line-height: 115%;font-size: 16pt; color:#0a0a0a; font-weight:bold;">
                <div style="margin: 30px 0 5px 0; background-color:rgb(177, 168, 168); padding: 5px;">
                   {{$ouvrage->libelle}}   
                </div>
              </div>
              <div style="z-index: 100;clear:both; display:block; margin: 10px 0 0px 0;text-align: center;line-height: 115%;font-size: 16pt; color:#006fc0; font-weight:bold;">
                <div style="margin: 5px 0;">
                  ETAT RECAPITULATIF DE GESTION
                </div>
              </div>

        <div style="clear:both; display:block;">  </div> 
        <div style="line-height: 1.3; margin:10px 0 5px;">
          <!-- titre --> 
          <h3>ETAT DE PAIEMENT OUVRAGE & PUBLICATION  </h3>
        </div>
        <div style="clear:both; ">

          <table  cellspacing="0" cellpadding="0" style=" cellspacing : 0; cellpadding:0; border:0; width: 100%; z-index:1000;  padding: 10px 0 0 0; margin: 0 ;">
            <thead>
              <tr style="margin:0;background-color: #abd"> 
                <th style="padding:5px; margin:0; border: 1px solid black; text-align:center;">N°</th>
                <th style="padding:5px; margin:0; border: 1px solid black; text-align:center;">Identité</th> 
                <th style="padding:5px; margin:0; border: 1px solid black; text-align:center;">E-mail</th> 
                <th style="padding:5px; margin:0; border: 1px solid black; text-align:center;">Montant payé</th>
                <th style="padding:5px; margin:0; border: 1px solid black; text-align:center;">Observation</th>
              </tr>
            </thead>
            <tbody>
              @php
                $i=0;
                $total = 0;
              @endphp
              @foreach($details_paiements as $item)  
                  @php
                      $total = $total + $item->montant;
                  @endphp
                  <tr tyle="margin:0;">
                    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;"><b>{{ ++$i  }}</b></td>
                    <td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->getPaiement->getAuditeur ? $item->getPaiement->getAuditeur->prenoms : '' }} {{ $item->getPaiement->getAuditeur ? $item->getPaiement->getAuditeur->nom : '' }}</td>
                    <td style="margin:0; padding:5px; border: 1px solid black; text-align:left;">{{ $item->getPaiement->email }}</td>
                    <td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">{{ number_format($item->montant, 0, ',', '.') }}</td>  
                    <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;">{{$item->getPaiement->id_modepaiement ? $item->getPaiement->getModepaiement->libelle : ''}}</td>   
                  </tr>  
              @endforeach
                
                <tr style="margin:0;">
                  <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;" colspan="3"><b>Total</b></td> 
                  <td style="margin:0; padding:5px; border: 1px solid black; text-align:right;">{{ number_format($total, 0, ',', '.') }}</td>  
                  <td style="margin:0; padding:5px; border: 1px solid black; text-align:center;"></td>   
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
                                        <div
                                            style="padding:0; margin:-20px 0 0; text-align:center; z-index: 100;">
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
                                style="display:block; clear:both; width:100%; background: #fff; border: 0; border-top: 10px solid #9cc2e5; border-bottom:  10px solid #1571c6;  margin: 5px 0 10px 0; color:#000000; font-size:10pt; text-align:center; padding:5px 0 5px 0">
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
