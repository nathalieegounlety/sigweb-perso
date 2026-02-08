<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Formations extends Model
{
    use HasFactory;
    protected $table = 'formations';
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
        'theme',
        'description',
        'cout_formation',
        'date_debut',
        'date_fin',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'statut',
        'presentiel',
        'id_webinaire',
        'afficher',
        'is_formation_dsc',
        'typeformation_id',
        'visuel'
    ];

    public function getUserCreated()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function getUserUpdated()
    {
        return $this->belongsTo(User::class,'updated_by','id');
    }

    public function getparticipant()
    {
        return $this->belongsTo(Participation::class);
    }

    public function getTypeformation()
    {
        return $this->belongsTo(Typeformations::class,"typeformation_id","id");
    }

    public function getTarification($code_formation)
    {
        $cout_formation = DB::table('tarificationsformations')
            ->join('formations', 'formations.code_formation', '=', 'tarificationsformations.code_formation')
            ->join('grilletarifaires', 'grilletarifaires.id', '=', 'tarificationsformations.id_grilletarifaire')
            ->select('tarificationsformations.id as tarif_id', 'grilletarifaires.id as get_grille_id', 'grilletarifaires.libelle as get_grille_libelle','grilletarifaires.montant','grilletarifaires.type_tarif')
            ->where('tarificationsformations.code_formation',$code_formation)
            ->orderBy('tarificationsformations.id', 'DESC')
            ->get();

        return $cout_formation;
    }

    public function tarifications()
    {
        return $this->hasMany(Tarificationsformation::class, 'code_formation', 'code_formation')
            ->leftJoin('grilletarifaires', 'grilletarifaires.id', '=', 'tarificationsformations.id_grilletarifaire')
            ->select(
                'tarificationsformations.id as tarif_id',
                'grilletarifaires.id as get_grille_id',
                'grilletarifaires.libelle as get_grille_libelle',
                'grilletarifaires.montant',
                'grilletarifaires.type_tarif',
                'tarificationsformations.code_formation'
            )
            ->orderBy('tarificationsformations.id', 'DESC');
    }

    public function tarificationById($tarif_id)
    {
        return $this->hasOne(TarificationsFormation::class, 'code_formation', 'code_formation')
            ->where('id', $tarif_id)
            ->orderBy('id', 'DESC');
    }

    public function getTarificationById($code_formation,$tarif_id)
    {
        $cout_formation = DB::table('tarificationsformations')
            ->join('formations', 'formations.code_formation', '=', 'tarificationsformation.code_formation')
            ->join('grilletarifaires', 'grilletarifaires.id', '=', 'tarificationsformation.id_grilletarifaire')
            ->select('tarificationsformation.id as tarif_id', 'grilletarifaires.id as get_grille_id', 'grilletarifaires.libelle as get_grille_libelle','grilletarifaires.montant','grilletarifaires.type_tarif')
            ->where('tarificationsformation.code_formation',$code_formation)
            ->where('tarificationsformation.id',$tarif_id)
            ->orderBy('tarificationsformation.id', 'DESC')
            ->get();

        return $cout_formation;
    }
}
