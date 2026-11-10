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
        Schema::table('formations_dscs', function (Blueprint $table) {
            $table->string('num_decision',100)->nullable()->after('date_remise_diplomes');
            $table->date('date_deliberation')->nullable()->after('num_decision');
            $table->date('date_signature')->nullable()->after('date_deliberation');
            $table->string('titre_autorite',200)->nullable()->after('date_signature');
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
