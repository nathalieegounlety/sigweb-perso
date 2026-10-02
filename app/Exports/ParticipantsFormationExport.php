<?php

namespace App\Exports;
 
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
 
class ParticipantsFormationExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(
        private Collection $rows,
        private bool $estDsc = false,
    ) {}
 
    /**
     * Définition unique des colonnes : [libellé, valeur, est un montant ?]
     * L'en-tête, les valeurs et le format numérique en découlent.
     */
    private function colonnes(): array
    {
        $cols = [
            ['Nom',       fn ($r) => $r->nom,            false],
            ['Prénoms',   fn ($r) => $r->prenoms,        false],
            ['E-mail',    fn ($r) => $r->email,          false],
            ['Téléphone', fn ($r) => $r->telephone,      false],
            [$this->estDsc ? 'Catégorie' : 'Mode', fn ($r) => $r->type_formation, false],
        ];
 
        if ($this->estDsc) {
            $cols[] = ["Frais d'inscription",    fn ($r) => (float) $r->frais_inscription,   true];
            $cols[] = ['Frais de participation', fn ($r) => (float) $r->frais_participation, true];
            $cols[] = ['Réduction',              fn ($r) => self::libelleReduction($r),      false];
        }
 
        return array_merge($cols, [
            ['Montant total',   fn ($r) => (float) $r->montant_total,       true],
            ['Statut',          fn ($r) => $r->statut_paiement ?: 'IMPAYE', false],
            ['Montant payé',    fn ($r) => (float) $r->montant_paye,        true],
            ['Montant restant', fn ($r) => (float) $r->montant_restant,     true],
            ['A participé',     fn ($r) => $r->a_participe ? 'OUI' : 'NON', false],
            ['Observation',     fn ($r) => $r->observations,                false],
        ]);
    }
 
    public static function libelleReduction($r): string
    {
        if ($r->have_reduction_dsc) {
            return $r->code_remise_dsc === 'BOURSE_COMPLET' ? '100% (Bourse complète)' : '5%';
        }
        return ((float) $r->reduction_affichee) . '%';
    }
 
    public function title(): string
    {
        return 'Participants';
    }
 
    public function collection()
    {
        return $this->rows;
    }
 
    public function headings(): array
    {
        return array_column($this->colonnes(), 0);
    }
 
    public function map($r): array
    {
        return array_map(fn ($c) => $c[1]($r), $this->colonnes());
    }
 
    public function styles(Worksheet $sheet)
    {
        foreach ($this->colonnes() as $i => $c) {
            if ($c[2]) {
                $lettre = Coordinate::stringFromColumnIndex($i + 1);
                $sheet->getStyle($lettre)->getNumberFormat()->setFormatCode('#,##0');
            }
        }
        return [1 => ['font' => ['bold' => true]]];
    }
}