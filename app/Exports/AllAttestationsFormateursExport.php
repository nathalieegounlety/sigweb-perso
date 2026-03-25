<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AllAttestationsFormateursExport implements FromQuery, WithHeadings, WithChunkReading
{
    private $code_formation;

    public function __construct($code_formation)
    {
        $this->code_formation = $code_formation;
    }

    public function query()
    {
        return DB::table('formationsformateurs')
            ->join('formations', 'formations.id', '=', 'formationsformateurs.formation_id')
            ->join('formateurs', 'formateurs.id', '=', 'formationsformateurs.formateur_id')
            ->leftjoin('pays', 'pays.id', '=', 'formateurs.nationalite')
            ->leftjoin('roleformateurs', 'formationsformateurs.roleformateur_id', '=', 'roleformateurs.id')
            ->select('formationsformateurs.id', 
            'formateurs.nom', 'formateurs.prenoms', 'formateurs.email','formateurs.telephone',
                'formationsformateurs.role',  'roleformateurs.libelle as role_formateur', 'pays.nationalite')
            ->where('formations.code_formation', $this->code_formation)->orderBy('formationsformateurs.id');
    }

    public function headings(): array
    {
        return ['Code formation','Nom','Prénoms','Email','Téléphone','Role 1','Role 2','Nationalité'];
    }

    

    public function chunkSize(): int
    {
        return 1000;
    }
}
