<?php
namespace App\Services\util;

use App\Models\Formations;
use App\Models\Historiqueactions;
use App\Models\Moodlemoyennegenerales;
use App\Models\Moodlemoyennes;
use App\Models\Paiements;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

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

    public function genererCodeAlphaNumeric($length = 16): string {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
        return $randomString;
    }

    public function genererNumeroPaiement(): string {$annee_actuelle = date('Y');
        $type_reference = "FACT";
        $indicatif = "ERS";

        return DB::transaction(function () use ($type_reference, $indicatif, $annee_actuelle) {
            // Recherche du dernier paiement de l'année en cours
            $lastPaiement = Paiements::lockForUpdate()
                ->whereYear('created_at', $annee_actuelle)
                ->orderBy('id', 'desc')
                ->first();

            $increment = 1;

            if ($lastPaiement && $lastPaiement->reference) {
                $parts = explode('-', $lastPaiement->reference);

                // Vérification du format
                if (count($parts) === 4 && is_numeric($parts[3])) {
                    $increment = (int) $parts[3] + 1;
                }
                // Sinon, on repart de 1 (sécurité)
            }

            $numeroFormate = str_pad($increment, 6, '0', STR_PAD_LEFT);

            // Format final : ERS-2025-FACT-000001
            return "{$indicatif}-{$annee_actuelle}-{$type_reference}-{$numeroFormate}";
        });
    }

    public function generateUrlSigned(string $routeName, array $params = []): string
    {
        return URL::signedRoute($routeName, $params);
    }

    public function getBladeAttestationParticipant($is_event, $formation)
    {
        $pdf = null;

        if ($is_event && ($formation->typeformation_id == 3 || $formation->typeformation_id == 5 )) {
            # code...
            $pdf = PDF::loadView('attestations.modele_attestation_evenement');

        } elseif ($is_event && $formation->typeformation_id == 1) { // formation
            # code...
            $pdf = PDF::loadView('attestations.modele_attestation_formation_evenement');
        }
        else{

            switch ($formation->typeformation_id) {
                case 1: // formation
                    # code...
                    $pdf = PDF::loadView('attestations.modele_attestation');
                    break;
                case 2: // conférence
                    # code...
                    $pdf = PDF::loadView('attestations.modele_attestation');
                    break;
                case 3: // colloque
                    # code...

                    $dynamique_blade = 'attestations.attestation_colloque_'.$formation->code_formation ;
                    $default_blade = 'attestations.modele_attestation_colloque';

                    if (View::exists($dynamique_blade)) {
                        $pdf = PDF::loadView($dynamique_blade);

                    } else {
                        $pdf = PDF::loadView($default_blade);
                    }
                    break;
                case 4: // forum
                    # code...
                    Log::info('begin print attestation forum');
                    $pdf = PDF::loadView('attestations.modele_attestation_forum');
                    break;
                case 5: // atelier
                    # code...
                    $pdf = PDF::loadView('attestations.modele_attestation_atelier');
                    break;
                case 6: // université
                    # code...
                    $pdf = PDF::loadView('attestations.modele_attestation_universite');
                    break;
                case 7: // salon
                    # code...
                    $pdf = PDF::loadView('attestations.modele_attestation_salon');
                    break;

                default:
                    # code...
                    $pdf = PDF::loadView('attestations.modele_attestation');
                    break;
            }
        }

        return $pdf;
    }

    // get blade attestation formateur
    public function getBladeAttestationFormateur($is_event, $typeformation_id)
    {
        $pdf = null;

        if ($is_event && ($typeformation_id == 3 || $typeformation_id == 5)) {
            # code...
            $pdf = PDF::loadView('attestations.modele_attestation_paneliste_evenement');

        } else {
            # code...
            switch ($typeformation_id) {
                case 1: // formation
                    # code...
                    $pdf = PDF::loadView('attestations.pdf_attestation_by_formateur');
                    break;
                case 2: // conférence
                    # code...
                    $pdf = PDF::loadView('attestations.pdf_attestation_by_formateur');
                    break;
                case 3: // colloque
                    # code...
                    # $pdf = PDF::loadView('attestations.modele_attestation_paneliste_colloque');
                    $pdf = PDF::loadView('attestations.modele_attestation_paneliste_colloque_ERS2024-M4');
                    break;
                case 4: // forum
                    # code...
                    $pdf = PDF::loadView('attestations.modele_attestation_paneliste_forum');
                    break;
                case 5: // atelier
                    # code...
                    $pdf = PDF::loadView('attestations.modele_attestation_paneliste_atelier');
                    break;
                case 6: // université
                    # code...
                    $pdf = PDF::loadView('attestations.modele_attestation_paneliste_universite');
                    break;
                case 7: // salon
                    # code...
                    $pdf = PDF::loadView('attestations.modele_attestation_paneliste_salon');
                    break;

                default:
                    # code...
                    $pdf = PDF::loadView('attestations.pdf_attestation_by_formateur');
                    break;


            }
        }

        return $pdf;
    }

    // save historique action
    public function saveHistoriqueAction ($action, $description, $user_id = null){
        $data = new Historiqueactions();
        $data->setAttribute('action',$action);
        $data->setAttribute('description',$description);
        $data->setAttribute('ip_adresse', $this->getIpAdresse());
        $data->setAttribute('mac_adresse', $this->getMacAdresse()); 
        $data->setAttribute('created_by',$user_id);
        $data->save();
    }

    private function getIpAdresse(): string
{
    $request = request();

    // Gère les proxies / load balancers
    return $request->header('X-Forwarded-For')
        ?? $request->header('X-Real-IP')
        ?? $request->ip()
        ?? 'unknown';
}

