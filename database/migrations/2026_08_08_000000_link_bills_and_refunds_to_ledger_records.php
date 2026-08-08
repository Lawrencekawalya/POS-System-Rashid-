<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->foreignId('sale_id')->nullable()->unique()->after('room_id')->constrained()->nullOnDelete();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('refunded_amount', 12, 2)->default(0)->after('paid_amount');
        });

        Schema::table('sale_refunds', function (Blueprint $table) {
            $table->foreignId('sale_item_id')->nullable()->after('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sale_refunds', function (Blueprint $table) {
            $table->dropForeign(['sale_item_id']);
            $table->dropColumn('sale_item_id');
            $table->foreignId('product_id')->nullable(false)->change();
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
            $table->dropUnique(['sale_id']);
            $table->dropColumn('sale_id');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('refunded_amount');
        });
    }
};
