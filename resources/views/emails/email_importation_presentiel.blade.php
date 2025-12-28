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
      <p>Bonjour Madame/Monsieur,<br>
    </p><br>
    <p>Nous vous confirmons que l'importation de la liste des participants à la formation {{ $contenu['code_formation'] }} sur la plateforme SIGWEB-ERSUMA s'est déroulée avec succès.</p>
    <p>Merci de vous rendre sur <a href="{{ route('login') }}" target="_blank" style="text-decoration: none;">SIGWEB-ERSUMA </a> pour récupérer les rapports.</p>  
    <p style="color: blue;">
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