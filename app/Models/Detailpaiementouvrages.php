<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detailpaiementouvrages extends Model
{
    use HasFactory;
    protected $table = 'detailpaiementouvrages';
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
        'paiementouvrage_id',
        'ouvrage_id',
        'montant',
        'reference',
        'created_at',
        'updated_at',
        'email'
    ];

    public function getOuvrage()
    {
        return $this->belongsTo(Ouvrages::class,'ouvrage_id');
    }

    public function getPaiement()
    {
        return $this->belongsTo(Paiement::class,'paiementouvrage_id');
    }

    public function paiement_valide() {
        return $this->getPaiement()->where('statut_paiement','=', 'PAYE');
    }
}
