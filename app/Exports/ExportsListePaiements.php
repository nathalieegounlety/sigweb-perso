<?php
// app/Exports/ExportsListePaiements.php
namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithCustomStartCell, WithEvents};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExportsListePaiements implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithCustomStartCell, WithEvents
{
    /**
     * @param array $criteres ['Libellé' => 'valeur'] : critères de filtre affichés au-dessus du tableau
     */
    public function __construct(
        private Collection $rows,
        private string $titre = 'SIGWEB EXTRACTION DES PAIEMENTS',
        private array $criteres = [],
    ) {}

    /** Colonnes contenant des montants (lettres Excel) : format numérique + total */
    private const COLONNES_MONTANTS = ['H', 'J', 'K', 'L', 'O'];
    private const DERNIERE_COLONNE  = 'P';

    /** Ligne de l'en-tête du tableau : titre (1) + date (2) + critères + 1 ligne vide */
    private function ligneEntete(): int
    {
        return 3 + max(1, count($this->criteres)) + 1;
    }

    public function startCell(): string
    {
        return 'A' . $this->ligneEntete();   // le tableau commence sous le titre et les critères
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Nom', 'Prénoms', 'Genre', 'Pays', 'Email',                         // A B C D E
            'Code Formation', 'Référence', 'Montant de base', 'Réduction',      // F G H I
            'Montant à payer', 'Montant total payé', 'Montant total restant',   // J K L
            'Statut', 'Mode paiement', 'Ecart', 'Date',                         // M N O P
        ];
    }

    public function map($r): array
    {
        return [
            $r->nom,
            $r->prenoms,
            $r->genre,
            $r->pays,
            $r->email,
            $r->code_formation,
            $r->reference_paiement,
            (float) $r->montant_base,
            $r->libelle_reduction,
            (float) $r->montant,
            (float) $r->montant_total_paye,
            (float) $r->montant_total_restant,
            $r->statut_paiement,
            $r->modepaiement,
            (float) $r->ecart_controle,
            $r->updated_at ? Carbon::parse($r->updated_at)->format('d/m/Y') : '',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet          = $event->sheet->getDelegate();
                $col            = self::DERNIERE_COLONNE;
                $entete         = $this->ligneEntete();
                $premiereDonnee = $entete + 1;
                $derniereDonnee = $entete + $this->rows->count();
                $derniereLigne  = $derniereDonnee;

                // ---------- 1. Titre ----------
                $sheet->setCellValue('A1', $this->titre);
                $sheet->mergeCells("A1:{$col}1");
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '006FC0']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(24);

                // ---------- 2. Date d'édition ----------
                $sheet->setCellValue('A2', 'Édité le ' . now()->format('d/m/Y à H:i'));
                $sheet->mergeCells("A2:{$col}2");
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['italic' => true, 'color' => ['rgb' => '6B7280']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // ---------- 3. Critères de filtre ----------
                $lignes = empty($this->criteres)
                    ? ['Critères : aucun (toutes les données)']
                    : array_map(fn ($l, $v) => "{$l} : {$v}", array_keys($this->criteres), $this->criteres);

                $n = 3;
                foreach ($lignes as $texte) {
                    $sheet->setCellValue("A{$n}", $texte);
                    $sheet->mergeCells("A{$n}:{$col}{$n}");   // fusion : n'élargit pas la colonne A
                    $n++;
                }

                // ---------- 4. En-tête du tableau ----------
                $sheet->getStyle("A{$entete}:{$col}{$entete}")->applyFromArray([
                    'font'      => ['bold' => true],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'AABBDD']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);

                // ---------- 5. Ligne TOTAL ----------
                if ($this->rows->isNotEmpty()) {
                    $derniereLigne = $derniereDonnee + 1;
                    $sheet->setCellValue("A{$derniereLigne}", 'TOTAL (' . $this->rows->count() . ' lignes)');

                    foreach (self::COLONNES_MONTANTS as $c) {
                        $sheet->setCellValue("{$c}{$derniereLigne}", "=SUM({$c}{$premiereDonnee}:{$c}{$derniereDonnee})");
                    }

                    $sheet->getStyle("A{$derniereLigne}:{$col}{$derniereLigne}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'AABBDD']],
                    ]);
                }

                // ---------- 6. Format des montants ----------
                foreach (self::COLONNES_MONTANTS as $c) {
                    $sheet->getStyle("{$c}{$premiereDonnee}:{$c}{$derniereLigne}")
                        ->getNumberFormat()->setFormatCode('#,##0');
                }

                // ---------- 7. Bordures du tableau (en-tête -> TOTAL) ----------
                $sheet->getStyle("A{$entete}:{$col}{$derniereLigne}")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN,   'color' => ['rgb' => '000000']],
                        'outline'    => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']],
                    ],
                ]);

                // ---------- 8. Confort : en-tête figé + filtres Excel ----------
                $sheet->freezePane('A' . $premiereDonnee);
                if ($this->rows->isNotEmpty()) {
                    $sheet->setAutoFilter("A{$entete}:{$col}{$derniereDonnee}");
                }
            },
        ];
    }
}