<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreignId('stock_product_id')->nullable()->after('category')->constrained('products')->nullOnDelete();
            $table->unsignedInteger('stock_quantity')->nullable()->after('stock_product_id');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreignId('stock_product_id')->nullable()->after('menu_item_id')->constrained('products')->nullOnDelete();
            $table->unsignedInteger('stock_quantity')->nullable()->after('stock_product_id');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['stock_product_id']);
            $table->dropColumn(['stock_product_id', 'stock_quantity']);
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropForeign(['stock_product_id']);
            $table->dropColumn(['stock_product_id', 'stock_quantity']);
        });
    }
};
