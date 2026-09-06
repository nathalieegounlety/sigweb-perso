<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormationsTranches extends Model
{
    
    use HasFactory;
    protected $table = 'formations_tranches';
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
        'formation_id',
        'grilletarifaire_id',
        'tranche',
        'taux_1', 
        'taux_2',
        'echeance_1',
        'echeance_2',
        'categorie_auditeur',
        'reduction', 
        'created_at',
        'updated_at', 
    ];
}
