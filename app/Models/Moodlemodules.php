<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Moodlemodules extends Model
{
    use HasFactory;
    protected $table = 'moodlemodules';
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
        'libelle',
        'credit',
        'code',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    public function getUserCreated()
    {
        return $this->belongsTo(User::class,'created_by','id');
    }

    public function getUserUpdated()
    {
        return $this->belongsTo(User::class,'updated_by','id');
    }
}
