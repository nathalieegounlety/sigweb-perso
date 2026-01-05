<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Roles;
use App\Models\User;
use App\Repositories\Contracts\UsersRepositoryInterface;
use App\Services\contracts\AuthServiceInterface;
use App\Services\util\GenarateurCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService implements AuthServiceInterface
{
    // injection de l'interface repository pour la logique métier (enregistrement des données dans la BDD)
    private UsersRepositoryInterface $userRepository;
    private GenarateurCodeService $genarateurCodeService;

    public function __construct(UsersRepositoryInterface $userRepository, GenarateurCodeService $genarateurCodeService)
    {
        $this->userRepository = $userRepository;
        $this->genarateurCodeService = $genarateurCodeService;
    }

    public function register(Request $data): User
    {
        $hashPassword = Hash::make($data->password);

        // vérifier si l'utilisateur (staff) existe déjà
        if($data->username){
            $checkUsername = User::whereUsername(trim($data->username))->exists();
            if ($checkUsername){
                throw new \Exception("Le nom d'utilisateur existe déjà.");
            }
        }
        if($data->email){
            $checkEmail = User::whereEmail(trim($data->email))->exists();
            if ($checkEmail){
                throw new \Exception("Le nom d'utilisateur existe déjà.");
            }
        }

        $code_user = $this->genarateurCodeService->genererCodeUser();
        $saveUser = new User();

        if (!empty($code_user)){
            $saveUser->setAttribute("name",$data->nom_prenoms);
            $saveUser->setAttribute("username",$data->username);
            $saveUser->setAttribute("email",$data->email);
            $saveUser->setAttribute("code_user",$code_user);
            $saveUser->setAttribute("password",$hashPassword);
            $saveUser->save();
        }

        // enregistrer les rôles si disponible
        if (is_array($data->idRoles) && count($data->idRoles) > 0) {

            // récupérer uniquement les ids valident
            $getValidRolesId = Roles::whereIn('id',$data->idRoles)->pluck('id')->toArray();

            if (count($getValidRolesId) === 0){
                return $saveUser ; // ResponseHandler::success("Création efféctuée & aucun identifiant de role valide");
            }

            $saveUser->roles()->sync($data->idRoles);
            return $saveUser;

        } else {
            // return response with default rôle
            $getValidRolesId = Roles::whereId(6)->pluck('id')->toArray();
            if (count($getValidRolesId) === 0){
                return $saveUser;
            }
            $idRole = $getValidRolesId[0];
            $saveUser->roles()->sync($idRole);
            return $saveUser;
        }

    }

    public function login(array $credentials): ?array
    {
        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return null;
        }

        /** @var User $user */
        $user = Auth::guard('api')->user()->load('roles.permissions');

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => (int) config('jwt.ttl') * 60,
            'user'         => [
                'name'  => $user->name,
                'email' => $user->email,
                'photo' => $user->logo_path,
                'roles' => $user->roles->pluck('libelle'),
                'permissions' => $user->roles
                    ->flatMap->permissions
                    ->pluck('libelle')
                    ->unique()
                    ->values(),
            ]
        ];
    }

    public function logout(): \Illuminate\Http\JsonResponse
    {
        // Auth::logout();
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return ResponseHandler::success('Successfully logged out');
        } catch (\Exception $e) {
            return ResponseHandler::error('Erreur lors de la déconnexion', 500);
        }
    }

    public function refresh(): string
    {
        return Auth::refresh();
    }

    public function me(): ?User
    {
        return Auth::user();
    }
}
