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
        Schema::create('pays', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('nom_pays',100);
            $table->string('code_iso',5)->index();
            $table->string('nationalite',200)->nullable();
            $table->string('flag',200);
            $table->string('coordonnee_maps',100);
            $table->boolean('enable')->default(true);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->onDelete(('cascade'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pays');
    }
};
