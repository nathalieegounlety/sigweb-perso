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
        Schema::create('detailspaiements', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('reference_paiement',100); // FK
            $table->string('email',100)->index()->nullable();
            $table->string('code_formation',100)->index()->nullable();
            $table->string('code_inscription',100)->nullable();
            $table->integer('montant_paye')->default(0);
            $table->integer('montant_restant')->default(0);
            $table->unsignedBigInteger('id_tarification_formation'); // FK
            $table->unsignedBigInteger('id_reduction'); // FK
            $table->boolean('have_reduction_dsc')->default(false);
            $table->string('preuve_path',100)->nullable();
            $table->string('statut_paiement',100)->nullable();
            $table->string('statut_traitement',100)->default('ATTENTE');
            $table->boolean('is_paiement_partiel',100)->default(false);
            $table->unsignedBigInteger('modepaiement_id')->nullable();
            $table->string('reference_paiement_partiel',100)->nullable();
            $table->string('commentaire',200)->nullable();
            $table->foreign('reference_paiement')->references('reference')->on('paiements')->onDelete(('cascade'));
            $table->foreign('id_tarification_formation')->references('id')->on('tarificationsformations')->onDelete(('cascade'));
            $table->foreign('id_reduction')->references('id')->on('reductions')->onDelete(('cascade'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detailspaiements');
    }
};
