<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modepaiements extends Model
{
        use HasFactory;
        protected $table = 'modepaiements';
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
            'updated_by',
            'created_at',
            'updated_at',
            'statut',
        ];

        public function getUserCreated()
        {
            return $this->belongsTo(User::class,'created_by');
        }

        public function getUserUpdated()
        {
            return $this->belongsTo(User::class,'updated_by');
        }
}
