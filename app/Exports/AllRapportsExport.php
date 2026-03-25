<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AllRapportsExport implements FromQuery, WithHeadings, WithChunkReading
{
    private $code_formation;

    public function __construct($code_formation)
    {
        $this->code_formation = $code_formation;
    }

    public function query()
    {
        return  DB::table('rapports')
            ->join('inscriptions', 'inscriptions.email', '=', 'rapports.email')
            ->leftJoin('auditeurs', 'inscriptions.email', '=', 'auditeurs.email') 
            ->select(
                'rapports.code_formation',
                'auditeurs.nom',
                'auditeurs.prenoms', 
                'rapports.email', 
                'rapports.temps_presence', 
                'auditeurs.pays'
            )
            ->where('rapports.code_formation', $this->code_formation)
            ->where('inscriptions.code_formation', $this->code_formation)
            ->orderBy('rapports.email');
    }

    public function headings(): array
    {
        return ['Code formation','Nom','Prénoms','Email','Temps de présence','Pays'];
    }

    

    public function chunkSize(): int
    {
        return 1000;
    }
}
