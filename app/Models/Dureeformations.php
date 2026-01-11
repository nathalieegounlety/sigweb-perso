<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dureeformations extends Model
{
    use HasFactory;
    protected $table = 'dureeformations';
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
        'code_formation',
        'id_webinaire',
        'duree',
        'duree_enregistrement'
    ];

    public function getFormation()
    {
        return $this->belongsTo(Formations::class,'code_formation','code_formation');
    }
}
