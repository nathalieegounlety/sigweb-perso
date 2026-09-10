<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Models\User;
use App\Services\contracts\AuthServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{

    private AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom_prenoms'=>'required|string|max:255',
            'username'=>'string|max:255',
            'email'=>'required|string|email|unique:users',
            'password'=>'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return ResponseHandler::error("Echec de validation des données", 422,$validator->errors());
        }

        $user = $this->authService->register($request);

        return ResponseHandler::success("Enregistrement effectué avec succès.", $user);
    }

    #[OA\Post(
        path: "/v1/login",
        summary: "Authentification",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "secret123"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "token", type: "string"),
                        // ajoute ici les autres champs que renvoie $data
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Identifiants invalides",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Invalid credentials"),
                    ]
                )
            ),
        ]
    )]
    public function login(Request $request)
    {

        $data = $this->authService->login($request->only('email','password'));

        if (!$data) {
            return response()->json(['error'=>'Invalid credentials'],401);
        }

        return response()->json($data);
    }

    public function me()
    {
        return response()->json($this->authService->me());
    }

    public function logout()
    {
       return $this->authService->logout();
    }

    public function refresh()
    {
        return response()->json([
            'access_token'=>$this->authService->refresh(),
            'token_type'=>'bearer',
            'expires_in'=> (int) config('jwt.ttl') * 60,
        ]);
    }

    
    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $exists = User::where('email', $request->email)->exists();
        return ResponseHandler::success('ok',['exists'=>$exists]); 
    }
}
