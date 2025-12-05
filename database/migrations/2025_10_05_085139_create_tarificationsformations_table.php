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
        Schema::create('tarificationsformations', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('code_formation',100);
            $table->unsignedBigInteger('id_grilletarifaire');
            $table->boolean('enable')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('code_formation')->references('code_formation')->on('formations')->onDelete(('cascade'));
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
        Schema::dropIfExists('tarificationsformations');
    }
};
