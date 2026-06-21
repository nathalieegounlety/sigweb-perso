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
        $this->middleware('auth:api',['except' => ['generateToken','getListeVar','sendPayIn','checkPayIn','webhook']]);
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

        // Decode the JSON payload into a PHP associative array
        $data = json_decode($payload, true);
        // Check if the payload was successfully decoded
        if ($data === null) {
            // Handle the error if the JSON could not be decoded
            http_response_code(400); // Respond with a 400 Bad Request status
            //echo "Invalid JSON received."; // Output error message
            Log::info("Invalid JSON received.");
            exit; // Stop further execution
        }
        // START Signature Verification
        // Retrieve AfribaPAY signature from headers
        $afribaPaySignature = $_SERVER['HTTP_AFRIBAPAY_SIGN'] ?? null;  
        // Sign the payload using your api_key
        $computedSignature = $this->afribapay_sign($payload, $api_key);  

        //Log::info($computedSignature);

        // Compare the signature received with the computed one
        if (hash_equals($computedSignature, $afribaPaySignature)) {
            // Signature is valid, meaning the webhook is from AfribaPAY
            //echo "Valid webhook";
            Log::info("Valid webhook");
        } else {
            // Signature mismatch, possibly indicating that the request did not originate from AfribaPAY
            http_response_code(403); // Respond with a 403 Forbidden status
            //echo "Invalid webhook";
            Log::info("Invalid webhook"); 
            exit; // Stop further execution
        }
        // END Signature Verification
        Log::info($data);
        // Extract important data from the decoded payload
        $orderId = $data['order_id'] ?? null;
        $status = $data['status'] ?? null;
        // Optional: Log the data for troubleshooting or record-keeping purposes
        file_put_contents("webhook_log.txt", date('Y-m-d H:i:s') . " - Received webhook: " . json_encode($data) . PHP_EOL, FILE_APPEND);
        // Process the transaction status
        if ($status === 'SUCCESS') {
            // Handle successful transaction (e.g., update order status in your database)
            http_response_code(200); // Respond with a 200 OK status
            //echo "Transaction successful!";
            Log::info("Transaction successful!");
        } elseif ($status === 'FAILED') {
            // Handle failed transaction
            http_response_code(200); // Respond with a 200 OK status
            //echo "Transaction failed.";
            Log::info("Transaction failed.");
        } else {
            // Handle any unexpected statuses
            http_response_code(400); // Respond with a 400 Bad Request status
            //echo "Unknown status.";
            Log::info("Unknown status.");
        }


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
