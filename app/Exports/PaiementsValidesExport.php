<?php
// app/Exports/PaiementsValidesExport.php
namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaiementsValidesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(private Collection $rows) {}

    public function collection() { return $this->rows; }

    public function headings(): array
    {
        return ['Nom', 'Prénoms', 'Email', 'Référence', 'Mode paiement', 'Montant (F CFA)', 'Code transaction', 'Type', 'Date'];
    }

    public function map($r): array
    {
        return [
            $r->nom, $r->prenoms, $r->email, $r->reference, $r->libelle_modepaiement,
            (float) $r->montant,   // reste numérique pour pouvoir faire des SOMME dans Excel
            $r->num_transaction, $r->type,
            $r->date ? \Carbon\Carbon::parse($r->date)->format('d/m/Y H:i') : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('F')->getNumberFormat()->setFormatCode('#,##0');
        return [1 => ['font' => ['bold' => true]]];
    }
}