<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InscriptionsDsc extends Model
{
    use HasFactory;
    protected $table = 'inscriptions_dsc';
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
        'inscrits_id',
        'code_inscription',
        'traiter_par',
        'updated_by',
        'created_at',
        'updated_at',
    ];

}
