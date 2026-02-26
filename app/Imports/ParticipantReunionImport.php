<?php

namespace App\Imports;

use App\Models\Inscriptions;
use App\Models\Panelistes;
use App\Models\Participants;
use App\Models\Sessionformations;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ParticipantReunionImport implements ToCollection
{
    private $code_formation;

    public function __construct($param)
    {
        $this->code_formation = $param; 
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {

        // je récupère le nombre de ligne dans le fichier csv incription
        $nbr_data_participant = count($rows);
        // initialiser le id_webinaire
        $id_webinaire = 0;
        //#################### je récupère id_webinaire dans le fichier
        //foreach ($rows[1] as $key => $value){ $id_webinaire = preg_replace("/\s+/", "", $rows[1][0]); }
        // ############### Traitement du fichier pour récupérer les données séparéments ################

        for($i = 0; $i < $nbr_data_participant; $i++){
            // saut des ligne
            // insertion du contenu dans la table session

            if($i == 1){
                Sessionformations::create([
                    'id_webinaire' => $id_webinaire,
                    'code_formation' => $this->code_formation,
                    'heure_debut' => $rows[$i][2],
                    'duree_reel' => $rows[$i][5],
                    'nbr_inscrit' => $rows[$i][6],
                    'nbr_annule' => 0, //$rows[$i][5],
                    'spectateurs_uniques' => 0, //$rows[$i][6],
                    'nbr_total_utilisateur' => $rows[$i][6], //$rows[$i][7],
                    'vues_simultanees_maxi' => 0, //$rows[$i][8],
                ]);
            }

            if($i > 3 ){

                $username = $rows[$i][0];
                $email = trim($rows[$i][1]);
                $duree = $rows[$i][2];
                $invite = trim($rows[$i][3]);

                // ############### enregistrer la liste des panélistes ######################
                if($invite == "Non"){
                    Panelistes::create([
                        'a_participe' => $invite,
                        'username' => $username,
                        'email' => $email,
                        //'heure_arrivee' => $rows[$i][3],
                        //'heure_depart' => $rows[$i][4],
                        'temps_presence' => $duree,
                        //'pays' => $rows[$i][6],
                        'id_webinaire' => $id_webinaire,
                        'code_formation' => $this->code_formation,
                    ]);

                }

                // ############### enregistrer la liste des auditeurs ######################
                if($invite == "Oui"){
                    // vérifier si l'e-mail existe dans la table des inscrits
                    $auditeur = Inscriptions::where('email', $email)->where('code_formation',$this->code_formation)->exists();
                    if ($auditeur) {
                        // if auditeur exist register data into table participant
                        Participants::create([
                            'a_participe' => $invite,
                            'username' => $username,
                            'email' => $email,
                            //'heure_arrivee' => $rows[$i][13],
                            //'heure_depart' => $rows[$i][14],
                            'temps_presence' => $duree,
                            'id_webinaire' => $id_webinaire,
                            'code_formation' => $this->code_formation,
                        ]);



                    }else{

                    }


                }

            }

        }

        return true;

    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
