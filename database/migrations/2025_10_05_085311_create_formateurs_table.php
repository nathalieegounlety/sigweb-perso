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
        Schema::create('formateurs', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('nom',100);
            $table->string('prenoms',200);
            $table->string('telephone',100);
            $table->string('email',100);
            $table->string('genre',20);
            $table->string('titre',100);
            $table->string('fonction',100);
            $table->string('specialite',100);
            $table->string('organisme',100);
            $table->unsignedBigInteger('nationalite'); // forenkey
            $table->boolean('enable')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('nationalite')->references('id')->on('pays')->onDelete(('cascade'));
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
        Schema::dropIfExists('formateurs');
    }
};
