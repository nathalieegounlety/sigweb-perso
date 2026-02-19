<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Services\contracts\RolesServiceInterface;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    private RolesServiceInterface $rolesService;

    public function __construct(RolesServiceInterface $rolesService)
    {
        // restriction des accès
         $this->middleware('auth:api'); // ['except' => ['store','index','update','remove']]

        $this->rolesService = $rolesService;
        //$this->middleware('role:ADMINISTRATEUR,USER')->only(['index']);
        //$this->middleware('role:ADMIN')->only(['index']);
        //$this->middleware('permission:CREATE')->only(['index']);

    }

    public function index(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $perPage = $request->get('per_page',50); // par défaut 50 éléments
            $roles = $this->rolesService->getRoles($request);

            // Transformer pour enlever le bloc 'links'
            $response = [
                'current_page' => $roles->currentPage(),
                'data'         => $roles->items(),
                'first_page_url' => $roles->url(1),
                'last_page_url'  => $roles->url($roles->lastPage()),
                'next_page_url'  => $roles->nextPageUrl(),
                'prev_page_url'  => $roles->previousPageUrl(),
                'per_page'       => $roles->perPage(),
                'total'          => $roles->total(),
            ];
            return ResponseHandler::success('ok',$response);

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }

    public function getRole(Request $request){
        try {

                    // validation des données du formulaire
                    $validated = $request->validate([
                        'idRole' => 'required|integer',
                    ]);

                    // procéder à l'enregistrement
                    return $role = $this->rolesService->getRole($request->idRole);

                } catch (\Illuminate\Validation\ValidationException $e) {
                    return ResponseHandler::error("Erreur de validation", 422, $e->errors());
                } catch (\Exception $e) {
                    return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
                }
    }

    public function store(Request $request)
    {

        try {

            // validation des données du formulaire
            $validated = $request->validate([
                'libelle' => 'required|string',
                'idPermissions' => 'array'
            ]);

            // procéder à l'enregistrement
            return $processSave = $this->rolesService->saveRole($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }

    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'libelle' => 'required|string',
                'id' => 'required|integer'
            ]);

            return $response = $this->rolesService->update($request);

            // call update service
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function remove(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|integer'
            ]);

            return $response = $this->rolesService->remove($request->id);

            // call update service
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
}
