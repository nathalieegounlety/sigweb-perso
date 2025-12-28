<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
</head>

<body> 
    
<div style=" display:block; max-width:800px; margin:20px auto;  padding:0; color:#fff; background: #071093; border-radius:5px;border:1px solid #138496;font-size:15px; font-family: Montserrat, sans serif;">
      
      <div style=" display:block;  text-align:center;padding:20px;margin: 0;">
         
          <h3 style="font-size: 150%; margin-top:10px;">Système Intégré de Gestion des WEBinaires (SIGWEB)</h3>
                    
      </div>
      
      <div style="background:#fff; color: #000; display:block;padding:30px; text-align: justify; ">
      <p>Bonjour {{$contenu['nom_prenoms']}},<br>   
    </p><br>
    
    <p>Veuillez trouver ci-après le lien de téléchargement de votre attestation au titre de la formation <b>{{$contenu['code_formation']}} - {{$contenu['theme']}} </b> : 
      <br><br>
      @php
          $signedUrl = $contenu['signedUrl']; 
      @endphp
       
      <strong style="font-size: 120%;"><a href="{{$signedUrl}}">Télécharger l'attestation {{$contenu['code_formation']}} de {{$contenu['nom_prenoms']}}</a></strong>
      
     </p>
     <p>Votre mot de passe pour l'ouverture de l'attestation est : <b> {{$contenu['password']}}</b></p>
   
    <p>Nous vous remercions de votre confiance et de votre fidélité à l'ERSUMA. <br>
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