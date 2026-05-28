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
        Schema::table('sale_items', function (Blueprint $table) {
            $table->string('item_type')->default('product')->after('sale_id');
            // We already have product_id. We'll rename it or add item_id.
            // Let's just add menu_item_id for now as it's cleaner for current logic.
            $table->foreignId('menu_item_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('item_type');
            $table->dropForeign(['menu_item_id']);
            $table->dropColumn('menu_item_id');
            $table->foreignId('product_id')->nullable(false)->change();
        });
    }
};
