<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Services\contracts\PermissionsServiceInterface;
use Illuminate\Http\Request;

class PermissionsController extends Controller
{
    private PermissionsServiceInterface $permissionService;

    public function __construct(PermissionsServiceInterface $permissionService)
    {
        $this->middleware('auth:api', ['except' => ['remove']]);
        $this->permissionService = $permissionService;
    }

    public function index(Request $request)
    {
        // récupérer la liste des rôles
        try {
            $perPage = $request->get('per_page',50); // par défaut 50 éléments
            $permissions = $this->permissionService->getPermissions($perPage);

            // Transformer pour enlever le bloc 'links'
            $response = [
                'current_page' => $permissions->currentPage(),
                'data'         => $permissions->items(),
                'first_page_url' => $permissions->url(1),
                'last_page_url'  => $permissions->url($permissions->lastPage()),
                'next_page_url'  => $permissions->nextPageUrl(),
                'prev_page_url'  => $permissions->previousPageUrl(),
                'per_page'       => $permissions->perPage(),
                'total'          => $permissions->total(),
            ];
            return ResponseHandler::success('ok',$response);

        }catch (\Exception $e){
            return ResponseHandler::error("Erreur lors de la récupération", 500, $e->getMessage());
        }
    }

    public function getPermission(Request $request){
        try {

                    // validation des données du formulaire
                    $validated = $request->validate([
                        'idPermission' => 'required|integer',
                    ]);

                    // procéder à l'enregistrement
                    return $permission = $this->permissionService->getPermission($request->idPermission);

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
                'description' => 'string'
            ]);

            // procéder à l'enregistrement
            return $processSave = $this->permissionService->savePermission($request);

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
                'description' => 'string',
                'id' => 'required|integer'
            ]);

            return $response = $this->permissionService->update($request);

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

           return $response = $this->permissionService->remove($request->id);

            // call update service
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation", 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
}
