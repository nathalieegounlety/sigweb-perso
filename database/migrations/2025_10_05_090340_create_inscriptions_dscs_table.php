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
        Schema::create('inscriptions_dscs', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->unsignedBigInteger('inscrits_id');
            $table->string('code_inscription',100);
            $table->unsignedBigInteger('traiter_par');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('inscrits_id')->references('id')->on('inscriptions')->onDelete(('cascade'));
            $table->foreign('traiter_par')->references('id')->on('users')->onDelete(('cascade'));
            $table->foreign('updated_by')->references('id')->on('users')->onDelete(('cascade'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscriptions_dscs');
    }
};
