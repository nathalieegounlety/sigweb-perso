<?php 

namespace App\Exports;
 
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
 
class ExportsStatsParticipants implements WithMultipleSheets
{
    public function __construct(
        private $formation,
        private array $lignesRecap,
        private bool $fmGratuite,
        private Collection $rows,
    ) {}
 
    public function sheets(): array
    {
        return [
            new RecapitulatifFormationSheet($this->formation, $this->lignesRecap, $this->fmGratuite),
            new ParticipantsFormationExport($this->rows, (bool) $this->formation->is_formation_dsc),
        ];
    }
}