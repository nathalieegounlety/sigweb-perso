<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->string('etat_approbation',20)->nullable()->change();
            $table->string('heure_inscription',100)->nullable()->change();
            if (!Schema::hasColumn('inscriptions', 'statut_dossier')) {
                $table->string('statut_dossier', 20)->default('N/A');
            } else {
                $table->string('statut_dossier', 20)->default('N/A')->change();
            }        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
