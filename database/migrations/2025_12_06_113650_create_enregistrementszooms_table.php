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
        Schema::create('enregistrementszooms', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->unsignedBigInteger('formation_id');
            $table->string('code_formation',100)->index()->nullable();
            $table->string('titre',150)->nullable();
            $table->unsignedBigInteger('sessionformation_id')->nullable();
            $table->string('url_youtube',150)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('formation_id')->references('id')->on('formations')->onDelete(('cascade'));
            $table->foreign('sessionformation_id')->references('id')->on('sessionformations')->onDelete(('cascade'));
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
        Schema::dropIfExists('enregistrementszooms');
    }
};
