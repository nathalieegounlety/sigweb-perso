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
        Schema::create('auditeurs', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('civilite',20)->nullable();
            $table->string('nom',100)->nullable();
            $table->string('prenoms',100)->nullable();
            $table->string('email',150)->index();
            $table->string('nationalite',100)->nullable();
            $table->string('pays',255)->nullable();
            $table->string('ville',150)->nullable();
            $table->string('code_pays',5)->nullable();
            $table->string('telephone',20)->nullable();
            $table->string('genre',5)->nullable();
            $table->string('secteur_activite',100)->nullable();
            $table->string('organisme',100)->nullable();
            $table->string('intitule_poste',100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditeurs');
    }
};
