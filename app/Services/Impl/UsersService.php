<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Roles;
use App\Models\User;
use App\Services\contracts\UsersInterface;
use App\Services\util\GenarateurCodeService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsersService implements UsersInterface
{
    private GenarateurCodeService $genarateurCodeService;

    public function __construct(GenarateurCodeService $genarateurCodeService)
    {
        $this->genarateurCodeService = $genarateurCodeService;
    }

    public function saveUser(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement saveUser() method.
        $hashPassword = Hash::make($data->password);

        // vérifier si l'utilisateur (staff) existe déjà
        if($data->username){
            $checkUsername = User::whereUsername(trim($data->username))->exists();
            if ($checkUsername){
                return ResponseHandler::error("Le nom d'utilisateur existe déjà.", 409);
            }
        }

        if($data->email){
            $checkEmail = User::whereEmail(trim($data->email))->exists();
            if ($checkEmail){
                return ResponseHandler::error("Le nom d'utilisateur existe déjà.", 409);
            }
        }

        $code_user = $this->genarateurCodeService->genererCodeUser();
        $saveUser = new User();

        if (!empty($code_user)){
            if($data->name){ $saveUser->setAttribute("name",trim($data->name)); }
            if($data->nom){ $saveUser->setAttribute("name",trim($data->nom).' '.trim($data->prenoms)); }
            $saveUser->setAttribute("name",$data->name);
            if($data->username){ $saveUser->setAttribute("username",$data->username); }
            $saveUser->setAttribute("email",trim($data->email));
            $saveUser->setAttribute("code_user",$code_user);
            $saveUser->setAttribute("password",$hashPassword);
            $saveUser->save();
        }

        // enregistrer les rôles si disponible
        if (is_array($data->idRoles) && count($data->idRoles) > 0) {

            // récupérer uniquement les ids valident
            $getValidRolesId = Roles::whereIn('id',$data->idRoles)->pluck('id')->toArray();

            if (count($getValidRolesId) === 0){
                return  ResponseHandler::success("Création efféctuée & aucun identifiant de role valide");
            }

            $saveUser->roles()->sync($data->idRoles);
            return  ResponseHandler::success("Création efféctuée");

        } else {
            // return response with default rôle
            $getValidRolesId = Roles::whereId(6)->pluck('id')->toArray();
            if (count($getValidRolesId) === 0){
                return  ResponseHandler::success("Création efféctuée & aucun identifiant de role valide");
            }
            $idRole = $getValidRolesId[0];
            $saveUser->roles()->sync($idRole);
            return  ResponseHandler::success("Création efféctuée");
        }
    }

    public function getUsers(Request $request): LengthAwarePaginator
    {
        // TODO: Implement getUsers() method.
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Filtres spécifiques
        $filterActif = $request->input('filter_actif');
        $filterUpdatedAt = $request->input('filter_updated_at');

        $queryData = User::select('users.id','users.code_user','users.name','users.username','users.email','users.actif','users.login_count','users.first_login','users.last_login')
            ->with('roles:id,libelle')
            ->whereDoesntHave('roles', function ($q) {
                $q->where('roles.id', 6);
            });

        // Recherche globale sur plusieurs champs
        if ($search) {
            $queryData->where(function ($q) use ($search) {
                $q->where('users.code_user', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.username', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%"); // Recherche sur auteur
            });
        }

        // Filtres par colonne
        if ($filterActif !== null && $filterActif !== '') {
            $queryData->where('users.actif', $filterActif); // 0 ou 1
        }
        if ($filterUpdatedAt) {
            $queryData->whereDate('users.updated_at', $filterUpdatedAt);
        }
        // Tri
            $queryData->orderBy("users.{$sortField}", $sortOrder);

        return $queryData->paginate($perPage);
    }

    public function update(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement update() method.
    }

    public function remove(int $idRole): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement remove() method.
    }

    public function getUser(int $id): ?array
    {
        // TODO: Implement getUser() method.
        $user = User::find($id)->load('roles.permissions');

        return [
            'user'         => [
                'name'  => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('libelle'),
                'permissions' => $user->roles
                    ->flatMap->permissions
                    ->pluck('libelle')
                    ->unique()
                    ->values(),
            ]
        ];
    }
}
