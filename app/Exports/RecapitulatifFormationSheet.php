<?php 

namespace App\Exports;
 
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
 
class RecapitulatifFormationSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    /** Ligne Excel de l'en-tête du tableau (après titre, thème, date, ligne vide). */
    private const LIGNE_ENTETE = 5;
 
    public function __construct(
        private $formation,
        private array $lignesRecap,
        private bool $fmGratuite,
    ) {}
 
    public function title(): string
    {
        return 'SIGWEB ETAT RECAPITULATIF DES PAIEMENTS DE LA FORMATION '.$this->formation->code_formation;
    }
 
    public function array(): array
    {
        $entete = ['', 'Inscrits', 'Gratuits', 'Payants'];
        if ($this->fmGratuite) {
            $entete[] = 'Attestations';
        }
        $entete[] = 'Ont participé';
 
        $data = [
            ['Etat récapitulatif - ' . $this->formation->code_formation],
            [$this->formation->theme ?? ''],
            ['Édité le ' . now()->format('d/m/Y à H:i')],
            [],
            $entete,
        ];
 
        foreach ($this->lignesRecap as $l) {
            $ligne = [$l['libelle'], $l['inscrits'], $l['gratuits'], $l['payants']];
            if ($this->fmGratuite) {
                $ligne[] = $l['attestations'];
            }
            $ligne[] = $l['participants'];
            $data[] = $ligne;
        }
 
        return $data;
    }
 
    public function styles(Worksheet $sheet)
    {
        $derniereColonne = $this->fmGratuite ? 'F' : 'E';
        $derniereLigne   = self::LIGNE_ENTETE + count($this->lignesRecap);
        $tableau         = 'A' . self::LIGNE_ENTETE . ':' . $derniereColonne . $derniereLigne;
 
        // Bordures sur tout le tableau
        $sheet->getStyle($tableau)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
 
        // En-tête et ligne TOTAL : fond bleu clair (comme l'écran)
        foreach ([self::LIGNE_ENTETE, $derniereLigne] as $ligne) {
            $sheet->getStyle("A{$ligne}:{$derniereColonne}{$ligne}")->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('AABBDD');
        }
 
        // Chiffres centrés
        $sheet->getStyle('B' . self::LIGNE_ENTETE . ":{$derniereColonne}{$derniereLigne}")
            ->getAlignment()->setHorizontal('center');
 
        return [
            1                  => ['font' => ['bold' => true, 'size' => 14]],
            2                  => ['font' => ['italic' => true]],
            self::LIGNE_ENTETE => ['font' => ['bold' => true]],
            $derniereLigne     => ['font' => ['bold' => true]],
            'A'                => ['font' => ['bold' => true]],
        ];
    }
}