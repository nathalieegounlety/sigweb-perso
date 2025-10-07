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
        Schema::create('rapports', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->string('email',200)->index();
            $table->integer('temps_presence')->default(0);
            $table->text('observation')->nullable();
            $table->string('code_formation',100)->index();
            $table->string('id_webinaire')->default(0);
            $table->boolean('attestation_send')->default(false);
            $table->foreign('code_formation')->references('code_formation')->on('formations');
            $table->foreign('email')->references('email')->on('inscriptions');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapports');
    }
};
