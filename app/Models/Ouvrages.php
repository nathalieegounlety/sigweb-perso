<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ouvrages extends Model
{
    //

    use HasFactory;
    protected $table = 'ouvrages';
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
        'montant',
        'visuel_path',
        'nbre_page',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    public function getUserCreated()
    {
        return $this->belongsTo(User::class,'created_by','id');
    }

    public function getUserUpdated()
    {
        return $this->belongsTo(User::class,'updated_by','id');
    }
}
