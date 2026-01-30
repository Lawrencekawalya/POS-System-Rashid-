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
        Schema::create('cash_reconciliations', function (Blueprint $table) {
            $table->id();

            $table->date('business_date');

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->decimal('cash_expected', 12, 2);
            $table->decimal('cash_counted', 12, 2);
            $table->decimal('difference', 12, 2); // counted − expected

            $table->string('status'); // balanced | over | short
            $table->text('notes')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->unique(['business_date', 'user_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_reconciliations');
    }
};
