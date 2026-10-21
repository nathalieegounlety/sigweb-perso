<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Illuminate\Support\now;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('detailspaiements', function (Blueprint $table) {
            $table->date('date_comptable')->after('commentaire')->nullable();
        });
        Schema::table('paiements_tranches', function (Blueprint $table) {
            $table->date('date_comptable')->after('traiter_par')->nullable();
        });
        Schema::table('detailpaiementouvrages', function (Blueprint $table) {
            $table->date('date_comptable')->after('reference')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
