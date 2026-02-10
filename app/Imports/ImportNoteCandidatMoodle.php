<?php

namespace App\Imports;

use App\Models\Moodlemodules;
use App\Models\Moodlemoyennes;
use App\Models\Moodlenotes;
use App\Models\Moodletypeexamens;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportNoteCandidatMoodle implements ToCollection
{
    private $code_formation;

    private $annee_academique;

    public function __construct($code_formation, $annee_academique)
    {
        $this->code_formation = $code_formation;
        $this->annee_academique = $annee_academique;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function collection(Collection $rows)
    {
        $nbr_ligne = count($rows);
        $nbr_colonne = $rows->first()->count();
        Log::info('Import notes - Début', ['lignes' => $nbr_ligne, 'colonnes' => $nbr_colonne]);

        $user = auth('api')->user();
        if (!$user) {
            Log::error("Aucun utilisateur connecté");
            return false;
        }

        for ($col = 3; $col < $nbr_colonne; $col++) {

            $tab_colonne = explode("-", $rows[0][$col]);
            Log::info("Traitement colonne", ['colonne' => $rows[0][$col], 'tab' => $tab_colonne]);

            if (!isset($tab_colonne[1]) || empty($tab_colonne[1])) {
                Log::error("Format de colonne invalide", ['colonne' => $rows[0][$col]]);
                continue;
            }

            $code_type_examen = trim($tab_colonne[0]);
            $code_module = trim($tab_colonne[1]);

            $module = Moodlemodules::whereCode($code_module)->first();
            $typeexamen = Moodletypeexamens::whereCode($code_type_examen)->first();

            if (!$module) {
                Log::error("Module introuvable", ['code_module' => $code_module]);
                continue;
            }
            if (!$typeexamen && !in_array($code_type_examen, ["NOTE FINALE", "NOTE SOUTENANCE"])) {
                Log::error("Type d'examen introuvable", ['code_type_examen' => $code_type_examen]);
                continue;
            }

            // Traitement des notes normales (CC, EXAM, RATP)
            if (!in_array($code_type_examen, ["NOTE FINALE", "NOTE SOUTENANCE"])) {
                for ($line = 1; $line < $nbr_ligne; $line++) {
                    $get_note = $rows[$line][$col];
                    if (empty($get_note) || $get_note == "-") {
                        Log::info("Note ignorée (vide ou '-')", [
                            'ligne' => $line,
                            'colonne' => $col,
                            'note' => $get_note,
                            'candidat' => trim($rows[$line][2])
                        ]);
                        continue;
                    }

                    try {
                        // vérifier que la note existe déjà pour mettre à jour sion enregistré
                        $checkNote = Moodlenotes::where("annee_academique", $this->annee_academique)
                            ->where("code_formation", $this->code_formation)
                            ->where("moodlemodule_id", $module->id)
                            ->where("moodletypeexamen_id", $typeexamen->id)
                            ->where("reference_candidat", trim($rows[$line][2]))
                            ->where("note", trim($get_note))
                            ->exists();

                        if ( $checkNote) {
                            # code...
                            $getNote = Moodlenotes::where("annee_academique", $this->annee_academique)
                                ->where("code_formation", $this->code_formation)
                                ->where("moodlemodule_id", $module->id)
                                ->where("moodletypeexamen_id", $typeexamen->id)
                                ->where("reference_candidat", trim($rows[$line][2]))
                                ->where("note", trim($get_note))
                                ->first();

                            $note = Moodlenotes::find($getNote->id);

                            $note->setAttribute("annee_academique", $this->annee_academique);
                            $note->setAttribute("code_formation", $this->code_formation);
                            $note->setAttribute("moodlemodule_id", $module->id);
                            $note->setAttribute("moodletypeexamen_id", $typeexamen->id);
                            $note->setAttribute("reference_candidat", trim($rows[$line][2]));
                            $note->setAttribute("note", trim($get_note));
                            $note->update();

                        } else {
                            # code...
                            $note = new Moodlenotes();
                            $note->setAttribute("annee_academique", $this->annee_academique);
                            $note->setAttribute("code_formation", $this->code_formation);
                            $note->setAttribute("moodlemodule_id", $module->id);
                            $note->setAttribute("moodletypeexamen_id", $typeexamen->id);
                            $note->setAttribute("reference_candidat", trim($rows[$line][2]));
                            $note->setAttribute("note", trim($get_note));
                            $note->save();

                        }


                        Log::info("Note enregistrée avec succès", [
                            'note_id' => $note->id,
                            'ligne' => $line,
                            'colonne' => $col,
                            'note' => $get_note,
                            'candidat' => trim($rows[$line][2])
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Échec de l'enregistrement de la note", [
                            'erreur' => $e->getMessage(),
                            'ligne' => $line,
                            'colonne' => $col,
                            'note' => $get_note,
                            'candidat' => trim($rows[$line][2])
                        ]);
                    }
                }
            }

            // Traitement des notes finales et soutenances
            if (in_array($code_type_examen, ["NOTE FINALE", "NOTE SOUTENANCE"])) {
                for ($line = 1; $line < $nbr_ligne; $line++) {
                    $get_note = $rows[$line][$col];
                    if (empty($get_note) || $get_note == "-") {

                        // save as 0 value

                        try {
                            // vérifier si la données existe déjà
                            $checkData = Moodlemoyennes::where("annee_academique", $this->annee_academique)
                                ->where("code_formation", $this->code_formation)
                                ->where("moodlemodule_id", $module->id)
                                ->where("note_totale", 0)
                                // ->where("note_coef", floatval(trim($get_note)) * $module->credit)
                                ->where("note_coef", 0)
                                ->where("reference_candidat", trim($rows[$line][2]))
                                ->exists();

                            if ($checkData) {
                                # code...
                                $getMoyenne = Moodlemoyennes::where("annee_academique", $this->annee_academique)
                                    ->where("code_formation", $this->code_formation)
                                    ->where("moodlemodule_id", $module->id)
                                    ->where("note_totale", 0)
                                    //  ->where("note_coef", floatval(trim($get_note)) * $module->credit)
                                    ->where("note_coef", 0)
                                    ->where("reference_candidat", trim($rows[$line][2]))
                                    ->exists();

                                $moyenne = Moodlemoyennes::find($getMoyenne->id);

                                $moyenne->setAttribute("annee_academique", $this->annee_academique);
                                $moyenne->setAttribute("code_formation", $this->code_formation);
                                $moyenne->setAttribute("moodlemodule_id", $module->id);
                                $moyenne->setAttribute("note_totale", 0);
                                //  $moyenne->setAttribute("note_coef", floatval(trim($get_note)) * $module->credit);
                                $moyenne->setAttribute("note_coef", 0);
                                $moyenne->setAttribute("reference_candidat", trim($rows[$line][2]));
                                $moyenne->update();
                            } else {
                                # code...
                                $moyenne = new Moodlemoyennes();
                                $moyenne->setAttribute("annee_academique", $this->annee_academique);
                                $moyenne->setAttribute("code_formation", $this->code_formation);
                                $moyenne->setAttribute("moodlemodule_id", $module->id);
                                $moyenne->setAttribute("note_totale", 0);
                                $moyenne->setAttribute("note_coef", 0);
                                //  $moyenne->setAttribute("note_coef", floatval(trim($get_note)) * $module->credit);
                                $moyenne->setAttribute("reference_candidat", trim($rows[$line][2]));
                                $moyenne->save();

                            }


                            Log::info("Moyenne enregistrée avec succès", [
                                'moyenne_id' => $moyenne->id,
                                'ligne' => $line,
                                'colonne' => $col,
                                'note' => $get_note,
                                'candidat' => trim($rows[$line][2])
                            ]);
                        } catch (\Exception $e) {
                            Log::error("Échec de l'enregistrement de la moyenne", [
                                'erreur' => $e->getMessage(),
                                'ligne' => $line,
                                'colonne' => $col,
                                'note' => $get_note,
                                'candidat' => trim($rows[$line][2])
                            ]);
                        }


                        // end save as







                        Log::info("Note finale ignorée (vide ou '-')", [
                            'ligne' => $line,
                            'colonne' => $col,
                            'note' => $get_note,
                            'candidat' => trim($rows[$line][2])
                        ]);
                        continue;
                    }else{

                        try {
                            // vérifier si la données existe déjà
                            $checkData = Moodlemoyennes::where("annee_academique", $this->annee_academique)
                                ->where("code_formation", $this->code_formation)
                                ->where("moodlemodule_id", $module->id)
                                ->where("note_totale", trim($get_note))
                                // ->where("note_coef", floatval(trim($get_note)) * $module->credit)
                                ->where("note_coef", floatval(trim($get_note)))
                                ->where("reference_candidat", trim($rows[$line][2]))
                                ->exists();

                            if ($checkData) {
                                # code...
                                $getMoyenne = Moodlemoyennes::where("annee_academique", $this->annee_academique)
                                    ->where("code_formation", $this->code_formation)
                                    ->where("moodlemodule_id", $module->id)
                                    ->where("note_totale", trim($get_note))
                                    //  ->where("note_coef", floatval(trim($get_note)) * $module->credit)
                                    ->where("note_coef", floatval(trim($get_note)))
                                    ->where("reference_candidat", trim($rows[$line][2]))
                                    ->exists();

                                $moyenne = Moodlemoyennes::find($getMoyenne->id);

                                $moyenne->setAttribute("annee_academique", $this->annee_academique);
                                $moyenne->setAttribute("code_formation", $this->code_formation);
                                $moyenne->setAttribute("moodlemodule_id", $module->id);
                                $moyenne->setAttribute("note_totale", trim($get_note));
                                //  $moyenne->setAttribute("note_coef", floatval(trim($get_note)) * $module->credit);
                                $moyenne->setAttribute("note_coef", floatval(trim($get_note)));
                                $moyenne->setAttribute("reference_candidat", trim($rows[$line][2]));
                                $moyenne->update();
                            } else {
                                # code...
                                $moyenne = new Moodlemoyennes();
                                $moyenne->setAttribute("annee_academique", $this->annee_academique);
                                $moyenne->setAttribute("code_formation", $this->code_formation);
                                $moyenne->setAttribute("moodlemodule_id", $module->id);
                                $moyenne->setAttribute("note_totale", trim($get_note));
                                $moyenne->setAttribute("note_coef", floatval(trim($get_note)));
                                //  $moyenne->setAttribute("note_coef", floatval(trim($get_note)) * $module->credit);
                                $moyenne->setAttribute("reference_candidat", trim($rows[$line][2]));
                                $moyenne->save();

                            }


                            Log::info("Moyenne enregistrée avec succès", [
                                'moyenne_id' => $moyenne->id,
                                'ligne' => $line,
                                'colonne' => $col,
                                'note' => $get_note,
                                'candidat' => trim($rows[$line][2])
                            ]);
                        } catch (\Exception $e) {
                            Log::error("Échec de l'enregistrement de la moyenne", [
                                'erreur' => $e->getMessage(),
                                'ligne' => $line,
                                'colonne' => $col,
                                'note' => $get_note,
                                'candidat' => trim($rows[$line][2])
                            ]);
                        }

                    }


                }
            }
        }

        return true;
    }


    public function chunkSize(): int
    {
        return 100;
    }
}
