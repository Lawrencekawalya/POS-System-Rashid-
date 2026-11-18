<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fresh cuts stay products with unit_type "portion" so sales and refunds keep using stock movements.
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('portions_per_kg', 6, 2)->nullable()->after('unit_type');
        });

        Schema::create('fresh_cut_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('kg', 8, 2);
            $table->decimal('portions_per_kg', 6, 2);
            $table->unsignedInteger('portions');
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->string('supplier')->nullable();
            $table->timestamp('purchased_at');
            $table->timestamps();

            $table->index(['product_id', 'purchased_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fresh_cut_batches');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('portions_per_kg');
        });
    }
};
