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
        Schema::create('point_de_paiements', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->string('nom_organisme', 100)->unique();
            $table->string('description')->nullable();
            $table->string('adresse');
            $table->string('ville', 200);
            $table->string('pays')->nullable();
            $table->string('telephone',100)->nullable();
            $table->string('courriel',100)->unique();
            $table->string('contact1');
            $table->string('telephone1',100)->nullable();
            $table->string('courriel1');
            $table->string('contact2');
            $table->string('telephone2',100)->nullable();
            $table->string('courriel2');
            $table->boolean('enable')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_de_paiements');
    }
};
