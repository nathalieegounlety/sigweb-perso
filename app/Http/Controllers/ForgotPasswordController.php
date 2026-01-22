<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        Log::info($request);
        try {
            $validated = $request->validate([
                'email' => 'required|email',
            ]);

            // vérifier si le compte est valide
            if(User::where("email",$request->email)->exists()){
                // compte okay

            }else{
                // compte inactif
                return ResponseHandler::error("Nous ne trouvons pas d\'utilisateur avec cette adresse e-mail.");
            }

            $token = Str::random(64);
            $user = User::where("email",$request->email)->first();
            $checkExistCompte = DB::table('password_reset_tokens')->where('email',$user->email)->first();
            if($checkExistCompte){
                DB::table('password_reset_tokens')->update([
                    'token' => $token,
                    'created_at' => Carbon::now()
                ]);

            }else{
                DB::table('password_reset_tokens')->insert([
                    'email' => $request->email,
                    'token' => $token,
                    'created_at' => Carbon::now()
                ]);

            }

            Mail::send('emails.forgetPassword', ['token' => $token,'email'=>$request->email,'nom_prenoms'=>$user->name], function($message) use($request){
                $message->to($request->email);
                $message->subject('SIGWEB Réinitialisation du mot de passe '.date("Y-m-d H:i:s"));
            });

            return ResponseHandler::success("Mail de réinitialisation envoyé avec une durée de 15 minutes de validité.",['success'=>true]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation ".$request, 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
}
