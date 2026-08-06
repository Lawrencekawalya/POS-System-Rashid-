<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cash_reconciliations', function (Blueprint $table) {
            // This drops the specific index causing the 1062 error
            $table->dropUnique(['business_date', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_reconciliations', function (Blueprint $table) {
            $table->unique(['business_date', 'user_id']);
        });
    }
};
