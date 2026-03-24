<?php

namespace App\Exports;

use App\Models\Auditeurs;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AuditeursExport implements FromQuery, WithHeadings, WithChunkReading
{ 
    public function query()
    {
        return Auditeurs::select('civilite','nom','prenoms','telephone','email','intitule_poste','genre','secteur_activite','pays')
        ->orderBy('nom');
    }

    public function headings(): array
    {
        return ['Titre','Nom','Prenoms','Téléphone','Email','Profession','Genre','Fonction','Pays'];
    }

    

    public function chunkSize(): int
    {
        return 1000;
    }
}
