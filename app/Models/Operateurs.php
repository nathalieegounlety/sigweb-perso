<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operateurs extends Model
{
        use HasFactory;
        protected $table = 'operateurs';
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
            'modepays_id',
            'operateur',
            'devise',
            'logo_path', 
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
            'is_enable',
        ];

        public function getModepays()
        {
            return $this->belongsTo(Modepays::class,'modepays_id');
        }

        public function getUserCreated()
        {
            return $this->belongsTo(User::class,'created_by');
        }

        public function getUserUpdated()
        {
            return $this->belongsTo(User::class,'updated_by');
        }
}
