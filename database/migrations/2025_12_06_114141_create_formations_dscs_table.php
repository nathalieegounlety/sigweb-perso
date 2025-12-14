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
        Schema::create('formations_dscs', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->unsignedBigInteger('formation_id');
            $table->string('modalites_path',200)->nullable();
            $table->string('duree',100)->nullable();
            $table->date('date_limite_paiement_inscription')->nullable();
            $table->string('date_limite_inscription',100)->nullable();
            $table->date('date_remise_diplomes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('formation_id')->references('id')->on('formations')->onDelete(('cascade'));
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
        Schema::dropIfExists('formations_dscs');
    }
};