private function getMacAdresse(): string
{ 
    if (PHP_OS_FAMILY === 'Windows') {
        $output = shell_exec('getmac /fo csv /nh');
        preg_match('/\"([0-9A-Fa-f\-]{17})\"/', $output, $matches);
        return $matches[1] ?? 'unknown';
    }

    if (PHP_OS_FAMILY === 'Linux') {
        $output = shell_exec("cat /sys/class/net/$(ip route show default | awk '/default/ {print $5}')/address");
        return trim($output) ?: 'unknown';
    }

    return 'unknown';
}

// calcul moyenne generale
public function startCalculMg(String $code_formation){
    
        try {
             $formations = Formations::whereCodeFormation($code_formation)->first();
        $anne_academique = date("Y",strtotime($formations->date_debut));
        $user = Auth('api')->user();
        // récupérer la liste des modules pour la formation
        $getModule = Moodlemoyennes::join("moodlemodules","moodlemodules.id","=","moodlemoyennes.moodlemodule_id")
        ->whereCodeFormation($code_formation)
        ->distinct("moodlemoyennes.moodlemodule_id")
        ->pluck("moodlemodules.code");
        
        Log::info('calcul moyenne begin ', ['module' => $getModule]); 
        // récupérer la sommes des crédits concernant les modules disponibles pour la formation
        //$somme_credit = Moodlemodules::whereIn("code",$getModule)->sum("credit");
        // 
        $somme_module = count($getModule);

        Log::info('calcul moyenne somme module ', ['module' => $somme_module]); 
        // récupérer la liste des candidats
        $getCandidat = Moodlemoyennes::whereCodeFormation($code_formation)
        ->distinct("reference_candidat")
        ->select("reference_candidat")
        ->get();
        // calcul la moyenne générale pour chaque candidat
        foreach ($getCandidat as $candidat) {
            # code...
            // récupérer la somme des notes coeficiée par module
            $somme_note = Moodlemoyennes::whereReferenceCandidat($candidat->reference_candidat)->whereCodeFormation($code_formation)->sum("note_totale");
            // calcul moyenne générale
            Log::info('calcul moyenne somme coef ', ['coef' => $somme_note]); 

            if ($somme_note > 0) {
                # code...
                $mg = round($somme_note/$somme_module,2);

                $checkData = Moodlemoyennegenerales::whereCodeFormation($code_formation)->whereReferenceCandidat($candidat->reference_candidat)->whereAnneeAcademique($anne_academique)->exists();
                
                if ($checkData) {
                    # code...
                    $moyenneGenerale = Moodlemoyennegenerales::whereCodeFormation($code_formation)->whereReferenceCandidat($candidat->reference_candidat)->whereAnneeAcademique($anne_academique)->first();
                    $updMG = Moodlemoyennegenerales::find($moyenneGenerale->id);
                    $updMG->setAttribute("code_formation",$code_formation);
                    $updMG->setAttribute("reference_candidat",$candidat->reference_candidat);
                    $updMG->setAttribute("moyenne",$mg);
                    $updMG->setAttribute("annee_academique",$anne_academique);
                    $updMG->setAttribute("created_by",$user->id);
                    $updMG->setAttribute("updated_by",$user->id);
                    $updMG->update();

                } else {
                    # code...
                    $moyenneGenerale = new Moodlemoyennegenerales();
                    $moyenneGenerale->setAttribute("code_formation",$code_formation);
                    $moyenneGenerale->setAttribute("reference_candidat",$candidat->reference_candidat);
                    $moyenneGenerale->setAttribute("moyenne",$mg);
                    $moyenneGenerale->setAttribute("annee_academique",$anne_academique);
                    $moyenneGenerale->setAttribute("created_by",$user->id);
                    $moyenneGenerale->setAttribute("updated_by",$user->id);
                    $moyenneGenerale->save();
                }
                
                

            }

        }

        return $result = [
                'statut' => true,
                'message' => "calcul effectué avec succès",   
            ];
            Log::info('calcul moyenne success ', ['request' => $result]); 
        } catch (\Exception $e) {
            //throw $th;
            return $result = [
                'statut' => false,
                'message' => $e->getMessage(),   
            ];
            Log::info('calcul moyenne error ', ['request' => $result]); 
        }
}

}
