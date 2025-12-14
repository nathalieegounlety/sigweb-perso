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
        Schema::create('tmpmoodlecandidats', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('nom',100);
            $table->string('prenoms',200);
            $table->string('email',150)->index();
            $table->string('ddn',20)->nullable();
            $table->string('lieu_naissance',150)->nullable();
            $table->string('telephone',20)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
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
        Schema::dropIfExists('tmpmoodlecandidats');
    }
};
