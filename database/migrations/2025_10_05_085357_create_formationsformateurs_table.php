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
        Schema::create('formationsformateurs', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->unsignedBigInteger('formation_id');
            $table->unsignedBigInteger('formateur_id');
            $table->string('role',100)->nullable();
            $table->unsignedBigInteger('roleformateur_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('formation_id')->references('id')->on('formations')->onDelete(('cascade'));
            $table->foreign('formateur_id')->references('id')->on('formateurs')->onDelete(('cascade'));
            $table->foreign('roleformateur_id')->references('id')->on('roleformateurs')->onDelete(('cascade'));
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
        Schema::dropIfExists('formationsformateurs');
    }
};
