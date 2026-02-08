<?php

namespace App\Http\Controllers;

use App\Exports\ExportListePresence;
use App\Exports\ExportsInscritsDsc;
use App\Exports\ExportsInscritsExcel;
use App\Exports\ExportsInscritsPresentielVisioExcel;
use App\Exports\ExportsInscritsVisioExcel;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportationExcelController extends Controller
{
    /**
     * Export stats mode paiement
     *
     */
    public function exportListeInscritsDsc($code_formation) // ExportInscritsExcel
    {
        return Excel::download(new ExportsInscritsDsc($code_formation), "SIGWEB_ERSUMA_liste_inscrits_dsc_" . $code_formation . '_' . date("Ymd") . '_' . date("His") . ".xlsx");
    }

    /**
     * Export stats mode paiement
     *
     */
    public function exportListeInscritPresentielleExcel($code_formation) // ExportInscritsExcel
    {
         return Excel::download(new ExportsInscritsExcel($code_formation) ,"SIGWEB_ERSUMA_liste_inscrits_presentiel_".$code_formation.'_'.date("Ymd").'_'.date("His").".xlsx");
    }
    /**
     * Export stats mode paiement
     *
     */
    public function exportListePresenceExcel($code_formation) // ExportInscritsExcel
    {
        //return Excel::download(new ExportAuditeurPays,"auditeur_pays_".date("His").".xlsx");
        return Excel::download(new ExportListePresence($code_formation) ,"SIGWEB_ERSUMA_liste_presence_".$code_formation.'_'.date("Ymd").'_'.date("His").".xlsx");
    }

    /**
     * Export stats mode paiement
     *
     */
    public function exportListeInscritVisioExcel($code_formation) // ExportInscritsExcel
    {
        return Excel::download(new ExportsInscritsVisioExcel($code_formation) ,"SIGWEB_ERSUMA_liste_inscrits_visio_".$code_formation.'_'.date("Ymd").'_'.date("His").".xlsx");
    }

    /**
     * Export stats mode paiement
     *
     */
    public function exportListeInscritPresentielleVisioExcel($code_formation) // ExportInscritsExcel
    {
        return Excel::download(new ExportsInscritsPresentielVisioExcel($code_formation) ,"SIGWEB_ERSUMA_liste_inscrits_".$code_formation.'_'.date("Ymd").'_'.date("His").".xlsx");
    }


}
