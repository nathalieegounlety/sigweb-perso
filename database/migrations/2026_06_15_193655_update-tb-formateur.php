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
        Schema::table('formateurs', function (Blueprint $table) {
            $table->text('fonction')->default(null)->nullable()->change();
            $table->text('organisme')->default(null)->nullable()->change();
            $table->text('specialite')->default(null)->nullable()->change()->nullable();
            $table->unsignedBigInteger('nationalite')->default(null)->nullable()->change();
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
