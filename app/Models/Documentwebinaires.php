<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documentwebinaires extends Model
{
    
    use HasFactory; 
    protected $table = 'documentwebinaires';
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
        'document_name'
    ];
}
