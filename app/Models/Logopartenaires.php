<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logopartenaires extends Model
{
    
    use HasFactory;
    protected $table = 'logopartenaires';
    protected $primaryKey = 'id';
    public $incrementing = true;

    public $timestamps = true;

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
        'code_formation',
        'logo_path', 
        'largeur',
        'hauteur'
    ];

    public function getFormation()
    {
        return $this->belongsTo(Formations::class,'code_formation');
    } 
}
