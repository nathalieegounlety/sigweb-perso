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
        Schema::create('autresparticipants', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('username',100);
            $table->string('heure_arrivee',100);
            $table->string('heure_depart',100);
            $table->string('temps_presence',10);
            $table->string('pays',255);
            $table->string('id_webinaire',50);
            $table->string('code_formation',100)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('autresparticipants');
    }
};
