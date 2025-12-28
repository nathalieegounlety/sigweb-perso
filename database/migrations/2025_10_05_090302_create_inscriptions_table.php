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
        Schema::create('inscriptions', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('email',100)->index();
            $table->string('heure_inscription',100);
            $table->string('etat_approbation',20);
            $table->string('id_webinaire',20);
            $table->string('code_formation',100);// FK
            $table->boolean('traiter')->default(false);
            $table->string('statut_dossier',20);
            $table->integer('nbre_relance')->default(0);
            $table->boolean('valider')->default(false);
            $table->string('observations',255)->nullable();
            $table->unsignedBigInteger('traiter_par')->nullable(); // FK
            $table->unsignedBigInteger('tarif_formation_id')->nullable(); // FK
            $table->integer('presentiel')->default(0);
            $table->string('type_formation',20)->nullable();
            $table->string('code_inscription',100)->nullable();
            $table->boolean('is_formation_dsc')->default(false);
            $table->boolean('is_delete')->default(false);
            $table->boolean('statut_traitement')->default(false);
            $table->boolean('categorie_auditeur')->default(false);
            $table->foreign('code_formation')->references('code_formation')->on('formations')->onDelete(('cascade'));
            $table->foreign('traiter_par')->references('id')->on('users')->onDelete(('cascade'));
            $table->foreign('tarif_formation_id')->references('id')->on('tarificationsformations')->onDelete(('cascade'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscriptions');
    }
};
