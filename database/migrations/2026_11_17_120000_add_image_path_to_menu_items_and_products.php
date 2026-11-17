<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('description');
            $this->stockPhotoColumns($table);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('menu_section');
            $this->stockPhotoColumns($table);
        });
    }

    /**
     * Stand-in (stock) photos are openly licensed and must credit their author.
     * A staff upload replaces them and clears these columns.
     */
    private function stockPhotoColumns(Blueprint $table): void
    {
        $table->string('image_credit', 500)->nullable()->after('image_path');
        $table->string('image_credit_url', 1000)->nullable()->after('image_credit');
        $table->boolean('image_is_stock')->default(false)->after('image_credit_url');
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'image_credit', 'image_credit_url', 'image_is_stock']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'image_credit', 'image_credit_url', 'image_is_stock']);
        });
    }
};
