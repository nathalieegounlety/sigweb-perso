<?php

namespace App\Imports;

use App\Models\Moodlemodules;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportMoodleModules implements ToCollection
{
    
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        // je récupère le nombre de ligne dans le fichier
        $nbre_ligne = count($rows);

        // mapping des column du fichier pour lecture
        $code_formation = 0;
        $anne_academique = 1;
        $code = 2;
        $intitule = 3;
        $credit = 4;
        $enseignant = 5;
        $user = auth('api')->user();
        for($i = 1; $i < $nbre_ligne; $i++){

            if (!empty($rows[$i][$code_formation]) && !empty($rows[$i][$anne_academique]) && !empty($rows[$i][$code]) && !empty($rows[$i][$intitule]) && !empty($rows[$i][$credit])) {
                # code...
                $get_code_formation = trim($rows[$i][$code_formation]); 
                $get_annee_academique = trim($rows[$i][$anne_academique]);
                $get_code = trim($rows[$i][$code]);
                $get_intitule = trim($rows[$i][$intitule]);
                $get_credit = trim($rows[$i][$credit]);
                $get_enseignant = trim($rows[$i][$enseignant]);

                // vérifier si le module existe déjà, si oui update sinon procéder à l'enregistrement
                $module = Moodlemodules::where("code_formation",$get_code_formation)
                ->where("annee_academique",$get_annee_academique)
                ->where("code",$get_code)
                ->first();

                if ($module) {
                    # code...
                    $upModule =  Moodlemodules::find($module->id);
                    $upModule->setAttribute('libelle', $get_intitule);
                    $upModule->setAttribute('credit', $get_credit);
                    $upModule->setAttribute('enseignant', $get_enseignant); 
                    $upModule->setAttribute('updated_by',$user->id);
                    $upModule->update(); 
                }else{
                    $addModule = new Moodlemodules();
                    $addModule->setAttribute('code_formation', $get_code_formation);
                    $addModule->setAttribute('code', $get_code);
                    $addModule->setAttribute('credit', $get_credit);
                    $addModule->setAttribute('enseignant', $get_enseignant); 
                    $addModule->setAttribute('libelle', $get_intitule);
                    $addModule->setAttribute('annee_academique', $get_annee_academique);   
                    $addModule->setAttribute('created_by',$user->id);
                    $addModule->setAttribute('updated_by',$user->id);
                    $addModule->save();
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
