<?php
if (! function_exists('getFormationsMail')) {
    function getFormationsMail($data)
    {
        $explode_data = explode("_", $data);
        $tarif_id = $explode_data[0] ?? null;

        if (!$tarif_id) {
            return collect();
        }

        $tarif_formation = \App\Models\Tarificationsformation::find($tarif_id);

        if (!$tarif_formation) {
            return collect();
        }

        return \App\Models\Formations::where('code_formation', $tarif_formation->code_formation)->get();
    }
}


if (!function_exists('getDureeEnregistrement')) {
    # code...
    function getDureeEnregistrement($code_formation){
        $check_duree_formation = Dureeformations::where("code_formation",$code_formation)->first();
        if ($check_duree_formation) {
            return $check_duree_formation->duree_enregistrement;
        } else {
            # code... create durée
            return 0;
        }

    }
}
