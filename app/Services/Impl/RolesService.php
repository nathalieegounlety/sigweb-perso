<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Permissions;
use App\Models\Roles;
use App\Repositories\Contracts\RolesRepositoryInterface;
use App\Services\contracts\RolesServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolesService implements RolesServiceInterface
{
    private RolesRepositoryInterface $rolesRepository;

    public function __construct(RolesRepositoryInterface $rolesRepository)
    {
        $this->rolesRepository = $rolesRepository;
    }

    public function saveRole(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement saveRole() method.
        $checkRole = Roles::whereLibelle($data->libelle)->exists();
        if ($checkRole) {
            return ResponseHandler::error("Le rôle existe déjà", 409);
        }
        // utilisation de transaction pour le process d'enregistrement des permissions

        return DB::transaction(function ()use ($data){

            $role = new Roles();
            $role->setAttribute('libelle', $data->libelle);

            if ($user = auth('api')->user()) {
                $role->setAttribute('created_by', $user->id);
                $role->setAttribute('updated_by', $user->id);
            }

            $role->save();

            // enregistrer les permissions si disponibles
            if (is_array($data->idPermissions) && count($data->idPermissions) > 0) {
                // récupérer uniquement les ids valident
                $getValidPermissionsId = Permissions::whereIn('id',$data->idPermissions)->pluck('id')->toArray();

                if (count($getValidPermissionsId) === 0){
                    return ResponseHandler::success("Création efféctuée & aucun identifiant de permission valide");
                }
                $role->permissions()->sync($data->idPermissions);
                return ResponseHandler::success("Création efféctuée ainsi que l'association des permissions");
            } else {
                // return response without permission
                return ResponseHandler::success("Création efféctuée");
            }

        });


    }

    public function getRole(int $id): Roles
    {
        // TODO: Implement getRole() method.
        return $queryRoles = Roles::select('id', 'libelle', 'updated_at')->withOnly('permissions:libelle as libelle_permission')->findOrFail($id);
    }


    public function getRoles(Request $request): LengthAwarePaginator
    {
        // TODO: Implement getRoles() method.
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Filtres spécifiques
        $filterLibelle = $request->input('filter_libelle');
        $filterIsEnable = $request->input('filter_is_enable');
        $filterUpdatedAt = $request->input('filter_updated_at');
        $filterAuthor = $request->input('filter_author');

        $query = Roles::select('roles.id', 'roles.libelle', 'roles.updated_at', 'roles.is_enable', 'roles.updated_by', 'roles.created_by')
            ->with([
                'getUserUpdated:id,name,email,code_user',
                'getUserCreated:id,name,email,code_user',
                'permissions:id,libelle'
            ]);

        // On fait la jointure dès qu'on a besoin de users.name (recherche globale OU filtre auteur OU tri par auteur)
        $needsUserJoin = $search || $filterAuthor || $sortField === 'get_user_updated.name' || $sortField === 'get_user_created.name';

        if ($needsUserJoin) {
            $query->leftJoin('users', 'roles.updated_by', '=', 'users.id');
            $query->leftJoin('users', 'roles.created_by', '=', 'users.id');
        }

        // Recherche globale sur plusieurs champs
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('roles.libelle', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%"); // Recherche sur auteur
            });
        }

        // Filtres par colonne
        if ($filterLibelle) {
            $query->where('roles.libelle', 'like', "%{$filterLibelle}%");
        }
        if ($filterIsEnable !== null && $filterIsEnable !== '') {
            $query->where('roles.is_enable', $filterIsEnable); // 0 ou 1
        }
        if ($filterUpdatedAt) {
            $query->whereDate('roles.updated_at', $filterUpdatedAt);
        }
        if ($filterAuthor) {
            $query->where('users.name', 'like', "%{$filterAuthor}%");
        }

        // Tri
        if ($sortField === 'get_user_updated.name' || $sortField === 'get_user_created.name' ) {
            $query->orderBy('users.name', $sortOrder);
        } else {
            $query->orderBy("roles.{$sortField}", $sortOrder);
        }

        $query->orderBy($sortField, $sortOrder);

        return $query->paginate($perPage);
    }

    public function update(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement update() method.
        $role = Roles::find($data['id']);

        if (!$role) {
            return ResponseHandler::error("Rôle à modifier introuvable", 404);
        }

        // check existing data
        $checkData = Roles::whereLibelle($data['libelle'])->where("id", "!=", $role->id)->exists();

        if ($checkData) {
            return ResponseHandler::error("Le libellé existe déjà", 409);
        } else {
            // process update
            $role->setAttribute('libelle', $data['libelle']);
            $role->setAttribute('updated_by', auth('api')->id());
            $role->update();
        }

        // enregistrer les permissions si disponibles
        if (is_array($data->idPermissions) && count($data->idPermissions) > 0) {
            // récupérer uniquement les ids valident
            $getValidPermissionsId = Permissions::whereIn('id',$data->idPermissions)->pluck('id')->toArray();

            if (count($getValidPermissionsId) === 0){
                return ResponseHandler::success("Modification efféctuée & aucun identifiant de permission valide");
            }
            $role->permissions()->sync($data->idPermissions);
            return ResponseHandler::success("Modification efféctuée ainsi que l'association des permissions");
        } else {
            // return response without permission
            return ResponseHandler::success("Modification efféctuée");
        }


    }

    public function remove(int $idRole): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement remove() method.
        $role = Roles::find($idRole);
        if (!$role) {
            return ResponseHandler::error("Rôle à supprimer introuvable", 404);
        }

        $deleteRole = $role->delete();

        if ($deleteRole) {
            return ResponseHandler::success("Suppression effectuée.");
        } else {
            return ResponseHandler::error("Echec lors de la suppression, vous ne pouvez pas surpprimer ce rôle.", 401);
        }
    }
}
