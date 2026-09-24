<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('product_inventory_id')
                ->nullable()
                ->after('order_id')
                ->constrained('product_inventories')
                ->onDelete('set null');

            $table->decimal('unit_price', 15, 2)->default(0)->after('qty');

            $table->decimal('subtotal', 15, 2)->default(0)->after('unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_inventory_id']);
            $table->dropColumn(['product_inventory_id', 'unit_price', 'subtotal']);
        });
    }
};