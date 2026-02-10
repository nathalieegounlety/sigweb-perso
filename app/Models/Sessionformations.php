<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sessionformations extends Model
{

    use HasFactory;
    protected $table = 'sessionformations';
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
        'id_webinaire',
        'code_formation',
        'heure_debut',
        'duree_reel',
        'nbr_inscrit',
        'nbr_annule',
        'spectateurs_uniques',
        'nbr_total_utilisateur',
        'vues_simultanees_maxi',
    ];

    public function getFormation()
    {
        return $this->belongsTo(Formations::class,'code_formation','code_formation');
    }
}
