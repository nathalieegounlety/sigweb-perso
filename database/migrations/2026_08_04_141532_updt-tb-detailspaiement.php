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
        
        Schema::table('detailspaiements', function (Blueprint $table) {
            $table->string('code_remise_dsc',30)->after('have_reduction_dsc')->nullable();
        });
        Schema::table('paiements', function (Blueprint $table) {  
            $table->string('code_remise_dsc',30)->after('montant_paye')->nullable();
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
