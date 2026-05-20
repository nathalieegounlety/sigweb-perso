<?php

namespace App\Imports;

use App\Models\Formateurs;
use App\Models\Pays;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportationFormateurs implements ToCollection
{
    /**
    * @param Collection $row
    */
    public function collection(Collection $rows)
    { 
        // je récupère le nombre de ligne dans le fichier 
        $nbr_data_presentiel = count($rows); 
        // mapping des column du fichier pour lecture
        $titre = 1; 
        $nom = 2; 
        $prenoms = 3; 
        $genre = 4;      
        $telephone = 5;  
        $email = 6;  
        $profession = 7;  
        $specialite = 8;  
        $organisme = 9; 
        $nationalite = 10;

        for($i = 1; $i < $nbr_data_presentiel; $i++){  
            if (!empty($rows[$i][$nom]) && !empty($rows[$i][$prenoms])) {
                # code...
                $user = auth('api')->user();
                $user_id = $user->id;
        
                $check_formateur = Formateurs::where("nom",trim($rows[$i][$nom]))->where("prenoms",trim($rows[$i][$prenoms]))->exists();
                if ($check_formateur) {
                    # code...
                    $formateur = Formateurs::where("nom",trim($rows[$i][$nom]))->where("prenoms",trim($rows[$i][$prenoms]))->first(); 
                    $formateur->setAttribute('nom', trim($rows[$i][$nom]));
                    $formateur->setAttribute('prenoms', trim($rows[$i][$prenoms]));
                    $formateur->setAttribute('telephone', trim($rows[$i][$telephone]));
                    $formateur->setAttribute('email', trim($rows[$i][$email]));
                    if (!empty($rows[$i][$nationalite])) {
                        # code...
                        $pays = Pays::where("nationalite",trim($rows[$i][$nationalite]))->first();
                        if ($pays) {
                            # code...
                            $formateur->setAttribute('nationalite', $pays->id);
                        }
                        
                    } 
                    $formateur->setAttribute('fonction', trim($rows[$i][$profession]));
                    $formateur->setAttribute('specialite', trim($rows[$i][$specialite]));
                    $formateur->setAttribute('organisme', trim($rows[$i][$organisme])); 
                    $formateur->setAttribute('titre', trim($rows[$i][$titre])); 
                    //$formateur->setAttribute('civilite', trim($rows[$i][$civilite])); 
                    $formateur->setAttribute('genre', trim($rows[$i][$genre]));  
                    $formateur->update(); 
                 }else{
                    $formateur = new Formateurs(); 
                    $formateur->setAttribute('nom', trim($rows[$i][$nom]));
                    $formateur->setAttribute('prenoms', trim($rows[$i][$prenoms]));
                    $formateur->setAttribute('telephone', trim($rows[$i][$telephone]));
                    $formateur->setAttribute('email', trim($rows[$i][$email]));
                    if (!empty($rows[$i][$nationalite])) {
                        # code...
                        $pays = Pays::where("nationalite",trim($rows[$i][$nationalite]))->first();
                        if ($pays) {
                            # code...
                            $formateur->setAttribute('nationalite', $pays->id);
                        }
                        
                    } 
                    $formateur->setAttribute('fonction', trim($rows[$i][$profession]));
                    $formateur->setAttribute('specialite', trim($rows[$i][$specialite]));
                    $formateur->setAttribute('organisme', trim($rows[$i][$organisme])); 
                    $formateur->setAttribute('titre', trim($rows[$i][$titre])); 
                    //$formateur->setAttribute('civilite', trim($rows[$i][$civilite])); 
                    $formateur->setAttribute('genre', trim($rows[$i][$genre])); 
                    $formateur->setAttribute('created_by', $user_id);
                    $formateur->setAttribute('created_at', new \DateTime());
                    $formateur->save(); 
                 }
           
            }
        }
          
        return true;
    }
}
