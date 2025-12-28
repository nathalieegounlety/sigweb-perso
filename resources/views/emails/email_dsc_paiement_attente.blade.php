<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
</head>

<body> 
    
<div style=" display:block; max-width:800px; margin:20px auto;  padding:0; color:#fff; background: #071093; border-radius:5px;border:1px solid #138496;font-size:15px; font-family: Montserrat, sans serif;">
      
      <div style=" display:block;  text-align:center;padding:20px;margin: 0;">
         
          <h3 style="font-size: 150%; margin-top:10px;">Système Intégré de Gestion des WEBinaires (SIGWEB)</h3>
            {{-- Tél. : +229 01 97 97 05 37 - E-mail:  <a style="color: #fff; text-decoration: none;"  href="mailto:formation.ersuma@ohada.org">formation.ersuma@ohada.org</a>   --}}          
      </div>
      
      <div style="background:#fff; color: #000; display:block;padding:30px; text-align: justify; ">
      <p>Bonjour {{ $contenu['prenoms'] }} {{ $contenu['nom'] }},</p>
      {{--<br><br>
    Bienvenue sur la <a href="{{ route('public') }}">plateforme de paiement</a> de l'ERSUMA.
    <br>--}}
    <p>Nous vous confirmons avoir enregistré votre paiement <b>N° {{ $contenu['reference_paiement'] }}</b> dont voici les détails :</p> <br>
    <b>N° PAIEMENT :</b> {{ $contenu['reference_paiement'] }} <br>
    <b>Mode de Paiement : </b> {{ $contenu['mode_paiement'] }} <br>
    <b>Montant Payé : </b> {{ $contenu['montant'] }} <br>
    <b>Montant Restant : </b> {{ $contenu['montant_restant'] }} <br>   

    <p>Votre paiement est en attente de traitement. Nous allons procéder aux vérifications et vous confirmer la validation dans les plus brefs délais.</p>
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
