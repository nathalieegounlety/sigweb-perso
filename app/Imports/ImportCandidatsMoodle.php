<?php

namespace App\Imports;

use App\Models\Tmpmoodlecandidats;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportCandidatsMoodle implements ToCollection
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        // je récupère le nombre de ligne dans le fichier
        $nbre_ligne = count($rows);

        // mapping des column du fichier pour lecture
        $nom = 1;
        $prenoms = 0;
        $email = 2;
        $ddn = 3;
        $lieu_naissance = 4;

        for($i = 1; $i < $nbre_ligne; $i++){

            if (!empty($rows[$i][$nom]) && !empty($rows[$i][$prenoms]) && !empty($rows[$i][$email])) {
                # code...
                $get_nom = trim($rows[$i][$nom]);
                $get_prenoms = trim($rows[$i][$prenoms]);
                $get_email = trim($rows[$i][$email]);
                $get_ddn = trim($rows[$i][$ddn]);
                $get_lieu_naissance = trim($rows[$i][$lieu_naissance]);

                // vérifier si le candidat existe déjà, sinon procéder à l'enregistrement
                $check_data = Tmpmoodlecandidats::where("email",$get_email)->exists();

                if ($check_data) {
                    # code...
                    $candidat = Tmpmoodlecandidats::where("email",$get_email)->first();
                    $candidat->setAttribute('nom', $get_nom);
                    $candidat->setAttribute('prenoms', $get_prenoms);
                    if (!empty($get_ddn) || $get_ddn != "") {
                        # code...
                        $candidat->setAttribute('ddn', Date::excelToDateTimeObject($get_ddn)->format('d/m/Y') );

                    }
                    if (!empty($get_lieu_naissance) || $get_lieu_naissance != "") {
                        # code...
                        $candidat->setAttribute('lieu_naissance', $get_lieu_naissance);
                    }
                    $candidat->update();
                }else{
                    $candidat = new Tmpmoodlecandidats();
                    $candidat->setAttribute('nom', $get_nom);
                    $candidat->setAttribute('prenoms', $get_prenoms);
                    $candidat->setAttribute('email', $get_email);
                    if (!empty($get_ddn) || $get_ddn != "") {
                        # code...
                        $candidat->setAttribute('ddn', Date::excelToDateTimeObject($get_ddn)->format('d/m/Y') );
                    }
                    if (!empty($get_lieu_naissance) || $get_lieu_naissance != "") {
                        # code...
                        $candidat->setAttribute('lieu_naissance', $get_lieu_naissance);
                    }
                    $candidat->save();
                }

            }
        }

        return true;

    }

    public function chunkSize(): int
    {
        return 100;
    }
}
