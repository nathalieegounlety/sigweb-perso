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
        Schema::create('participants', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('a_participe',20)->nullable();
            $table->string('username',100)->nullable();
            $table->string('email',100)->index()->nullable();
            $table->string('heure_arrivee',100)->nullable();
            $table->string('heure_depart',100)->nullable();
            $table->string('temps_presence',100)->nullable();
            $table->string('id_webinaire',100)->nullable();
            $table->string('code_formation',100)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
