<?php

namespace App\Imports;

use App\Mail\NotificationNewCompteAuditeur;
use App\Models\Auditeurs;
use App\Models\Dureeformations;
use App\Models\Inscriptions;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class InscriptionReunionImport implements ToCollection, WithCustomCsvSettings
{
    private $code_formation;

    public function __construct($param)
    {
        $this->code_formation = $param;
       // print($this->code_formation);
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

    public function genererPassword($length = 25) {
        $characters = '01234567@$89ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function genererCodeUserService(): string {
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


    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function collection(Collection $rows)
    {

        // je récupère le nombre de ligne dans le fichier csv incription
        $nbr_data_inscription = count($rows);
        // initialiser le id_webinaire
        $id_webinaire = 0;
        $duree_formation = 0;
        //#################### je récupère id_webinaire dans le fichier
        /*foreach ($rows[3] as $key => $value){
            $id_webinaire = preg_replace("/\s+/", "", $rows[3][1]);
            $duree_formation = $rows[3][3];
        } */
        // ############### récupération de la liste des inscrits à la formation ################
        // ligne N°5 dans le fichier
        for($i = 1; $i < $nbr_data_inscription; $i++){

            // vérifier si l'e-mail existe dans la table des inscrits
            $auditeur = Inscriptions::where('email', $rows[$i][2])->where('code_formation',$this->code_formation)->exists();
            if ($auditeur) {
                // if auditeur exist sauter

            }else{
                // if auditeur not exist register data into table inscrit before insert participant

                //register data into inscrit

                // vérifier le format de l'importation
                if(!empty($rows[$i][2])){
                    # code... nouveau modele
                    Inscriptions::create( [
                        //'nom' => $rows[$i][1],
                        //'prenoms' => $rows[$i][0],
                        'email' => trim($rows[$i][2]),
                        // 'genre' => $rows[$i][12],
                        //'pays' => $rows[$i][11],
                        //'ville' => $rows[$i][5],
                        //'code_pays' => $rows[$i][6],
                        //'telephone' => $rows[$i][7],
                        //'secteur_activite' => $rows[$i][8],
                        //'organisation' => $rows[$i][9],
                        //'intitule_poste' => $rows[$i][10],
                        'heure_inscription' => trim($rows[$i][3]),
                        'etat_approbation' => trim($rows[$i][4]),
                        'id_webinaire' => $id_webinaire,
                        'code_formation' => $this->code_formation,
                    ]);


                    //############# create compte auditeur ##################
                    // créer le compte de l'auditeur si celà n'existe pas
                    $email = trim($rows[$i][2]);
                    $check_auditeur = Auditeurs::where("email",$email)->exists();
                    $check_user = User::where("email",$email)->exists();

                    if($check_user == false && $check_auditeur == false){
                        //$nbre = $nbre + 1;
                        // if user not exist, create user acount
                        // generator password
                        $generate_password = $this->genererPassword(8);
                        $hash_generate_password= Hash::make($generate_password);
                        $codeUtilisateur = $this->genererCodeUserService();
                        $user = new User();
                        $user->setAttribute("code_user",$codeUtilisateur);
                        $user->setAttribute("name",trim($rows[$i][0])." ".trim($rows[$i][1]));
                        $user->setAttribute("email",$email);
                        $user->setAttribute("email_verified_at",date('Y-m-d H:i:s'));
                        $user->setAttribute("password",$hash_generate_password);
                        $user->save();

                        // save role user
                        $role_id = 6 ; // auditeur role
                        $user->roles()->sync($role_id); 

                        // register data into auditeur table
                        $auditeur = new Auditeurs();
                        $auditeur->setAttribute("nom",trim($rows[$i][1]));
                        $auditeur->setAttribute("prenoms",trim($rows[$i][0]));
                        $auditeur->setAttribute("email",$email);
                        $auditeur->setAttribute("telephone",trim($rows[$i][7]));
                        $auditeur->setAttribute("pays",trim($rows[$i][11]));
                        $auditeur->setAttribute("code_pays",trim($rows[$i][6]));
                        $auditeur->setAttribute("nationalite","");
                        $auditeur->setAttribute("ville",trim($rows[$i][5]));
                        $auditeur->setAttribute("genre",trim($rows[$i][12]));
                        $auditeur->setAttribute("secteur_activite",trim($rows[$i][8]));
                        $auditeur->setAttribute("organisme",trim($rows[$i][9]));
                        $auditeur->setAttribute("intitule_poste",trim($rows[$i][10]));
                        $auditeur->save();

                        // send mail for user to inform that about acount
                        // send identifiant to mail for user
                        $contenu = [
                            'nom_prenoms' => $user->name,
                            'email' => trim($user->email) ,
                            'mot_de_passe' => $generate_password,
                            'subject'=>'SIGWEB - CREATION DE COMPTE'
                        ];

                        try {
                            # code...
                            Mail::to(trim($user->email))->queue(new NotificationNewCompteAuditeur($contenu));

                        } catch(\Exception $e){
                            // Get error here

                        }


                    }

                    //#######################################################

                }



            }

            /*  } */

        }

        // enregistrer la durée de la formation pour exploitation dans rapports

        Dureeformations::create([
            'id_webinaire' => $id_webinaire,
            'code_formation' => $this->code_formation,
            'duree' => 0, // $duree_formation,
        ]);

        return true;

    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
