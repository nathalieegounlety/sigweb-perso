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
         Schema::create('reductions_formations', function (Blueprint $table) {
            $table->id()->autoIncrement();

           $table->unsignedBigInteger('reduction_id');
            $table->unsignedBigInteger('formation_id');

            $table->index('reduction_id');
            $table->index('formation_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reductions_formations');
    }
};
