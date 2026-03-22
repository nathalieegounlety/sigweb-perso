<?php

namespace App\Imports;

use App\Mail\NotificationNewCompteAuditeur;
use App\Models\Auditeurs;
use App\Models\Detailspaiements;
use App\Models\Formations;
use App\Models\Inscriptions;
use App\Models\Paiements;
use App\Models\Pays;
use App\Models\Rapports;
use App\Models\Tarificationsformation;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;

class InscriptionPresentielVisioImport implements ToCollection
{
    private $code_formation;
    private $cout_presentiel;
    private $cout_visio;

    public function __construct($code_formation,$cout_presentiel,$cout_visio)
    {
        $this->code_formation = $code_formation;
        $this->cout_presentiel = $cout_presentiel;
        $this->cout_visio = $cout_visio;
        //print($this->code_formation);
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

    public function genererPassword($length = 25) {
        $characters = '01234567@$89ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function genererCodeAlphaNumeric($length = 16) {
        $characters = '0123456789';
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
        $column_code_formation = 1;
        $column_prenoms = 2;
        $column_nom = 3;
        $column_profession_fonction = 4;  // represente intitule_poste dans la table inscrit
        // $column_pays = 4;
        $column_code_pays = 5;
        $column_nationalite = 6;
        $column_telephone = 7;
        $column_email = 8;
        $column_genre = 9;
        //$column_id_webinaire = 10;
        $column_a_participer = 10;
        $column_mode = 11;

        // print_r("column_presentiel ===> ".$rows[1][$column_presentiel]." nom ===> ".$rows[1][$column_nationalite]);
        // insertion des données dans la table inscrits
        for($i = 1; $i < $nbr_data_presentiel; $i++){

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
                    $codeUtilisateur = $this->genererCodeUserService();
                    $hash_generate_password= Hash::make($generate_password);
                    //
                    $user = new User();
                    $user->setAttribute("code_user",$codeUtilisateur);
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
                    $tarif = false;
                    $montant = 0;
                    $id_tarif = 0;

                    $generateCodeInscription = $this->genererCodeAlphaNumeric(8);
                    $insert_inscription = new Inscriptions();
                    $insert_inscription->setAttribute("email",trim($email));
                    $insert_inscription->setAttribute("id_webinaire",$this->code_formation);
                    $insert_inscription->setAttribute("code_formation",$this->code_formation);
                    $insert_inscription->setAttribute("code_inscription",$generateCodeInscription);
                    if(!empty(trim($rows[$i][$column_mode]))){
                        if (trim($rows[$i][$column_mode]) == "VISIO") {
                            # code...
                            $insert_inscription->setAttribute("presentiel",0);
                            $insert_inscription->setAttribute("type_formation",'VISIO');
                            if (!empty($this->cout_visio)) {
                                # code...
                                $insert_inscription->setAttribute("tarif_formation_id",$this->cout_visio);
                                $tarif = true;
                                $getTarif = Tarificationsformation::find($this->cout_visio);
                                $montant = $getTarif->getGrilletarifaire->montant;
                                $id_tarif = $this->cout_visio;
                            }
                        } else {
                            # code...
                            $insert_inscription->setAttribute("presentiel",1);
                            $insert_inscription->setAttribute("type_formation",'PRESENTIEL');
                            if (!empty($this->cout_presentiel)) {
                                # code...
                                $insert_inscription->setAttribute("tarif_formation_id",$this->cout_presentiel);
                                $tarif = true;
                                $getTarif = Tarificationsformation::find($this->cout_presentiel);
                                $montant = $getTarif->getGrilletarifaire->montant;
                                $id_tarif = $this->cout_presentiel;
                            }
                        }
                    }

                    $insert_inscription->save();

                    if ($tarif == true) {
                        # code...
                        // déclaration des informations liées au paiement.
// générer le numero de reference facture
                        // procédure d'incrémentation du numéro inventaire
                        $annee_actuelle = date('Y');
                        $type_reference = "FACT";
                        $indicatif = "ERS";
                        $id_1 = "";

                        // récupérer le dernier enregistrement
                        $last_numero_paiement = Paiements::orderBy('id', 'desc')->first();

                        if ($last_numero_paiement == NULL) {
                            $last_id = "";
                        } else {
                            $last_id = $last_numero_paiement->reference;
                        }

                        if (!empty($last_id)) {
                            $id_1 = explode('-', $last_id);
                            //vérifier si nous somme dans une nouvelle année pour réinitialiser le compteur
                            $get_date = $id_1[1];
                            if ($annee_actuelle > $get_date) {
                                # code...
                                $numero = '0000000';
                            } else {
                                # code...
                                $numero = $id_1[3];
                            }
                        } else {
                            $id_1 = '000000';
                            $numero = '000000';
                        }

                        $numero_fact = $numero + 1;

                        $numero_fact_formatted = str_pad($numero_fact, 6, "0", STR_PAD_LEFT);
                        $reference_paiement = $indicatif . '-' . $annee_actuelle . '-' . $type_reference . '-' . $numero_fact_formatted;

                        // enregistrement du paiement
                        $statut_details_paiement = "IMPAYE";
                        $statut_paiement = "EN ATTENTE";

                        $paiement = new Paiements();
                        $paiement->setAttribute('reference', $reference_paiement);
                        $paiement->setAttribute('email', $email);
                        $paiement->setAttribute('telephone', trim($rows[$i][$column_telephone]));
                        $paiement->setAttribute('code_inscription', $insert_inscription->code_inscription);
                        $paiement->setAttribute('montant_total', $montant);
                        $paiement->setAttribute('montant_restant', $montant);
                        //$paiement->setAttribute('id_modepaiement', 1); // KKIAPAY
                        $paiement->setAttribute('statut_paiement', $statut_paiement);
                        $paiement->setAttribute('type', "FORMATION");
                        $paiement->save();

                        // enregistrement détail paiement
                        $details_paiement = new Detailspaiements();
                        $details_paiement->setAttribute('reference_paiement', $paiement->reference);
                        $details_paiement->setAttribute('email', $email);
                        $details_paiement->setAttribute('code_formation', $this->code_formation);
                        $details_paiement->setAttribute('code_inscription', $insert_inscription->code_inscription);
                        $details_paiement->setAttribute('id_tarification_formation', $id_tarif);
                        $details_paiement->setAttribute('montant_restant', $montant);
                        $details_paiement->setAttribute('statut_paiement', 'IMPAYE');
                        $details_paiement->save();

                    }

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
                    $codeUtilisateur = $this->genererCodeUserService();
                    $user = new User();
                    $user->setAttribute("code_user",$codeUtilisateur);
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

            }

            // confirmation des participants ayant réellement paricipé à la formation
            if ($rows[$i][$column_a_participer] == "OUI" || $rows[$i][$column_a_participer] == "Oui" || $rows[$i][$column_a_participer] == "oui") {

                if(!empty(trim($email))){
                    # code...
                    // VÉRIFIER SI LE PARTICIPANT EST DEJA IMPORTE DANS RAPPORT ET SI LE MODE EST PRESENTIEL
                    if (!empty($rows[$i][$column_a_participer]) && $rows[$i][$column_a_participer] == "PRESENTIEL") {
                        # code...
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
            }
            // mise à jour du champ présentiel dans la table formation
            $formation = Formations::where('code_formation',$this->code_formation)->first();
            $formation->setAttribute('presentiel', 1);
            $formation->update();

        }

        // insertion des données dans la table rapports

        return true;

    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
