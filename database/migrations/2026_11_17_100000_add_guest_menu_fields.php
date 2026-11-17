<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->text('description')->nullable()->after('category');
            $table->boolean('show_on_menu')->default(false)->after('description');
            $table->unsignedInteger('sort_order')->default(0)->after('show_on_menu');
        });

        Schema::table('products', function (Blueprint $table) {
            // Bar section on the guest menu; null keeps the product off the menu.
            $table->string('menu_section')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn(['description', 'show_on_menu', 'sort_order']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('menu_section');
        });
    }
};
