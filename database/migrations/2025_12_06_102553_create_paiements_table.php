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
        Schema::create('paiements', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('email',150)->index();
            $table->string('code_inscription',100)->nullable();
            $table->string('telephone',50)->nullable();
            $table->string('pays',50)->nullable();
            $table->string('reference',100)->index();
            $table->integer('montant_total')->default(0);
            $table->integer('montant_paye')->default(0);
            $table->integer('montant_restant')->default(0);
            $table->unsignedBigInteger('id_modepaiement')->nullable();
            $table->string('num_transaction',100)->nullable();
            $table->string('statut_transaction',100)->nullable();
            $table->string('statut_paiement',100)->nullable();
            $table->boolean('is_paiement_partiel')->default(false);
            $table->boolean('facnormalisee')->default(false);
            $table->string('codemcf',100)->nullable();
            $table->string('nim',100)->nullable();
            $table->string('compteur',100)->nullable();
            $table->string('qrcode',100)->nullable();
            $table->dateTime('heurenormalisation')->nullable();
            $table->integer('relance')->default(0);
            $table->string('statut_traitement',100)->default('ATTENTE');
            $table->string('commentaire')->nullable();
            $table->dateTime('date_traitement')->nullable();
            $table->string('type',100)->default('FORMATION');
            $table->string('mode_transaction',100)->default('momo');
            $table->boolean('declasse')->default(false);
            $table->unsignedBigInteger('agent_traiteur')->nullable();
            $table->foreign('id_modepaiement')->references('id')->on('modepaiements')->onDelete(('cascade'));
            $table->foreign('agent_traiteur')->references('id')->on('users')->onDelete(('cascade'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
