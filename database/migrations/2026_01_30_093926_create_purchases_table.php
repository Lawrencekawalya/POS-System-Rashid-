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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();

            // Who recorded the purchase
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Optional supplier / reference
            $table->string('supplier_name')->nullable();
            $table->string('reference_no')->nullable();

            // Cached total for reporting
            $table->decimal('total_cost', 12, 2);

            // Business date
            $table->timestamp('purchased_at')->useCurrent();

            // Immutable record
            $table->timestamp('created_at')->useCurrent();

            $table->index('purchased_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
