@include('vendor.mail.helpers')
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
</head>

<body>

<div style=" display:block; max-width:800px; margin:20px auto;  padding:0; color:#fff; background: #071093; border-radius:5px;border:1px solid #138496;font-size:15px; font-family: Montserrat, sans serif;">

      <div style=" display:block;  text-align:center;padding:20px;margin: 0;">

          <h3 style="font-size: 150%; margin-top:10px;">Système Intégré de Gestion des WEBinaires (SIGWEB)</h3>

      </div>

      <div style="background:#fff; color: #000; display:block;padding:30px; text-align: justify; ">
    <p>Bonjour {{ $contenu['prenoms'] }} {{ $contenu['nom'] }},
    </p>
    <p>Nous vous confirmons avoir enregistré votre inscription
      @if (count($contenu['formations_classique']) > 1)
      aux formations : <br><br>
        @foreach ($contenu['formations'] as $item)
            @foreach (getFormationsMail($item['tarif']) as $data)
        - <b>{{$data->code_formation}}</b> {{$data->theme}} <br>
            @endforeach
        @endforeach
      @else
      à la formation  <br><br>
      @foreach ($contenu['formations'] as $item)
          @foreach (getFormationsMail($item['tarif']) as $data)
          - <b>{{$data->code_formation}}</b> {{$data->theme}} <br>
          @endforeach
      @endforeach
      @endif
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
