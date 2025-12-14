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
        Schema::create('formations_tanches', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->unsignedBigInteger('formation_id');
            $table->unsignedBigInteger('grilletarifaire_id');
            $table->string('tranche',100);
            $table->integer('taux_1')->default(0);
            $table->integer('taux_2')->default(0);
            $table->date('echeance_1')->nullable();
            $table->date('echeance_2')->nullable();
            $table->string('categorie_auditeur',100);
            $table->integer('reduction')->default(0);
            $table->foreign('formation_id')->references('id')->on('formations')->onDelete(('cascade'));
            $table->foreign('grilletarifaire_id')->references('id')->on('grilletarifaires')->onDelete(('cascade'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formations_tanches');
    }
};
