<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tranches extends Model
{
    use HasFactory;
    protected $table = 'tranches';
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
        'code_inscription',
        'email',
        'tranche_1',
        'tranche_2',
        'echeance_1',
        'echeance_2',
        'statut',
        'nbre_relance',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];
}
