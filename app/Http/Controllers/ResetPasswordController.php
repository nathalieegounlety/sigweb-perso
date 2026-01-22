<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{

    public function infoResetPassword(Request $request)
    {
        try {

            $request->validate([
                'token' => 'required',
            ]);
            // vérifier si le token est valide ou expiré
            $check_token = DB::table('password_reset_tokens')->where([ 'token' => $request->token ])->first();
            if ($check_token) {
                # code...
                $expiredAt = Carbon::parse($check_token->created_at)->addMinutes(15);

                if (now()->greaterThan($expiredAt)) {
                    return ResponseHandler::success(
                        'Code de réinitialisation du mot de passe expiré',
                        ['success' => false]
                    );
                }

                return ResponseHandler::success('ok',['success'=>true, 'token'=>$request->token]);

            } else {
                # code...
                return ResponseHandler::success('Code de réinitialisation du mot de passe invalid',['success'=>false]);

            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation ".$request, 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }


    public function reset(Request $request)
    {
        try {

            $request->validate([
                'email' => 'required|email|exists:users',
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required',
                'token' => 'required',

            ]);

            $updatePassword = DB::table('password_reset_tokens')
                ->where([
                    'email' => $request->email,
                    'token' => $request->token
                ])
                ->latest('created_at')
                ->first();

            if(!$updatePassword){
                return ResponseHandler::success('Invalid token!',['success'=>false]);
            }

            // vérifier si le token a expiré
            $expiredAt = Carbon::parse($updatePassword->created_at)->addSeconds(1200);
            $date_validation = date('Y-m-d H:i:s');

            if($date_validation > $expiredAt){ // token expiré
                return ResponseHandler::success('Code de réinitialisation de mot de passe expiré',['success'=>false]);
            }

            $user = User::where('email', $request->email)->update(['password' => Hash::make($request->password)]);

            DB::table('password_reset_tokens')->where(['email'=> $request->email])->delete();
            return ResponseHandler::success('Votre mot de passe a été changé',['success'=>true]);


        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation ".$request, 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
}
