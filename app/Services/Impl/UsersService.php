<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Roles;
use App\Models\User;
use App\Services\contracts\UsersInterface;
use App\Services\util\GenarateurCodeService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UsersService implements UsersInterface
{
    private GenarateurCodeService $genarateurCodeService;

    public function __construct(GenarateurCodeService $genarateurCodeService)
    {
        $this->genarateurCodeService = $genarateurCodeService;
    }

    private const ROLE_PAR_DEFAUT = 6;

    public function createUser(array $data): User
{
    $email    = strtolower(trim((string) ($data['email'] ?? '')));
    $username = !empty($data['username']) ? trim($data['username']) : null;
    $password = (string) ($data['password'] ?? '');
 
    // Nom : "nom prenoms" s'ils sont fournis, sinon "name"
    $name = !empty($data['nom'])
        ? trim(trim($data['nom']) . ' ' . trim((string) ($data['prenoms'] ?? '')))
        : trim((string) ($data['name'] ?? ''));
 
    /* ---------- Contrôles métier ---------- */
    if ($email === '') {
        throw new \DomainException("L'adresse e-mail est obligatoire.");
    }
    if ($password === '') {
        throw new \DomainException("Le mot de passe est obligatoire.");
    }
    if ($username && User::where('username', $username)->exists()) {
        throw new \DomainException("Le nom d'utilisateur {$username} existe déjà.");
    }
    if (User::where('email', $email)->exists()) {
        throw new \DomainException("Un compte existe déjà avec l'adresse {$email}.");
    }
 
    // Rôles 
    $rolesDemandes = is_array($data['idRoles'] ?? null) ? $data['idRoles'] : [];
 
    if (count($rolesDemandes) > 0) {
        $roles = Roles::whereIn('id', $rolesDemandes)->pluck('id')->all();
        if (count($roles) === 0) {
            throw new \DomainException("Aucun des rôles sélectionnés n'est valide.");
        }
    } else {
        $roles = Roles::whereKey(self::ROLE_PAR_DEFAUT)->pluck('id')->all();
        if (count($roles) === 0) {
            throw new \DomainException("Le rôle par défaut n'existe pas.");
        }
    }
 
    $codeUser = $this->genarateurCodeService->genererCodeUser();
    if (empty($codeUser)) {
        throw new \RuntimeException("Impossible de générer le code utilisateur.");
    }
 
    /* ---------- Création : utilisateur + rôles, tout ou rien ---------- */
    return DB::transaction(function () use ($name, $username, $email, $codeUser, $password, $roles) {
        $user = new User();
        $user->forceFill([
            'name'      => $name,
            'username'  => $username,
            'email'     => $email,
            'code_user' => $codeUser,
            'password'  => Hash::make($password),
        ])->save();
 
        $user->roles()->sync($roles);   // uniquement les ids validés
 
        return $user;
    });
}
 
 
/**
 * Point d'entrée de la route API : même format de réponse que le reste de l'application.
 */
public function saveUser(Request $data): \Illuminate\Http\JsonResponse
{
    try {
        $user = $this->createUser($data->all());
 
    } catch (\DomainException $e) {
        return ResponseHandler::success($e->getMessage(), [
            'success' => false,
            'message' => $e->getMessage(),
        ]);
 
    } catch (QueryException $e) {
        // 1062 : email ou username déjà pris par une requête simultanée
        if (($e->errorInfo[1] ?? null) == 1062) {
            return ResponseHandler::success("Cet utilisateur existe déjà", [
                'success' => false,
                'message' => "Un compte existe déjà avec cette adresse e-mail ou ce nom d'utilisateur.",
            ]);
        }
        Log::error('Création utilisateur - erreur SQL : ' . $e->getMessage());
        return ResponseHandler::error("Erreur lors de la création de l'utilisateur", 500, $e->getMessage());
 
    } catch (\Throwable $e) {
        Log::error('Création utilisateur - erreur : ' . $e->getMessage());
        return ResponseHandler::error("Erreur lors de la création de l'utilisateur", 500, $e->getMessage());
    }
 
    return ResponseHandler::success("Création effectuée", [
        'success' => true,
        'message' => "Utilisateur créé avec succès",
        'user_id' => $user->id,
    ]);
}

    public function saveUserOld(Request $data): \Illuminate\Http\JsonResponse
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

    // définition de la fonction permettant d'initialiser la requête de sélection
    public function requestUsersQuery(Request $request){
        $queryData = User::select('users.id','users.code_user','users.name','users.username','users.email','users.actif','users.login_count','users.first_login','users.last_login','users.updated_at','users.created_at')
            ->with('roles:id,libelle')
            ->whereDoesntHave('roles', function ($q) {
                $q->where('roles.id', 6);
            }); 
         // Recherche globale sur plusieurs champs
        $search = $request->input('search');
        if ($search) {
            $queryData->where(function ($q) use ($search) {
                $q->where('users.code_user', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.username', 'like', "%{$search}%")  
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWherehas('roles', fn ($r) => $r->where('roles.libelle','like',"%{$search}%")); // Recherche sur auteur
            });
        }
        // définition des filtres de multiples critères
        $queryData
        ->when($request->filled('role_id'), fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('roles.id', $request->role_id)))
        ->when($request->has('actif') && $request->actif !== '', fn ($q) => $q->where('actif', (int) $request->actif)) 
        ->when($request->filled('date_debut'), fn ($q) => $q->whereDate('updated_at', '>=', $request->date_debut))
        ->when($request->filled('date_fin'),   fn ($q) => $q->whereDate('updated_at', '<=', $request->date_fin));
        
        return $queryData;
    }

    private function appliquerFiltre($query, Request $request)
    {
        // définition des champs de trie asc ou desc
        $allowed = ['name', 'email', 'actif', 'updated_at'];
        $field = in_array($request->sort_field, $allowed, true) ? $request->sort_field : 'updated_at';
        return $query->orderBy($field, $request->sort_order === 'asc' ? 'asc' : 'desc');
    }

    public function getUsers(Request $request): LengthAwarePaginator
    {
        // TODO: Implement getUsers() method.
        return $this->appliquerFiltre($this->requestUsersQuery($request), $request)
        ->paginate($request->input('per_page', 50));

        /* $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Filtres spécifiques
        $filterActif = $request->input('filter_actif');
        $filterUpdatedAt = $request->input('filter_updated_at');

        $queryData = User::select('users.id','users.code_user','users.name','users.username','users.email','users.actif','users.login_count','users.first_login','users.last_login','users.updated_at')
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

        return $queryData->paginate($perPage); */
    }

    public function getExportUsers(Request $request)
    {
        return $this->appliquerFiltre($this->requestUsersQuery($request), $request)->get();
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
