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
        Schema::create('auditeurs_pieces', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->unsignedBigInteger('inscrits_typespieces_id');
            $table->unsignedBigInteger('auditeur_id');
            $table->string('piece_path');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('inscrits_typespieces_id')->references('id')->on('inscrits_type_pieces')->onDelete(('cascade'));
            $table->foreign('auditeur_id')->references('id')->on('auditeurs')->onDelete(('cascade'));
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
        Schema::dropIfExists('auditeurs_pieces');
    }
};
