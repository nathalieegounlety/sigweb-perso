<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "User",
    type: "object",
    title: "User",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "name", type: "string", example: "Jean"),
        new OA\Property(property: "email", type: "string", example: "jean@example.com"),
        new OA\Property(property: "password", type: "string", example: "12345678"),
    ]
)]
class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'code_user',
        'username',
        'actif',
        'login_count',
        'first_login',
        'last_login'
    ];


    // récupérer la liste des rôles d'un utilisateurs
    public function roles()
    {
       return $this->belongsToMany(Roles::class, 'roles_users' ,'user_id','role_id');
    }

    // assigner un ou plusieurs rôle à un utilisateur
    public function assignRoles(array $roleIds)
    {
        $this->roles()->sync($roleIds);
        return $this->roles;
    }

    // vérifier si le rôle à une permission spécifique
    public function hasPermission(string $permission): bool
    {
        // parcourir les rôles de l'utilisateurs et vérifier si au moin un rôle à la permission requise
        foreach ($this->roles as $role) {
            # code...
            $checkPermission = $role->permissions->pluck("libelle")->contains($permission);
            if ($checkPermission) {
                # code...
                return true;
            }
        }

        return false;
    }

    // retourne la liste des rôles (utile à la connexion)
    public function getRolesList()
    {
        return $this->roles()->get(['id','libelle']);
    }




    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    // JWT
    public function getJWTIdentifier() {
        return $this->getKey();
    }

    public function getJWTCustomClaims() {
        return [];
    }

}
