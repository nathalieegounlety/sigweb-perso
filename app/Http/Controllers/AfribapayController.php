<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Services\contracts\AfribapayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AfribapayController extends Controller
{
     private AfribapayInterface $afribapayService;

    public function __construct(AfribapayInterface $afribapayService)
    {
        // restriction des accès
        $this->middleware('auth:api',['except' => ['generateToken','getListeVar','sendPayIn','checkPayIn']]);
        $this->afribapayService = $afribapayService;
    }

    
    /**
     * Store a newly created resource in storage.
     */
    public function geToken(Request $request)
    {
        try { 
            // procéder à l'enregistrement
             $token = $this->afribapayService->getToken();
             return ResponseHandler::success("ok",$token);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function generateToken()
    {
        try { 
            // procéder à l'enregistrement
             $token = $this->afribapayService->generateToken();
             return ResponseHandler::success("ok",$token);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function getHeaders()
    {
        return [
            'Authorization' => 'Bearer '.$this->getToken(),
            'Accept' => 'application/json'
        ];
    }

    public function requestAfribapay(string $method,string $url,array $data = [])
    {
        $response = Http::withHeaders(
            $this->getHeaders()
        )->$method($url, $data);

        if ($response->status() == 401) {

            Cache::forget('afribapay_token');

            $response = Http::withHeaders([
                'Authorization' =>
                    'Bearer '.$this->generateToken()
            ])->$method($url, $data);
        }

        return $response;
    }

    public function getListeVar(Request $request){
        try { 

            return $data = $this->afribapayService->listeVariables();

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function sendPayIn(Request $request){
        try { 

            return $data = $this->afribapayService->payIn($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function checkPayIn(Request $request, $id){
        try { 

            return $data = $this->afribapayService->checkPayIn($request, $id);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function afribapay_sign($json, $api_key) {
        $hash = hash_hmac('sha256', $json, $api_key);
        return $hash;
    }

    public function webhook(Request $request){
       
        // You can find your endpoint's secret key in your webhook settings
        $api_key = env("AFRIBAPAY_SECRET_API_KEY"); 

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_X_FEDAPAY_SIGNATURE'];
        // Retrieve AfribaPAY signature from headers
        $afribaPaySignature = $_SERVER['HTTP_AFRIBAPAY_SIGN'];
        // Sign the payload using your api_key
        $computedSignature = $this->afribapay_sign($payload, $api_key);  

        Log::info($computedSignature);


        /*
        $event = null;

        try {
            $event = \FedaPay\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload 
            return response()->json(["data" => null, "statut" => "Invalid payload"], 200);
            exit();
        } catch (\FedaPay\Error\SignatureVerification $e) {
            // Invalid signature

            return response()->json(["data" => null, "statut" => "Invalid payload"], 200);
            exit();
        }*/
    }

}
