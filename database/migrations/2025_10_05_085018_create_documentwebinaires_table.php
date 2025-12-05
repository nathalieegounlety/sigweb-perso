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
        Schema::create('documentwebinaires', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('id_webinaire',100);
            $table->string('code_formation',100);
            $table->foreign('code_formation')->references('code_formation')->on('formations');
            $table->string('document_name',255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentwebinaires');
    }
};
