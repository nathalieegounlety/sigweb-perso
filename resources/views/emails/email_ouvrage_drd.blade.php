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
    <p>Bonjour {{ $contenu['name'] }},
    </p>
    <p>Nous vous informons qu'une nouvelle commande vient d'être enregistrée sur la plateforme, vous trouverez ci-dessous les détails :</p> <br>
    <b>Prénoms & Nom :</b> {{ $contenu['prenoms'] }} {{ $contenu['nom'] }} <br>
    <b>E-mail :</b> {{ $contenu['email'] }} <br>
    <b>N° PAIEMENT :</b> <b>N° {{ $contenu['reference_paiement'] }}</b><br>
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
