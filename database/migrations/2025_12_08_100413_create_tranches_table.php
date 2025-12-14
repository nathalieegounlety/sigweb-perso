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
        Schema::create('tranches', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('code_inscription',100); // FK
            $table->string('email',100);
            $table->integer('tranche_1')->default(0);
            $table->integer('tranche_2')->default(0);
            $table->date('echeance_1');
            $table->date('echeance_2');
            $table->string('statut',20);
            $table->integer('nbre_relance')->default(0);
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
        Schema::dropIfExists('tranches');
    }
};
