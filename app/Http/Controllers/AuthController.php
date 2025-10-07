<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
            'username'=>'required|string|max:255',
            'email'=>'required|string|email|unique:users',
            'password'=>'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return ResponseHandler::error("Echec de validation des données", 422,$validator->errors());
        }

        $user = $this->authService->register($request);

        return ResponseHandler::success("Enregistrement effectué avec succès.", $user);
    }

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
        $this->authService->logout();
        return response()->json(['message'=>'Successfully logged out']);
    }

    public function refresh()
    {
        return response()->json([
            'access_token'=>$this->authService->refresh(),
            'token_type'=>'bearer',
            'expires_in'=> (int) config('jwt.ttl') * 60,
        ]);
    }
}
