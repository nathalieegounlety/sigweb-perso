<?php

namespace App\Imports;

use App\Models\Dureeformations;
use App\Models\Inscriptions;
use App\Models\Panelistes;
use App\Models\Participants;
use App\Models\Sessionformations;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class WebinairezoomImportation implements ToCollection, WithCustomCsvSettings
{
    private $code_formation;

    public function __construct($param)
    {
        $this->code_formation = $param; 
    }

     public function getCsvSettings(): array
    {
        return [
           'delimiter'        => ',',
            'input_encoding'   => 'UTF-8',
            'enclosure'        => '"',
            'escape_character' => '"',   
            'input_bom'        => true, 
        ];
    }

    public function genererPassword($length = 25)
    {
        $characters = '01234567@$89ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        try {
            //code...
            
Log::info("start import wb");
        // je récupère le nombre de ligne dans le fichier csv incription
        $nbr_data_participant = count($rows);
        // initialiser le id_webinaire
        $id_webinaire = 0;

         

        //#################### je récupère id_webinaire dans le fichier
        foreach ($rows[3] as $key => $value) {
            $id_webinaire = preg_replace("/\s+/", "", $rows[3][1]);
        }
        // ############### Traitement du fichier pour récupérer les données séparéments ################
        // check paneliste row
        $paneliste = false;
        $saut_ligne_paneliste = 0;
        $nbr_start_insert_paneliste = 0;
        // check participant row
        $participant = false;
        $saut_ligne_participant = 0;
        $nbr_start_insert_participant = 0;
        // check autre présent row
        $autre_present = false;
        $saut_ligne_autre_present = 0;
        $nbr_start_insert_autre_present = 0;

        for ($i = 0; $i < $nbr_data_participant; $i++) {
            // saut des ligne
            // insertion du contenu dans la table session

            if ($i == 3) {


                Sessionformations::create([
                    'id_webinaire' => $id_webinaire,
                    'code_formation' => $this->code_formation,
                    'heure_debut' => $rows[$i][2],
                    'duree_reel' => $rows[$i][3],
                    'nbr_inscrit' => $rows[$i][4],
                    'nbr_annule' => 0,
                    'spectateurs_uniques' => $rows[$i][6],
                    'nbr_total_utilisateur' => $rows[$i][7],
                    'vues_simultanees_maxi' => $rows[$i][8],
                ]);

                // check if durée formation exist
                $check_duree_formation = Dureeformations::where("code_formation", $this->code_formation)->exists();
                if ($check_duree_formation) {
                    # code... update durée
                    $duree_formation = Dureeformations::where("code_formation", $this->code_formation)->first();
                    $duree_update = $duree_formation->duree + $rows[$i][3];
                    $duree_formation->setAttribute("duree", $duree_update);
                    $duree_formation->update();
                } else {
                    # code... create durée
                    Dureeformations::create([
                        'id_webinaire' => $id_webinaire,
                        'code_formation' => $this->code_formation,
                        'duree' => $rows[$i][3], //$duree_formation,
                    ]);
                }
            }

            if ($i >= 0) {

                if ($rows[$i][0] == "Renseignements sur le panéliste") {
                    $paneliste = true;

                    //  $nbr_start_insert_paneliste = $i + 1; // saut de ligne (entête)
                    //  echo "Renseignements sur le panéliste";
                } else if ($rows[$i][0] == "Renseignements sur le participant non-intervenant") {
                    // echo $rows[$i][0];
                    $participant = true;
                    // $nbr_start_insert_paneliste = $i + 1; // saut de ligne (entête) 

                } else if ($rows[$i][0] == "Autres présents") {
                    // echo $rows[$i][0];
                    $autre_present = true;
                    // echo "Renseignements sur les participants";
                    // return dd($rows[$i][0]);
                }

                // ############### enregistrer la liste des panélistes ######################
                if ($paneliste == true && $participant == false && $autre_present == false) {
                    $saut_ligne_paneliste++;
                    if ($saut_ligne_paneliste > 2) { // saut de 2 ligne
                        if(!empty($rows[$i][2])){
                            Panelistes::create([
                                'a_participe' => $rows[$i][0],
                                'username' => $rows[$i][1],
                                'email' => trim($rows[$i][2]),
                                'heure_arrivee' => $rows[$i][3],
                                'heure_depart' => $rows[$i][4],
                                'temps_presence' => $rows[$i][5],
                                'pays' => $rows[$i][7],
                                'id_webinaire' => $id_webinaire,
                                'code_formation' => $this->code_formation,
                            ]);

                        }
                    }
                }

                // ############### enregistrer la liste des auditeurs ######################
                if ($paneliste == true && $participant == true && $autre_present == false) {
                    $saut_ligne_participant++;
                    if ($saut_ligne_participant > 2) { // saut de 2 ligne
                        $get_email = $rows[$i][4];
                        $a_participe = $rows[$i][0];
                        //$username = $rows[$i][1];
                        $heure_arrivee = $rows[$i][7];
                        $heure_depart = $rows[$i][8];
                        $temps_presence = $rows[$i][9]; 
                        if (!empty($get_email)) {
                            # code...

                            // vérifier si l'e-mail existe dans la table des inscrits
                            $check_auditeur = Inscriptions::where('email', $get_email)->where('code_formation', $this->code_formation)->exists();
                            if ($check_auditeur) {
                                $auditeur = Inscriptions::where('email', $get_email)->where('code_formation', $this->code_formation)->first();
                                // if auditeur exist register data into table participant
                                Participants::create([
                                    'a_participe' => $a_participe,
                                    'username' => $auditeur->nom.' '.$auditeur->prenoms,
                                    'email' => $get_email,
                                    'heure_arrivee' => $heure_arrivee,
                                    'heure_depart' => $heure_depart,
                                    'temps_presence' => $temps_presence,
                                    'id_webinaire' => $id_webinaire,
                                    'code_formation' => $this->code_formation,
                                ]);

                            } else {
                                // on enregistre rien
                            }

                        }
                    }
                }
            }
        }

        } catch (\Throwable $th) {
            //throw $th;
            Log::info($th->getMessage());
        }

        return true;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
