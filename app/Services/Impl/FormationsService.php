<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Formations;
use App\Models\FormationsTranches;
use App\Models\Grilletarifaires;
use App\Models\Logopartenaires;
use App\Models\Tarificationsformation;
use App\Services\contracts\FormationsInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormationsService implements FormationsInterface
{

    public function saveFormation(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement saveFormation() method.
        // sauvegarde des logos si disponible
        $have_logo = false;
        $logo_partenaires = $request->logos;

        // sauvegarde du visuel
        $name_visuel = null;
        if ($request->file) {
            $visuel = $request->file;
            $extension = $visuel->getClientOriginalExtension();
            $name_visuel = 'visuel_'.$request->code_formation. '_' .date('Ymd-His'). '.'.$visuel->extension();
            //$visuel->move(public_path('img/visuel/'), $name_visuel);
            $visuel->storeAs('visuel_formation', $name_visuel, 'public');

        }
        // procéder à l'enregistrement des données
        // traitement des données liées à la tarification
        //1- extration des données liés au cout de la formation
        $cout_formation = $request->cout_formation;
        // vérifier le nom d'élément dans la table cout_formation (maximum 4)
        $nbr_cout = count($cout_formation);
        $check_attestation = false;

        if($nbr_cout > 10){
            // erreur les coût associés à une formation ne peut pas dépassé trois rubriques
            return ResponseHandler::error("les coûts associés à la formation ne peut pas dépassé trois rubriques.", 401);
        }else{

            // enregistrement des données de la formation
            $formation = new Formations();
            $formation->setAttribute('code_formation',$request->get('code_formation')) ;
            $formation->setAttribute('theme',$request->get('theme')) ;
            $formation->setAttribute('id_webinaire',$request->get('id_webinaire')) ;
            $formation->setAttribute('description',$request->get('description')) ;
            $formation->setAttribute('date_debut',$request->get('date_debut')) ;
            $formation->setAttribute('date_fin',$request->get('date_fin')) ;
            $formation->setAttribute('date_limite',$request->get('date_limite')) ;
            $formation->setAttribute('statut','ACTIF');
            $formation->setAttribute('visuel',$name_visuel) ;
            if($request->get('fm_visio') === true){
                $formation->setAttribute('fm_visio',1) ;
            }else{
                $formation->setAttribute('fm_visio',0) ;
            }

            if($request->get('fm_presentielle') === true){
                $formation->setAttribute('fm_presentielle',1) ;
            }else{
                $formation->setAttribute('fm_presentielle',0) ;
            }
            if ($request->get('is_formation_dsc') === true) {
                # code...
                $formation->setAttribute('is_formation_dsc',1) ;
            }else{
                $formation->setAttribute('is_formation_dsc',0) ;
            }
            $formation->setAttribute('lieu',$request->get('lieu')) ;
            //$formation->setAttribute('is_formation',$request->is_formation) ;
            $formation->setAttribute('created_by', auth('api')->id());
            $formation->setAttribute('updated_by', auth('api')->id());
            $formation->setAttribute('typeformation_id',$request->get('typeformation_id')) ;
            //$formation->setAttribute('created_at', new \DateTime());
            $formation->setAttribute('is_event', $request->is_event);
            $formation->setAttribute('is_coorganisation', $request->is_coorganisation);
            if ($request->titre_evenement) {
                # code...
                $formation->setAttribute('titre_evenement', $request->titre_evenement);
            }
            $formation->save();
            // vérifier si la formation est gratuite pour insérer le montant attestation dynamiquement
            for($i = 0; $i<$nbr_cout;$i++){
                $get_cout = $cout_formation[$i];
                if (!empty($get_cout)) {
                    # code...
                    $id_grille = $get_cout ;

                    $grilleTarifaire = Grilletarifaires::find($id_grille) ;

                    // enregistrement dans la table tarification
                    // vérifier si la données existe dans le cas contraire enregistrer
                    $check_existe = Tarificationsformation::where('code_formation',$request->code_formation)
                        ->where('id_grilletarifaire',$id_grille)->get();

                    if (count($check_existe) == 0) { // je fais l'enregistrement
                        # code...
                        $tarification_formation = new Tarificationsformation();
                        $tarification_formation->setAttribute('code_formation',$request->get('code_formation')) ;
                        $tarification_formation->setAttribute('id_grilletarifaire',$id_grille) ;
                        $tarification_formation->setAttribute('created_by', auth('api')->id());
                        $tarification_formation->setAttribute('created_at', new \DateTime());
                        $tarification_formation->save();


                        if ($formation->is_formation_dsc) {

                            $type_tarif = Grilletarifaires::where('type_tarif', 'INSCRIPTION')
                                ->orWhere('type_tarif', 'ETUDIANTS')
                                ->orWhere('type_tarif', 'PROFESSIONNELS')
                                ->distinct()->get();

                            //dd( $type_tarif);

                            if ($type_tarif != "INSCRIPTION")  {

                                $formation_tranche = new FormationsTranches();
                                $formation_tranche->setAttribute('formation_id',$formation->id) ;
                                $formation_tranche->setAttribute('grilletarifaire_id', $id_grille) ;
                                $formation_tranche->setAttribute('tranche', 'tranche 1') ;
                                $formation_tranche->setAttribute('taux_1', 100) ;
                                $formation_tranche->setAttribute('taux_2', 0) ;
                                $formation_tranche->setAttribute('echeance_1', '2024-06-30') ;
                                $formation_tranche->setAttribute('echeance_2', null) ;
                                $formation_tranche->setAttribute('categorie_auditeur',$grilleTarifaire->libelle) ;
                                $formation_tranche->setAttribute('reduction',5);
                                $formation_tranche->setAttribute('created_at', new \DateTime());
                                $formation_tranche->save();


                                $formation_tranche = new FormationsTranches();
                                $formation_tranche->setAttribute('formation_id',$formation->id) ;
                                $formation_tranche->setAttribute('grilletarifaire_id', $id_grille) ;
                                $formation_tranche->setAttribute('tranche', 'tranche 2') ;
                                $formation_tranche->setAttribute('taux_1', 50) ;
                                $formation_tranche->setAttribute('taux_2', 50) ;
                                $formation_tranche->setAttribute('echeance_1', '2024-06-30') ;
                                $formation_tranche->setAttribute('echeance_2', '2024-10-30') ;
                                $formation_tranche->setAttribute('categorie_auditeur', $grilleTarifaire->libelle) ;
                                $formation_tranche->setAttribute('reduction',0) ;
                                $formation_tranche->setAttribute('created_at', new \DateTime());
                                $formation_tranche->save();

                                # code...

                            }

                        }

                        # code...
                    } else {
                        # code...
                    }
                }

            }

            // save logo partenaire si disponible
             if($request->logos){
                if(count($logo_partenaires) > 0){
                    $i=0;
                    foreach ($logo_partenaires as $key => $logo) {
                        $i= $i+1;
                        # code...
                        if ($logo) {
                            # code...
                            $extension = $logo->getClientOriginalExtension();
                            $name_logo = 'logo_'.$request->code_formation. '_' .date('YmdHis').$i. '.'.$logo->extension();
                            //$logo->move(public_path('img/logo/'), $name_logo);
                            $logo->storeAs('logo_partenaire', $name_logo, 'public');

                            // enregistrement dans la table logo
                            $logo_partenaire = new Logopartenaires();
                            $logo_partenaire->setAttribute('logo_path',$name_logo);
                            $logo_partenaire->setAttribute('code_formation',$formation->code_formation);
                            $logo_partenaire->setAttribute('created_by', auth('api')->id());
                            $logo_partenaire->setAttribute('updated_by', auth('api')->id());
                            $logo_partenaire->save();
                        }
                    }
                }
            }


        }
        // return
        return ResponseHandler::success("Enregistrement effectuée.");
    }

    public function getFormation(int $id): Formations
    {
        // TODO: Implement getFormation() method.
    }

    public function getFormations(Request $request): LengthAwarePaginator
    {
        // TODO: Implement getFormations() method.
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Filtres spécifiques
        $filterTypeFormation = $request->input('filter_type_formation');
        $filterIsEnable = $request->input('filter_enable');
        $filterUpdatedAt = $request->input('filter_updated_at');
        $filterAuthor = $request->input('filter_author');
        $filterDateDebut = $request->input('filter_date_debut');
        $filterDateFin = $request->input('filter_date_fin');

        $queryFormations = Formations::select('formations.*')
            ->with('getUserUpdated:id,name,email,code_user')
            ->with('getTypeformation:id,libelle')
            ->with('tarifications');

        // On fait la jointure dès qu'on a besoin de users.name (recherche globale OU filtre auteur OU tri par auteur)
        $needsUserJoin = $search || $filterAuthor || $sortField === 'get_user_updated.name';

        if ($needsUserJoin) {
            $queryFormations->leftJoin('users', 'formations.updated_by', '=', 'users.id');
        }

        // Recherche globale sur plusieurs champs
        if ($search) {
            $queryFormations->where(function ($q) use ($search) {
                $q->where('formations.code_formation', 'like', "%{$search}%")
                    ->orWhere('formations.theme', 'like', "%{$search}%")
                ->orWhere('formations.id_webinaire', 'like', "%{$search}%"); // Recherche sur auteur
            });
        }

        // Filtres par colonne
        if ($filterTypeFormation) {
            $queryFormations->leftJoin('typeformations', 'formations.typeformation_id', '=', 'typeformations.id');
            $queryFormations->where('typeformations.libelle', 'like', "%{$filterTypeFormation}%");
        }

        if ($filterIsEnable !== null && $filterIsEnable !== '') {
            $queryFormations->where('formations.enable', $filterIsEnable); // 0 ou 1
        }
        if ($filterDateDebut) {
            $queryFormations->whereDate('formations.date_debut', $filterDateDebut);
        }
        if ($filterDateFin) {
            $queryFormations->whereDate('formations.date_fin', $filterDateFin);
        }
        if ($filterUpdatedAt) {
            $queryFormations->whereDate('formations.updated_at', $filterUpdatedAt);
        }
        if ($filterAuthor) {
            $queryFormations->where('users.name', 'like', "%{$filterAuthor}%");
        }

        // Tri
        if ($sortField === 'get_user_updated.name') {
            $queryFormations->orderBy('users.name', $sortOrder);
        } else {
            $queryFormations->orderBy("formations.{$sortField}", $sortOrder);
        }

       // $queryFormations->orderBy($sortField, $sortOrder);

        return $queryFormations->paginate($perPage);
    }

    public function update(Request $data): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement update() method.
    }

    public function remove(int $idRole): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement remove() method.
    }

    public function getRecentFormations(Request $request)
    {
        // TODO: Implement getRecentFormations() method.
        $perPage = min($request->input('per_page', 6),6);

        $date_now = date('Y-m-d');
        // $date_now = date('Y-m-d',strtotime($date_now . "-45 days"));
        $date_actuelle = date('m/d/Y');
        $get_date = date('Y-m-d',strtotime($date_actuelle . "+60 days"));

        $queryFormations = Formations::select('formations.*')
            ->with('getTypeformation:id,libelle')
            ->with('tarifications')
            ->whereDate('formations.date_fin','>=',$date_now)
            ->where('formations.is_formation_dsc',false)
            ->where('formations.statut','ACTIF')
            ->where('formations.enable',true)
            ->orderBy("formations.date_debut","ASC")
            ->limit(6) ;
        return $queryFormations->get();
    }

    public function getDetailsFormation(Request $request): \Illuminate\Http\JsonResponse
    {
        $queryFormations = Formations::select('formations.*')
            ->with('getTypeformation:id,libelle')
            ->with('tarifications')
            ->where('formations.id', $request->id)
            ->get()->first();
        return ResponseHandler::success('ok', $queryFormations);
    }

    public function getFormationsAttestation(Request $request): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement getFormationsAttestation() method.
        $liste_formations = Formations::query()
            ->where('statut', 'INACTIF')
            ->where(function ($q) {
                $q->where('enable', true)
                    ->orWhere('presentiel', 1);
            })
            ->select([
                'code_formation as code',
                DB::raw("CONCAT(formations.code_formation, ' - ', formations.theme) AS name")
            ])
            ->orderBy('id', 'DESC')
            ->get();
        return ResponseHandler::success('ok', $liste_formations);
    }
}
