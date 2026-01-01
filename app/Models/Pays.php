<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pays extends Model
{

    use HasFactory;
    protected $table = 'pays';
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
        'nom_pays',
        'code_iso',
        'indicatif',
        'nationalite',
        'flag',
        'coordonnee_maps',
        'nationalite',
        'created_at',
        'updated_at',
        'updated_by',
    ];
}
