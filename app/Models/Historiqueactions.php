<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Historiqueactions extends Model
{
    
    use HasFactory;
    protected $table = 'historiqueactions';
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
        'action',
        'description',
        'ip_adresse',
        'created_by',
        'mac_adresse',
        'created_at',
        'updated_at',
    ];

    public function getUserCreated()
    {
        return $this->belongsTo(User::class,'created_by','id');
    }
}
 