<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscriptions extends Model
{

    use HasFactory;
    protected $table = 'inscriptions';
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
        'email',
        'heure_inscription',
        'etat_approbation',
        'id_webinaire',
        'code_formation',
        'nationalite',
        'presentiel',
        'genre',
        'code_inscription',
        'tarif_formation_id',
        'type_formation',
        'categorie_auditeur',
        'traiter',
        'valider',
        'observations',
        'traiter_par'
    ];

    public function getAuditeur()
    {
        return $this->belongsTo(Auditeurs::class,'email','email');
    }

    public function getFormation()
    {
        return $this->belongsTo(Formations::class,'code_formation','code_formation');
    }

    public function getTarifFormation()
    {
        return $this->belongsTo(Tarificationsformation::class,'tarif_formation_id');
    }

    public function getPays()
    {
        return $this->belongsTo(Pays::class,'pays');
    }
}
