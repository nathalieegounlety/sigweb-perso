<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Services\contracts\UsersInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class UsersController extends Controller
{
    private UsersInterface $usersInterface;

    public function __construct(UsersInterface $usersInterface)
    {
        // restriction des accès
         $this->middleware('auth:api', ['except' => ['store']]);

        $this->usersInterface = $usersInterface;
        //$this->middleware('role:ADMINISTRATEUR,USER')->only(['index']);
        //$this->middleware('role:ADMIN')->only(['index']);
        //$this->middleware('permission:CREATE')->only(['index']);

    }

    #[OA\Get(
        path: "/v1/utilisateurs",
        summary: "Liste des utilisateurs",
        security: [["bearerAuth" => []]],
        tags: ["Users"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Succès",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/User")
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié"),
        ]
    )]
    public function index(Request $request)
    {
        // récupérer la liste
        try {
            $perPage = $request->get('per_page',50); // par défaut 50 éléments
            $data = $this->usersInterface->getUsers($request);

            // Transformer pour enlever le bloc 'links'
            $response = [
                'current_page' => $data->currentPage(),
                'data'         => $data->items(),
                'first_page_url' => $data->url(1),
                'last_page_url'  => $data->url($data->lastPage()),
                'next_page_url'  => $data->nextPageUrl(),
                'prev_page_url'  => $data->previousPageUrl(),
                'per_page'       => $data->perPage(),
                'total'          => $data->total(),
            ];
            return ResponseHandler::success('ok',$response);

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }

    public function getUser(Request $request){
        try {

            // validation des données du formulaire
            $validated = $request->validate([
                'id' => 'required|integer',
            ]);

            // procéder à l'enregistrement
            return $user = $this->usersInterface->getRole($request->id);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function store(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'name'=>'required|string|max:255',
                'username'=>'string|max:255',
                'email'=>'required|string|email|unique:users',
                'password'=>'required|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return ResponseHandler::error("Echec de validation des données", 422,$validator->errors());
            }

            // procéder à l'enregistrement
            return $processSave = $this->usersInterface->saveUser($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }
}
