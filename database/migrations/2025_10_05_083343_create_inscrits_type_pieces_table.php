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
        Schema::create('inscrits_type_pieces', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->boolean("is_professionnel")->default(true);
            $table->boolean("is_etudiant")->default(true);
            $table->string('libelle',100)->unique();
            $table->boolean("enable")->default(true);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscrits_type_pieces');
    }
};
