<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Moodlemodules;
use App\Services\contracts\MoodlesmodulesInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MoodlesmodulesService implements MoodlesmodulesInterface
{
   // private MoodlesmodulesRepositoryInterface $moodlesmodulesRepository;

   // public function __construct(MoodlesmodulesRepositoryInterface $moodlesmodulesRepository)
  //  {
    //    $this->moodlesmodulesRepository = $moodlesmodulesRepository;
  //  }

    public function saveMoodlesmodule(Request $data): \Illuminate\Http\JsonResponse
    {
        // Validation des données
        $validator = Validator::make($data->all(), [
            'code' => 'required|unique:moodlemodules,code',
            'libelle' => 'required',
            'annee_academique' => 'required',
            'code_formation' => 'required',
            'credit' => 'required',
        ]);

        if ($validator->fails()) {
            return ResponseHandler::error($validator->errors()->first(), 400);
        }

        // Vérification de l'existence du module
        $checkMoodlesmodule = Moodlemodules::whereLibelle($data->libelle)->exists();
        if ($checkMoodlesmodule) {
            return ResponseHandler::error("Le module existe déjà", 409);
        }

        // Utilisation de transaction pour l'enregistrement
        return DB::transaction(function () use ($data) {
            $moodlesmodule = new Moodlemodules();
            $moodlesmodule->setAttribute('libelle', $data->libelle);
            $moodlesmodule->setAttribute('code', $data->code);
            $moodlesmodule->setAttribute('credit', $data->credit);
            $moodlesmodule->setAttribute('code_formation', $data->code_formation);
            $moodlesmodule->setAttribute('annee_academique', $data->annee_academique);
            $moodlesmodule->setAttribute('enseignant', $data->enseignant); 

            if ($user = auth('api')->user()) {
                $moodlesmodule->setAttribute('created_by', $user->id);
                $moodlesmodule->setAttribute('updated_by', $user->id);
            }

            $moodlesmodule->save();

            return ResponseHandler::success("Enregistrement effectué avec succès");
        });
    }

    public function getMoodlesmodule(int $id): Moodlemodules
    {
        return Moodlemodules::select('id', 'libelle', 'code', 'credit', 'updated_at')
            ->withOnly('moodlemodules:libelle as libelle_moodlemodule')
            ->findOrFail($id);
    }

    public function getMoodlesmodules(Request $request): LengthAwarePaginator
    {
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        $filterLibelle = $request->input('filter_libelle');
        $filterCode = $request->input('filter_code');
        $filterCodeFormation = $request->input('filter_code_formation');
        $filterIsEnable = $request->input('filter_enable');
        $filterUpdatedAt = $request->input('filter_updated_at');
        $filterAuthor = $request->input('filter_author');

       $query = Moodlemodules::select('moodlemodules.id', 'moodlemodules.libelle','moodlemodules.code','moodlemodules.credit', 
       'moodlemodules.updated_at', 'moodlemodules.enable', 'moodlemodules.updated_by','formations.theme as libelle_formation',
       'moodlemodules.code_formation','moodlemodules.annee_academique','moodlemodules.enseignant')
       ->leftJoin('formations','formations.code_formation','=','moodlemodules.code_formation')
            ->with(['getUserUpdated:id,name,email,code_user']);

        $needsUserJoin = $search || $filterAuthor || $sortField === 'get_user_updated.name';

        if ($needsUserJoin) {
            $query->leftJoin('users', 'moodlemodules.updated_by', '=', 'users.id');
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('moodlemodules.libelle', 'like', "%{$search}%")
                ->orWhere('moodlemodules.credit', 'like', "%{$search}%")
                ->orWhere('moodlemodules.code', 'like', "%{$search}%")
                ->orWhere('moodlemodules.enseignant', 'like', "%{$search}%")
                ->orWhere('moodlemodules.code_formation', 'like', "%{$search}%")
                ->orWhere('moodlemodules.annee_academique', 'like', "%{$search}%") 
                    ->orWhere('users.name', 'like', "%{$search}%");
            });
        }

        if ($filterLibelle) {
            $query->where('moodlemodules.libelle', 'like', "%{$filterLibelle}%");
        }

        if ($filterCode) {
            $query->where('moodlemodules.code', 'like', "%{$filterCode}%");
        }

        if ($filterCodeFormation) {
            $query->where('moodlemodules.libelle', 'like', "%{$filterCodeFormation}%");
        }

        if ($filterIsEnable !== null && $filterIsEnable !== '') {
            $query->where('moodlemodules.enable', $filterIsEnable);
        }

        if ($filterUpdatedAt) {
            $query->whereDate('moodlemodules.updated_at', $filterUpdatedAt);
        }

        if ($filterAuthor) {
            $query->where('users.name', 'like', "%{$filterAuthor}%");
        }

        if ($sortField === 'get_user_updated.name') {
            $query->orderBy('users.name', $sortOrder);
        } else {
            $query->orderBy("moodlemodules.{$sortField}", $sortOrder);
        }

        return $query->paginate($perPage);
    }

    public function update(Request $data): \Illuminate\Http\JsonResponse
    {
        $moodlesmodule = Moodlemodules::find($data['id']);

        if (!$moodlesmodule) {
            return ResponseHandler::error("Module à modifier introuvable", 404);
        }

        // Validation des données
        $validator = Validator::make($data->all(), [
            'code' => 'required',
            'code_formation' => 'required',
            'credit' => 'required',
            'libelle' => 'required',
            'annee_academique' => 'required',
        ]);

        if ($validator->fails()) {
            return ResponseHandler::error($validator->errors()->first(), 400);
        }

        // Vérification de l'existence du libellé
        $checkData = Moodlemodules::whereLibelle($data['libelle'])
        ->whereCodeFormation($data['code_formation'])
        ->whereAnneeAcademique($data['annee_academique'])
        ->whereCode($data['code'])
        ->where("id", "!=", $moodlesmodule->id)->exists();

        if ($checkData) {
            return ResponseHandler::error("Le module existe déjà", 409);
        }

        // Mise à jour du module
        $moodlesmodule->setAttribute('libelle', $data['libelle']);
        $moodlesmodule->setAttribute('code', $data['code']);
        $moodlesmodule->setAttribute('credit', $data['credit']);
        $moodlesmodule->setAttribute('code_formation', $data['code_formation']);
        $moodlesmodule->setAttribute('annee_academique', $data['annee_academique']);
        $moodlesmodule->setAttribute('enseignant', $data['enseignant']);
        $moodlesmodule->setAttribute('updated_by', auth('api')->id());
        $moodlesmodule->update();

        return ResponseHandler::success("Modification effectuée avec succès");
    }

    public function remove(int $idMoodlesmodule): \Illuminate\Http\JsonResponse
    {
        $moodlesmodule = Moodlemodules::find($idMoodlesmodule);

        if (!$moodlesmodule) {
            return ResponseHandler::error("Module à supprimer introuvable", 404);
        }

        $deleteMoodlesmodule = $moodlesmodule->delete();

        if ($deleteMoodlesmodule) {
            return ResponseHandler::success("Suppression effectuée.");
        } else {
            return ResponseHandler::error("Échec lors de la suppression, vous ne pouvez pas supprimer ce module.", 401);
        }
    }
}
