<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <style>
         .custom-link {
            color: #0066cc; /* Couleur bleue pour le lien */
            text-decoration: none; /* Supprime le soulignement */
            transition: color 0.3s ease; /* Transition douce pour la couleur */
        }

        .custom-link:hover {
            color: #004499; /* Couleur plus foncée au survol */
            text-decoration: underline; /* Soulignement au survol */
        }

        .custom-link:active {
            color: #ff0000; /* Couleur rouge au clic */
        }
    </style>
</head>

<body>

    <div
        style=" display:block; max-width:800px; margin:20px auto;  padding:0; color:#fff; background: #071093; border-radius:5px;border:1px solid #138496;font-size:15px; font-family: Montserrat, sans serif;">

        <div style=" display:block;  text-align:center;padding:20px;margin: 0;">

            <h3 style="font-size: 150%; margin-top:10px;">Système Intégré de Gestion des WEBinaires (SIGWEB)</h3>
            {{-- Tél. : +229 01 97 97 05 37 - E-mail:  <a style="color: #fff; text-decoration: none;"  href="mailto:formation.ersuma@ohada.org">formation.ersuma@ohada.org</a> --}}
        </div>

        <div style="background:#fff; color: #000; display:block; padding:30px; text-align: justify;">
            <p>Bonjour <b>Mme/M.</b> {{ $contenu['nom_prenoms'] }},</p>

            @if ($contenu['statut'] == 'VALIDEE')
                <p>Nous vous informons que votre candidature a été examinée par la Commission d’admission aux Diplômes
                    de Spécialité et Certificats (DSC) de l’ERSUMA et a reçu un avis favorable. Nous sommes heureux de
                    vous compter parmi nos auditeurs pour la rentrée prochaine.
                </p>

                <p>Afin de poursuivre la procédure d’admission, veuillez procéder au paiement des frais d'inscription
                    et de formation dans les délais exigés. Pour ce faire, votre facture proforma et <a href="https://sigweb.ersuma.org/moyens_de_paiement"  class="custom-link">les moyens de paiement</a> sont disponibles dans votre espace personnel sur SIGWEB.
                </p>

                <p>Nous vous remercions de votre confiance et de votre fidélité à l'ERSUMA.</p>


            @elseif ($contenu['statut'] == 'REJETEE')
                <p>Nous vous informons que votre candidature a été examinée par la Commission d’admission aux Diplômes
                    de Spécialité et Certificats (DSC) de l’ERSUMA. A l’issue du processus nous sommes au regret de vous
                    informer que votre candidature n’a pas été retenue pour la rentrée prochaine.
                </p>

                <p>En vous remerciant de votre confiance et de votre fidélité à l'ERSUMA, nous restons disponibles pour
                    vous accompagner dans le renforcement de vos capacités.</p>


            @elseif ($contenu['statut'] == 'SOUS RESERVE')
                <p>Nous vous informons que votre candidature a été examinée par la Commission d’admission aux Diplômes
                    de Spécialité et Certificats (DSC) de l’ERSUMA. A l’issue du processus votre candidature a été
                    retenue sous réserve de fournir : <br>
                    {!! nl2br(e($contenu['observations'])) !!}
                </p>

                <p>Vous disposez d'un délai de huit (08) jours à compter de la présente notification pour régulariser
                    votre dossier. Passé ce délai, votre admission sera annulée.
                    En vous remerciant de votre confiance et de votre fidélité à l'ERSUMA, nous restons disponibles pour
                    vous accompagner dans le renforcement de vos capacités.
                </p>

            @elseif ($contenu['statut'] == 'RELANCE')
                <p> {!! nl2br(e($contenu['observations'])) !!} </p>
            @endif

            <center>« L’ERSUMA, pour une règle de droit effective, efficace et efficiente »</center>
        
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
