<?php

namespace App\Imports;

use App\Mail\NotificationNewCompteAuditeur;
use App\Models\Auditeurs;
use App\Models\Formations;
use App\Models\Inscriptions;
use App\Models\Pays;
use App\Models\Rapports;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;

class PresentielImport implements ToCollection
{
    private $code_formation;

    public function __construct($param)
    {
        $this->code_formation = $param; 
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


    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function collection(Collection $rows)
    {

        // je récupère le nombre de ligne dans le fichier
        $nbr_data_presentiel = count($rows);
        // mapping des column du fichier pour lecture
        $column_code = 0;
        $column_prenoms = 1;
        $column_nom = 2;
        $column_profession_fonction = 3;  // represente intitule_poste dans la table inscrit
        // $column_pays = 4;
        $column_code_pays = 4;
        $column_nationalite = 5;
        $column_telephone = 6;
        $column_email = 7;
        $column_genre = 8;
        //$column_id_webinaire = 10;
        $column_a_participer = 9;

        // print_r("column_presentiel ===> ".$rows[1][$column_presentiel]." nom ===> ".$rows[1][$column_nationalite]);
        // insertion des données dans la table inscrits
        for($i = 1; $i < $nbr_data_presentiel; $i++){

        if (!empty($rows[$i][$column_nom]) && !empty($rows[$i][$column_prenoms]) && !empty($rows[$i][$column_code])) {
            # code... 
            if(!empty($rows[$i][$column_email])){
                $email = $rows[$i][$column_email];
            }else{
                $nom = Str::ascii(Str::lower(str_replace(' ', '', $rows[$i][$column_nom])));
                $prenoms = Str::ascii(Str::lower(str_replace(' ', '', $rows[$i][$column_prenoms])));
                $email = $prenoms.".".$nom.date("Ymdhis")."@gmail.com";
            }
            // vérifier si l'e-mail existe dans la table des inscrits
            $auditeur = Inscriptions::where('email', trim($email))->where('code_formation',$this->code_formation)->exists();
            if ($auditeur) {
                // if auditeur exist sauter
                // récupérer le pays à partir du code
                $get_pays = Pays::where("code_iso",trim($rows[$i][$column_code_pays]))->exists();
                if ($get_pays) {
                    $get_pays_ok = Pays::where("code_iso",trim($rows[$i][$column_code_pays]))->get()->first();
                    $pays = $get_pays_ok->nom_pays;
                    # code...
                } else {
                    # code...
                    $pays = "";
                }

                // créer le compte de l'auditeur si celà n'existe pas
                //$email = trim($rows[$i][$column_email]);
                $check_auditeur = Auditeurs::where("email",$email)->exists();
                $check_user = User::where("email",$email)->exists();

                if($check_user == false && $check_auditeur == false){
                    //$nbre = $nbre + 1;
                    // if user not exist, create user acount
                    // generator password
                    $generate_password = $this->genererPassword(8);
                    $hash_generate_password= Hash::make($generate_password);
                    $user = new User();
                    $user->setAttribute("name",trim($rows[$i][$column_prenoms])." ".trim($rows[$i][$column_nom]));
                    $user->setAttribute("email",$email);
                    $user->setAttribute("email_verified_at",date('Y-m-d H:i:s'));
                    $user->setAttribute("password",$hash_generate_password);
                    $user->save();

                    // save role user
                    $role_id = 6 ; // auditeur role
                    $user->roles()->sync($role_id);

                    // register data into auditeur table
                    $auditeur = new Auditeurs();
                    $auditeur->setAttribute("nom",trim($rows[$i][$column_nom]));
                    $auditeur->setAttribute("prenoms",trim($rows[$i][$column_prenoms]));
                    $auditeur->setAttribute("email",$email);
                    $auditeur->setAttribute("telephone",trim($rows[$i][$column_telephone]));
                    $auditeur->setAttribute("pays",trim($pays));
                    $auditeur->setAttribute("code_pays",trim($rows[$i][$column_code_pays]));
                    $auditeur->setAttribute("nationalite",trim($rows[$i][$column_nationalite]));
                    //$auditeur->setAttribute("ville",$inscrit->ville);
                    $auditeur->setAttribute("genre",trim($rows[$i][$column_genre]));
                    //$auditeur->setAttribute("secteur_activite",$inscrit->secteur_activite);
                    //$auditeur->setAttribute("organisme",$inscrit->organisme);
                    $auditeur->setAttribute("intitule_poste",trim($rows[$i][$column_profession_fonction]));
                    $auditeur->save();

                    // send mail for user to inform that about acount
                    // send identifiant to mail for user
                    $contenu = [
                        'nom_prenoms' => trim($user->name),
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
            }else{

                if(!empty(trim($email))){

                    // if auditeur not exist register data into table inscrit before into participant

                    //register data into inscrit

                    // récupérer le pays à partir du code
                    $get_pays = Pays::where("code_iso",trim($rows[$i][$column_code_pays]))->exists();
                    if ($get_pays) {
                        $get_pays_ok = Pays::where("code_iso",trim($rows[$i][$column_code_pays]))->get()->first();
                        $pays = $get_pays_ok->nom_pays;
                        # code...
                    } else {
                        # code...
                        $pays = "";
                    }
                    // dd("test");
                    $insert_inscription = new Inscriptions();
                    $insert_inscription->setAttribute("email",trim($email));
                    $insert_inscription->setAttribute("id_webinaire",$this->code_formation);
                    $insert_inscription->setAttribute("code_formation",$this->code_formation);
                    $insert_inscription->setAttribute("presentiel",1);
                    $insert_inscription->setAttribute("type_formation",'PRESENTIEL');
                    $insert_inscription->save(); 

                }

                // créer le compte de l'auditeur si celà n'existe pas
                // $email = trim($rows[$i][$column_email]);
                $check_auditeur = Auditeurs::where("email",$email)->exists();
                $check_user = User::where("email",$email)->exists();

                if($check_user == false && $check_auditeur == false){
                    //$nbre = $nbre + 1;
                    // if user not exist, create user acount
                    // generator password
                    $generate_password = $this->genererPassword(8);
                    $hash_generate_password= Hash::make($generate_password);
                    $user = new User();
                    $user->setAttribute("name",trim($rows[$i][$column_prenoms])." ".trim($rows[$i][$column_nom]));
                    $user->setAttribute("email",$email);
                    $user->setAttribute("email_verified_at",date('Y-m-d H:i:s'));
                    $user->setAttribute("password",$hash_generate_password);
                    $user->save();
                    $role_id = 6 ; // auditeur role
                    $user->roles()->sync($role_id);

                    // register data into auditeur table
                    $auditeur = new Auditeurs();
                    $auditeur->setAttribute("nom",trim($rows[$i][$column_nom]));
                    $auditeur->setAttribute("prenoms",trim($rows[$i][$column_prenoms]));
                    $auditeur->setAttribute("email",$email);
                    $auditeur->setAttribute("telephone",trim($rows[$i][$column_telephone]));
                    $auditeur->setAttribute("pays",trim($pays));
                    $auditeur->setAttribute("code_pays",trim($rows[$i][$column_code_pays]));
                    $auditeur->setAttribute("nationalite",trim($rows[$i][$column_nationalite]));
                    //$auditeur->setAttribute("ville",$inscrit->ville);
                    $auditeur->setAttribute("genre",trim($rows[$i][$column_genre]));
                    //$auditeur->setAttribute("secteur_activite",$inscrit->secteur_activite);
                    //$auditeur->setAttribute("organisme",$inscrit->organisme);
                    $auditeur->setAttribute("intitule_poste",trim($rows[$i][$column_profession_fonction]));
                    $auditeur->save();

                    // send mail for user to inform that about acount
                    // send identifiant to mail for user
                    $contenu = [
                        'nom_prenoms' => trim($user->name),
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

            }

            // confirmation des participants ayant réellement paricipé à la formation
            if (trim($rows[$i][$column_a_participer]) == "OUI" || trim($rows[$i][$column_a_participer]) == "Oui" || trim($rows[$i][$column_a_participer]) == "oui") {

                if(!empty(trim($email))){
                    # code...
                    // VÉRIFIER SI LE PARTICIPANT EST DEJA IMPORTE DANS RAPPORT
                    $check_rapport = Rapports::where("email",$email)
                        ->where("temps_presence",0)
                        ->where("code_formation",trim($this->code_formation))
                        ->exists();
                    if ($check_rapport) {
                        # code...
                    } else {
                        # code...
                        Rapports::create( [
                            'email' => trim($email),
                            'temps_presence' => 0,
                            'telephone' => trim($rows[$i][$column_telephone]),
                            'intitule_poste' => trim($rows[$i][$column_profession_fonction]),
                            'id_webinaire' => trim($this->code_formation),
                            'code_formation' => trim($this->code_formation),
                        ]);
                    }

                }
            }
            // mise à jour du champ présentiel dans la table formation
            $formation = Formations::where('code_formation',$this->code_formation)->first();
            $formation->setAttribute('presentiel', 1);
            $formation->update();
        }

        }

        // insertion des données dans la table rapports

        return true;

    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
