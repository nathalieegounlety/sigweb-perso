<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Permissions;
use App\Services\contracts\PermissionsServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class PermissionsService implements PermissionsServiceInterface
{

    public function savePermission(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement savePermission() method.
        // vérifier si la permission existe déjà
        $checkPermission = Permissions::whereLibelle($data->libelle)->exists();
        if ($checkPermission) {
            return ResponseHandler::error("La permission existe déjà", 409);
        }
        $permission = new Permissions();
        $permission->setAttribute('libelle', $data->libelle);
        $permission->setAttribute('description', $data->description);

        if (auth('api')->user()) {
            $user = auth('api')->user();
            $permission->setAttribute('created_by', $user->id);
            $permission->setAttribute('updated_by', $user->id);
        }

        $permission->save();
        return ResponseHandler::success("Création efféctuée");

    }

    public function getPermission(int $id): Permissions
    {
        // TODO: Implement getPermission() method.
        $permission = Permissions::find($id);
        if (!$permission) {
            return ResponseHandler::error("La permission n'existe pas", 409);
        }
        return ResponseHandler::success("ok", $permission);
    }

    public function getPermissions(int $perPage): LengthAwarePaginator
    {
        // TODO: Implement getPermissions() method.
        $queryPermissions = Permissions::with('getUserUpdated:id,name,email,code_user')
            ->select('permissions.id', 'permissions.libelle', 'permissions.updated_at')
            ->paginate($perPage);

        return $queryPermissions;
    }

    public function update(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement update() method.
        $permission = Permissions::find($data->id);

        if (!$permission) {
            return ResponseHandler::error("Permission à modifier introuvable",404);
        }
        // check existing data
        $checkData = Permissions::whereLibelle($data->libelle)->where("id", "!=", $permission->id)->exists();

        if ($checkData) {
            return ResponseHandler::error("Le libellé existe déjà");
        } else {
            // process update
            $permission->setAttribute('libelle', $data->libelle);
            if(!is_null($data->description)){
                $permission->setAttribute('description', $data->description);
            }
            $permission->setAttribute('updated_by', auth('api')->id());
            $permission->update();
            return ResponseHandler::success("Modification effectuée avec succès");
        }
    }

    public function remove(int $idPermission): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement remove() method.
        // TODO: Implement remove() method.
        $permission = Permissions::find($idPermission);
        if (!$permission) {
            return ResponseHandler::error("Permission à supprimer introuvable",404);
        }

        $deletePermission = $permission->delete();

        if ($deletePermission) {
            return ResponseHandler::success("Suppression effectuée.");
        } else {
            return ResponseHandler::error("Echec lors de la suppression, vous ne pouvez pas surpprimer cette permission : $permission->libelle.",401);
        }
    }
}
