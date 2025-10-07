<?php
namespace App\Services\util;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GenarateurCodeService {

    public function genererCodeUser(): string {
        // Process de génération du code utilisateur au format SW-COUNTER-YEAR => SW000000125
        $annee_actuelle = date('y'); // récupérer l'année courante sur 2 positions
        $prefixe = "SW";

        return DB::transaction(function () use ($prefixe, $annee_actuelle){
             // récupère le dernier codeUser de l'année en cours
            $lastUser = User::where('code_user', 'like', $prefixe.'%'.$annee_actuelle)
                ->orderByDesc('id')
                ->lockForUpdate() // permet de bloquer la lecture de la table courante avant la fin du traitement
                ->first();

            // calcule le prochain compteur
            if ($lastUser) {
                $lastCode = $lastUser->code_user; // ex: SW0000124yy
                $counter = (int) substr($lastCode, 2, 7); // extrait les 7 chiffres
                $counter++;
            } else {
                $counter = 1; // premier utilisateur de l'année
            }

            // génère le codeUser
            $codeUser = 'SW' . str_pad($counter, 7, '0', STR_PAD_LEFT) . $annee_actuelle;

            return $codeUser;
        });
        return "";
    }
}
