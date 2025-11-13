<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGWEB-ERSUMA | Système Intégré de Gestion des Webinaires de l'ERSUMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Fond bleu OHADA */
        body {
            background: linear-gradient(-45deg, #233468, #1E3A8A, #002333, #233468);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            color: white;
            font-family: 'Tahoma', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        @keyframes gradient {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        /* Conteneur principal */
        .landing-container {
            text-align: center;
            max-width: 1200px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Logo OHADA */
        .logo-ohada {
            width: 150px;
            height: auto;
            margin-bottom: 20px;
            filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.5));
        }

        /* Titre principal */
        .main-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            letter-spacing: 1px;
        }

        /* Sous-titre */
        .subtitle {
            font-size: 1.3rem;
            margin-bottom: 30px;
            opacity: 0.9;
            max-width: 600px;
            line-height: 1.5;
        }

        /* Bouton d'action */
        .cta-button {
            background-color: #F6AD55;
            /* Orange OHADA */
            color: #233468;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .cta-button:hover {
            background-color: #DDA003;
            /* Jaune OHADA */
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        }

        /* Pied de page */
        .footer {
            margin-top: 50px;
            font-size: 0.9rem;
            opacity: 0.7;
        }
    </style>
</head>

<body>
    <div class="landing-container">
        <!-- Logo OHADA -->
        <img src="{{ asset('/img/logo-ohada-new500.png') }}" alt="Logo OHADA" class="logo-ohada">

        <!-- Titre principal -->
        <h1 class="main-title">SIGWEB-ERSUMA</h1>

        <!-- Sous-titre -->
        <p class="subtitle">
            Système intégré de gestion de webinaires de l'ERSUMA
        </p>

        <!-- Bouton d'appel à l'action -->
        <a href="https://dev-frontend-sigweb.ersuma.org/" class="cta-button" target="_blank">
            Accéder à la plateforme <i class="bi bi-arrow-right"></i>
        </a>

        <!-- Pied de page -->
        <div class="footer">
            <p>© 2021-2025 SIGWEB-ERSUMA | Tous droits réservés</p>
        </div>
    </div>
    <div id="particles-js" style="position: absolute; width: 100%; height: 100%; z-index: -1;"></div>
</body>
<!-- Liens vers la bibliothèque -->
<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script>
    particlesJS("particles-js", {
        particles: {
            number: {
                value: 30,
                density: {
                    enable: true,
                    value_area: 800
                }
            },
            color: {
                value: "#ffffff"
            },
            opacity: {
                value: 0.3,
                random: true
            },
            size: {
                value: 3,
                random: true
            },
            line_linked: {
                enable: false
            },
            move: {
                speed: 0.5,
                direction: "top",
                random: true
            }
        }
    });
</script>

</html>
