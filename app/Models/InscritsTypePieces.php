<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InscritsTypePieces extends Model
{
    use HasFactory;
    protected $table = 'inscrits_type_pieces';
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
        return $this->belongsTo(User::class,'updated_by');
    }
}
