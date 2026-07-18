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
        Schema::create('modepays', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->unsignedBigInteger('pays_id');
            $table->unsignedBigInteger('modepaiement_id');
            $table->boolean('is_enable')->default(true);  
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('pays_id')->references('id')->on('pays')->onDelete(('cascade'));
            $table->foreign('modepaiement_id')->references('id')->on('modepaiements')->onDelete(('cascade'));
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
        Schema::dropIfExists('modepays');
    }
};
