<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Autreparticipant extends Model
{
    use HasFactory;
    protected $table = 'autresparticipants';
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
                        'username',  
                        'heure_arrivee',
                        'heure_depart',
                        'temps_presence',
                        'pays',
                        'id_webinaire',
                        'code_formation',  
    ];
}
