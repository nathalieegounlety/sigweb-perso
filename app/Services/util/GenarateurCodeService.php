<?php
namespace App\Services\util;
use App\Models\Paiements;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
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
}
