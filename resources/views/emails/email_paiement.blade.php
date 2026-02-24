<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
</head>

<body> 
@php  $code_paiement = $contenu['reference_paiement']; @endphp
    
<div style=" display:block; max-width:800px; margin:20px auto;  padding:0; color:#fff; background: #071093; border-radius:5px;border:1px solid #138496;font-size:15px; font-family: Montserrat, sans serif;">
      
      <div style=" display:block;  text-align:center;padding:20px;margin: 0;">
         
          <h3 style="font-size: 150%; margin-top:10px;">Système Intégré de Gestion des WEBinaires (SIGWEB)</h3>
                 
      </div>
       
      <div style="background:#fff; color: #000; display:block;padding:30px; text-align: justify; ">
    <p>Bonjour {{ $contenu['prenoms'] }} {{ $contenu['nom'] }},
    </p>
    <p>Nous vous confirmons avoir enregistré votre demande de paiement <b>N° {{ $contenu['reference_paiement'] }}</b>.</p>
    <p>Si le paiement n'a pas abouti, vous pouvez  <a href="{{env('FRONTEND_URL')}}/paiement/{{$contenu['code_inscription']}}" target="_blank" ><b>retourner sur la page de paiement</b></a> pour essayer à nouveau. </p>
          <p>En cas d’échec de paiement en ligne, merci de contacter l’ERSUMA : </p>
          <i class="bi bi-envelope-fill"></i>- Par e-mail à l’adresse ersuma@ohada.org <br>
          <i class="bi bi-telephone-fill"></i>- Par téléphone au +229 01 97 97 05 37 <br>
          <p style="border: 1px solid #444; background-color:#dae7f7; margin: 20px auto; padding: 10px;">
              Si vous souhaitez payer par les autres modes (Virement, Ecobank Rapid Transfer, Espèces, …), <b>merci de télécharger <a href="{{$contenu['url_proforma']}}" target="_blank" rel="noopener noreferrer" style="text-decoration: none; color:#071093;">la facture proforma</a> et <a href="{{env('FRONTEND_URL')}}/moyens_de_paiement" title="Modalités de paiement v2024" target="_blank" rel="noopener noreferrer" style="text-decoration: none; color:#071093;">les moyens de paiement disponibles</a></b>.
          </p>
    <p>Merci de votre attention. <br>
    <center>« L’ERSUMA, pour une règle de droit effective, efficace et efficiente »</center>
    </p>
    
          <!-- source notification -->
            @include('emails.source_notification')
            <!-- end source notification -->
      </div>
      <!-- inc footer -->     
      @include('emails.email_footer') 
      <!-- end footer -->
</div> 
   
</body>
</html>
