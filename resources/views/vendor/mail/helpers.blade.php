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

// code_inscription
if (!function_exists('getNbrPiecesMail')) {
    # code...
    function getNbrPiecesMail($code_inscription){
        $user = auth()->user();

        // Effectuer une jointure entre les tables auditeurs et inscrits
        $inscrit = \App\Models\Inscriptions::where("code_inscription", $code_inscription)->first();

        // Déterminer le type de formation
        $typeFormation = $inscrit->type_formation;

        // Récupérer les pièces déjà enregistrées
        $pieces = \App\Models\AuditeursPieces::where('created_by', $user->id)->pluck('inscrits_typespieces_id');

        // Exclure la pièce appropriée en fonction du type de formation
        if ($typeFormation == 'PROFESSIONNELS') {
            $typePieces = \App\Models\InscritsTypePieces::whereNotIn('id', $pieces)->where('is_professionnel', true)->orderBy("libelle", "asc")->get();
            $nbre_piece_require = \App\Models\InscritsTypePieces::where('is_professionnel', true)->count();

        } else {
            $typePieces = \App\Models\InscritsTypePieces::whereNotIn('id', $pieces)->where('is_etudiant', true)->orderBy("libelle", "asc")->get();
            $nbre_piece_require = \App\Models\InscritsTypePieces::where('is_etudiant', true)->count();
        }

        $nombrepieces = count($pieces);

        return  $nbre_piece_require - $nombrepieces;
    }
}
