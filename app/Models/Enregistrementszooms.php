<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enregistrementszooms extends Model 
{
    use HasFactory;

    protected $table = 'enregistrementszooms'; 

    protected $fillable = [
        'formation_id',
        'sessionformation_id',
        'url_youtube',
        'titre',
        'code_formation',
        'created_by',
        'updated_by',
    ];

    public function formation()
    {
        return $this->belongsTo(Formations::class, 'formation_id');
    }

    public function sessionFormation()
    {
        return $this->belongsTo(Sessionformations::class, 'sessionformation_id'); // Sans "s"
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
