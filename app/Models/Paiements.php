<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiements extends Model
{
    use HasFactory;
    protected $table = 'paiements';
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
        'telephone',
        'pays',
        'reference',
        'montant_total',
        'montant_a_payer',
        'id_modepaiement',
        'num_transaction',
        'statut_transaction',
        'created_at',
        'updated_at',
        'agent_traiteur',
        'statut_traitement',
        'commentaire',
        'date_traitement',
        'code_inscription',
        'is_paiement_partiel',
        'facnormalisee',
        'codemcf',
        'nim',
        'compteur',
        'qrcode',
        'heurenormalisation',
        'code_remise_dsc'
    ];



    public function getAuditeur()
    {
        return $this->belongsTo(Auditeurs::class,'email',"email");
    }

    public function getModepaiement()
    {
        return $this->belongsTo(Modepaiements::class,'id_modepaiement');
    }
}
