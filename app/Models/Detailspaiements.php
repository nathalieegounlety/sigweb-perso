<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detailspaiements extends Model
{
    use HasFactory;
    protected $table = 'detailspaiements';
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
        'reference_paiement',
        'id_tarification_formation',
        'id_reduction',
        'statut_paiement',
        'statut_traitement',
        'created_at',
        'updated_at',
        'preuve_path',
        'email',
        'code_formation',
        'code_inscription',
        'montant_paye',
        'montant_restant',
        'modepaiement_id',
        'is_paiement_partiel',
        'reference_paiement_partiel',
        'commentaire'
    ];

    public function getReduction()
    {
        return $this->belongsTo(Reductions::class,'id_reduction','id');
    }

    public function getTarification()
    {
        return $this->belongsTo(Tarificationsformation::class,'id_tarification_formation','id');
    }

    public function getAuditeur()
    {
        return $this->hasOne(Auditeurs::class,'email','email');
    }

    public function getPaiement()
    {
        return $this->hasOne(Paiements::class,'reference','reference_paiement');
    }

    public function getModepaiement()
    {
        return $this->belongsTo(Modepaiements::class,'modepaiement_id');
    }
}
