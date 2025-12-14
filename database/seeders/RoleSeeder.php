<?php

namespace Database\Seeders;

use App\Models\Roles;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->info('Seeding interdit en production !');
            return;
        }

        // Ou demander confirmation en CLI
        if (!$this->command->confirm('Voulez-vous vraiment insérer 1000 rôles ?')) {
            return;
        }

        // Vide la table avant de commencer
        Roles::truncate();

        // Création de 10 000 rôles
        // On utilise sequence pour garantir un libelle unique sans utiliser unique()
        // Et create() pour que Laravel formate correctement les dates
        Roles::factory()
            ->count(20000)
            ->sequence(fn ($sequence) => [
                'libelle' => 'Role ' . ($sequence->index + 1),
            ])
            ->create([
                'created_by' => 1,
                'updated_by' => 1,
            ]);

        $this->command->info('20 000 rôles créés avec succès !');
    }
}
