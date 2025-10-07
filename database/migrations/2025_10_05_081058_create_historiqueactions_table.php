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
        Schema::create('historiqueactions', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->string('action',255);
            $table->text('description')->nullable();
            $table->string('ip_adresse',20)->nullable();
            $table->string('mac_adresse',20)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historiqueactions');
    }
};
