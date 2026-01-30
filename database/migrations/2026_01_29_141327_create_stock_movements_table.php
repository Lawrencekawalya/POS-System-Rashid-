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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            // Which product this movement affects
            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            // Positive = stock in, Negative = stock out
            $table->integer('quantity');

            // Business meaning of the movement
            $table->string('type');
            // purchase, sale, adjustment, return

            // Link to the source record
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            // Optional explanation
            $table->string('remarks')->nullable();

            // Creation time only (immutable record)
            $table->timestamp('created_at')->useCurrent();

            // Helpful index for lookups
            $table->index(['product_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
