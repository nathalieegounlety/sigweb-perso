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
        Schema::create('formations', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->string('code_formation',100)->unique();
            $table->text('theme');
            $table->string('option')->nullable();
            $table->string('date_debut',20);
            $table->string('date_fin',20);
            $table->string('id_webinaire',20)->nullable();
            $table->dateTime('date_limite')->nullable();
            $table->string('cout_formation',20)->nullable();
            $table->boolean('is_formation')->default(true);
            $table->boolean('is_event')->default(true);
            $table->string('titre_evenement',255)->nullable();
            $table->text('description')->nullable();
            $table->string('visuel',100)->nullable();
            $table->string('statut',10)->default('ACTIF');
            $table->boolean('enable')->default(true);
            $table->integer('presentiel')->default(0);
            $table->string('lieu',100)->nullable();
            $table->integer('fm_presentielle')->nullable();
            $table->integer('fm_visio')->nullable();
            $table->unsignedBigInteger('typeformation_id')->nullable();
            $table->boolean('afficher')->default(true);
            $table->boolean('is_formation_dsc')->default(false);
            $table->boolean('is_coorganisation')->default(false);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('typeformation_id')->references('id')->on('typeformations')->onDelete(('cascade'));
            $table->foreign('created_by')->references('id')->on('users')->onDelete(('cascade'));
            $table->foreign('updated_by')->references('id')->on('users')->onDelete(('cascade'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formations');
    }
};
