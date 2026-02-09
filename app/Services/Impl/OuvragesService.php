<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Mail\MailPaiementOuvrage;
use App\Mail\NotificationOuvrageDRD;
use App\Models\Auditeurs;
use App\Models\Detailpaiementouvrages;
use App\Models\Paiements;
use App\Models\User;
use App\Services\contracts\OuvragesInterface;
use App\Services\util\GenarateurCodeService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use \App\Models\Ouvrages;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OuvragesService implements OuvragesInterface
{
    private GenarateurCodeService $genarateurCodeService;

    public function __construct(GenarateurCodeService $genarateurCodeService)
    {
        $this->genarateurCodeService = $genarateurCodeService;
    }
    public function saveOuvrage(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement saveOuvrage() method.
        $checkData = Ouvrages::whereLibelle($data->libelle)
            ->whereMontant($data->montant)
            ->exists();
        if ($checkData) {
            return ResponseHandler::error("La données existe déjà", 409);
        }

        $dataCreate = new Ouvrages();
        $dataCreate->setAttribute('libelle', $data->libelle);
        $dataCreate->setAttribute('montant', $data->montant);
        $dataCreate->setAttribute('nbre_page',$data->nbre_page);
        // sauvegarde visuel si disponible
        if ($data->visuel) {
            $visuel = $data->visuel;
            $extension = $visuel->getClientOriginalExtension();
            $name_visuel = 'visuel_'.date('Ymd-His'). '.'.$visuel->extension();
            $visuel->storeAs('visuel_ouvrage', $name_visuel, 'public');
            $dataCreate->setAttribute('visuel_path', $name_visuel);
        }

        if (auth('api')->user()) {
            $user = auth('api')->user();
            $dataCreate->setAttribute('created_by', $user->id);
            $dataCreate->setAttribute('updated_by', $user->id);
        }

        $dataCreate->save();
        return ResponseHandler::success("Création efféctuée");
    }

    public function getOuvrage(int $id)
    {
        // TODO: Implement getOuvrage() method.
        $data = Ouvrages::find($id);
        if (!$data) {
            return ResponseHandler::error("La données n'existe pas", 409);
        }
        return ResponseHandler::success("ok", $data);
    }

    public function getOuvrages(Request $request): LengthAwarePaginator
    {
        // TODO: Implement getOuvrages() method.
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Filtres spécifiques
        $filterLibelle = $request->input('filter_libelle');
        $filterIsEnable = $request->input('filter_is_enable');
        $filterUpdatedAt = $request->input('filter_updated_at');
        $filterAuthor = $request->input('filter_author');
        $filterMontant = $request->input('montant');

        $query = Ouvrages::with('getUserUpdated:id,name,email,code_user')
            ->select('ouvrages.id', 'ouvrages.libelle','ouvrages.enable','ouvrages.montant', 'ouvrages.updated_at',
                'ouvrages.updated_by','ouvrages.nbre_page','ouvrages.visuel_path');

        // On fait la jointure
        $needsUserJoin = $search || $filterAuthor || $sortField === 'get_user_updated.name';

        if ($needsUserJoin) {
            $query->leftJoin('users', 'ouvrages.updated_by', '=', 'users.id');
        }

        // Recherche globale sur plusieurs champs
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->Where('ouvrages.libelle', 'like', "%{$search}%")
                    ->orWhere('ouvrages.montant', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%"); // Recherche sur auteur
            });
        }

        // Filtres par colonne
        if ($filterLibelle) {
            $query->where('ouvrages.libelle', 'like', "%{$filterLibelle}%");
        }
        if ($filterMontant) {
            $query->where('ouvrages.montant', 'like', "%{$filterMontant}%");
        }
        if ($filterIsEnable !== null && $filterIsEnable !== '') {
            $query->where('ouvrages.enable', $filterIsEnable); // 0 ou 1
        }
        if ($filterUpdatedAt) {
            $query->whereDate('ouvrages.updated_at', $filterUpdatedAt);
        }
        if ($filterAuthor) {
            $query->where('users.name', 'like', "%{$filterAuthor}%");
        }

        // Tri
        if ($sortField === 'get_user_updated.name') {
            $query->orderBy('users.name', $sortOrder);
        } else {
            $query->orderBy("ouvrages.{$sortField}", $sortOrder);
        }

        $query->orderBy($sortField, $sortOrder);

        return $query->paginate($perPage);
    }

    public function update(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement update() method.
        $dataUpdate = Ouvrages::find($data->id);

        if (!$dataUpdate) {
            return ResponseHandler::error("Données à modifier introuvable",404);
        }
        // check existing data
        $checkData = Ouvrages::whereLibelle($data->libelle)
            ->whereMontant($data->montant)
            ->where("id", "!=", $dataUpdate->id)->exists();

        if ($checkData) {
            return ResponseHandler::error("Le données existe déjà");
        } else {
            // process update
            $dataUpdate->setAttribute('libelle', $data->libelle);
            $dataUpdate->setAttribute('montant', $data->montant);
            $dataUpdate->setAttribute('updated_by', auth('api')->id());
            $dataUpdate->setAttribute('nbre_page',$data->nbre_page);
            // sauvegarde visuel si disponible
            if ($data->visuel) {
                $visuel = $data->visuel;
                $extension = $visuel->getClientOriginalExtension();
                $name_visuel = 'visuel_'.date('Ymd-His'). '.'.$visuel->extension();
                $visuel->storeAs('visuel_ouvrage', $name_visuel, 'public');
                $dataUpdate->setAttribute('visuel_path', $name_visuel);
            }
            $dataUpdate->update();
            return ResponseHandler::success("Modification effectuée avec succès");
        }
    }

    public function remove(int $id): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement remove() method.
        $dataDelete = Ouvrages::find($id);
        if (!$dataDelete) {
            return ResponseHandler::error("Données à supprimer introuvable",404);
        }

        $delete = $dataDelete->delete();

        if ($delete) {
            return ResponseHandler::success("Suppression effectuée.");
        } else {
            return ResponseHandler::error("Echec lors de la suppression, vous ne pouvez pas surpprimer cette données : $dataDelete->libelle.",401);
        }
    }

    public function objOuvrage(): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement objOuvrage() method.
        $data = Ouvrages::where('enable', true)->get(['id', 'libelle', 'montant']);
        return ResponseHandler::success("ok",$data);
    }

    public function saveChoixOuvrages(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement saveChoixOuvrages() method.
        // récupérer les données du formulaire
        $ouvrages = collect($request->input('liste_ouvrages'))->map(fn ($ouvrage) => (object) $ouvrage);
        $montantTotal = $request->input('montant_total');
        $user = Auth('api')->user();
        $auditeur = Auditeurs::where("email",$user->email)->first();

        // récupération des variables
        $nom = $auditeur->nom;
        $prenoms = $auditeur->prenoms;
        $email = $auditeur->email;
        $telephone = $auditeur->telephone;

        // enregistrement paiement ouvrage

        // générer le numero de reference facture : ERS-2025-FAC-000001
        $reference_paiement =$this->genarateurCodeService->genererNumeroPaiement();

        // enregistrement du paiement
        $paiement = new Paiements();
        $paiement->setAttribute('reference',$reference_paiement);
        $paiement->setAttribute('email',$email);
        $paiement->setAttribute('telephone',$telephone);
        $paiement->setAttribute('montant_total',$montantTotal);
        $paiement->setAttribute('montant_restant',$montantTotal);
        $paiement->setAttribute('statut_traitement',"ATTENTE");
        $paiement->setAttribute('statut_paiement',"EN ATTENTE");
        $paiement->setAttribute('type',"OUVRAGE");

        // enregistrement des détails paiement ouvrage

        try {
            //code...
            $paiement->save();
            foreach ($ouvrages as $key => $data) {
                # code...
                    $details_paiement = new Detailpaiementouvrages();
                    $details_paiement->setAttribute('paiementouvrage_id',$paiement->id);
                    $details_paiement->setAttribute('ouvrage_id',$data->ouvrage_id);
                    $details_paiement->setAttribute('reference',$data->reference);
                    $details_paiement->setAttribute('montant',$data->montant);
                    $details_paiement->setAttribute('email',$auditeur->email);
                    $details_paiement->save();
            }

            // envoie de mail pour la refence de la facture

            $contenu = [
                'nom' => $nom,
                'prenoms' => $prenoms,
                'reference_paiement' => $paiement->reference,
                'subject' => 'INFORMATION SUR VOTRE DEMANDE DE PAIEMENT',
                'email'=>$email,
                'url_proforma' => $this->genarateurCodeService->generateUrlSigned('proforma-ouvrage',['reference' =>$paiement->reference])
            ];

            try {
                # code...
                Mail::to($email)->cc(env('MAIL_CC'))->bcc(env('MAIL_BCC'))->queue(new MailPaiementOuvrage($contenu));
            } catch(\Exception $e){
                // Get error here
                Log::info($e->getMessage());
            }

            //Notifier DRD des commandes

            if (env('PAIEMENT_NOTIFIER_DRD')) {
                # code...
                // send mail drd

                $liste_DRD = User::whereHas('roles', function ($q) {
                    $q->where('libelle', 'DOCUMENTATION');
                })->pluck('email');

                // dd($liste_DRD);
                    $contenu = [
                        'name' => 'DOCUMENTATION',
                        'nom' => $nom,
                        'prenoms' => $prenoms,
                        'reference_paiement' => $paiement->reference,
                        'subject' => 'NOUVELLE COMMANDE D\'OUVRAGE'.date('d-m-Y H:i:s'),
                        'email'=>$email
                    ];
                    try {
                        //  Log::info('Envoi d\'email de commandes à : ' . $drd->email);
                        Mail::to(env('MAIL_CC'))->cc($liste_DRD)->bcc(env('MAIL_BCC'))->queue(new NotificationOuvrageDRD($contenu));
                        // Log::info('Email envoyé avec succès à : ' . $drd->email);
                    } catch (\Exception $e) {
                        Log::error('Erreur d\'envoi de l\'email au DRD : ' . $e->getMessage());
                    }

            }

            $reference_paiement = $paiement->reference;

            // redirection sur la page de paiement facture avec les références de la factures
            return ResponseHandler::success('Enregistrement effectué avec succès. Le lien de paiement vous a été envoyé par mail',['reference'=>$reference_paiement,'success'=>true]);


        } catch (\Exception $e) {
            //throw $th;
            //echo $e->getMessage();
            return ResponseHandler::error('Echec lors de l\'enregistrement des données',400,['success'=>false]);
        }

    }

    public function getRecapOuvrages(Request $request): LengthAwarePaginator
    {
        // TODO: Implement getRecapOuvrages() method.
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Filtres spécifiques
        $filterIdOuvrage = $request->input('id_ouvrage');

        $query = Detailpaiementouvrages::leftJoin('paiements','paiements.id','=','detailpaiementouvrages.paiementouvrage_id')
            ->leftJoin('auditeurs','auditeurs.email','=','paiements.email')
            ->leftJoin('modepaiements','modepaiements.id','=','paiements.id_modepaiement')
            ->select('detailpaiementouvrages.id', 'auditeurs.nom','auditeurs.prenoms','auditeurs.email', 'paiements.montant_total',
                'paiements.montant_paye','paiements.montant_restant','modepaiements.libelle','detailpaiementouvrages.montant')
            ->where("detailpaiementouvrages.ouvrage_id",$filterIdOuvrage);

        // Recherche globale sur plusieurs champs
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->Where('modepaiements.libelle', 'like', "%{$search}%")
                    ->orWhere('auditeurs.nom', 'like', "%{$search}%")
                    ->orWhere('auditeurs.prenoms', 'like', "%{$search}%")
                    ->orWhere('auditeurs.email', 'like', "%{$search}%");
            });
        }
        $query->orderBy($sortField, $sortOrder);

        return $query->paginate($perPage);
    }
}
