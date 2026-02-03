<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Formations;
use App\Models\FormationsDsc;
use App\Services\contracts\FormationsdscInterface;
use App\Services\util\GenarateurCodeService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormationsdscService implements FormationsdscInterface
{
    private GenarateurCodeService $genarateurCodeService;

    public function __construct(GenarateurCodeService $genarateurCodeService)
    {
        $this->genarateurCodeService = $genarateurCodeService;
    }
    public function saveFormationdsc(Request $data): \Illuminate\Http\JsonResponse
    {
        $formation = Formations::whereCodeFormation($data->code_formation)->first();
        $checkData = FormationsDsc::whereFormationId($formation->id)
            ->exists();
        if ($checkData) {
            return ResponseHandler::error("La données existe déjà", 409);
        }

        $file = $data->file('visuel_modalite');
        $path = null;
        if (!empty($file)) {
            $allowedfileExtension = ['pdf','PDF'];
            $extension_document = $file->getClientOriginalExtension();
            $check = in_array($extension_document, $allowedfileExtension);
            if($check) {
                $path =  "modalite_document_".date('Ymd-His').'.'.$extension_document;
                $file->storeAs('modalites', $path,'public');
            }else {
                return ResponseHandler::error("Echec de l'enregistrement. Format du document interdit", 409);
            }
        }

        $user = auth('api')->user();
        $user_id = $user->id;
        $formationdsc = new FormationsDSC();
        $formationdsc->setAttribute('formation_id', $formation->id);
        $formationdsc->setAttribute('modalites_path', $path);
        $formationdsc->setAttribute('duree', $data->get('duree'));
        $formationdsc->setAttribute('date_limite_paiement_inscription', $data->get('date_paiement'));
        $formationdsc->setAttribute('date_limite_inscription', $data->get('date_inscription'));
        $formationdsc->setAttribute('date_remise_diplomes', $data->get('date_diplome'));
        $formationdsc->setAttribute('created_by', $user_id);
        $formationdsc->setAttribute('updated_by', $user_id);
        $formationdsc->save();
        return ResponseHandler::success("Création efféctuée");
    }

    public function getFormationDsc(int $id): \App\Models\FormationsDsc
    {
        $data = new FormationsDsc();
        return ResponseHandler::success("ok", $data);
    }

    public function getFormationsdsc(Request $request): LengthAwarePaginator
    {
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Filtres spécifiques
        $filterCodeFormation = $request->input('filter_code_formation');
        $filterDuree = $request->input('filter_duree');
        $filterDateLimitePaiement = $request->input('filter_date_limite_paiement_inscription');
        $filterDateLimiteInscription = $request->input('filter_date_limite_inscription');
        $filterDateRemiseDiplome = $request->input('filter_date_remise_diplomes');
        $filterUpdatedAt = $request->input('filter_updated_at');
        $filterAuthor = $request->input('filter_author');

        $query = FormationsDsc::with('getUserUpdated:id,name,email,code_user')
            ->join('formations','formations.id','=','formations_dscs.formation_id')
            ->select('formations_dscs.id', 'formations_dscs.duree','formations_dscs.date_limite_paiement_inscription','formations_dscs.date_limite_inscription',
                'formations_dscs.updated_at','formations_dscs.date_remise_diplomes','formations.code_formation','formations_dscs.formation_id',
                'formations_dscs.updated_by','formations_dscs.modalites_path','formations.theme');

        // On fait la jointure
        $needsUserJoin = $search || $filterAuthor || $sortField === 'get_user_updated.name';

        if ($needsUserJoin) {
            $query->leftJoin('users', 'formations_dscs.updated_by', '=', 'users.id');
        }

        // Recherche globale sur plusieurs champs
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->Where('formations.code_formation', 'like', "%{$search}%")
                    ->orWhere('formations_dscs.duree', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%"); // Recherche sur auteur
            });
        }

        // Filtres par colonne
        if ($filterDateLimiteInscription) {
            $query->where('formations_dscs.date_limite_inscription', 'like', "%{$filterDateLimiteInscription}%");
        }
        if ($filterDateLimitePaiement) {
            $query->where('formations_dscs.date_limite_paiement_inscription', 'like', "%{$filterDateLimitePaiement}%");
        }
        if ($filterDateRemiseDiplome) {
            $query->where('formations_dscs.date_remise_diplomes', 'like', "%{$filterDateRemiseDiplome}%");
        }
        if ($filterUpdatedAt) {
            $query->whereDate('formations_dscs.updated_at', $filterUpdatedAt);
        }
        if ($filterAuthor) {
            $query->where('users.name', 'like', "%{$filterAuthor}%");
        }

        // Tri
        if ($sortField === 'get_user_updated.name') {
            $query->orderBy('users.name', $sortOrder);
        } else {
            $query->orderBy("formations_dscs.{$sortField}", $sortOrder);
        }

        $query->orderBy($sortField, $sortOrder);

        return $query->paginate($perPage);
    }

    public function update(Request $data): \Illuminate\Http\JsonResponse
    {
        $user = auth('api')->user();
        $user_id = $user->id;
        $dataUpdate = FormationsDsc::find($data->id);

        if (!$dataUpdate) {
            return ResponseHandler::error("Données à modifier introuvable",404);
        }
        // check existing data
        $formation = Formations::whereCodeFormation($data->code_formation)->first();
        $checkData = FormationsDsc::whereFormationId($formation->id)
            ->where("id", "!=", $dataUpdate->id)->exists();

        if ($checkData) {
            return ResponseHandler::error("La données existe déjà");
        } else {
            // process update
            if ($data->hasFile('visuel_modalite')) {
                $file = $data->file('visuel_modalite');
                if (!empty($file)) {
                    $allowedfileExtension = ['pdf', 'PDF'];
                    $extension_document = $file->getClientOriginalExtension();
                    $check = in_array($extension_document, $allowedfileExtension);
                    if ($check) {
                        $path = "modalite_document_" . date('Ymd-His') . '.' . $extension_document;
                        $file->storeAs('modalites', $path, 'public');
                        $dataUpdate->setAttribute('modalites_path', $path);
                    } else {
                        return ResponseHandler::error("Echec de l'enregistrement. Format du document interdit", 409);
                    }
                }
            }

            $dataUpdate->setAttribute('duree', $data->get('duree'));
            $dataUpdate->setAttribute('date_limite_paiement_inscription', $data->get('date_paiement'));
            $dataUpdate->setAttribute('date_limite_inscription', $data->get('date_inscription'));
            $dataUpdate->setAttribute('date_remise_diplomes', $data->get('date_diplome'));
            $dataUpdate->setAttribute('updated_by', $user_id);
            $dataUpdate->update();
            return ResponseHandler::success("Modification effectuée avec succès");
        }

    }

    public function remove(int $id): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement remove() method.
        $dataDelete = FormationsDsc::find($id);
        if (!$dataDelete) {
            return ResponseHandler::error("Données à supprimer introuvable",404);
        }

        $delete = $dataDelete->delete();

        if ($delete) {
            return ResponseHandler::success("Suppression effectuée.");
        } else {
            return ResponseHandler::error("Echec lors de la suppression, vous ne pouvez pas surpprimer cette données : $dataDelete->duree.",401);
        }
    }

    public function objFormationDsc(): \Illuminate\Http\JsonResponse
    {
        // TODO: Implement objFormationDsc() method.
        $data = Formations::where('is_formation_dsc', true)
            ->select([
                'code_formation as code',
                DB::raw("CONCAT(formations.code_formation, ' - ', formations.theme) AS name")
            ])
            ->get();
        return ResponseHandler::success("ok",$data);
    }
}
