<?php
// app/Exports/PaiementsValidesExport.php
namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportsListePaiements implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(private Collection $rows) {}

    public function collection() { return $this->rows; }

    public function headings(): array
    {
        return ['Nom', 'Prénoms', 'Email', 'Référence', 'Montant','Code Formation','Statut','Mode paiement', 'Date'];
    }

    public function map($r): array
    {
        return [
            $r->nom, $r->prenoms, $r->email, $r->reference_paiement, (float) $r->montant,
            $r->code_formation,$r->statut_paiement, $r->modepaiement,  
            $r->updated_at ? \Carbon\Carbon::parse($r->updated_at)->format('d/m/Y H:i') : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('F')->getNumberFormat()->setFormatCode('#,##0');
        return [1 => ['font' => ['bold' => true]]];
    }
}