<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formationsformateurs extends Model
{
    use HasFactory;
    protected $table = 'formationsformateurs';
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
        'formation_id',
        'formateur_id',
        'role',
        'roleformateur_id',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    public function getRoleFormateur()
    {
        return $this->belongsTo(Roleformateurs::class,'roleformateur_id','id');
    }

    public function getFormation()
    {
        return $this->belongsTo(Formations::class,'formation_id','id');
    }


    public function getFormateur()
    {
        return $this->belongsTo(Formateurs::class,'formateur_id','id');
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
