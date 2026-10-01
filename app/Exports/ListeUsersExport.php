<?php
// app/Exports/PaiementsValidesExport.php
namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ListeUsersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(private Collection $rows) {}

    public function collection() { return $this->rows; }

    public function headings(): array
    {
        return ['identifiant','Nom et Prénoms',  'Email', 'Rôle', 'Statut', 'Date création', 'Date modification'];
    }

    public function map($r): array
    {
        return [
            $r->code_user, $r->name, $r->email, 
            $r->roles->pluck('libelle')->implode(', ') ?: 'Aucun rôle',
            $r->actif ? 'Actif' : 'Inactif',
            $r->created_at ? \Carbon\Carbon::parse($r->created_at)->format('d/m/Y H:i:s') : '',
            $r->updated_at ? \Carbon\Carbon::parse($r->updated_at)->format('d/m/Y H:i:s') : '',
        ];
    }

    public function styles(Worksheet $sheet)
    { 
        return [1 => ['font' => ['bold' => true]]];
    }
}