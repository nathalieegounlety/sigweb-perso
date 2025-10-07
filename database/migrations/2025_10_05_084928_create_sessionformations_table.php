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
        Schema::create('sessionformations', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->string('id_webinaire');
            $table->string('code_formation',100);
            $table->foreign('code_formation')->references('code_formation')->on('formations');
            $table->string('heure_debut');
            $table->integer('duree_reel');
            $table->integer('nbr_inscrit');
            $table->integer('nbr_annule');
            $table->integer('spectateurs_uniques')->default(0);
            $table->integer('nbr_total_utilisateur')->default(0);
            $table->integer('vues_simultanees_maxi')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessionformations');
    }
};
