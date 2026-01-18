<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class points_de_paiements extends Model
{
    use HasFactory;
    protected $table = 'point_de_paiements';
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
        'nom_organisme',
        'description',
        'adresse',
        'ville',
        'pays',
        'telephone',
        'courriel',
        'contact1',
        'courriel1',
        'telephone1',
        'contact2',
        'courriel2',
        'telephone2',
        'created_by',
        'updated_by',
        'enable'
    ];

    public function getUserCreated()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function getUserUpdated()
    {
        return $this->belongsTo(User::class,'updated_by');
    }
}
