<?php

namespace App\Http\Controllers;

use App\Models\Auditeurs;
use App\Models\cr;
use Illuminate\Http\Request;

class AuditeursContoller extends Controller
{
     /**
     * Génère un mot de passe aléatoire.
     */
    private function genererPassword($length = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        return substr(str_shuffle($chars), 0, $length);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation des données
        $validatedData = $request->validate([
            'email' => 'required|email|unique:auditeurs,email|unique:users,email',
            'nom' => 'required|string|max:255',
            'prenoms' => 'required|string|max:255',
            'pays' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'ville' => 'nullable|string|max:255',
            'genre' => 'nullable|string|max:1',
            'secteur_activite' => 'nullable|string|max:255',
            'organisation' => 'nullable|string|max:255',
            'intitule_poste' => 'required|string|max:255',
        ]);

        // Génération d'un mot de passe aléatoire
        $generate_password = $this->genererPassword(8);
        $hash_generate_password = Hash::make($generate_password);

        // Vérification si l'utilisateur ou l'auditeur existe déjà
        $check_user = User::where("email", $validatedData['email'])->exists();
        $check_auditeur = Auditeurs::where("email", $validatedData['email'])->exists();

        if ($check_user || $check_auditeur) {
            return redirect()->back()->with("error", "Cet auditeur existe déjà.");
        }

        // Création de l'utilisateur
        $user = new User();
        $user->name = trim($validatedData['prenoms']) . " " . trim($validatedData['nom']);
        $user->email = $validatedData['email'];
        $user->password = $hash_generate_password;
        $user->email_verified_at = now();
        $user->save();

        // Attribution du rôle "auditeur" (ID 6)
        $role_user = new ModelsRolesusers();
        $role_user->roles_id = 6; // ID du rôle "auditeur"
        $role_user->users_id = $user->id;
        $role_user->save();

        // Récupération du pays
        $pays = Pays::where("nom_pays", $validatedData['pays'])->first();

        // Création de l'auditeur
        $auditeur = new Auditeurs();
        $auditeur->nom = trim($validatedData['nom']);
        $auditeur->prenoms = trim($validatedData['prenoms']);
        $auditeur->email = trim($validatedData['email']);
        $auditeur->telephone = trim($validatedData['telephone']);
        $auditeur->ville = trim($validatedData['ville'] ?? '');
        $auditeur->genre = trim($validatedData['genre'] ?? '');
        $auditeur->secteur_activite = trim($validatedData['secteur_activite'] ?? '');
        $auditeur->organisme = trim($validatedData['organisation'] ?? '');
        $auditeur->intitule_poste = trim($validatedData['intitule_poste']);

        if ($pays) {
            $auditeur->pays = $pays->nom_pays;
            $auditeur->code_pays = $pays->code_iso;
            $auditeur->nationalite = $pays->nationalite;
        } else {
            $auditeur->pays = $validatedData['pays'];
        }

        $auditeur->save();

        // Retour avec un message de succès
        return redirect()->route('auditeurs.index')->with('success', 'Enregistrement effectué avec succès.');
    }
}

    /**
     * Display the specified resource.
     */
    public function show(cr $cr)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(cr $cr)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, cr $cr)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(cr $cr)
    {
        //
    }
}
