<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
</head>

<body>

    <div
        style=" display:block; max-width:800px; margin:20px auto;  padding:0; color:#fff; background: #071093; border-radius:5px;border:1px solid #138496;font-size:15px; font-family: Montserrat, sans serif;">

        <div style=" display:block;  text-align:center;padding:20px;margin: 0;">

            <h3 style="font-size: 150%; margin-top:10px;">Système Intégré de Gestion des WEBinaires (SIGWEB)</h3>
        </div>

        <div style="background:#fff; color: #000; display:block;padding:30px; text-align: justify; ">


            <h2 style="text-align: center">Demande de réinitialisation du mot de passe</h2>
            <p>Bonjour {{ $nom_prenoms }},
            </p>

            <p>Vous avez demandé la réinitialisation de votre mot de passe.</p>

            <p>Si vous n'avez pas fait cette demande, ignorez simplement cet email.</p>
            <p>Vous souhaitez procéder: </p>

            <a href="{{env('FRONTEND_URL')}}/reset-password/{{$token}}">Cliquez ici pour réinitialiser votre mot de passe</a>
            </p>
            <p><b>PS : Code de réinitialisation valable pour 15 minutes</b> </p>
            <br />

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
