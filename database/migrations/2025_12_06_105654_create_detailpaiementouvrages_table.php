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
        Schema::create('detailpaiementouvrages', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->unsignedBigInteger('paiementouvrage_id'); // FK
            $table->string('email',100)->index()->nullable();
            $table->string('prenoms',100)->nullable();
            $table->unsignedBigInteger('ouvrage_id'); // FK
            $table->integer('montant')->default(0);
            $table->string('reference',100)->nullable();
            $table->foreign('paiementouvrage_id')->references('id')->on('paiements')->onDelete(('cascade'));
            $table->foreign('ouvrage_id')->references('id')->on('ouvrages')->onDelete(('cascade'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detailpaiementouvrages');
    }
};
