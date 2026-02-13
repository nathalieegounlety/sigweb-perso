<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Panelistes extends Model
{
    
    use HasFactory;
    protected $table = 'panelistes';
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
        'a_participe',
        'username',
        'email',
        'pays',
        'heure_arrivee',
        'heure_depart',
        'temps_presence',
        'id_webinaire',
        'code_formation', 
    ];

    public function getInscrit()
    {
        return $this->belongsTo(Inscriptions::class,'id', 'email');
    }
}
