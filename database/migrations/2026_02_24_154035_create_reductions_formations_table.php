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

            $table->foreignId('reduction_id')
                ->constrained('reductions')
                ->cascadeOnDelete();

            $table->foreignId('formation_id')
                ->constrained('formations')
                ->cascadeOnDelete();

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
