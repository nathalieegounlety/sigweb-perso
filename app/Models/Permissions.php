<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permissions extends Model
{
        use HasFactory;
        protected $table = 'permissions';
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
            'description',
            'created_by',
            'updated_by'
        ];

        public function getUserCreated()
        {
            return $this->belongsTo(User::class,'created_by');
        }

        public function getUserUpdated()
        {
            return $this->belongsTo(User::class,'updated_by');
        }

        // récupérer les rôles associés à une permission
        public function roles()
        {
            return $this->belongsToMany(Roles::class,'permissions_roles');
        }
}
