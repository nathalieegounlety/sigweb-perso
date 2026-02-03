<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormationsDsc extends Model
{
    use HasFactory;
    protected $table = 'formations_dscs';
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
        'formations_id',
        'modalites_path',
        'duree',
        'date_limite_paiement_inscription',
        'date_remise_diplomes',
        'created_by',
        'updated_by'
    ];

    public function getFormation()
    {
        return $this->belongsTo(Formations::class,'formations_id','id');
    }

    public function getUserCreated()
    {
        return $this->belongsTo(User::class,'created_by','id');
    }

    public function getUserUpdated()
    {
        return $this->belongsTo(User::class,'updated_by','id');
    }
}
