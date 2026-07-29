<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "SIGWEB API Documentation",
    description: "Documentation de l'API SIGWEB"
)]
#[OA\Server(
    url: \L5_SWAGGER_CONST_HOST,
    description: "Serveur local"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "Entrer le token JWT sous la forme: Bearer {token}"
)]

// Déclaration des modules (tags)
#[OA\Tag(
    name: "Auth",
    description: "Authentification et gestion des tokens JWT"
)]
#[OA\Tag(
    name: "Users",
    description: "Gestion des utilisateurs"
)]
#[OA\Tag(
    name: "formations",
    description: "Gestion des formations"
)]
class OpenApiSpec
{
}