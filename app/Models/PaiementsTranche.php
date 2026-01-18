<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaiementsTranche extends Model
{
    use HasFactory;

    protected $table = 'paiements_tranches';
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
        'reference',
        'details_paiements_id',
        'montant_paye',
        'montant_restant',
        'reference',
        'modepaiement_id',
        'updated_at',
        'tranche_id',
        'telephone',
        'num_transaction',
        'statut_transaction',
        'statut_traitement',
        'is_inscription',
        'preuve_path',
        'traiter_par',
        'created_by',
        'updated_by',
        'created_at'
    ];


    public function getDetailPaiement()
    {
        return $this->hasOne(Detailspaiements::class,'datails_paiements_id','id');
    }

    public function getModepaiement()
    {
        return $this->belongsTo(Modepaiements::class,'modepaiement_id');
    }
}
