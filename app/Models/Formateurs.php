<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formateurs extends Model
{
    use HasFactory;
    protected $table = 'formateurs';
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
        'nom',
        'prenoms',
        'telephone',
        'email',
        'genre',
        'pays_id',
        'titre',
        'civilite',
        'fonction',
        'specialite',
        'organisme',
        'nationalite',
        'created_by', 
        'updated_by', 
        'created_at',
        'updated_at', 
    ];
    
    public function getNationalite()
    {
        return $this->belongsTo(Pays::class,'nationalite','id');
    }
    
    public function getPays()
    {
        return $this->belongsTo(Pays::class,'pays_id','id');
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
