<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Roles extends Model
{
            use HasFactory;

            protected $table = 'roles';
            protected $primaryKey = 'id';
            public $incrementing = true;


            /**
             * The attributes that are mass assignable.
             *
             * @var array
             */
            protected $guarded = [];

            /**
             * The attributes that are mass assignable.
             *
             * @var array
             */
            protected $fillable = [
                'libelle',
                'created_by',
                'updated_by'
            ];

            public function getUserCreated()
            {
                return $this->belongsTo(User::class,'created_by');
            }

            public function getUserUpdated()
            {
                return $this->belongsTo(User::class,'updated_by','id');
            }

            public function permissions()
            {
                return $this->belongsToMany(Permissions::class,'permissions_roles','role_id','permission_id');
            }

            // récupérer les utilisateurs ayant le rôle courant
            public function users()
            {
                return $this->belongsToMany(User::class,'roles_users','role_id','user_id');
            }

            // vérifier si un rôle à une permission
            public function checkPermissionInRole(string $permissionId): bool {
                return $this->permissions->pluck("libelle")->contains($permissionId);
            }

}
