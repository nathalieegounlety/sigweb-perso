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
        Schema::create('paiements_tranches', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->unsignedBigInteger('details_paiements_id'); // FK
            $table->integer('montant_paye')->default(0);
            $table->integer('montant_restant')->default(0);
            $table->string('reference',100);
            $table->unsignedBigInteger('modepaiement_id')->nullable();
            $table->unsignedBigInteger('tranche_id')->nullable();
            $table->string('telephone',100)->nullable();
            $table->string('num_transaction',100)->nullable();
            $table->string('statut_transaction',100)->nullable();
            $table->string('statut_traitement',100)->nullable();
            $table->boolean('is_inscription')->default(false);
            $table->string('preuve_path',100)->nullable();
            $table->unsignedBigInteger('traiter_par')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('details_paiements_id')->references('id')->on('detailspaiements')->onDelete(('cascade'));
            $table->foreign('modepaiement_id')->references('id')->on('modepaiements')->onDelete(('cascade'));
            $table->foreign('tranche_id')->references('id')->on('tranches')->onDelete(('cascade'));
            $table->foreign('created_by')->references('id')->on('users')->onDelete(('cascade'));
            $table->foreign('tranche_id')->references('id')->on('users')->onDelete(('cascade'));
            $table->foreign('updated_by')->references('id')->on('users')->onDelete(('cascade'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements_tranches');
    }
};
