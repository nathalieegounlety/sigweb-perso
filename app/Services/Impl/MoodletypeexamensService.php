<?php

namespace App\Services\Impl;

use App\Helpers\ResponseHandler;
use App\Models\Moodletypeexamens;
use App\Services\contracts\MoodletypeexamensInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MoodletypeexamensService implements MoodletypeexamensInterface
{
   // private MoodletypeexamensRepositoryInterface $moodletypeexamensRepository;

   // public function __construct(MoodletypeexamensRepositoryInterface $moodletypeexamensRepository)
  //  {
    //    $this->moodletypeexamensRepository = $moodletypeexamensRepository;
  //  }

    public function saveMoodletypeexamen(Request $data): \Illuminate\Http\JsonResponse
    {
        // Validation des données
        $validator = Validator::make($data->all(), [
            'code' => 'required|unique:moodletypeexamens,code',
            'libelle' => 'required',
        ]);

        if ($validator->fails()) {
            return ResponseHandler::error($validator->errors()->first(), 400);
        }

        // Vérification de l'existence du module
        $checkMoodletypeexamen = Moodletypeexamens::whereLibelle($data->libelle)->exists();
        if ($checkMoodletypeexamen) {
            return ResponseHandler::error("Le module existe déjà", 409);
        }

        // Utilisation de transaction pour l'enregistrement
        return DB::transaction(function () use ($data) {
            $moodletypeexamen = new Moodletypeexamens();
            $moodletypeexamen->setAttribute('libelle', $data->libelle);
            $moodletypeexamen->setAttribute('code', $data->code);

            if ($user = auth('api')->user()) {
                $moodletypeexamen->setAttribute('created_by', $user->id);
                $moodletypeexamen->setAttribute('updated_by', $user->id);
            }

            $moodletypeexamen->save();

            return ResponseHandler::success("Enregistrement effectué avec succès");
        });
    }

    public function getMoodletypeexamen(int $id): Moodletypeexamens
    {
        return Moodletypeexamens::select('id', 'libelle', 'code', 'updated_at')
            ->withOnly('moodletypeexamens:libelle as libelle_moodletypeexamen')
            ->findOrFail($id);
    }

    public function getMoodletypeexamens(Request $request): LengthAwarePaginator
    {
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        $filterLibelle = $request->input('filter_libelle');
        $filterIsEnable = $request->input('filter_enable');
        $filterUpdatedAt = $request->input('filter_updated_at');
        $filterAuthor = $request->input('filter_author');

       $query = Moodletypeexamens::select('moodletypeexamens.id', 'moodletypeexamens.libelle','moodletypeexamens.code', 'moodletypeexamens.updated_at', 'moodletypeexamens.enable', 'moodletypeexamens.updated_by')
            ->with(['getUserUpdated:id,name,email,code_user']);

        $needsUserJoin = $search || $filterAuthor || $sortField === 'get_user_updated.name';

        if ($needsUserJoin) {
            $query->leftJoin('users', 'moodletypeexamens.updated_by', '=', 'users.id');
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('moodletypeexamens.libelle', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%");
            });
        }

        if ($filterLibelle) {
            $query->where('moodletypeexamens.libelle', 'like', "%{$filterLibelle}%");
        }

        if ($filterIsEnable !== null && $filterIsEnable !== '') {
            $query->where('moodletypeexamens.enable', $filterIsEnable);
        }

        if ($filterUpdatedAt) {
            $query->whereDate('moodletypeexamens.updated_at', $filterUpdatedAt);
        }

        if ($filterAuthor) {
            $query->where('users.name', 'like', "%{$filterAuthor}%");
        }

        if ($sortField === 'get_user_updated.name') {
            $query->orderBy('users.name', $sortOrder);
        } else {
            $query->orderBy("moodletypeexamens.{$sortField}", $sortOrder);
        }

        return $query->paginate($perPage);
    }

    public function update(Request $data): \Illuminate\Http\JsonResponse
    {
        $moodletypeexamen = Moodletypeexamens::find($data['id']);

        if (!$moodletypeexamen) {
            return ResponseHandler::error("Module à modifier introuvable", 404);
        }

        // Validation des données
        $validator = Validator::make($data->all(), [
            'code' => [
                'required',
                \Illuminate\Validation\Rule::unique('moodletypeexamens')->where(function ($query) use ($data, $moodletypeexamen) {
                    return $query->whereCode($data->code)->whereNotIn('code', [$moodletypeexamen->code]);
                }),
            ],
            'libelle' => 'required',
        ]);

        if ($validator->fails()) {
            return ResponseHandler::error($validator->errors()->first(), 400);
        }

        // Vérification de l'existence du libellé
        $checkData = Moodletypeexamens::whereLibelle($data['libelle'])->where("id", "!=", $moodletypeexamen->id)->exists();

        if ($checkData) {
            return ResponseHandler::error("Le libellé existe déjà", 409);
        }

        // Mise à jour du module
        $moodletypeexamen->setAttribute('libelle', $data['libelle']);
        $moodletypeexamen->setAttribute('code', $data['code']);
        $moodletypeexamen->setAttribute('updated_by', auth('api')->id());
        $moodletypeexamen->update();

        return ResponseHandler::success("Modification effectuée avec succès");
    }

    public function remove(int $idMoodletypeexamen): \Illuminate\Http\JsonResponse
    {
        $moodletypeexamen = Moodletypeexamens::find($idMoodletypeexamen);

        if (!$moodletypeexamen) {
            return ResponseHandler::error("Module à supprimer introuvable", 404);
        }

        $deleteMoodletypeexamen = $moodletypeexamen->delete();

        if ($deleteMoodletypeexamen) {
            return ResponseHandler::success("Suppression effectuée.");
        } else {
            return ResponseHandler::error("Échec lors de la suppression, vous ne pouvez pas supprimer ce module.", 401);
        }
    }
}
