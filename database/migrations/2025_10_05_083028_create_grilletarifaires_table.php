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
        Schema::create('grilletarifaires', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('libelle');
            $table->string('type_tarif',100);
            $table->integer('montant')->default(0);
            $table->boolean('is_enable')->default(true);
            $table->unsignedBigInteger('created_by');
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
        Schema::dropIfExists('grilletarifaires');
    }
};
