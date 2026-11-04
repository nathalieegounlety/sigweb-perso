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
        Schema::table('moodlemodules', function (Blueprint $table) {
            $table->string('annee_academique',100)->nullable()->after('id');
            $table->string('code_formation',100)->nullable()->after('code');
            $table->string('enseignant')->nullable()->after('code_formation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
