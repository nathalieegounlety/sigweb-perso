<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rapports extends Model
{

    use HasFactory;
    protected $table = 'rapports';
    protected $primaryKey = 'id';
    public $incrementing = true;

    public $timestamps = true;

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
        'email',
        'temps_presence',
        'id_webinaire'
    ];

    public function getInscrit()
    {
        return $this->belongsTo(Inscriptions::class,'email','email');
    }
}
