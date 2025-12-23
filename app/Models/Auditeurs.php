<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auditeurs extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenoms',
        'email',
        'telephone',
        'ville',
        'genre',
        'secteur_activite',
        'organisme',
        'intitule_poste',
        'pays',
        'code_pays',
        'nationalite',
    ];
}
