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
      <p>Bonjour {{ $contenu['nom_prenoms'] }},
    </p>
    <p>Afin de mieux vous satisfaire, les services offerts par le SIGWEB-ERSUMA sont désormais rattachés à un compte unique d’utilisateur.
    <p>A cet effet, veuillez trouver ci-dessous vos paramètres de connexion :</p>
    <b>Lien d'accès au SIGWEB :</b> <a href="{{ env('FRONTEND_URL') }}/auth/login">{{ env('FRONTEND_URL') }}/auth/login</a> <br>
    <b>Nom d'utilisateur :</b> {{ $contenu['email'] }} <br>
    <b>Mot de passe : </b> {{ $contenu['mot_de_passe'] }} <br>
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
