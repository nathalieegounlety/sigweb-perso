<?php

namespace App\Exports;

use App\Models\Detailpaiementouvrages;
use App\Models\Ouvrages;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class ExportEtatRecapitulatifOuvrage implements FromView
{

    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function view(): View
    {
        $ouvrage = Ouvrages::find($this->id);
        $details_paiements = Detailpaiementouvrages::with('paiement_valide')->where("ouvrage_id",$ouvrage->id)->get();


        return view('statistiques.export_etat_recapitulatif_ouvrage', [
            'ouvrage' => $ouvrage,
            'details_paiements' => $details_paiements
        ]);
    }
}
