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

      @php
          $auditeur = getAuditeurByRef($contenu['reference_paiement']);
      @endphp
      
      <div style="background:#fff; color: #000; display:block;padding:30px; text-align: justify; ">
      <p>Bonjour {{ $contenu['name'] }},<br><br>
    Bienvenue sur la <a href="{{ route('public') }}">plateforme de paiement</a> de l'ERSUMA.
    </p><br>
    <p>Nous vous informons qu'un nouveau paiement vient d'être enregistré sur la plateforme, vous trouverez ci-dessous les détails :</p> <br> 
    <b>Type de paiement :</b> ACHAT OUVRAGES ET PUBLICATIONS<br>
    <b>Prénoms & Nom :</b> {{ $auditeur->prenoms ?  $auditeur->prenoms :  '' }} {{ $auditeur->nom ?  $auditeur->nom : '' }} <br>
    <b>E-mail :</b> {{ $auditeur->email ?  $auditeur->email : '' }} <br>
    <b>N° PAIEMENT :</b> {{ $contenu['reference_paiement'] }} <br>
    <b>Mode de Paiement : </b> {{ $contenu['mode_paiement'] }} <br>
    <b>Montant Payé : </b> {{ $contenu['montant'] }} <br> 
    <p>Merci de faire diligence <br>
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