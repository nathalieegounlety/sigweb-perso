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
        Schema::create('moodlemoyennegenerales', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->integer('annee_academique')->default(0);
            $table->string('code_formation',100)->index();
            $table->double('moyenne')->default(0);
            $table->string('reference_candidat',100)->index();
            $table->string('observation')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
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
        Schema::dropIfExists('moodlemoyennegenerales');
    }
};
