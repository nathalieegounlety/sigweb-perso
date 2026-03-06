<?php

namespace App\Http\Controllers;
use App\Exports\ExportsInscritsFormations;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExcelPresentielController extends Controller
{
    //
    public function exportListeInscritFormation()
{
    return Excel::download(new ExportsInscritsFormations(), "SIGWEB_ERSUMA_liste_inscrits_formations_" . date("Ymd") . '_' . date("His") . ".xlsx");
}

}
