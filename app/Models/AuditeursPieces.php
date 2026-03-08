<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditeursPieces extends Model
{
    use HasFactory;
    protected $table = 'auditeurs_pieces';
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
        'inscrits_typespieces_id',
        'auditeur_id',
        'piece_path',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'code_formation'
    ];

    public function getUserCreated()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function getUserUpdated()
    {
        return $this->belongsTo(User::class,'updated_by');
    }

    public function getTypesPieces()
    {
        return $this->belongsTo(InscritsTypePieces::class,'inscrits_typespieces_id','id');
    }

    public function getInscrit()
    {
        return $this->belongsTo(Auditeurs::class,'auditeur_id','id');
    }
}
