<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarificationsformation extends Model
{

    use HasFactory;
    protected $table = 'tarificationsformations';
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
        'id_grilletarifaire',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'enable'
    ];
    public function getGrilletarifaire()
    {
        return $this->belongsTo(Grilletarifaires::class,'id_grilletarifaire');
    }

    public function getFormation()
    {
        return $this->belongsTo(Formations::class,'code_formation',"code_formation");
    }

    public function getUserCreated()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function getUserUpdated()
    {
        return $this->belongsTo(User::class,'updated_by');
    }
}
