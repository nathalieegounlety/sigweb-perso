<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participants extends Model
{
    use HasFactory;
    protected $table = 'participants';
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
        'heure_arrivee',
        'heure_depart',
        'temps_presence',
        'id_webinaire',
        'code_formation',
    ];

    public function getFormation()
    {
        return $this->belongsTo(Formations::class,'code_formation','code_formation');
    }

    public function getInscrit()
    {
        return $this->belongsTo(Inscriptions::class,'email','email');
    }
}
