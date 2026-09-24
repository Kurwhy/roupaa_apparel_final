<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_type', ['dp', 'lunas'])->nullable()->after('status');
            $table->decimal('dp_amount', 15, 2)->default(0)->after('payment_type');
            $table->string('midtrans_snap_token')->nullable()->after('dp_amount');
            $table->string('midtrans_order_id')->nullable()->after('midtrans_snap_token');
            $table->enum('payment_status', ['unpaid', 'dp_paid', 'paid'])->default('unpaid')->after('midtrans_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'dp_amount', 'midtrans_snap_token', 'midtrans_order_id', 'payment_status']);
        });
    }
};
