<?php

namespace App\Exports;

use App\Models\Participants;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AllParticipantsExport implements FromQuery, WithHeadings, WithChunkReading
{
    private $code_formation;

    public function __construct($code_formation)
    {
        $this->code_formation = $code_formation;
    }

    public function query()
    {
        return Participants::select('code_formation','username','email','heure_arrivee','heure_depart','temps_presence')
        ->where('code_formation', $this->code_formation)->orderBy('email');
    }

    public function headings(): array
    {
        return ['Code formation','Username','Email','Heure arrivée','Heure départ','temps_presence'];
    }

    

    public function chunkSize(): int
    {
        return 1000;
    }
}
